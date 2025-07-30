<?php
require_once '../config/config.php';
require_once '../classes/AccountPayable.php';

requireLogin();

$csrfToken = $_GET['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo '<p class="text-danger">Token CSRF inválido.</p>';
    exit;
}

$accountId = (int)$_GET['account_id'] ?? 0;

if (!$accountId) {
    echo '<p class="text-danger">ID de cuenta inválido.</p>';
    exit;
}

$accountPayable = new AccountPayable();
$account = $accountPayable->getById($accountId);

if (!$account || $account['user_id'] != getCurrentUserId()) {
    echo '<p class="text-danger">Cuenta no encontrada.</p>';
    exit;
}

$payments = $accountPayable->getPayments($accountId);
?>

<div class="mb-3">
    <h6>Cuenta: <?php echo htmlspecialchars($account['creditor_name']); ?></h6>
    <p class="text-muted"><?php echo htmlspecialchars($account['description']); ?></p>
    <div class="row">
        <div class="col-md-4">
            <strong>Total:</strong> <?php echo formatCurrency($account['total_amount']); ?>
        </div>
        <div class="col-md-4">
            <strong>Pagado:</strong> <?php echo formatCurrency($account['paid_amount']); ?>
        </div>
        <div class="col-md-4">
            <strong>Pendiente:</strong> <?php echo formatCurrency($account['total_amount'] - $account['paid_amount']); ?>
        </div>
    </div>
</div>

<hr>

<h6>Historial de Pagos</h6>

<?php if (empty($payments)): ?>
    <p class="text-muted text-center">No hay pagos registrados.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm">
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
                        <td><?php echo formatCurrency($payment['amount']); ?></td>
                        <td><?php echo ucfirst($payment['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($payment['notes']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePayment(<?php echo $payment['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
function deletePayment(paymentId) {
    if (confirm('¿Está seguro de que desea eliminar este pago?')) {
        const formData = new FormData();
        formData.append('payment_id', paymentId);
        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
        
        fetch('ajax/delete_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la lista de pagos
                const accountId = <?php echo $accountId; ?>;
                showLoading(document.getElementById('payments-content'));
                
                fetch(`ajax/get_payments.php?account_id=${accountId}&csrf_token=<?php echo generateCSRFToken(); ?>`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('payments-content').innerHTML = data;
                        // Recargar también la página principal para actualizar totales
                        if (window.parent && window.parent.location) {
                            window.parent.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el pago');
        });
    }
}
</script>
