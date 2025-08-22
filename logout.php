<?php
session_start();
session_unset();      // Elimina todas las variables de sesión
session_destroy();    // Destruye la sesión

// Evitar que el navegador guarde en caché la sesión anterior
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// Redirige al login
header("Location: Acceso.php");
exit;
