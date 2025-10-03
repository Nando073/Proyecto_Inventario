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
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_completo'])) {
    header("Location: ../Acceso.php");
    exit();
}

// Roles permitidos en el sistema
$rolesPermitidos = ['Administrador','Funcionario','Supervisor','Operador'];

// Verificar que tenga al menos un rol válido
if (!isset($_SESSION['rol_asignado']) || count(array_intersect($rolesPermitidos, $_SESSION['rol_asignado'])) === 0) {
    header("Location: ../acceso_denegado.php");
    exit();
}


// ✅ FUNCIONES DE VERIFICACIÓN DE ROLES
if (!function_exists('tieneRol')) {
    function tieneRol($rolesRequeridos) {
        if (!isset($_SESSION['rol_asignado'])) return false;
        
        if (is_string($rolesRequeridos)) {
            $rolesRequeridos = [$rolesRequeridos];
        }
        
        return count(array_intersect($rolesRequeridos, $_SESSION['rol_asignado'])) > 0;
    }
}

if (!function_exists('verificarAcceso')) {
    function verificarAcceso($rolesPermitidos) {
        if (!tieneRol($rolesPermitidos)) {
            header("Location: ../acceso_denegado.php");
            exit();
        }
    }
}

// Para mostrar el nombre del usuario
$nombreUsuario = $_SESSION['nombre_completo'] ?? 'PERFIL';
?>