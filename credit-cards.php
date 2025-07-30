<?php
require_once 'config/config.php';
require_once 'classes/CreditCard.php';

requireLogin();

$title = 'Tarjetas de Crédito';
$userId = getCurrentUserId();

// Inicializar clase
$creditCard = new CreditCard();

// Obtener tarjetas de crédito
$cards = $creditCard->getByUser($userId);

// Obtener monedas disponibles
$currencies = $creditCard->getCurrencies();

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
        $cardName = sanitize($_POST['card_name']);
        $cardNumber = sanitize($_POST['card_number']);
        $creditLimit = (float)$_POST['credit_limit'];
        $cutOffDate = (int)$_POST['cut_off_date'];
        $paymentDueDate = (int)$_POST['payment_due_date'];
        $minimumPaymentPercentage = (float)$_POST['minimum_payment_percentage'];
        $currency = sanitize($_POST['currency']);
        
        if ($cardName && $cardNumber && $creditLimit > 0 && $cutOffDate && $paymentDueDate) {
            try {
                $creditCard->create($userId, $cardName, $cardNumber, $creditLimit, $cutOffDate, $paymentDueDate, $minimumPaymentPercentage, $currency);
                $success = 'Tarjeta de crédito agregada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al agregar la tarjeta: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos obligatorios.';
        }
    } elseif ($action === 'add_transaction') {
        $cardId = (int)$_POST['card_id'];
        $type = $_POST['type'];
        $amount = (float)$_POST['amount'];
        $description = sanitize($_POST['description']);
        $transactionDate = $_POST['transaction_date'];
        
        if ($cardId && $type && $amount > 0 && $transactionDate) {
            try {
                $creditCard->addTransaction($cardId, $type, $amount, $description, $transactionDate);
                $success = ($type === 'charge' ? 'Cargo' : 'Pago') . ' registrado exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al registrar la transacción: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos obligatorios.';
        }
    } elseif ($action === 'change_color') {
        $cardId = (int)$_POST['card_id'];
        $cardColor = sanitize($_POST['card_color']);
        
        if ($cardId && $cardColor) {
            try {
                $creditCard->updateColor($cardId, $cardColor);
                $success = 'Color de la tarjeta actualizado exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al actualizar el color: ' . $e->getMessage();
            }
        } else {
            $error = 'Datos inválidos para cambiar el color.';
        }
    } elseif ($action === 'delete') {
        $cardId = (int)$_POST['card_id'];
        
        if ($cardId) {
            try {
                $creditCard->delete($cardId);
                $success = 'Tarjeta de crédito eliminada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al eliminar la tarjeta: ' . $e->getMessage();
            }
        }
    }
    } // Cierre del bloque else de validación CSRF
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-credit-card-2-front"></i> Tarjetas de Crédito</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCardModal">
            <i class="bi bi-plus"></i> Nueva Tarjeta
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

<!-- Tarjetas de crédito -->
<?php if (empty($cards)): ?>
    <div class="text-center py-5">
        <i class="bi bi-credit-card-2-front fs-1 text-muted"></i>
        <h3 class="text-muted">No hay tarjetas de crédito registradas</h3>
        <p class="text-muted">Agregue su primera tarjeta de crédito para comenzar a gestionar sus gastos.</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCardModal">
            <i class="bi bi-plus"></i> Agregar Primera Tarjeta
        </button>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($cards as $card): ?>
            <?php
            $availableCredit = $creditCard->getAvailableCredit($card['id']);
            $minimumPayment = $creditCard->getMinimumPayment($card['id']);
            $cutOffPayment = $creditCard->getCutOffPayment($card['id']);
            $cutOffDate = $creditCard->getCutOffDate($card['id']);
            $nextPaymentDate = $creditCard->getNextPaymentDate($card['id']);
            $utilizationPercent = ($card['current_balance'] / $card['credit_limit']) * 100;
            $dynamicStatus = $creditCard->getDynamicCardStatus($card['id']);
            
            // Definir colores y textos para cada estado
            $statusConfig = [
                'paid' => ['color' => 'success', 'text' => 'Pagada', 'icon' => 'check-circle'],
                'pending' => ['color' => 'warning', 'text' => 'Pendiente', 'icon' => 'clock'],
                'overdue' => ['color' => 'danger', 'text' => 'Vencida', 'icon' => 'exclamation-triangle'],
                'active' => ['color' => 'info', 'text' => 'Activa', 'icon' => 'credit-card']
            ];
            $status = $statusConfig[$dynamicStatus] ?? $statusConfig['active'];
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100" style="border-left: 5px solid <?php echo htmlspecialchars($card['card_color'] ?? '#007bff'); ?>">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($card['card_color'] ?? '#007bff'); ?>15, <?php echo htmlspecialchars($card['card_color'] ?? '#007bff'); ?>05);">
                        <div>
                            <h5 class="mb-0" style="color: <?php echo htmlspecialchars($card['card_color'] ?? '#007bff'); ?>"><?php echo htmlspecialchars($card['card_name']); ?></h5>
                            <span class="badge bg-<?php echo $status['color']; ?> mt-1">
                                <i class="bi bi-<?php echo $status['icon']; ?>"></i> <?php echo $status['text']; ?>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="addTransaction(<?php echo $card['id']; ?>, 'charge')">
                                    <i class="bi bi-plus-circle text-danger"></i> Agregar Cargo
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="addTransaction(<?php echo $card['id']; ?>, 'payment')">
                                    <i class="bi bi-dash-circle text-success"></i> Agregar Pago
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="viewTransactions(<?php echo $card['id']; ?>)">
                                    <i class="bi bi-list"></i> Ver Transacciones
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="changeCardColor(<?php echo $card['id']; ?>, '<?php echo htmlspecialchars($card['card_color'] ?? '#007bff'); ?>')">
                                    <i class="bi bi-palette"></i> Cambiar Color
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCard(<?php echo $card['id']; ?>)">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Número</small>
                                <div>****<?php echo substr($card['card_number'], -4); ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Límite</small>
                                <div><?php echo formatCurrency($card['credit_limit']); ?></div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Balance Actual</small>
                                <div class="text-danger"><?php echo formatCurrency($card['current_balance']); ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Disponible</small>
                                <div class="text-success"><?php echo formatCurrency($availableCredit); ?></div>
                            </div>
                        </div>
                        
                        <!-- Barra de utilización -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Utilización</small>
                                <small class="text-muted"><?php echo number_format($utilizationPercent, 1); ?>%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar <?php echo $utilizationPercent > 80 ? 'bg-danger' : ($utilizationPercent > 60 ? 'bg-warning' : 'bg-success'); ?>" 
                                     role="progressbar" style="width: <?php echo $utilizationPercent; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Corte</small>
                                <div><?php echo $card['cut_off_date']; ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Pago</small>
                                <div><?php echo $card['payment_due_date']; ?></div>
                            </div>
                        </div>
                        
                        <?php if ($card['current_balance'] > 0): ?>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small class="text-muted">Pago Mínimo</small>
                                    <div class="text-warning"><?php echo formatCurrency($minimumPayment); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Pago al Corte</small>
                                    <div class="text-info"><?php echo formatCurrency($cutOffPayment); ?></div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Próximo Pago</small>
                                    <div><?php echo formatDate($nextPaymentDate); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Fecha de Corte</small>
                                    <div><?php echo formatDate($cutOffDate); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal para agregar tarjeta -->
<div class="modal fade" id="addCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Tarjeta de Crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="card_name" class="form-label">Nombre de la Tarjeta</label>
                        <input type="text" class="form-control" id="card_name" name="card_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="card_number" class="form-label">Número de Tarjeta (últimos 4 dígitos)</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" maxlength="4" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="credit_limit" class="form-label">Límite de Crédito</label>
                                <input type="number" step="0.01" class="form-control" id="credit_limit" name="credit_limit" required>
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
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cut_off_date" class="form-label">Día de Corte</label>
                                <select class="form-select" id="cut_off_date" name="cut_off_date" required>
                                    <option value="">Seleccionar...</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_due_date" class="form-label">Día de Pago</label>
                                <select class="form-select" id="payment_due_date" name="payment_due_date" required>
                                    <option value="">Seleccionar...</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="minimum_payment_percentage" class="form-label">Porcentaje de Pago Mínimo (%)</label>
                        <input type="number" step="0.01" class="form-control" id="minimum_payment_percentage" name="minimum_payment_percentage" value="5.00" required>
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

<!-- Modal para agregar transacción -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalTitle">Agregar Transacción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_transaction">
                    <input type="hidden" name="card_id" id="transaction_card_id">
                    <input type="hidden" name="type" id="transaction_type">
                    
                    <div class="mb-3">
                        <label for="transaction_amount" class="form-label">Monto</label>
                        <input type="number" step="0.01" class="form-control" id="transaction_amount" name="amount" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transaction_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="transaction_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="transaction_submit_btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver transacciones -->
<div class="modal fade" id="viewTransactionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transacciones de Tarjeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="transactions-content">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar color -->
<div class="modal fade" id="changeColorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Color de Tarjeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_color">
                    <input type="hidden" name="card_id" id="color_card_id">
                    
                    <div class="mb-3">
                        <label for="card_color" class="form-label">Seleccionar Color</label>
                        <input type="color" class="form-control form-control-color" id="card_color" name="card_color" value="#007bff" title="Elegir color">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Colores Predefinidos</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm color-preset" data-color="#007bff" style="background-color: #007bff; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#28a745" style="background-color: #28a745; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#dc3545" style="background-color: #dc3545; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#ffc107" style="background-color: #ffc107; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#17a2b8" style="background-color: #17a2b8; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#6f42c1" style="background-color: #6f42c1; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#fd7e14" style="background-color: #fd7e14; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#e83e8c" style="background-color: #e83e8c; width: 40px; height: 40px; border-radius: 50%;"></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Cambiar Color</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form para eliminar -->
<form id="deleteForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="card_id" id="delete_card_id">
</form>

<script>
function addTransaction(cardId, type) {
    document.getElementById('transaction_card_id').value = cardId;
    document.getElementById('transaction_type').value = type;
    
    if (type === 'charge') {
        document.getElementById('transactionModalTitle').textContent = 'Agregar Cargo';
        document.getElementById('transaction_submit_btn').textContent = 'Registrar Cargo';
        document.getElementById('transaction_submit_btn').className = 'btn btn-danger';
    } else {
        document.getElementById('transactionModalTitle').textContent = 'Agregar Pago';
        document.getElementById('transaction_submit_btn').textContent = 'Registrar Pago';
        document.getElementById('transaction_submit_btn').className = 'btn btn-success';
    }
    
    new bootstrap.Modal(document.getElementById('addTransactionModal')).show();
}

function viewTransactions(cardId) {
    const modal = new bootstrap.Modal(document.getElementById('viewTransactionsModal'));
    const modalContent = document.getElementById('transactions-content');
    
    showLoading(modalContent);
    modal.show();
    
    fetch(`ajax/get_credit_card_transactions.php?card_id=${cardId}&csrf_token=<?php echo generateCSRFToken(); ?>`)
        .then(response => response.text())
        .then(data => {
            modalContent.innerHTML = data;
        })
        .catch(error => {
            modalContent.innerHTML = '<p class="text-danger">Error al cargar las transacciones.</p>';
        });
}

function deleteCard(id) {
    if (confirmDelete('¿Está seguro de que desea eliminar esta tarjeta de crédito?')) {
        document.getElementById('delete_card_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

function changeCardColor(cardId, currentColor) {
    document.getElementById('color_card_id').value = cardId;
    document.getElementById('card_color').value = currentColor;
    
    new bootstrap.Modal(document.getElementById('changeColorModal')).show();
}

// Función global para eliminar transacciones de tarjetas de crédito
function deleteTransaction(transactionId) {
    if (confirm('¿Está seguro de que desea eliminar esta transacción?')) {
        const formData = new FormData();
        formData.append('transaction_id', transactionId);
        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
        
        fetch('ajax/delete_credit_card_transaction.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la lista de transacciones en el modal
                const modalContent = document.getElementById('transactions-content');
                const cardId = window.currentCardId;
                
                if (cardId) {
                    showLoading(modalContent);
                    
                    fetch(`ajax/get_credit_card_transactions.php?card_id=${cardId}&csrf_token=<?php echo generateCSRFToken(); ?>`)
                        .then(response => response.text())
                        .then(data => {
                            modalContent.innerHTML = data;
                            // Recargar también la página principal para actualizar totales
                            location.reload();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la transacción');
        });
    }
}

// Manejar clicks en colores predefinidos
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.color-preset').forEach(button => {
        button.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            document.getElementById('card_color').value = color;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
