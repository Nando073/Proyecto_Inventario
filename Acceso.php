<?php
session_start(); // <-- ¡IMPORTANTE!
//require_once 'DATOS/D_Usuario.php';
require_once 'NEGOCIO/N_Usuario.php';
$usuarioService = new N_Usuario();

//$error = ' ';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
    $clave = trim(filter_input(INPUT_POST, 'clave', FILTER_SANITIZE_STRING));

    // Suponiendo que loguear devuelve un array con los datos del usuario si es válido, o false/null si no lo es
    $usuarioValido = $usuarioService->loguear($usuario, $clave);

   if ($usuarioValido && is_array($usuarioValido)) {
    $_SESSION['nombre_usuario'] = $usuarioValido['usuario'];
    $_SESSION['id_funcionario'] = $usuarioValido['id_funcionario'];

    require_once 'NEGOCIO/N_Funcionario.php';
    $funcionarioService = new N_Funcionario();
    $funcionario = $funcionarioService->buscarPorId($usuarioValido['id_funcionario']);
    $_SESSION['nombre_completo'] = $funcionario['f_nombre'] . ' ' . $funcionario['f_apellido'];

    require_once 'NEGOCIO/N_RolUsuario.php';
    $rolUsuarioService = new N_RolUsuario();
    $rolesUsuario = $rolUsuarioService->obtenerRolesPorUsuario($usuarioValido['id_usuario']);
    // Si retorna array asociativo:
    $rolesUsuario = array_column($rolesUsuario, 'r_nombre');
    $_SESSION['roles'] = $rolesUsuario;

    if (in_array('Administrador', $rolesUsuario)) {
        header('Location: PRESENTACION/ADM_Usuario.php');
        exit();
    } elseif (in_array('Operador', $rolesUsuario)) {
        header('Location: PRESENTACION/ADM_Material.php');
        exit();
    } elseif (in_array('Consulta', $rolesUsuario)) {
        header('Location: TRANSACCIONAL/Stock.php');
        exit();
    } else {
        header('Location: acceso_denegado.php');
        exit();
    }
   
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
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario"  required>
                    <span class="icon">📧</span>
                </div>
                <div class="input-group">
                    <input type="password" class="form-control" id="clave" name="clave" placeholder="Contraseña"  required>
                    <span class="icon">🔒</span>
                </div>
                <button type="submit"  name="login" class="login-btn">INICIAR SESION</button>
                <a href=""><img class="btnGoogle" src="IMG/google.png"></a>
                </form>
                <p class="terms">Está de acuerdo con los érminos y condiciones</p>
            </div>
        </div>
    </div>
</body>
</html>