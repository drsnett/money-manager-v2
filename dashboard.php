<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/Transaction.php';
require_once 'classes/AccountPayable.php';
require_once 'classes/AccountReceivable.php';
require_once 'classes/CreditCard.php';
require_once 'classes/Debt.php';
require_once 'classes/BankAccount.php';
require_once 'classes/Notification.php';
require_once 'classes/Cache.php';

requireLogin();

$title = 'Dashboard';
$userId = getCurrentUserId();

// Inicializar notificaciones
$notification = new Notification();

// Generar notificaciones automáticas si es necesario
$notification->generateDueNotifications();

// Inicializar clases
$transaction = new Transaction();
$accountPayable = new AccountPayable();
$accountReceivable = new AccountReceivable();
$creditCard = new CreditCard();
$debt = new Debt();
$bankAccount = new BankAccount();

// Obtener estadísticas
$balance = $transaction->getBalance($userId);
$upcomingPayments = $accountPayable->getUpcoming($userId, 7);
$upcomingReceivables = $accountReceivable->getUpcoming($userId, 7);
$overduePayments = $accountPayable->getOverdue($userId);
$overdueReceivables = $accountReceivable->getOverdue($userId);
$creditCardsWithPayments = $creditCard->getCardsWithPaymentsDue($userId, 7);

// Obtener estadísticas de deudas
$debtStats = $debt->getDebtStats($userId);
$upcomingDebts = $debt->getUpcomingDebts($userId, 7);
$overdueDebts = $debt->getOverdueDebts($userId);

// Actualizar estado de tarjetas vencidas y obtener tarjetas vencidas
$creditCard->updateOverdueStatus($userId);
$overdueCreditCards = $creditCard->getOverdueCards($userId);

// Obtener estadísticas de cuentas bancarias
$bankStats = $bankAccount->getStats($userId);
$bankAccounts = $bankAccount->getByUserId($userId);

// Obtener transacciones recientes
$recentTransactions = $transaction->getByUser($userId, 10);

// Obtener estadísticas mensuales
$currentMonth = date('n');
$currentYear = date('Y');
$monthlyStats = $transaction->getMonthlyStats($userId, $currentYear, $currentMonth);

// Procesar estadísticas mensuales
$monthlyIncome = 0;
$monthlyExpenses = 0;
foreach ($monthlyStats as $stat) {
    if ($stat['type'] === 'income') {
        $monthlyIncome = $stat['total'];
    } else {
        $monthlyExpenses = $stat['total'];
    }
}

// Actualizar estados de cuentas
$accountPayable->updateAllStatuses();
$accountReceivable->updateAllStatuses();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Actualizar
            </button>
        </div>
    </div>
</div>

<!-- Estadísticas principales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-stat income">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Ingresos Totales</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($balance['total_income']); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-arrow-up-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat expense">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Gastos Totales</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($balance['total_expenses']); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-arrow-down-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat balance">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Balance</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($balance['balance']); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-wallet2 fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Balance Mensual</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($monthlyIncome - $monthlyExpenses); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-calendar-month fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas de deudas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-stat debt">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Deudas Activas</h6>
                    <h3 class="mb-0"><?php echo $debtStats['active_count']; ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-credit-card fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat debt">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Total Adeudado</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($debtStats['total_current_balance']); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-currency-dollar fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat debt">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Interés Mensual</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($debtStats['monthly_interest']); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-percent fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat debt">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Próximos Vencimientos</h6>
                    <h3 class="mb-0"><?php echo count($upcomingDebts); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-alarm fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas de cuentas bancarias -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-stat bank-stat">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Total Cuentas</h6>
                    <h3 class="mb-0"><?php echo $bankStats['total_accounts']; ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-bank fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat bank-stat">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Cuentas Activas</h6>
                    <h3 class="mb-0"><?php echo $bankStats['active_accounts']; ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-check-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat bank-stat">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Balance Total</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($bankStats['total_balance']); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-currency-dollar fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="dashboard-stat bank-stat">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0">Balance Promedio</h6>
                    <h3 class="mb-0"><?php echo formatCurrency($bankStats['average_balance'] ?? 0); ?></h3>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-graph-up fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de ingresos vs gastos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart"></i> Ingresos vs Gastos (Este Mes)</h5>
            </div>
            <div class="card-body">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Transacciones recientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-clock-history"></i> Transacciones Recientes</h5>
                <a href="transactions.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentTransactions)): ?>
                    <p class="text-muted text-center">No hay transacciones recientes.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $trans): ?>
                                    <tr>
                                        <td><?php echo formatDate($trans['transaction_date']); ?></td>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo $trans['category_color']; ?>">
                                                <?php echo htmlspecialchars($trans['category_name']); ?>
                                            </span>
                                            <br>
                                            <small><?php echo htmlspecialchars($trans['description']); ?></small>
                                        </td>
                                        <td class="<?php echo $trans['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($trans['type'] === 'income' ? '+' : '-') . formatCurrency($trans['amount']); ?>
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

<!-- Alertas y recordatorios -->
<div class="row mt-4">
    <!-- Cuentas por pagar próximas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-exclamation-triangle text-warning"></i> Pagos Próximos (7 días)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingPayments)): ?>
                    <p class="text-muted text-center">No hay pagos próximos.</p>
                <?php else: ?>
                    <?php foreach ($upcomingPayments as $payment): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($payment['creditor_name']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    Vence: <?php echo formatDate($payment['due_date']); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="text-danger"><?php echo formatCurrency($payment['total_amount'] - $payment['paid_amount']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3">
                        <a href="accounts-payable.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Cuentas por cobrar próximas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-info-circle text-info"></i> Cobros Próximos (7 días)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingReceivables)): ?>
                    <p class="text-muted text-center">No hay cobros próximos.</p>
                <?php else: ?>
                    <?php foreach ($upcomingReceivables as $receivable): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($receivable['debtor_name']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    Vence: <?php echo formatDate($receivable['due_date']); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="text-success"><?php echo formatCurrency($receivable['total_amount'] - $receivable['received_amount']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3">
                        <a href="accounts-receivable.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Deudas próximas a vencer -->
<?php if (!empty($upcomingDebts)): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-exclamation-triangle text-warning"></i> Deudas Próximas a Vencer (7 días)</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($upcomingDebts as $upcomingDebt): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($upcomingDebt['creditor_name']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    Vence: <?php echo formatDate($upcomingDebt['due_date']); ?>
                                    | Interés: <?php echo $upcomingDebt['interest_rate']; ?>% mensual
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="text-danger"><?php echo formatCurrency($upcomingDebt['current_balance']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3">
                        <a href="debts.php" class="btn btn-sm btn-outline-primary">Ver todas las deudas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tarjetas de crédito vencidas -->
    <?php if (!empty($overdueCreditCards)): ?>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5><i class="bi bi-credit-card"></i> Tarjetas Vencidas</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($overdueCreditCards as $card): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($card['card_name']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    Vencida hace: <?php echo round($card['days_overdue']); ?> días
                                    | Balance: <?php echo formatCurrency($card['current_balance']); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger"><?php echo formatCurrency($card['current_balance']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3">
                        <a href="credit-cards.php" class="btn btn-sm btn-outline-danger">Ver tarjetas</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Cuentas vencidas -->
<?php if (!empty($overduePayments) || !empty($overdueReceivables) || !empty($overdueDebts) || !empty($overdueCreditCards)): ?>
    <div class="row mt-4">
        <?php if (!empty($overduePayments)): ?>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="bi bi-exclamation-triangle"></i> Pagos Vencidos</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($overduePayments as $payment): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($payment['creditor_name']); ?></strong>
                                    <br>
                                    <small class="text-danger">
                                        Venció: <?php echo formatDate($payment['due_date']); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="text-danger"><?php echo formatCurrency($payment['total_amount'] - $payment['paid_amount']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($overdueReceivables)): ?>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="bi bi-exclamation-triangle"></i> Cobros Vencidos</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($overdueReceivables as $receivable): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($receivable['debtor_name']); ?></strong>
                                    <br>
                                    <small class="text-warning">
                                        Venció: <?php echo formatDate($receivable['due_date']); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="text-warning"><?php echo formatCurrency($receivable['total_amount'] - $receivable['received_amount']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($overdueDebts)): ?>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="bi bi-exclamation-triangle"></i> Deudas Vencidas</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($overdueDebts as $overdueDebt): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($overdueDebt['creditor_name']); ?></strong>
                                    <br>
                                    <small class="text-danger">
                                        Venció: <?php echo formatDate($overdueDebt['due_date']); ?>
                                        | <?php echo $overdueDebt['interest_rate']; ?>% mensual
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="text-danger"><?php echo formatCurrency($overdueDebt['current_balance']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
// Gráfico de ingresos vs gastos
const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
const incomeExpenseChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Ingresos', 'Gastos'],
        datasets: [{
            data: [<?php echo $monthlyIncome; ?>, <?php echo $monthlyExpenses; ?>],
            backgroundColor: ['#28a745', '#dc3545'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Auto-refresh cada 5 minutos
setTimeout(() => {
    location.reload();
}, 300000);
</script>

<?php include 'includes/footer.php'; ?>
