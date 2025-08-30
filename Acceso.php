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

        // Traer roles
        require_once 'NEGOCIO/N_RolUsuario.php';
        $rolUsuarioService = new N_RolUsuario();
        $rolesUsuario = $rolUsuarioService->obtenerRolesPorUsuario($usuarioValido['id_usuario']);
        $rolesUsuario = array_column($rolesUsuario, 'r_nombre');
        $_SESSION['roles'] = $rolesUsuario;

        // ✅ REDIRECCIÓN MEJORADA - Prioridad de roles (4 ROLES)
        $paginaInicio = 'PRESENTACION/Inicio.php'; // Página por defecto
        
        // Definir prioridad de redirección (de mayor a menor privilegio)
        if (in_array('Administrador', $rolesUsuario)) {
            $paginaInicio = 'PRESENTACION/ADM_Usuario.php';
        } 
        elseif (in_array('Operador', $rolesUsuario)) {
            $paginaInicio = 'PRESENTACION/ADM_Material.php';
        } 
        elseif (in_array('Supervisor', $rolesUsuario)) {
            $paginaInicio = 'TRANSACCIONAL/Stock.php';
        }
        elseif (in_array('Funcionario', $rolesUsuario)) {
            $paginaInicio = 'TRANSACCIONAL/Stock.php';
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
    <style>
        /* body {
            background-image: url('IMG/fondoWeb.jpg');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
        } */
    </style>
</head>
<body>

    <div class="container">
        <div class="login-box">
            <div class="image-section">
                <img src="IMG/log.webp">
            </div>
            <div class="form-section">
                <h2>Iniciar Sesión</h2>
                <form  method="post">
                <div class="input-group">
                    <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="off" placeholder="Usuario"  required>
                    <span class="icon">📧</span>
                </div>
                <div class="input-group">
                    <input type="password" class="form-control" id="clave" name="clave" autocomplete="new-password" placeholder="Contraseña"  required>
                    <span class="icon">🔒</span>
                </div>
                <button type="submit"  name="login" class="login-btn">INICIAR SESION</button>
                <a href=""><img class="btnGoogle" src="IMG/google.png"></a>
                </form>
                <p class="terms">Está de acuerdo con los érminos y condiciones</p>
            </div>
        </div>
    </div>
<script>
    document.getElementById('usuario').value = '';
    document.getElementById('clave').value = '';
    function toggleMenu() {
        const menu = document.getElementById('menuUsuario');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
    window.addEventListener("pageshow", function(event) {
        if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
            window.location.reload(true); // recarga completa desde el servidor
        }
});
</script>

</body>
</html>