<?php
/**
 * Archivo de configuración central del proyecto
 * Define rutas absolutas para evitar problemas de navegación entre navegadores
 */

// Definir la ruta base del proyecto (ajusta según tu servidor)
// Para XAMPP en Windows, la ruta base es /DDE_INVENTARIO/
define('BASE_PATH', '/DDE_INVENTARIO/');

// Ruta absoluta del servidor (filesystem)
define('ROOT_PATH', __DIR__ . '/');

// URLs absolutas para recursos comunes
define('URL_BASE', BASE_PATH);
define('URL_CSS', BASE_PATH . 'CSS/');
define('URL_JS', BASE_PATH . 'JS/');
define('URL_IMG', BASE_PATH . 'IMG/');

// URLs para módulos
define('URL_DATOS', BASE_PATH . 'DATOS/');
define('URL_NEGOCIO', BASE_PATH . 'NEGOCIO/');
define('URL_PRESENTACION', BASE_PATH . 'PRESENTACION/');
define('URL_TRANSACCIONAL', BASE_PATH . 'TRANSACCIONAL/');
define('URL_REPORTES', BASE_PATH . 'REPORTES/');

// Función helper para generar URLs absolutas
function url($path = '') {
    return BASE_PATH . ltrim($path, '/');
}

// Función helper para generar rutas de archivos absolutas
function path($path = '') {
    return ROOT_PATH . ltrim($path, '/');
}
?>
