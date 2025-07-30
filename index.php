<?php
require_once 'config/config.php';

// Verificar si existe la base de datos
$dbPath = __DIR__ . '/data/money_manager.db';
if (!file_exists($dbPath)) {
    redirect('install.php');
}

// Verificar si la base de datos tiene usuarios (solo si el archivo existe)
try {
    // Verificar que el archivo existe y tiene contenido antes de conectar
    if (filesize($dbPath) > 0) {
        $pdo = new PDO('sqlite:' . $dbPath);
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        
        if ($userCount == 0) {
            redirect('install.php');
        }
    } else {
        // Si el archivo está vacío, redireccionar a instalación
        redirect('install.php');
    }
} catch (Exception $e) {
    // Si hay error al acceder a la base de datos, redireccionar a instalación
    redirect('install.php');
}

// Verificar si el usuario está autenticado
if (!isLoggedIn()) {
    redirect('login.php');
}

// Redireccionar al dashboard
redirect('dashboard.php');
?>
