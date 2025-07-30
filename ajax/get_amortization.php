<?php
require_once '../config/config.php';
require_once '../classes/Debt.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación sin redireccionar
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida. Por favor, inicie sesión.']);
    exit;
}

$csrfToken = $_GET['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

if (!isset($_GET['debt_id']) || !is_numeric($_GET['debt_id']) || !isset($_GET['months']) || !is_numeric($_GET['months'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
    exit;
}

$debtId = intval($_GET['debt_id']);
$months = intval($_GET['months']);
$userId = getCurrentUserId();

if ($months < 1 || $months > 120) {
    echo json_encode(['success' => false, 'message' => 'El plazo debe estar entre 1 y 120 meses.']);
    exit;
}

$debt = new Debt();
$debtData = $debt->getById($debtId);

if (!$debtData || $debtData['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Deuda no encontrada.']);
    exit;
}

try {
    // Calcular el balance actual actualizado
    $currentBalance = $debt->calculateCurrentBalance($debtId);
    
    // Debug: verificar datos
    if ($currentBalance <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'La deuda está completamente pagada. Balance actual: ' . formatCurrency($currentBalance)
        ]);
        exit;
    }
    
    // Usar el método getAmortizationTable que ahora usa el balance actual
    $amortization = $debt->getAmortizationTable($debtId, $months);
    
    // Si no hay amortización (deuda pagada), retornar mensaje apropiado
    if (empty($amortization)) {
        echo json_encode([
            'success' => false, 
            'message' => 'No se puede calcular amortización. La deuda puede estar completamente pagada.'
        ]);
        exit;
    }
    
    // Calcular resumen
    $monthlyPayment = 0;
    $totalInterest = 0;
    $totalPayment = 0;
    
    foreach ($amortization as $payment) {
        $monthlyPayment = $payment['monthly_payment']; // Todas las cuotas son iguales
        $totalInterest += $payment['interest_payment'];
        $totalPayment += $payment['monthly_payment'];
    }
    
    // Formatear datos para la respuesta
    $formattedAmortization = [];
    foreach ($amortization as $payment) {
        $formattedAmortization[] = [
            'month' => $payment['month'],
            'monthly_payment' => formatCurrency($payment['monthly_payment']),
            'principal_payment' => formatCurrency($payment['principal_payment']),
            'interest_payment' => formatCurrency($payment['interest_payment']),
            'remaining_balance' => formatCurrency($payment['remaining_balance']),
            'date' => $payment['date']
        ];
    }
    
    $summary = [
        'monthly_payment' => formatCurrency($monthlyPayment),
        'total_interest' => formatCurrency($totalInterest),
        'total_payment' => formatCurrency($totalPayment),
        'current_balance' => formatCurrency($currentBalance),
        'principal_amount' => formatCurrency($debtData['principal_amount']),
        'months' => $months
    ];
    
    echo json_encode([
        'success' => true,
        'amortization' => $formattedAmortization,
        'summary' => $summary
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error al calcular la amortización: ' . $e->getMessage()
    ]);
}
?>