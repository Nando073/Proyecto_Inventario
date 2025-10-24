<?php
session_start();
require_once 'NEGOCIO/N_Usuario.php';
$usuarioService = new N_Usuario();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
    $clave   = trim(filter_input(INPUT_POST, 'clave', FILTER_SANITIZE_STRING));

    $usuarioValido = $usuarioService->loguear($usuario, $clave);

    if ($usuarioValido && is_array($usuarioValido)) {
        $_SESSION['id_usuario'] = $usuarioValido['id_usuario'];
        $_SESSION['id_funcionario'] = $usuarioValido['id_funcionario'];
        $_SESSION['nombre_usuario'] = $usuarioValido['usuario'];

        // Traer nombre completo del funcionario
        require_once 'NEGOCIO/N_Funcionario.php';
        $funcionarioService = new N_Funcionario();
        $funcionario = $funcionarioService->buscarPorId($usuarioValido['id_funcionario']);
        $_SESSION['nombre_completo'] = $funcionario['f_nombre'] . ' ' . $funcionario['f_apellido'];

        // Traer rol_asignado
        require_once 'NEGOCIO/N_RolUsuario.php';
        $rolUsuarioService = new N_RolUsuario();
        $rolesUsuario = $rolUsuarioService->obtenerRolUsuarioAsignado($usuarioValido['id_usuario']);
        $rolesUsuario = array_column($rolesUsuario, 'r_nombre');
        //echo '<pre>'; print_r($rolesUsuario); echo '</pre>'; exit;
        $_SESSION['rol_asignado'] = $rolesUsuario;

        //  REDIRECCIÓN MEJORADA - Prioridad de rol_asignado (4 ROLES)
        $paginaInicio = 'PRESENTACION/Inicio.php'; // Página por defecto
        
        // Definir prioridad de redirección (de mayor a menor privilegio)
        if (in_array('Administrador', $rolesUsuario)) {
            $paginaInicio = 'PRESENTACION/ADM_Usuario.php';
        } 
        elseif (in_array('Operador', $rolesUsuario)) {
            $paginaInicio = 'PRESENTACION/ADM_Material.php';
        } 
        elseif (in_array('Supervisor', $rolesUsuario)) {
            $paginaInicio = 'TRANSACCIONAL/CATALOGO/Generar_Solicitud.php';
        }
        elseif (in_array('Funcionario', $rolesUsuario)) {
            $paginaInicio = 'TRANSACCIONAL/CATALOGO/Generar_Solicitud.php';
        }

        header("Location: $paginaInicio");
        exit();

    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="style.css?v=<?php echo(rand()); ?>">
  <!-- libreria de google para validar cuentas -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>

</head>
<body>
  <div class="container">
    <div class="login-box">
      <div class="image-section">
        <img src="IMG/log.webp" alt="Imagen login">
      </div>
      <div class="form-section">
        <h2>Iniciar Sesión</h2>
        <form method="post" autocomplete="off">
            <div class="input-group">
                <input type="text" id="usuario" name="usuario" placeholder="Usuario" autocomplete="off" required>
                <span class="icon">👤</span>
            </div>
            <div class="input-group">
                <input type="password" id="clave" name="clave" placeholder="Contraseña" autocomplete="off" required>
                <button type="button" id="togglePassword" class="eye-btn" title="Mostrar contraseña">
                    👁
                </button>
            </div>
            <button type="submit" name="login" class="login-btn">INICIAR SESIÓN</button>
            
           <!-- boton pa inicir con correro -->
                 <div id="g_id_onload"
                    data-client_id="490875069077-r6fbg30cr232d2b3j9undq3evoq5cq25.apps.googleusercontent.com"
                    data-login_uri="http://localhost/DDE_INVENTARIO/VALIDAR_GOOGLE.php"
                    data-auto_prompt="false">
                </div>

                <div class="g_id_signin"
                    data-type="standard"
                    data-size="large"
                    data-theme="outline"
                    data-text="signin_with"
                    data-shape="rectangular"
                    data-logo_alignment="left">
                </div>
            
        </form>
        <p class="terms">Está de acuerdo con los términos y condiciones</p>
      </div>
    </div>
  </div>
<script>
    document.getElementById('usuario').value = '';
    document.getElementById('clave').value = '';
    const passwordInput = document.getElementById("clave");
    const togglePassword = document.getElementById("togglePassword");

    function toggleMenu() {
        const menu = document.getElementById('menuUsuario');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
    window.addEventListener("pageshow", function(event) {
        if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
            window.location.reload(true); // recarga completa desde el servidor
        }

        // Flag para saber si la contraseña está visible en móvil
        let passwordVisibleMobile = false;
        
    // Mostrar la contraseña solo mientras se mantiene presionado el clic
    togglePassword.addEventListener("mousedown", () => {
        passwordInput.type = "text";
    });

    togglePassword.addEventListener("mouseup", () => {
        passwordInput.type = "password";
    });

    togglePassword.addEventListener("mouseleave", () => {
        passwordInput.type = "password";
    });
    // --- Móvil: hacer click para alternar ---
    togglePassword.addEventListener("click", () => {
        if (isTouchDevice()) {
            passwordVisibleMobile = !passwordVisibleMobile;
            passwordInput.type = passwordVisibleMobile ? "text" : "password";
        }
    });

    // --- Función para detectar touch devices ---
    function isTouchDevice() {
        return ('ontouchstart' in window) || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
    }
});
</script>

</body>
</html>