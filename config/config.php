<?php
// Incluir configuración automática
require_once __DIR__ . '/auto_config.php';

// Funciones de utilidad
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function formatCurrency($amount) {
    // Manejar valores nulos o no numéricos
    if ($amount === null || $amount === '' || !is_numeric($amount)) {
        $amount = 0;
    }
    return '$' . number_format((float)$amount, 2);
}

function formatCurrencyWithColor($amount) {
    // Manejar valores nulos o no numéricos
    if ($amount === null || $amount === '' || !is_numeric($amount)) {
        $amount = 0;
    }
    
    $formatted = '$' . number_format((float)$amount, 2);
    $class = (float)$amount < 0 ? 'text-danger' : 'text-success';
    
    return "<span class='$class'>$formatted</span>";
}

function formatDate($date) {
    if (empty($date) || $date === null) {
        return 'No disponible';
    }
    return date('d/m/Y', strtotime($date));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// La función requireLogin ahora está en auto_config.php

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function isAdmin() {
    return $_SESSION['is_admin'] ?? false;
}

// La función redirect ahora está en auto_config.php

function showAlert($message, $type = 'info') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'paid' => 'success',
        'overdue' => 'danger',
        'partial' => 'info'
    ];
    
    $class = $badges[$status] ?? 'secondary';
    $text = ucfirst($status);
    
    return "<span class='badge bg-$class'>$text</span>";
}

function calculateDaysDifference($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->days;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Cargar configuración de entorno
require_once __DIR__ . '/env_config.php';

// Configuración de errores según el entorno
if (env('APP_ENV', 'production') === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Configuración de zona horaria
date_default_timezone_set(env('APP_TIMEZONE', 'America/Mexico_City'));

// Constantes de la aplicación
define('APP_NAME', env('APP_NAME', 'Money Manager'));
define('APP_VERSION', '2.0.0');
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', false));
define('APP_URL', env('APP_URL', 'http://localhost'));

// Constantes de archivos
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', env_int('MAX_FILE_SIZE', 5 * 1024 * 1024)); // 5MB por defecto
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Constantes de moneda
define('CURRENCY_SYMBOL', env('CURRENCY_SYMBOL', '$'));
define('CURRENCY_DECIMALS', env_int('CURRENCY_DECIMALS', 2));
define('CURRENCY_CODE', env('CURRENCY_CODE', 'USD'));

// Constantes de caché
define('CACHE_ENABLED', env_bool('CACHE_ENABLED', true));
define('CACHE_DEFAULT_TTL', env_int('CACHE_DEFAULT_TTL', 3600));

// Constantes de notificaciones
define('NOTIFICATIONS_ENABLED', env_bool('NOTIFICATIONS_ENABLED', true));
define('NOTIFICATIONS_EMAIL_ENABLED', env_bool('NOTIFICATIONS_EMAIL_ENABLED', false));
define('NOTIFICATIONS_AUTO_CLEANUP', env_bool('NOTIFICATIONS_AUTO_CLEANUP', true));

// Configuración de WhatsApp (desde .env)
$whatsappConfig = EnvConfig::getWhatsAppConfig();
define('WHATSAPP_ENABLED', $whatsappConfig['enabled']);
define('WHATSAPP_API_URL', $whatsappConfig['api_url']);
define('WHATSAPP_API_KEY', $whatsappConfig['api_key']);
define('WHATSAPP_PHONE', $whatsappConfig['phone']);

// Constantes de seguridad
define('SESSION_LIFETIME', env_int('SESSION_LIFETIME', 7200)); // 2 horas
define('MAX_LOGIN_ATTEMPTS', env_int('MAX_LOGIN_ATTEMPTS', 5));
define('LOCKOUT_DURATION', env_int('LOCKOUT_DURATION', 900)); // 15 minutos
define('PASSWORD_MIN_LENGTH', env_int('PASSWORD_MIN_LENGTH', 8));

// Constantes de base de datos
define('DB_BACKUP_ENABLED', env_bool('DB_BACKUP_ENABLED', true));
define('DB_BACKUP_RETENTION_DAYS', env_int('DB_BACKUP_RETENTION_DAYS', 30));

// Crear directorios necesarios
$directories = [
    __DIR__ . '/../data',
    __DIR__ . '/../uploads',
    __DIR__ . '/../logs',
    __DIR__ . '/../data/cache',
    __DIR__ . '/../backups'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configurar autoloader para nuevas clases
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Inicializar servicios si están habilitados
if (CACHE_ENABLED) {
    // El caché se inicializa automáticamente cuando se usa
}

// La limpieza automática de notificaciones se realizará mediante cron jobs
// para evitar la creación automática de la base de datos
?>
