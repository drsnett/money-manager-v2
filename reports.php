<?php
require_once 'config/config.php';
require_once 'classes/Transaction.php';
require_once 'classes/Category.php';
require_once 'classes/AccountPayable.php';
require_once 'classes/AccountReceivable.php';
require_once 'classes/CreditCard.php';

requireLogin();

$title = 'Reportes';
$userId = getCurrentUserId();

// Inicializar clases
$transaction = new Transaction();
$category = new Category();
$accountPayable = new AccountPayable();
$accountReceivable = new AccountReceivable();
$creditCard = new CreditCard();

// Obtener parámetros de fecha
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-t');
$reportType = $_GET['report_type'] ?? 'general';

// Obtener datos según el tipo de reporte
$balance = $transaction->getBalance($userId);
$categoryStats = $transaction->getCategoryStats($userId, $dateFrom, $dateTo);
$categories = $category->getByUser($userId);

// Procesar estadísticas por categoría
$incomeStats = array_filter($categoryStats, function($stat) { return $stat['type'] === 'income'; });
$expenseStats = array_filter($categoryStats, function($stat) { return $stat['type'] === 'expense'; });

// Calcular totales del período
$totalIncome = array_sum(array_column($incomeStats, 'total'));
$totalExpenses = array_sum(array_column($expenseStats, 'total'));
$periodBalance = $totalIncome - $totalExpenses;

// Obtener datos de cuentas por pagar y cobrar
$accountsPayable = $accountPayable->getByUser($userId);
$accountsReceivable = $accountReceivable->getByUser($userId);

// Obtener datos de tarjetas de crédito
$creditCards = $creditCard->getByUser($userId);

// Manejo de exportación se mueve a export.php

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-graph-up"></i> Reportes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData('csv', 'export.php?export=csv&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>')">
                <i class="bi bi-file-earmark-text"></i> CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData('excel', 'export.php?export=excel&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>')">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData('pdf', 'export.php?export=pdf&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>')">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printContent('report-content')">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Filtros de fecha -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="col-md-3">
                <label for="date_from" class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo; ?>">
            </div>
            <div class="col-md-3">
                <label for="report_type" class="form-label">Tipo de Reporte</label>
                <select class="form-select" id="report_type" name="report_type">
                    <option value="general" <?php echo $reportType === 'general' ? 'selected' : ''; ?>>General</option>
                    <option value="income" <?php echo $reportType === 'income' ? 'selected' : ''; ?>>Solo Ingresos</option>
                    <option value="expense" <?php echo $reportType === 'expense' ? 'selected' : ''; ?>>Solo Gastos</option>
                    <option value="categories" <?php echo $reportType === 'categories' ? 'selected' : ''; ?>>Por Categorías</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">Generar Reporte</button>
            </div>
        </form>
    </div>
</div>

<div id="report-content">
    <!-- Resumen del período -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Ingresos del Período</h5>
                    <h3><?php echo formatCurrency($totalIncome); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Gastos del Período</h5>
                    <h3><?php echo formatCurrency($totalExpenses); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Balance del Período</h5>
                    <h3><?php echo formatCurrencyWithColor($periodBalance); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Balance Total</h5>
                    <h3><?php echo formatCurrencyWithColor($balance['balance']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-pie-chart"></i> Distribución de Ingresos</h5>
                </div>
                <div class="card-body">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-pie-chart"></i> Distribución de Gastos</h5>
                </div>
                <div class="card-body">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de categorías -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5><i class="bi bi-arrow-up-circle"></i> Ingresos por Categoría</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($incomeStats)): ?>
                        <p class="text-muted text-center">No hay ingresos en el período seleccionado.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Monto</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($incomeStats as $stat): ?>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $stat['category_color']; ?>">
                                                    <?php echo htmlspecialchars($stat['category_name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatCurrency($stat['total']); ?></td>
                                            <td><?php echo $totalIncome > 0 ? number_format(($stat['total'] / $totalIncome) * 100, 1) : 0; ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-success">
                                        <th>Total</th>
                                        <th><?php echo formatCurrency($totalIncome); ?></th>
                                        <th>100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5><i class="bi bi-arrow-down-circle"></i> Gastos por Categoría</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($expenseStats)): ?>
                        <p class="text-muted text-center">No hay gastos en el período seleccionado.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Monto</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expenseStats as $stat): ?>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $stat['category_color']; ?>">
                                                    <?php echo htmlspecialchars($stat['category_name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatCurrency($stat['total']); ?></td>
                                            <td><?php echo $totalExpenses > 0 ? number_format(($stat['total'] / $totalExpenses) * 100, 1) : 0; ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-danger">
                                        <th>Total</th>
                                        <th><?php echo formatCurrency($totalExpenses); ?></th>
                                        <th>100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de cuentas por pagar y cobrar -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-credit-card"></i> Resumen Cuentas por Pagar</h5>
                </div>
                <div class="card-body">
                    <?php
                    $totalPayable = 0;
                    $totalPaid = 0;
                    $overdue = 0;
                    
                    foreach ($accountsPayable as $account) {
                        $totalPayable += $account['total_amount'];
                        $totalPaid += $account['paid_amount'];
                        if ($account['status'] === 'overdue') {
                            $overdue += ($account['total_amount'] - $account['paid_amount']);
                        }
                    }
                    $pendingPayable = $totalPayable - $totalPaid;
                    ?>
                    <div class="row">
                        <div class="col-6">
                            <strong>Total por Pagar:</strong>
                            <div class="text-danger"><?php echo formatCurrency($totalPayable); ?></div>
                        </div>
                        <div class="col-6">
                            <strong>Total Pagado:</strong>
                            <div class="text-success"><?php echo formatCurrency($totalPaid); ?></div>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Pendiente:</strong>
                            <div class="text-warning"><?php echo formatCurrency($pendingPayable); ?></div>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Vencido:</strong>
                            <div class="text-danger"><?php echo formatCurrency($overdue); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-cash-stack"></i> Resumen Cuentas por Cobrar</h5>
                </div>
                <div class="card-body">
                    <?php
                    $totalReceivable = 0;
                    $totalReceived = 0;
                    $overdueReceivable = 0;
                    
                    foreach ($accountsReceivable as $account) {
                        $totalReceivable += $account['total_amount'];
                        $totalReceived += $account['received_amount'];
                        if ($account['status'] === 'overdue') {
                            $overdueReceivable += ($account['total_amount'] - $account['received_amount']);
                        }
                    }
                    $pendingReceivable = $totalReceivable - $totalReceived;
                    ?>
                    <div class="row">
                        <div class="col-6">
                            <strong>Total por Cobrar:</strong>
                            <div class="text-primary"><?php echo formatCurrency($totalReceivable); ?></div>
                        </div>
                        <div class="col-6">
                            <strong>Total Cobrado:</strong>
                            <div class="text-success"><?php echo formatCurrency($totalReceived); ?></div>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Pendiente:</strong>
                            <div class="text-warning"><?php echo formatCurrency($pendingReceivable); ?></div>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Vencido:</strong>
                            <div class="text-danger"><?php echo formatCurrency($overdueReceivable); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de tarjetas de crédito -->
    <?php if (!empty($creditCards)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-credit-card-2-front"></i> Resumen Tarjetas de Crédito</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tarjeta</th>
                                        <th>Límite</th>
                                        <th>Balance</th>
                                        <th>Disponible</th>
                                        <th>Utilización</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalLimit = 0;
                                    $totalBalance = 0;
                                    foreach ($creditCards as $card):
                                        $available = $card['credit_limit'] - $card['current_balance'];
                                        $utilization = ($card['current_balance'] / $card['credit_limit']) * 100;
                                        $totalLimit += $card['credit_limit'];
                                        $totalBalance += $card['current_balance'];
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($card['card_name']); ?></td>
                                            <td><?php echo formatCurrency($card['credit_limit']); ?></td>
                                            <td class="text-danger"><?php echo formatCurrency($card['current_balance']); ?></td>
                                            <td class="text-success"><?php echo formatCurrency($available); ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar <?php echo $utilization > 80 ? 'bg-danger' : ($utilization > 60 ? 'bg-warning' : 'bg-success'); ?>" 
                                                         style="width: <?php echo $utilization; ?>%">
                                                        <?php echo number_format($utilization, 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th>Total</th>
                                        <th><?php echo formatCurrency($totalLimit); ?></th>
                                        <th class="text-danger"><?php echo formatCurrency($totalBalance); ?></th>
                                        <th class="text-success"><?php echo formatCurrency($totalLimit - $totalBalance); ?></th>
                                        <th><?php echo number_format(($totalBalance / $totalLimit) * 100, 1); ?>%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Datos para gráficos
const incomeData = {
    labels: <?php echo json_encode(array_column($incomeStats, 'category_name')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($incomeStats, 'total')); ?>,
        backgroundColor: <?php echo json_encode(array_column($incomeStats, 'category_color')); ?>,
        borderWidth: 2
    }]
};

const expenseData = {
    labels: <?php echo json_encode(array_column($expenseStats, 'category_name')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($expenseStats, 'total')); ?>,
        backgroundColor: <?php echo json_encode(array_column($expenseStats, 'category_color')); ?>,
        borderWidth: 2
    }]
};

// Configuración común para los gráficos
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};

// Crear gráficos
const incomeCtx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(incomeCtx, {
    type: 'doughnut',
    data: incomeData,
    options: chartOptions
});

const expenseCtx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(expenseCtx, {
    type: 'doughnut',
    data: expenseData,
    options: chartOptions
});
</script>

<?php include 'includes/footer.php'; ?>
