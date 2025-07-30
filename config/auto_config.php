<?php
// Configuración automática que detecta el entorno (local vs servidor)

// Configuración de sesión segura (ANTES de cualquier salida)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Detectar si estamos en local o en servidor
function isLocalEnvironment() {
    $localHosts = ['localhost', '127.0.0.1', '::1'];
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    
    // Verificar si es un dominio de producción conocido
    $productionDomains = ['panel.drsnet.ovh', 'drsnet.ovh'];
    foreach ($productionDomains as $domain) {
        if (strpos($httpHost, $domain) !== false || strpos($serverName, $domain) !== false) {
            return false;
        }
    }
    
    return in_array($serverName, $localHosts) || 
           in_array($httpHost, $localHosts) || 
           strpos($httpHost, 'localhost') !== false ||
           strpos($httpHost, '127.0.0.1') !== false;
}

// Detectar la ruta base automáticamente
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = dirname($scriptName);
    
    // Si estamos en el directorio raíz, devolver /
    if ($scriptDir === '/' || $scriptDir === '\\') {
        return '/';
    }
    
    // Si estamos en un subdirectorio como admin, ajax, etc., subir un nivel
    $pathParts = explode('/', trim($scriptDir, '/'));
    $projectSubdirs = ['admin', 'ajax', 'includes', 'config', 'classes'];
    
    // Si el último directorio es uno de los subdirectorios del proyecto, removerlo
    if (!empty($pathParts) && in_array(end($pathParts), $projectSubdirs)) {
        array_pop($pathParts);
    }
    
    // Reconstruir la ruta
    $basePath = '/' . implode('/', $pathParts);
    
    // Asegurar que termine con /
    return rtrim($basePath, '/') . '/';
}

// Configuración unificada
$basePath = getBasePath();
define('BASE_URL', $basePath);
define('ENVIRONMENT', 'production');

// Modo de producción basado en la constante ENVIRONMENT
if (!defined('PRODUCTION_MODE')) {
    define('PRODUCTION_MODE', ENVIRONMENT === 'production');
}

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', PRODUCTION_MODE ? '0' : '1');
ini_set('log_errors', 1);

// Crear directorio de logs si no existe
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

ini_set('error_log', $logDir . '/error.log');

// Configuración de seguridad adicional
ini_set('expose_php', 0);

// Headers de seguridad (solo si no se han enviado headers)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Solo agregar HSTS si estamos en HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Función requireLogin para autenticación
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php'));
        exit();
    }
}

// Función para generar URLs absolutas
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

// Función para generar URLs de assets
function asset($path) {
    return BASE_URL . ltrim($path, '/');
}

// Función para redireccionar
function redirect($path) {
    if (strpos($path, 'http') === 0) {
        header("Location: $path");
    } else {
        header('Location: ' . url($path));
    }
    exit();
}

?>