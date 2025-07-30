<?php
require_once '../config/config.php';
require_once '../classes/Debt.php';

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

if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de pago inválido']);
    exit;
}

$paymentId = intval($_POST['payment_id']);
$userId = getCurrentUserId();
$debt = new Debt();

try {
    // Obtener conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar que el pago pertenece al usuario actual
    $stmt = $db->prepare("
        SELECT dp.*, d.user_id 
        FROM debt_payments dp 
        JOIN debts d ON dp.debt_id = d.id 
        WHERE dp.id = ?
    ");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
        exit;
    }
    
    if ($payment['user_id'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar este pago']);
        exit;
    }
    
    // Eliminar el pago
    $result = $debt->deletePayment($paymentId);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Pago eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el pago']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>