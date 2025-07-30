<?php
require_once '../config/config.php';
require_once '../classes/AccountPayable.php';

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

$paymentId = (int)$_POST['payment_id'] ?? 0;

if (!$paymentId) {
    echo json_encode(['success' => false, 'message' => 'ID de pago inválido']);
    exit;
}

$accountPayable = new AccountPayable();

try {
    // Verificar que el pago pertenece al usuario actual
    $payment = $accountPayable->getPaymentById($paymentId);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
        exit;
    }
    
    // Verificar que la cuenta pertenece al usuario actual
    $account = $accountPayable->getById($payment['account_payable_id']);
    
    if (!$account || $account['user_id'] != getCurrentUserId()) {
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar este pago']);
        exit;
    }
    
    // Eliminar el pago
    $result = $accountPayable->deletePayment($paymentId);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Pago eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el pago']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
