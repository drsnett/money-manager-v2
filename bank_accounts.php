<?php
require_once 'config/config.php';
require_once 'classes/BankAccount.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$bankAccount = new BankAccount();
$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $bankName = sanitize($_POST['bank_name']);
                $accountName = sanitize($_POST['account_name']);
                $accountNumber = sanitize($_POST['account_number']);
                $accountType = sanitize($_POST['account_type']);
                $currentBalance = floatval($_POST['current_balance']);
                $currency = sanitize($_POST['currency']);
                $notes = sanitize($_POST['notes']);
                
                // Verificar si el número de cuenta ya existe
                if ($bankAccount->accountNumberExists($userId, $accountNumber)) {
                    $message = 'El número de cuenta ya existe.';
                    $messageType = 'danger';
                } else {
                    if ($bankAccount->create($userId, $bankName, $accountName, $accountNumber, $accountType, $currentBalance, $currency, $notes)) {
                        $message = 'Cuenta bancaria agregada exitosamente.';
                        $messageType = 'success';
                    } else {
                        $message = 'Error al agregar la cuenta bancaria.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $bankName = sanitize($_POST['bank_name']);
                $accountName = sanitize($_POST['account_name']);
                $accountNumber = sanitize($_POST['account_number']);
                $accountType = sanitize($_POST['account_type']);
                $currentBalance = floatval($_POST['current_balance']);
                $currency = sanitize($_POST['currency']);
                $notes = sanitize($_POST['notes']);
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
                // Verificar si el número de cuenta ya existe (excluyendo la cuenta actual)
                if ($bankAccount->accountNumberExists($userId, $accountNumber, $id)) {
                    $message = 'El número de cuenta ya existe.';
                    $messageType = 'danger';
                } else {
                    if ($bankAccount->update($id, $userId, $bankName, $accountName, $accountNumber, $accountType, $currentBalance, $currency, $notes, $isActive)) {
                        $message = 'Cuenta bancaria actualizada exitosamente.';
                        $messageType = 'success';
                    } else {
                        $message = 'Error al actualizar la cuenta bancaria.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                if ($bankAccount->delete($id, $userId)) {
                    $message = 'Cuenta bancaria eliminada exitosamente.';
                    $messageType = 'success';
                } else {
                    $message = 'Error al eliminar la cuenta bancaria.';
                    $messageType = 'danger';
                }
                break;
                
            case 'update_balance':
                $id = intval($_POST['id']);
                $newBalance = floatval($_POST['new_balance']);
                if ($bankAccount->updateBalance($id, $userId, $newBalance)) {
                    $message = 'Balance actualizado exitosamente.';
                    $messageType = 'success';
                } else {
                    $message = 'Error al actualizar el balance.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Obtener datos
$accounts = $bankAccount->getByUserId($userId);
$stats = $bankAccount->getStats($userId);
$accountTypes = $bankAccount->getAccountTypes();
$currencies = $bankAccount->getCurrencies();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-bank"></i> Cuentas Bancarias</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                    <i class="bi bi-plus-circle"></i> Agregar Cuenta
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card dashboard-stat bank-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total de Cuentas</h6>
                                    <h3 class="card-title mb-0"><?php echo $stats['total_accounts']; ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-bank"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-stat bank-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Cuentas Activas</h6>
                                    <h3 class="card-title mb-0"><?php echo $stats['active_accounts']; ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-stat bank-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Balance Total</h6>
                                    <h3 class="card-title mb-0"><?php echo formatCurrency($stats['total_balance']); ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-stat bank-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Balance Promedio</h6>
                                    <h3 class="card-title mb-0"><?php echo formatCurrency($stats['average_balance'] ?? 0); ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de cuentas bancarias -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Listado de Cuentas Bancarias</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($accounts)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-bank" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">No hay cuentas bancarias registradas</h5>
                            <p class="text-muted">Comienza agregando tu primera cuenta bancaria.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                                <i class="bi bi-plus-circle"></i> Agregar Primera Cuenta
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Banco</th>
                                        <th>Nombre de Cuenta</th>
                                        <th>Número de Cuenta</th>
                                        <th>Tipo</th>
                                        <th>Balance Actual</th>
                                        <th>Moneda</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($accounts as $account): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($account['bank_name']); ?></td>
                                            <td><?php echo htmlspecialchars($account['account_name']); ?></td>
                                            <td><?php echo htmlspecialchars($account['account_number']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $accountTypes[$account['account_type']]; ?>
                                                </span>
                                            </td>
                                            <td class="<?php echo $account['current_balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatCurrency($account['current_balance']); ?>
                                            </td>
                                            <td><?php echo $account['currency']; ?></td>
                                            <td>
                                                <?php if ($account['is_active']): ?>
                                                    <span class="badge bg-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editAccount(<?php echo htmlspecialchars(json_encode($account)); ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="updateBalance(<?php echo $account['id']; ?>, '<?php echo htmlspecialchars($account['account_name']); ?>', <?php echo $account['current_balance']; ?>)">
                                                        <i class="bi bi-currency-exchange"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteAccount(<?php echo $account['id']; ?>, '<?php echo htmlspecialchars($account['account_name']); ?>')">
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
        </div>
    </div>
</div>

<!-- Modal Agregar Cuenta -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Agregar Cuenta Bancaria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Nombre del Banco *</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_name" class="form-label">Nombre de la Cuenta *</label>
                                <input type="text" class="form-control" id="account_name" name="account_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Número de Cuenta *</label>
                                <input type="text" class="form-control" id="account_number" name="account_number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_type" class="form-label">Tipo de Cuenta *</label>
                                <select class="form-select" id="account_type" name="account_type" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <?php foreach ($accountTypes as $key => $value): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="current_balance" class="form-label">Balance Inicial</label>
                                <input type="number" class="form-control" id="current_balance" name="current_balance" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Moneda</label>
                                <select class="form-select" id="currency" name="currency">
                                    <?php foreach ($currencies as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $key === 'USD' ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cuenta -->
<div class="modal fade" id="editAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Cuenta Bancaria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editAccountForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_bank_name" class="form-label">Nombre del Banco *</label>
                                <input type="text" class="form-control" id="edit_bank_name" name="bank_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_account_name" class="form-label">Nombre de la Cuenta *</label>
                                <input type="text" class="form-control" id="edit_account_name" name="account_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_account_number" class="form-label">Número de Cuenta *</label>
                                <input type="text" class="form-control" id="edit_account_number" name="account_number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_account_type" class="form-label">Tipo de Cuenta *</label>
                                <select class="form-select" id="edit_account_type" name="account_type" required>
                                    <?php foreach ($accountTypes as $key => $value): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_current_balance" class="form-label">Balance Actual</label>
                                <input type="number" class="form-control" id="edit_current_balance" name="current_balance" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_currency" class="form-label">Moneda</label>
                                <select class="form-select" id="edit_currency" name="currency">
                                    <?php foreach ($currencies as $key => $value): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" checked>
                            <label class="form-check-label" for="edit_is_active">
                                Cuenta activa
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Actualizar Balance -->
<div class="modal fade" id="updateBalanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-currency-exchange"></i> Actualizar Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="updateBalanceForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_balance">
                    <input type="hidden" name="id" id="balance_id">
                    <p>Cuenta: <strong id="balance_account_name"></strong></p>
                    <div class="mb-3">
                        <label for="new_balance" class="form-label">Nuevo Balance</label>
                        <input type="number" class="form-control" id="new_balance" name="new_balance" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Actualizar Balance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Cuenta -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Eliminar Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="deleteAccountForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>¿Estás seguro de que deseas eliminar la cuenta <strong id="delete_account_name"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta acción no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAccount(account) {
    document.getElementById('edit_id').value = account.id;
    document.getElementById('edit_bank_name').value = account.bank_name;
    document.getElementById('edit_account_name').value = account.account_name;
    document.getElementById('edit_account_number').value = account.account_number;
    document.getElementById('edit_account_type').value = account.account_type;
    document.getElementById('edit_current_balance').value = account.current_balance;
    document.getElementById('edit_currency').value = account.currency;
    document.getElementById('edit_notes').value = account.notes || '';
    document.getElementById('edit_is_active').checked = account.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editAccountModal')).show();
}

function updateBalance(id, accountName, currentBalance) {
    document.getElementById('balance_id').value = id;
    document.getElementById('balance_account_name').textContent = accountName;
    document.getElementById('new_balance').value = currentBalance;
    
    new bootstrap.Modal(document.getElementById('updateBalanceModal')).show();
}

function deleteAccount(id, accountName) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_account_name').textContent = accountName;
    
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>