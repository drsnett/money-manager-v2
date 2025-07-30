<?php
require_once '../config/config.php';
require_once '../classes/CreditCard.php';

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

$transactionId = (int)$_POST['transaction_id'] ?? 0;

if (!$transactionId) {
    echo json_encode(['success' => false, 'message' => 'ID de transacción inválido']);
    exit;
}

$creditCard = new CreditCard();

try {
    // Verificar que la transacción pertenece al usuario actual
    $transaction = $creditCard->getTransactionById($transactionId);
    
    if (!$transaction) {
        echo json_encode(['success' => false, 'message' => 'Transacción no encontrada']);
        exit;
    }
    
    // Verificar que la tarjeta pertenece al usuario actual
    $card = $creditCard->getById($transaction['credit_card_id']);
    
    if (!$card || $card['user_id'] != getCurrentUserId()) {
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar esta transacción']);
        exit;
    }
    
    // Eliminar la transacción
    $result = $creditCard->deleteTransaction($transactionId);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Transacción eliminada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la transacción']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
