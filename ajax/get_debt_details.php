<?php
require_once '../config/config.php';
require_once '../classes/Debt.php';

requireLogin();

$csrfToken = $_GET['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Token CSRF inválido.</div>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">ID de deuda inválido.</div>';
    exit;
}

$debtId = intval($_GET['id']);
$userId = getCurrentUserId();
$debt = new Debt();

$debtData = $debt->getById($debtId);
if (!$debtData || $debtData['user_id'] != $userId) {
    echo '<div class="alert alert-danger">Deuda no encontrada.</div>';
    exit;
}

$payments = $debt->getPayments($debtId);
$currentBalance = $debt->calculateCurrentBalance($debtId);
$totalPayments = $debt->getTotalPayments($debtId);
$projection = $debt->getMonthlyInterestProjection($debtId, 12);

// Calcular estadísticas
$totalInterest = $currentBalance - $debtData['principal_amount'] + $totalPayments;
$monthsElapsed = 0;
if ($debtData['start_date']) {
    $startDate = new DateTime($debtData['start_date']);
    $currentDate = new DateTime();
    $monthsElapsed = $startDate->diff($currentDate)->m + ($startDate->diff($currentDate)->y * 12);
}
?>

<div class="row">
    <!-- Información general -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6><i class="bi bi-info-circle"></i> Información General</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Acreedor:</strong></td>
                        <td><?php echo htmlspecialchars($debtData['creditor_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Descripción:</strong></td>
                        <td><?php echo htmlspecialchars($debtData['description']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Capital Inicial:</strong></td>
                        <td><?php echo formatCurrency($debtData['principal_amount']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Balance Actual:</strong></td>
                        <td class="<?php echo $currentBalance > 0 ? 'text-danger' : 'text-success'; ?>">
                            <strong><?php echo formatCurrency($currentBalance); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Interés Mensual:</strong></td>
                        <td><?php echo number_format($debtData['monthly_interest_rate'], 2); ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Inicio:</strong></td>
                        <td><?php echo formatDate($debtData['start_date']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Vencimiento:</strong></td>
                        <td><?php echo $debtData['due_date'] ? formatDate($debtData['due_date']) : 'Sin fecha'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            <?php 
                            $statusClass = [
                                'active' => 'bg-warning',
                                'paid' => 'bg-success',
                                'suspended' => 'bg-secondary'
                            ];
                            $statusText = [
                                'active' => 'Activa',
                                'paid' => 'Pagada',
                                'suspended' => 'Suspendida'
                            ];
                            ?>
                            <span class="badge <?php echo $statusClass[$debtData['status']]; ?>">
                                <?php echo $statusText[$debtData['status']]; ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas financieras -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6><i class="bi bi-calculator"></i> Estadísticas Financieras</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total Pagado:</strong></td>
                        <td class="text-success"><?php echo formatCurrency($totalPayments); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Interés Generado:</strong></td>
                        <td class="text-danger"><?php echo formatCurrency($totalInterest); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Meses Transcurridos:</strong></td>
                        <td><?php echo $monthsElapsed; ?> meses</td>
                    </tr>
                    <tr>
                        <td><strong>Interés Promedio Mensual:</strong></td>
                        <td class="text-danger">
                            <?php echo $monthsElapsed > 0 ? formatCurrency($totalInterest / $monthsElapsed) : formatCurrency(0); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>% del Capital Pagado:</strong></td>
                        <td>
                            <?php 
                            $percentPaid = $debtData['principal_amount'] > 0 ? ($totalPayments / $debtData['principal_amount']) * 100 : 0;
                            echo number_format($percentPaid, 1) . '%';
                            ?>
                        </td>
                    </tr>
                </table>
                
                <!-- Barra de progreso -->
                <div class="mt-3">
                    <label class="form-label">Progreso de Pago</label>
                    <div class="progress">
                        <div class="progress-bar <?php echo $percentPaid >= 100 ? 'bg-success' : 'bg-primary'; ?>" 
                             style="width: <?php echo min(100, $percentPaid); ?>%">
                            <?php echo number_format(min(100, $percentPaid), 1); ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de pagos -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="bi bi-clock-history"></i> Historial de Pagos</h6>
                <button type="button" class="btn btn-sm btn-success" onclick="addPayment(<?php echo $debtId; ?>)">
                    <i class="bi bi-plus-circle"></i> Nuevo Pago
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <div class="text-center py-3">
                        <i class="bi bi-inbox display-6 text-muted"></i>
                        <p class="text-muted">No hay pagos registrados para esta deuda.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Notas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo formatDate($payment['payment_date']); ?></td>
                                        <td class="text-success"><?php echo formatCurrency($payment['amount']); ?></td>
                                        <td>
                                            <?php 
                                            $methods = [
                                                'cash' => 'Efectivo',
                                                'bank_transfer' => 'Transferencia',
                                                'credit_card' => 'T. Crédito',
                                                'debit_card' => 'T. Débito',
                                                'check' => 'Cheque',
                                                'other' => 'Otro'
                                            ];
                                            echo $methods[$payment['payment_method']] ?? $payment['payment_method'];
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment['notes']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePayment(<?php echo $payment['id']; ?>)" 
                                                    title="Eliminar pago">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <td><strong>Total Pagado:</strong></td>
                                    <td><strong class="text-success"><?php echo formatCurrency($totalPayments); ?></strong></td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    

</div>

<script>
// La función deletePayment está definida globalmente en debts.php
// Aquí solo definimos variables que pueden ser útiles para el contexto del modal
const debtId = <?php echo $debtId; ?>;
console.log('Modal de detalles cargado para deuda ID:', debtId);
</script>