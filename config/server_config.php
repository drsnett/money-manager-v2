<?php
// Configuración específica para el servidor de producción
// Este archivo debe ser incluido en lugar de config.php en el servidor

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funciones de utilidad
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
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

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function isAdmin() {
    return $_SESSION['is_admin'] ?? false;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

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

// Configuración de errores para producción
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../data/error.log');

// Zona horaria
date_default_timezone_set('America/Santo_Domingo');

// Constantes para el servidor - se define automáticamente en auto_config.php
// define('BASE_URL', '/desarrollo/'); // Ya no es necesario hardcodear
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_DECIMALS', 2);

// Crear directorio de datos si no existe
if (!file_exists(__DIR__ . '/../data')) {
    mkdir(__DIR__ . '/../data', 0755, true);
}

// Crear directorio de uploads si no existe
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>