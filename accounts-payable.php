<?php
require_once 'config/config.php';
require_once 'classes/AccountPayable.php';
require_once 'classes/BankAccount.php';
require_once 'classes/CreditCard.php';

requireLogin();

$title = 'Cuentas por Pagar';
$userId = getCurrentUserId();

// Inicializar clases
$accountPayable = new AccountPayable();
$bankAccount = new BankAccount();
$creditCard = new CreditCard();

// Obtener cuentas por pagar, cuentas bancarias y tarjetas de crédito
$accounts = $accountPayable->getByUser($userId);
$bankAccounts = $bankAccount->getByUserId($userId);
// Filtrar solo cuentas activas
$bankAccounts = array_filter($bankAccounts, function($account) {
    return $account['is_active'] == 1;
});
$creditCards = $creditCard->getByUser($userId);

// Manejo de formularios
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $creditorName = sanitize($_POST['creditor_name']);
        $description = sanitize($_POST['description']);
        $totalAmount = (float)$_POST['total_amount'];
        $dueDate = $_POST['due_date'];
        $isRecurring = isset($_POST['is_recurring']) ? 1 : 0;
        $recurringType = !empty($_POST['recurring_type']) ? $_POST['recurring_type'] : null;
        $bankAccountId = !empty($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null;
        
        if ($creditorName && $totalAmount > 0 && $dueDate) {
            try {
                $accountPayable->create($userId, $creditorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType, $bankAccountId);
                $success = 'Cuenta por pagar agregada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al agregar la cuenta: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos obligatorios.';
        }
    } elseif ($action === 'edit') {
        $accountId = (int)$_POST['account_id'];
        $creditorName = sanitize($_POST['creditor_name']);
        $description = sanitize($_POST['description']);
        $totalAmount = (float)$_POST['total_amount'];
        $dueDate = $_POST['due_date'];
        $isRecurring = isset($_POST['is_recurring']) ? 1 : 0;
        $recurringType = !empty($_POST['recurring_type']) ? $_POST['recurring_type'] : null;
        $bankAccountId = !empty($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null;
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'paid'])) {
            $status = 'pending';
        }

        if ($accountId && $creditorName && $totalAmount > 0 && $dueDate) {
            try {
                $accountPayable->update($accountId, $creditorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType, $bankAccountId, $status);
                $success = 'Cuenta por pagar actualizada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al actualizar la cuenta: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos obligatorios.';
        }
    } elseif ($action === 'add_payment') {
        $accountId = (int)$_POST['account_id'];
        $amount = (float)$_POST['amount'];
        $paymentDate = $_POST['payment_date'];
        $paymentMethod = sanitize($_POST['payment_method']);
        $notes = sanitize($_POST['notes']);
        
        // Determinar la fuente de pago según la selección
        $bankAccountId = null;
        $creditCardId = null;
        
        if (!empty($_POST['payment_source_type'])) {
            if ($_POST['payment_source_type'] === 'bank_account' && !empty($_POST['bank_account_id'])) {
                $bankAccountId = (int)$_POST['bank_account_id'];
            } elseif ($_POST['payment_source_type'] === 'credit_card' && !empty($_POST['credit_card_id'])) {
                $creditCardId = (int)$_POST['credit_card_id'];
            }
        }
        
        $createNext = isset($_POST['create_next']) ? 1 : 0;

        if ($accountId && $amount > 0 && $paymentDate) {
            try {
                $accountPayable->addPayment($accountId, $amount, $paymentDate, $paymentMethod, $notes, $bankAccountId, $creditCardId);

                $account = $accountPayable->getById($accountId);
                if ($account['status'] === 'paid' && $account['is_recurring']) {
                    if ($createNext) {
                        $accountPayable->createNextRecurring($accountId);
                    } else {
                        $accountPayable->update($accountId, $account['creditor_name'], $account['description'], $account['total_amount'], $account['due_date'], 0, null, $account['bank_account_id']);
                    }
                }

                $success = 'Pago registrado exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al registrar el pago: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos obligatorios.';
        }
    } elseif ($action === 'delete') {
        $accountId = (int)$_POST['account_id'];
        
        if ($accountId) {
            try {
                $accountPayable->delete($accountId);
                $success = 'Cuenta por pagar eliminada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al eliminar la cuenta: ' . $e->getMessage();
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-credit-card"></i> Cuentas por Pagar</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData('csv', 'accounts-payable.php')">
                <i class="bi bi-download"></i> Exportar
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="processRecurring()" id="processRecurringBtn">
                <i class="bi bi-arrow-repeat"></i> Procesar Recurrencias
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            <i class="bi bi-plus"></i> Nueva Cuenta por Pagar
        </button>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Resumen -->
<div class="row mb-4">
    <?php
    $totalPending = 0;
    $totalPaid = 0;
    $totalOverdue = 0;
    $totalPartial = 0;
    
    // Obtener el mes y año actual
    $currentMonth = date('Y-m');
    
    foreach ($accounts as $account) {
        $remaining = $account['total_amount'] - $account['paid_amount'];
        
        // Para la tarjeta "Pendientes", solo contar las del mes actual
        if ($account['status'] === 'pending') {
            $accountMonth = date('Y-m', strtotime($account['due_date']));
            if ($accountMonth === $currentMonth) {
                $totalPending += $remaining;
            }
        } else {
            switch ($account['status']) {
                case 'paid':
                    $totalPaid += $account['total_amount'];
                    break;
                case 'overdue':
                    $totalOverdue += $remaining;
                    break;
                case 'partial':
                    $totalPartial += $remaining;
                    break;
            }
        }
    }
    ?>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pendientes (Este Mes)</h5>
                <h3><?php echo formatCurrency($totalPending); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Vencidas</h5>
                <h3><?php echo formatCurrency($totalOverdue); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Parciales</h5>
                <h3><?php echo formatCurrency($totalPartial); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Pagadas</h5>
                <h3><?php echo formatCurrency($totalPaid); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de cuentas por pagar -->
<div class="card">
    <div class="card-header">
        <h5>Lista de Cuentas por Pagar</h5>
    </div>
    <div class="card-body">
        <?php if (empty($accounts)): ?>
            <div class="text-center py-4">
                <i class="bi bi-credit-card fs-1 text-muted"></i>
                <p class="text-muted">No hay cuentas por pagar registradas.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                    <i class="bi bi-plus"></i> Agregar Primera Cuenta
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Acreedor</th>
                            <th>Descripción</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Pendiente</th>
                            <th>Vencimiento</th>
                            <th>Cuenta Bancaria</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($account['creditor_name']); ?></strong>
                                    <?php if ($account['is_recurring']): ?>
                                        <br><small class="text-muted">
                                            <i class="bi bi-arrow-repeat"></i> 
                                            <?php echo ucfirst($account['recurring_type']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($account['description']); ?></td>
                                <td><?php echo formatCurrency($account['total_amount']); ?></td>
                                <td><?php echo formatCurrency($account['paid_amount']); ?></td>
                                <td><?php echo formatCurrency($account['total_amount'] - $account['paid_amount']); ?></td>
                                <td>
                                    <?php echo formatDate($account['due_date']); ?>
                                    <?php if ($account['status'] === 'overdue'): ?>
                                        <br><small class="text-danger">
                                            <i class="bi bi-exclamation-triangle"></i> Vencida
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($account['bank_account_display']): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-bank"></i> <?php echo htmlspecialchars($account['bank_account_display']); ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">Sin cuenta</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo getStatusBadge($account['status']); ?></td>
                                <td>
                                    <?php if ($account['status'] !== 'paid'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addPayment(<?php echo $account['id']; ?>, '<?php echo htmlspecialchars($account['creditor_name']); ?>', <?php echo $account['total_amount'] - $account['paid_amount']; ?>, <?php echo $account['is_recurring']; ?>)">
                                            <i class="bi bi-cash"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick='editPayable(<?php echo json_encode($account); ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewPayments(<?php echo $account['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAccount(<?php echo $account['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para agregar cuenta -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Cuenta por Pagar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="creditor_name" class="form-label">Acreedor</label>
                        <input type="text" class="form-control" id="creditor_name" name="creditor_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total_amount" class="form-label">Monto Total</label>
                        <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_account_id" class="form-label">Cuenta Bancaria (Opcional)</label>
                        <select class="form-select" id="bank_account_id" name="bank_account_id">
                            <option value="">Sin cuenta específica</option>
                            <?php foreach ($bankAccounts as $bankAcc): ?>
                                <option value="<?php echo $bankAcc['id']; ?>">
                                    <?php echo htmlspecialchars($bankAcc['bank_name'] . ' - ' . $bankAcc['account_name'] . ' (' . formatCurrency($bankAcc['current_balance']) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" onchange="toggleRecurring()">
                            <label class="form-check-label" for="is_recurring">
                                Es recurrente
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="recurring_type_div" style="display: none;">
                        <label for="recurring_type" class="form-label">Tipo de Recurrencia</label>
                        <select class="form-select" id="recurring_type" name="recurring_type">
                            <option value="">Seleccionar...</option>
                            <option value="weekly">Semanal</option>
                            <option value="biweekly">Quincenal</option>
                            <option value="monthly">Mensual</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar cuenta -->
<div class="modal fade" id="editAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cuenta por Pagar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="account_id" id="edit_account_id">

                    <div class="mb-3">
                        <label for="edit_creditor_name" class="form-label">Acreedor</label>
                        <input type="text" class="form-control" id="edit_creditor_name" name="creditor_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_total_amount" class="form-label">Monto Total</label>
                        <input type="number" step="0.01" class="form-control" id="edit_total_amount" name="total_amount" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_due_date" class="form-label">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="edit_due_date" name="due_date" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Estatus</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="pending">Pendiente</option>
                            <option value="paid">Pagado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_bank_account_id" class="form-label">Cuenta Bancaria (Opcional)</label>
                        <select class="form-select" id="edit_bank_account_id" name="bank_account_id">
                            <option value="">Sin cuenta específica</option>
                            <?php foreach ($bankAccounts as $bankAcc): ?>
                                <option value="<?php echo $bankAcc['id']; ?>">
                                    <?php echo htmlspecialchars($bankAcc['bank_name'] . ' - ' . $bankAcc['account_name'] . ' (' . formatCurrency($bankAcc['current_balance']) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_recurring" name="is_recurring" onchange="toggleEditRecurring()">
                            <label class="form-check-label" for="edit_is_recurring">
                                Es recurrente
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="edit_recurring_type_div" style="display: none;">
                        <label for="edit_recurring_type" class="form-label">Tipo de Recurrencia</label>
                        <select class="form-select" id="edit_recurring_type" name="recurring_type">
                            <option value="">Seleccionar...</option>
                            <option value="weekly">Semanal</option>
                            <option value="biweekly">Quincenal</option>
                            <option value="monthly">Mensual</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
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
                <h5 class="modal-title">Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_payment">
                    <input type="hidden" name="account_id" id="payment_account_id">
                    
                    <div class="alert alert-info">
                        <strong>Acreedor:</strong> <span id="payment_creditor_name"></span><br>
                        <strong>Monto pendiente:</strong> <span id="payment_pending_amount"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Monto del Pago</label>
                        <input type="number" step="0.01" class="form-control" id="payment_amount" name="amount" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Fecha del Pago</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago</label>
                        <select class="form-select" id="payment_method" name="payment_method" required onchange="togglePaymentSource()">
                            <option value="cash">Efectivo</option>
                            <option value="debit_card">Tarjeta de Débito</option>
                            <option value="credit_card">Tarjeta de Crédito</option>
                            <option value="bank_transfer">Transferencia Bancaria</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="payment_source_container">
                        <label for="payment_source_type" class="form-label">Fuente de Pago (Opcional)</label>
                        <select class="form-select" id="payment_source_type" name="payment_source_type" onchange="togglePaymentSourceOptions()">
                            <option value="">Sin fuente específica</option>
                            <option value="bank_account">Cuenta Bancaria</option>
                            <option value="credit_card">Tarjeta de Crédito</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="bank_account_container" style="display: none;">
                        <label for="payment_bank_account_id" class="form-label">Cuenta Bancaria</label>
                        <select class="form-select" id="payment_bank_account_id" name="bank_account_id">
                            <option value="">Seleccionar cuenta</option>
                            <?php foreach ($bankAccounts as $bankAcc): ?>
                                <option value="<?php echo $bankAcc['id']; ?>">
                                    <?php echo htmlspecialchars($bankAcc['bank_name'] . ' - ' . $bankAcc['account_name'] . ' (' . formatCurrency($bankAcc['current_balance']) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="credit_card_container" style="display: none;">
                        <label for="payment_credit_card_id" class="form-label">Tarjeta de Crédito</label>
                        <select class="form-select" id="payment_credit_card_id" name="credit_card_id">
                            <option value="">Seleccionar tarjeta</option>
                            <?php foreach ($creditCards as $card): ?>
                                <option value="<?php echo $card['id']; ?>">
                                    <?php echo htmlspecialchars($card['card_name'] . ' - Disponible: ' . formatCurrency($card['credit_limit'] - $card['current_balance'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="payment_notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="mb-3" id="create_next_container" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="create_next" name="create_next" checked>
                            <label class="form-check-label" for="create_next">
                                Generar cuenta del siguiente mes
                            </label>
                        </div>
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

<!-- Modal para ver pagos -->
<div class="modal fade" id="viewPaymentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historial de Pagos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="payments-content">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form para eliminar -->
<form id="deleteForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="account_id" id="delete_account_id">
</form>

<script>
function toggleRecurring() {
    const checkbox = document.getElementById('is_recurring');
    const div = document.getElementById('recurring_type_div');
    
    if (checkbox.checked) {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
    }
}

function addPayment(accountId, creditorName, pendingAmount, isRecurring) {
    document.getElementById('payment_account_id').value = accountId;
    document.getElementById('payment_creditor_name').textContent = creditorName;
    document.getElementById('payment_pending_amount').textContent = formatCurrency(pendingAmount);
    document.getElementById('payment_amount').max = pendingAmount;

    const createNextContainer = document.getElementById('create_next_container');
    createNextContainer.style.display = 'none';
    document.getElementById('payment_amount').value = '';

    document.getElementById('create_next').checked = true;

    document.getElementById('payment_amount').oninput = function() {
        const amount = parseFloat(this.value || 0);
        if (isRecurring && Math.abs(amount - pendingAmount) < 0.01) {
            createNextContainer.style.display = 'block';
        } else {
            createNextContainer.style.display = 'none';
        }
    };

    new bootstrap.Modal(document.getElementById('addPaymentModal')).show();
}

function viewPayments(accountId) {
    showLoading(document.getElementById('payments-content'));
    
    fetch(`ajax/get_payments.php?account_id=${accountId}&csrf_token=<?php echo generateCSRFToken(); ?>`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('payments-content').innerHTML = data;
            new bootstrap.Modal(document.getElementById('viewPaymentsModal')).show();
        })
        .catch(error => {
            document.getElementById('payments-content').innerHTML = '<p class="text-danger">Error al cargar los pagos.</p>';
        });
}

function deleteAccount(id) {
    if (confirmDelete('¿Está seguro de que desea eliminar esta cuenta por pagar?')) {
        document.getElementById('delete_account_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

function togglePaymentSource() {
    const paymentMethod = document.getElementById('payment_method').value;
    const sourceContainer = document.getElementById('payment_source_container');
    
    // Mostrar el selector de fuente de pago para métodos que lo requieren
    if (paymentMethod === 'debit_card' || paymentMethod === 'credit_card' || paymentMethod === 'bank_transfer') {
        sourceContainer.style.display = 'block';
        
        // Auto-seleccionar la fuente apropiada según el método
        const sourceType = document.getElementById('payment_source_type');
        if (paymentMethod === 'credit_card') {
            sourceType.value = 'credit_card';
        } else if (paymentMethod === 'debit_card' || paymentMethod === 'bank_transfer') {
            sourceType.value = 'bank_account';
        }
        togglePaymentSourceOptions();
    } else {
        sourceContainer.style.display = 'none';
        // Limpiar selecciones
        document.getElementById('payment_source_type').value = '';
        togglePaymentSourceOptions();
    }
}

function togglePaymentSourceOptions() {
    const sourceType = document.getElementById('payment_source_type').value;
    const bankContainer = document.getElementById('bank_account_container');
    const creditContainer = document.getElementById('credit_card_container');
    
    // Ocultar ambos contenedores
    bankContainer.style.display = 'none';
    creditContainer.style.display = 'none';
    
    // Limpiar valores
    document.getElementById('payment_bank_account_id').value = '';
    document.getElementById('payment_credit_card_id').value = '';
    
    // Mostrar el contenedor apropiado
    if (sourceType === 'bank_account') {
        bankContainer.style.display = 'block';
    } else if (sourceType === 'credit_card') {
        creditContainer.style.display = 'block';
    }
}

function editPayable(data) {
    document.getElementById('edit_account_id').value = data.id;
    document.getElementById('edit_creditor_name').value = data.creditor_name;
    document.getElementById('edit_description').value = data.description || '';
    document.getElementById('edit_total_amount').value = data.total_amount;
    document.getElementById('edit_due_date').value = data.due_date;
    document.getElementById('edit_status').value = data.status;
    document.getElementById('edit_bank_account_id').value = data.bank_account_id || '';
    document.getElementById('edit_is_recurring').checked = data.is_recurring == 1;
    if (data.is_recurring == 1) {
        document.getElementById('edit_recurring_type_div').style.display = 'block';
        document.getElementById('edit_recurring_type').value = data.recurring_type;
    } else {
        document.getElementById('edit_recurring_type_div').style.display = 'none';
        document.getElementById('edit_recurring_type').value = '';
    }
    new bootstrap.Modal(document.getElementById('editAccountModal')).show();
}

function toggleEditRecurring() {
    const checkbox = document.getElementById('edit_is_recurring');
    const div = document.getElementById('edit_recurring_type_div');
    div.style.display = checkbox.checked ? 'block' : 'none';
}

function processRecurring() {
    const btn = document.getElementById('processRecurringBtn');
    const originalText = btn.innerHTML;
    
    // Deshabilitar botón y mostrar estado de carga
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    // Enviar token CSRF mediante FormData
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

    fetch('ajax/process_recurring.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Recargar la página para mostrar las nuevas cuentas creadas
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert('danger', data.message || 'Error al procesar las recurrencias');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error de conexión al procesar las recurrencias');
    })
    .finally(() => {
        // Restaurar botón
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>

<?php include 'includes/footer.php'; ?>
