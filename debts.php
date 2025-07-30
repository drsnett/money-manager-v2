<?php
require_once 'config/config.php';
require_once 'classes/Debt.php';

requireLogin();

$title = 'Gestión de Deudas';
$userId = getCurrentUserId();
$debt = new Debt();

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_debt':
                    $creditorName = sanitize($_POST['creditor_name']);
                    $description = sanitize($_POST['description']);
                    $principalAmount = floatval($_POST['principal_amount']);
                    $monthlyInterestRate = floatval($_POST['monthly_interest_rate']);
                    $startDate = $_POST['start_date'];
                    $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
                    
                    if (empty($creditorName) || $principalAmount <= 0 || $monthlyInterestRate < 0) {
                        throw new Exception('Por favor, complete todos los campos requeridos correctamente.');
                    }
                    
                    $debt->create($userId, $creditorName, $description, $principalAmount, $monthlyInterestRate, $startDate, $dueDate);
                    $_SESSION['success'] = 'Deuda agregada exitosamente.';
                    break;
                    
                case 'edit_debt':
                    $debtId = intval($_POST['debt_id']);
                    $creditorName = sanitize($_POST['creditor_name']);
                    $description = sanitize($_POST['description']);
                    $principalAmount = floatval($_POST['principal_amount']);
                    $monthlyInterestRate = floatval($_POST['monthly_interest_rate']);
                    $startDate = $_POST['start_date'];
                    $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
                    
                    if (empty($creditorName) || $principalAmount <= 0 || $monthlyInterestRate < 0) {
                        throw new Exception('Por favor, complete todos los campos requeridos correctamente.');
                    }
                    
                    $debt->update($debtId, $creditorName, $description, $principalAmount, $monthlyInterestRate, $startDate, $dueDate);
                    $debt->calculateCurrentBalance($debtId);
                    $_SESSION['success'] = 'Deuda actualizada exitosamente.';
                    break;
                    
                case 'delete_debt':
                    $debtId = intval($_POST['debt_id']);
                    $debt->delete($debtId);
                    $_SESSION['success'] = 'Deuda eliminada exitosamente.';
                    break;
                    
                case 'add_payment':
                    $debtId = intval($_POST['debt_id']);
                    $amount = floatval($_POST['amount']);
                    $paymentDate = $_POST['payment_date'];
                    $paymentMethod = sanitize($_POST['payment_method']);
                    $notes = sanitize($_POST['notes']);
                    
                    if ($amount <= 0) {
                        throw new Exception('El monto del pago debe ser mayor a cero.');
                    }
                    
                    $debt->addPayment($debtId, $amount, $paymentDate, $paymentMethod, $notes);
                    $_SESSION['success'] = 'Pago registrado exitosamente.';
                    break;
                    
                case 'update_status':
                    $debtId = intval($_POST['debt_id']);
                    $status = sanitize($_POST['status']);
                    $debt->updateStatus($debtId, $status);
                    $_SESSION['success'] = 'Estado actualizado exitosamente.';
                    break;
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: debts.php');
    exit;
}

// Obtener datos
$debts = $debt->getByUser($userId);
$debtStats = $debt->getTotalDebtsByUser($userId);
$overdueDebts = $debt->getOverdue($userId);
$upcomingDebts = $debt->getUpcoming($userId, 30);

// Actualizar balances
$debt->updateAllBalances($userId);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-credit-card-2-back"></i> Gestión de Deudas  (BETA)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="calculadora_amortizacion.php" class="btn btn-outline-info">
                <i class="bi bi-calculator"></i> Calculadora de Amortización
            </a>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDebtModal">
            <i class="bi bi-plus-circle"></i> Nueva Deuda
        </button>
    </div>
</div>

<!-- Estadísticas de deudas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Deuda Total Activa</h6>
                        <h3 class="mb-0"><?php echo formatCurrency($debtStats['total_active_debt']); ?></h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Capital Inicial</h6>
                        <h3 class="mb-0"><?php echo formatCurrency($debtStats['total_principal']); ?></h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="bi bi-cash-stack fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Deudas Activas</h6>
                        <h3 class="mb-0"><?php echo $debtStats['active_debts']; ?></h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="bi bi-list-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Total Deudas</h6>
                        <h3 class="mb-0"><?php echo $debtStats['total_debts']; ?></h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="bi bi-collection fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas -->
<?php if (!empty($overdueDebts)): ?>
<div class="alert alert-danger" role="alert">
    <h5><i class="bi bi-exclamation-triangle"></i> Deudas Vencidas</h5>
    <p>Tienes <?php echo count($overdueDebts); ?> deuda(s) vencida(s) que requieren atención inmediata.</p>
</div>
<?php endif; ?>

<?php if (!empty($upcomingDebts)): ?>
<div class="alert alert-warning" role="alert">
    <h5><i class="bi bi-clock"></i> Próximos Vencimientos</h5>
    <p>Tienes <?php echo count($upcomingDebts); ?> deuda(s) que vencen en los próximos 30 días.</p>
</div>
<?php endif; ?>

<!-- Lista de deudas -->
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-list"></i> Mis Deudas</h5>
    </div>
    <div class="card-body">
        <?php if (empty($debts)): ?>
            <div class="text-center py-4">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted">No tienes deudas registradas</h4>
                <p class="text-muted">Haz clic en "Nueva Deuda" para agregar tu primera deuda.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Acreedor</th>
                            <th>Descripción</th>
                            <th>Capital Inicial</th>
                            <th>Balance Actual</th>
                            <th>Interés Mensual</th>
                            <th>Fecha Inicio</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debts as $debtItem): ?>
                            <?php 
                            $currentBalance = $debt->calculateCurrentBalance($debtItem['id']);
                            $isOverdue = $debtItem['due_date'] && $debtItem['due_date'] < date('Y-m-d') && $debtItem['status'] === 'active';
                            $isUpcoming = $debtItem['due_date'] && $debtItem['due_date'] <= date('Y-m-d', strtotime('+30 days')) && $debtItem['due_date'] >= date('Y-m-d') && $debtItem['status'] === 'active';
                            ?>
                            <tr class="<?php echo $isOverdue ? 'table-danger' : ($isUpcoming ? 'table-warning' : ''); ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($debtItem['creditor_name']); ?></strong>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge bg-danger ms-1">Vencida</span>
                                    <?php elseif ($isUpcoming): ?>
                                        <span class="badge bg-warning ms-1">Próximo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($debtItem['description']); ?></td>
                                <td><?php echo formatCurrency($debtItem['principal_amount']); ?></td>
                                <td>
                                    <strong class="<?php echo $currentBalance > 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo formatCurrency($currentBalance); ?>
                                    </strong>
                                </td>
                                <td><?php echo number_format($debtItem['monthly_interest_rate'], 2); ?>%</td>
                                <td><?php echo formatDate($debtItem['start_date']); ?></td>
                                <td><?php echo $debtItem['due_date'] ? formatDate($debtItem['due_date']) : 'Sin fecha'; ?></td>
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
                                    <span class="badge <?php echo $statusClass[$debtItem['status']]; ?>">
                                        <?php echo $statusText[$debtItem['status']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="viewDebtDetails(<?php echo $debtItem['id']; ?>)" 
                                                title="Ver detalles y amortización">
                                            <i class="bi bi-calculator"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="addPayment(<?php echo $debtItem['id']; ?>)" 
                                                title="Agregar pago">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" 
                                                onclick="editDebt(<?php echo htmlspecialchars(json_encode($debtItem)); ?>)" 
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteDebt(<?php echo $debtItem['id']; ?>, '<?php echo htmlspecialchars($debtItem['creditor_name']); ?>')" 
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para agregar deuda -->
<div class="modal fade" id="addDebtModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nueva Deuda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_debt">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="creditor_name" class="form-label">Acreedor *</label>
                                <input type="text" class="form-control" id="creditor_name" name="creditor_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="principal_amount" class="form-label">Monto del Capital *</label>
                                <input type="number" class="form-control" id="principal_amount" name="principal_amount" 
                                       step="0.01" min="0.01" required onchange="calculateLoanDetails()">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="monthly_interest_rate" class="form-label">Interés Mensual (%) *</label>
                                <input type="number" class="form-control" id="monthly_interest_rate" name="monthly_interest_rate" 
                                       step="0.01" min="0" required onchange="calculateLoanDetails()">
                                <div class="form-text">Ejemplo: 10 para 10% mensual</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="loan_term_months" class="form-label">Plazo (Meses) *</label>
                                <input type="number" class="form-control" id="loan_term_months" name="loan_term_months" 
                                       min="1" max="360" value="12" required onchange="calculateLoanDetails()">
                                <div class="form-text">De 1 a 360 meses</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Fecha de Inicio *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" id="due_date" name="due_date">
                                <div class="form-text">Opcional</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen del Préstamo -->
                    <div id="loanSummary" class="alert alert-success" style="display: none;">
                        <h6><i class="bi bi-calculator"></i> Resumen del Préstamo</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Cuota Mensual:</strong><br>
                                <span id="monthlyPaymentDisplay" class="h5 text-primary">$0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Total a Pagar:</strong><br>
                                <span id="totalPaymentDisplay" class="h6">$0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Total Intereses:</strong><br>
                                <span id="totalInterestDisplay" class="h6 text-warning">$0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Número de Cuotas:</strong><br>
                                <span id="numberOfPaymentsDisplay" class="h6">0</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h6><i class="bi bi-info-circle"></i> Nueva Funcionalidad: Tabla de Amortización</h6>
                        <p class="mb-0">Una vez creada la deuda, podrás ver la tabla de amortización que muestra:
                        <br>• <strong>Cuota mensual fija</strong> para cualquier plazo que elijas
                        <br>• <strong>Distribución de capital e interés</strong> en cada pago
                        <br>• <strong>Saldo restante</strong> después de cada cuota
                        <br>• <strong>Interés calculado sobre el capital restante</strong> (no sobre el capital inicial)</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Deuda</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar deuda -->
<div class="modal fade" id="editDebtModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Deuda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_debt">
                    <input type="hidden" name="debt_id" id="edit_debt_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_creditor_name" class="form-label">Acreedor *</label>
                                <input type="text" class="form-control" id="edit_creditor_name" name="creditor_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_principal_amount" class="form-label">Monto del Capital *</label>
                                <input type="number" class="form-control" id="edit_principal_amount" name="principal_amount" 
                                       step="0.01" min="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_monthly_interest_rate" class="form-label">Interés Mensual (%) *</label>
                                <input type="number" class="form-control" id="edit_monthly_interest_rate" name="monthly_interest_rate" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_start_date" class="form-label">Fecha de Inicio *</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_due_date" class="form-label">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" id="edit_due_date" name="due_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Deuda</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para agregar pago -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_payment">
                    <input type="hidden" name="debt_id" id="payment_debt_id">
                    
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Monto del Pago *</label>
                        <input type="number" class="form-control" id="payment_amount" name="amount" 
                               step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Fecha del Pago *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Método de Pago</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="cash">Efectivo</option>
                                    <option value="bank_transfer">Transferencia</option>
                                    <option value="credit_card">Tarjeta de Crédito</option>
                                    <option value="debit_card">Tarjeta de Débito</option>
                                    <option value="check">Cheque</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="payment_notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de deuda -->
<div class="modal fade" id="debtDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye"></i> Detalles de la Deuda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="debtDetailsContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
// Función para calcular automáticamente los detalles del préstamo
function calculateLoanDetails() {
    const principal = parseFloat(document.getElementById('principal_amount').value) || 0;
    const monthlyRate = parseFloat(document.getElementById('monthly_interest_rate').value) || 0;
    const months = parseInt(document.getElementById('loan_term_months').value) || 0;
    
    // Validar que todos los campos tengan valores válidos
    if (principal <= 0 || monthlyRate < 0 || months <= 0) {
        document.getElementById('loanSummary').style.display = 'none';
        return;
    }
    
    // Convertir tasa mensual a decimal
    const rate = monthlyRate / 100;
    
    // Calcular cuota mensual usando la fórmula de amortización
    let monthlyPayment;
    if (rate === 0) {
        // Si no hay interés, dividir el capital entre los meses
        monthlyPayment = principal / months;
    } else {
        // Fórmula de cuota fija con interés compuesto
        monthlyPayment = principal * (rate * Math.pow(1 + rate, months)) / (Math.pow(1 + rate, months) - 1);
    }
    
    // Calcular totales
    const totalPayment = monthlyPayment * months;
    const totalInterest = totalPayment - principal;
    
    // Formatear números como moneda
    const formatter = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
    
    // Actualizar la interfaz
    document.getElementById('monthlyPaymentDisplay').textContent = formatter.format(monthlyPayment);
    document.getElementById('totalPaymentDisplay').textContent = formatter.format(totalPayment);
    document.getElementById('totalInterestDisplay').textContent = formatter.format(totalInterest);
    document.getElementById('numberOfPaymentsDisplay').textContent = months;
    
    // Mostrar el resumen
    document.getElementById('loanSummary').style.display = 'block';
}

// Calcular automáticamente cuando se carga la página si hay valores
document.addEventListener('DOMContentLoaded', function() {
    // Agregar eventos de input para cálculo en tiempo real
    const inputs = ['principal_amount', 'monthly_interest_rate', 'loan_term_months'];
    inputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', calculateLoanDetails);
            element.addEventListener('keyup', calculateLoanDetails);
        }
    });
});

function editDebt(debtData) {
    document.getElementById('edit_debt_id').value = debtData.id;
    document.getElementById('edit_creditor_name').value = debtData.creditor_name;
    document.getElementById('edit_description').value = debtData.description || '';
    document.getElementById('edit_principal_amount').value = debtData.principal_amount;
    document.getElementById('edit_monthly_interest_rate').value = debtData.monthly_interest_rate;
    document.getElementById('edit_start_date').value = debtData.start_date;
    document.getElementById('edit_due_date').value = debtData.due_date || '';
    
    new bootstrap.Modal(document.getElementById('editDebtModal')).show();
}

function addPayment(debtId) {
    document.getElementById('payment_debt_id').value = debtId;
    new bootstrap.Modal(document.getElementById('addPaymentModal')).show();
}

function deleteDebt(debtId, creditorName) {
    if (confirm(`¿Estás seguro de que deseas eliminar la deuda con ${creditorName}?\n\nEsta acción no se puede deshacer y eliminará todos los pagos asociados.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_debt">
            <input type="hidden" name="debt_id" value="${debtId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function viewDebtDetails(debtId) {
    console.log('Cargando detalles de deuda ID:', debtId);
    
    // Cargar detalles vía AJAX
    fetch(`ajax/get_debt_details.php?id=${debtId}&csrf_token=<?php echo generateCSRFToken(); ?>`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            console.log('Contenido HTML cargado, insertando en modal...');
            document.getElementById('debtDetailsContent').innerHTML = html;
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('debtDetailsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error al cargar detalles:', error);
            alert('Error al cargar los detalles de la deuda: ' + error.message);
        });
}

// Función global para eliminar pagos (disponible para el contenido del modal)
function deletePayment(paymentId) {
    console.log('Intentando eliminar pago ID:', paymentId);
    
    if (confirm('¿Estás seguro de que deseas eliminar este pago?')) {
        fetch('ajax/delete_debt_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `payment_id=${paymentId}&csrf_token=<?php echo generateCSRFToken(); ?>`
        })
        .then(response => {
            console.log('Respuesta del servidor:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos de respuesta:', data);
            if (data.success) {
                // Buscar el ID de la deuda actual desde el contenido del modal
                const debtDetailsContent = document.getElementById('debtDetailsContent');
                const debtIdMatch = debtDetailsContent.innerHTML.match(/const debtId = (\d+);/);
                
                if (debtIdMatch) {
                    const debtId = debtIdMatch[1];
                    console.log('Recargando detalles para deuda ID:', debtId);
                    
                    // Recargar el contenido del modal
                    fetch(`ajax/get_debt_details.php?id=${debtId}&csrf_token=<?php echo generateCSRFToken(); ?>`)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('debtDetailsContent').innerHTML = html;
                            console.log('Modal recargado exitosamente');
                        })
                        .catch(error => {
                            console.error('Error al recargar detalles:', error);
                            location.reload();
                        });
                } else {
                    console.log('No se pudo encontrar el ID de la deuda, recargando página');
                    location.reload();
                }
            } else {
                alert('Error al eliminar el pago: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el pago: ' + error.message);
        });
    }
}


</script>

<?php include 'includes/footer.php'; ?>