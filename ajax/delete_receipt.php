<?php
require_once '../config/config.php';
require_once '../classes/AccountReceivable.php';

requireLogin();

$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$receiptId = (int)$_POST['receipt_id'] ?? 0;

if (!$receiptId) {
    echo json_encode(['success' => false, 'message' => 'ID de cobro inválido']);
    exit;
}

$accountReceivable = new AccountReceivable();

try {
    // Verificar que el cobro pertenece al usuario actual
    $receipt = $accountReceivable->getReceiptById($receiptId);
    
    if (!$receipt) {
        echo json_encode(['success' => false, 'message' => 'Cobro no encontrado']);
        exit;
    }
    
    // Verificar que la cuenta pertenece al usuario actual
    $account = $accountReceivable->getById($receipt['account_receivable_id']);
    
    if (!$account || $account['user_id'] != getCurrentUserId()) {
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar este cobro']);
        exit;
    }
    
    // Eliminar el cobro
    $result = $accountReceivable->deleteReceipt($receiptId);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cobro eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el cobro']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
