<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rolesPermitidos = ['Administrador','Consulta','Supervisor','Operador'];
if (!isset($_SESSION['roles']) || count(array_intersect($rolesPermitidos, $_SESSION['roles'])) === 0) {
    header('Location: ../Acceso.php');
    exit();
}
if (!isset($_SESSION['nombre_usuario'])) {
    // Si no hay sesión activa, redirige al login
    header("Location: ../Acceso.php");
    exit();
}
$nombreUsuario = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'PERFIL';
?>