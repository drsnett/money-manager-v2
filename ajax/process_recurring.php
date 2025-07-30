<?php
/**
 * Endpoint AJAX para procesar cuentas recurrentes manualmente
 */

require_once '../config/config.php';
require_once '../scripts/process_recurring_accounts.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado (para AJAX)
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF inválido']);
    exit;
}

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Crear instancia del procesador
    $processor = new RecurringProcessor();
    
    // Procesar todas las cuentas recurrentes
    $processed = $processor->processAll();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'message' => $processed > 0 
            ? "Se generaron {$processed} nuevas cuentas recurrentes." 
            : "No hay cuentas recurrentes pendientes de procesar."
    ]);
    
} catch (Exception $e) {
    // Error en el procesamiento
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar cuentas recurrentes: ' . $e->getMessage()
    ]);
}
?>