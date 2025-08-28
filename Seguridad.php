<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 Evitar cache del navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// 👤 Verificar que haya sesión iniciada
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Acceso.php"); // redirige al login
    exit();
}

// Roles permitidos en el sistema
$rolesPermitidos = ['Administrador','Consulta','Supervisor','Operador'];

// Verificar que tenga al menos un rol válido
if (!isset($_SESSION['roles']) || count(array_intersect($rolesPermitidos, $_SESSION['roles'])) === 0) {
    header("Location: ../acceso_denegado.php");
    exit();
}

// Para mostrar el nombre del usuario en la barra de navegación
$nombreUsuario = $_SESSION['nombre_usuario'] ?? 'PERFIL';
?>
