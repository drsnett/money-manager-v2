<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/Transaction.php';
require_once 'classes/Category.php';
require_once 'classes/BankAccount.php';

requireLogin();

$title = 'Transacciones';
$userId = getCurrentUserId();

// Inicializar clases
$transaction = new Transaction();
$category = new Category();
$bankAccount = new BankAccount();

// Obtener categorías y cuentas bancarias
$categories = $category->getByUser($userId);
$bankAccounts = $bankAccount->getByUserId($userId);

// Filtros
$filters = [
    'type' => $_GET['type'] ?? '',
    'category_id' => $_GET['category_id'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'payment_method' => $_GET['payment_method'] ?? ''
];

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Obtener transacciones
$transactions = $transaction->getByUser($userId, $perPage, $offset, $filters);
$totalTransactions = $transaction->count($userId, $filters);
$totalPages = ceil($totalTransactions / $perPage);

// Manejo de formularios
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token CSRF inválido. Por favor, recarga la página e intenta nuevamente.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
        $categoryId = (int)$_POST['category_id'];
        $type = $_POST['type'];
        $amount = (float)$_POST['amount'];
        $description = sanitize($_POST['description']);
        $paymentMethod = sanitize($_POST['payment_method']);
        $transactionDate = $_POST['transaction_date'];
        $bankAccountId = !empty($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null;
        
        if ($categoryId && $type && $amount > 0 && $transactionDate) {
            try {
                $transaction->create($userId, $categoryId, $type, $amount, $description, $paymentMethod, $transactionDate, $bankAccountId);
                $success = 'Transacción agregada exitosamente.';
                // Recargar página para mostrar la nueva transacción
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al agregar la transacción: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos obligatorios.';
        }
    } elseif ($action === 'edit') {
        $transactionId = (int)$_POST['transaction_id'];
        $categoryId = (int)$_POST['category_id'];
        $type = $_POST['type'];
        $amount = (float)$_POST['amount'];
        $description = sanitize($_POST['description']);
        $paymentMethod = sanitize($_POST['payment_method']);
        $transactionDate = $_POST['transaction_date'];
        $bankAccountId = !empty($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null;
        
        if ($transactionId && $categoryId && $type && $amount > 0 && $transactionDate) {
            try {
                $transaction->update($transactionId, $categoryId, $type, $amount, $description, $paymentMethod, $transactionDate, $bankAccountId);
                $success = 'Transacción actualizada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al actualizar la transacción: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos obligatorios.';
        }
    } elseif ($action === 'delete') {
        $transactionId = (int)$_POST['transaction_id'];
        
        if ($transactionId) {
            try {
                $transaction->delete($transactionId);
                $success = 'Transacción eliminada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al eliminar la transacción: ' . $e->getMessage();
            }
        }
    }
    } // Cierre del bloque else de validación CSRF
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-arrow-left-right"></i> Transacciones</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel"></i> Filtros
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData('csv', 'transactions.php')">
                <i class="bi bi-download"></i> Exportar
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
            <i class="bi bi-plus"></i> Nueva Transacción
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

<!-- Filtros activos -->
<?php if (array_filter($filters)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6>Filtros activos:</h6>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($filters as $key => $value): ?>
                    <?php if ($value): ?>
                        <span class="badge bg-secondary">
                            <?php
                            switch ($key) {
                                case 'type':
                                    echo ($value === 'income' ? 'Ingresos' : 'Gastos');
                                    break;
                                case 'category_id':
                                    $cat = array_filter($categories, function($c) use ($value) { return $c['id'] == $value; });
                                    echo 'Categoría: ' . (reset($cat)['name'] ?? 'N/A');
                                    break;
                                case 'date_from':
                                    echo 'Desde: ' . formatDate($value);
                                    break;
                                case 'date_to':
                                    echo 'Hasta: ' . formatDate($value);
                                    break;
                                case 'payment_method':
                                    echo 'Método: ' . ucfirst($value);
                                    break;
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a href="transactions.php" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Tabla de transacciones -->
<div class="card">
    <div class="card-header">
        <h5>Lista de Transacciones (<?php echo $totalTransactions; ?> total)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($transactions)): ?>
            <div class="text-center py-4">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted">No hay transacciones que mostrar.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                    <i class="bi bi-plus"></i> Agregar Primera Transacción
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Método</th>
                            <th>Cuenta Bancaria</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $trans): ?>
                            <tr>
                                <td><?php echo formatDate($trans['transaction_date']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $trans['category_color']; ?>">
                                        <?php echo htmlspecialchars($trans['category_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $trans['type'] === 'income' ? 'success' : 'danger'; ?>">
                                        <?php echo $trans['type'] === 'income' ? 'Ingreso' : 'Gasto'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($trans['description']); ?></td>
                                <td><?php echo ucfirst($trans['payment_method']); ?></td>
                                <td>
                                    <?php if ($trans['bank_account_display']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($trans['bank_account_display']); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Sin cuenta</small>
                                    <?php endif; ?>
                                </td>
                                <td class="<?php echo $trans['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($trans['type'] === 'income' ? '+' : '-') . formatCurrency($trans['amount']); ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editTransaction(<?php echo htmlspecialchars(json_encode($trans)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTransaction(<?php echo $trans['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Paginación">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($filters); ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($filters); ?>">Siguiente</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para agregar transacción -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Transacción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="addTransactionForm" onsubmit="return validateTransactionForm('addTransactionForm')">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Seleccionar...</option>
                            <option value="income">Ingreso</option>
                            <option value="expense">Gasto</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Categoría</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Monto</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash">Efectivo</option>
                            <option value="debit_card">Tarjeta de Débito</option>
                            <option value="credit_card">Tarjeta de Crédito</option>
                            <option value="bank_transfer">Transferencia Bancaria</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_account_id" class="form-label">Cuenta Bancaria <small class="text-muted">(Opcional)</small></label>
                        <select class="form-select" id="bank_account_id" name="bank_account_id">
                            <option value="">Sin cuenta específica</option>
                            <?php foreach ($bankAccounts as $account): ?>
                                <?php if ($account['is_active']): ?>
                                    <option value="<?php echo $account['id']; ?>">
                                        <?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_name']); ?>
                                        (<?php echo formatCurrency($account['current_balance']); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecciona la cuenta bancaria asociada a esta transacción</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
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

<!-- Modal para editar transacción -->
<div class="modal fade" id="editTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Transacción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="editTransactionForm" onsubmit="return validateTransactionForm('editTransactionForm')">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="transaction_id" id="edit_transaction_id">
                    
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Tipo</label>
                        <select class="form-select" id="edit_type" name="type" required>
                            <option value="">Seleccionar...</option>
                            <option value="income">Ingreso</option>
                            <option value="expense">Gasto</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Categoría</label>
                        <select class="form-select" id="edit_category_id" name="category_id" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Monto</label>
                        <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_payment_method" class="form-label">Método de Pago</label>
                        <select class="form-select" id="edit_payment_method" name="payment_method" required>
                            <option value="cash">Efectivo</option>
                            <option value="debit_card">Tarjeta de Débito</option>
                            <option value="credit_card">Tarjeta de Crédito</option>
                            <option value="bank_transfer">Transferencia Bancaria</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_bank_account_id" class="form-label">Cuenta Bancaria <small class="text-muted">(Opcional)</small></label>
                        <select class="form-select" id="edit_bank_account_id" name="bank_account_id">
                            <option value="">Sin cuenta específica</option>
                            <?php foreach ($bankAccounts as $account): ?>
                                <?php if ($account['is_active']): ?>
                                    <option value="<?php echo $account['id']; ?>">
                                        <?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_name']); ?>
                                        (<?php echo formatCurrency($account['current_balance']); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecciona la cuenta bancaria asociada a esta transacción</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_transaction_date" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="edit_transaction_date" name="transaction_date" required>
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

<!-- Modal de filtros -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="filter_type" class="form-label">Tipo</label>
                        <select class="form-select" id="filter_type" name="type">
                            <option value="">Todos</option>
                            <option value="income" <?php echo $filters['type'] === 'income' ? 'selected' : ''; ?>>Ingresos</option>
                            <option value="expense" <?php echo $filters['type'] === 'expense' ? 'selected' : ''; ?>>Gastos</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="filter_category_id" class="form-label">Categoría</label>
                        <select class="form-select" id="filter_category_id" name="category_id">
                            <option value="">Todas</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $filters['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="filter_date_from" class="form-label">Fecha desde</label>
                        <input type="date" class="form-control" id="filter_date_from" name="date_from" value="<?php echo $filters['date_from']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="filter_date_to" class="form-label">Fecha hasta</label>
                        <input type="date" class="form-control" id="filter_date_to" name="date_to" value="<?php echo $filters['date_to']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="filter_payment_method" class="form-label">Método de Pago</label>
                        <select class="form-select" id="filter_payment_method" name="payment_method">
                            <option value="">Todos</option>
                            <option value="cash" <?php echo $filters['payment_method'] === 'cash' ? 'selected' : ''; ?>>Efectivo</option>
                            <option value="debit_card" <?php echo $filters['payment_method'] === 'debit_card' ? 'selected' : ''; ?>>Tarjeta de Débito</option>
                            <option value="credit_card" <?php echo $filters['payment_method'] === 'credit_card' ? 'selected' : ''; ?>>Tarjeta de Crédito</option>
                            <option value="bank_transfer" <?php echo $filters['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Transferencia Bancaria</option>
                            <option value="check" <?php echo $filters['payment_method'] === 'check' ? 'selected' : ''; ?>>Cheque</option>
                            <option value="other" <?php echo $filters['payment_method'] === 'other' ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="transactions.php" class="btn btn-secondary">Limpiar</a>
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form para eliminar -->
<form id="deleteForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="transaction_id" id="delete_transaction_id">
</form>

<script>
const categories = <?php echo json_encode($categories); ?>;

// Filtrar categorías por tipo
function filterCategories(type, selectId) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Seleccionar...</option>';
    
    categories.forEach(category => {
        if (category.type === type) {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            select.appendChild(option);
        }
    });
}

// Event listeners para filtrar categorías
document.getElementById('type').addEventListener('change', function() {
    filterCategories(this.value, 'category_id');
});

document.getElementById('edit_type').addEventListener('change', function() {
    filterCategories(this.value, 'edit_category_id');
});

// Función para editar transacción
function editTransaction(transaction) {
    document.getElementById('edit_transaction_id').value = transaction.id;
    document.getElementById('edit_type').value = transaction.type;
    document.getElementById('edit_amount').value = transaction.amount;
    document.getElementById('edit_description').value = transaction.description;
    document.getElementById('edit_payment_method').value = transaction.payment_method;
    document.getElementById('edit_transaction_date').value = transaction.transaction_date;
    
    // Seleccionar cuenta bancaria si existe
    const bankAccountSelect = document.getElementById('edit_bank_account_id');
    if (transaction.bank_account_id) {
        bankAccountSelect.value = transaction.bank_account_id;
    } else {
        bankAccountSelect.value = '';
    }
    
    // Filtrar categorías y seleccionar la actual
    filterCategories(transaction.type, 'edit_category_id');
    setTimeout(() => {
        document.getElementById('edit_category_id').value = transaction.category_id;
    }, 100);
    
    new bootstrap.Modal(document.getElementById('editTransactionModal')).show();
}

// Función para eliminar transacción
function deleteTransaction(id) {
    if (confirmDelete('¿Está seguro de que desea eliminar esta transacción?')) {
        document.getElementById('delete_transaction_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
