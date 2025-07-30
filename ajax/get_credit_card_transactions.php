<?php
require_once '../config/config.php';
require_once '../classes/CreditCard.php';

requireLogin();

$csrfToken = $_GET['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo '<p class="text-danger">Token CSRF inválido.</p>';
    exit;
}

$cardId = (int)$_GET['card_id'] ?? 0;

if (!$cardId) {
    echo '<p class="text-danger">ID de tarjeta inválido.</p>';
    exit;
}

$creditCard = new CreditCard();
$card = $creditCard->getById($cardId);

if (!$card || $card['user_id'] != getCurrentUserId()) {
    echo '<p class="text-danger">Tarjeta no encontrada.</p>';
    exit;
}

$transactions = $creditCard->getTransactions($cardId, 50);
?>

<div class="mb-3">
    <h6>Tarjeta: <?php echo htmlspecialchars($card['card_name']); ?></h6>
    <div class="row">
        <div class="col-md-3">
            <strong>Límite:</strong> <?php echo formatCurrency($card['credit_limit']); ?>
        </div>
        <div class="col-md-3">
            <strong>Balance:</strong> <?php echo formatCurrency($card['current_balance']); ?>
        </div>
        <div class="col-md-3">
            <strong>Disponible:</strong> <?php echo formatCurrency($card['credit_limit'] - $card['current_balance']); ?>
        </div>
        <div class="col-md-3">
            <strong>Utilización:</strong> <?php echo number_format(($card['current_balance'] / $card['credit_limit']) * 100, 1); ?>%
        </div>
    </div>
</div>

<hr>

<h6>Historial de Transacciones</h6>

<?php if (empty($transactions)): ?>
    <p class="text-muted text-center">No hay transacciones registradas.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $transaction['type'] === 'charge' ? 'danger' : 'success'; ?>">
                                <?php echo $transaction['type'] === 'charge' ? 'Cargo' : 'Pago'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td class="<?php echo $transaction['type'] === 'charge' ? 'text-danger' : 'text-success'; ?>">
                            <?php echo ($transaction['type'] === 'charge' ? '+' : '-') . formatCurrency($transaction['amount']); ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTransaction(<?php echo $transaction['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- La función deleteTransaction está definida globalmente en credit-cards.php -->
<script>
// Almacenar el ID de la tarjeta para uso de la función global deleteTransaction
window.currentCardId = <?php echo $cardId; ?>;
</script>
