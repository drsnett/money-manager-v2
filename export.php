<?php
require_once 'config/config.php';
require_once 'classes/Transaction.php';
require_once 'classes/AccountPayable.php';
require_once 'classes/AccountReceivable.php';
require_once 'classes/CreditCard.php';
require_once 'classes/Category.php';

requireLogin();

$userId = getCurrentUserId();
$exportType = $_GET['export'] ?? 'csv';
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Instanciar clases
$transaction = new Transaction();
$accountPayable = new AccountPayable();
$accountReceivable = new AccountReceivable();
$creditCard = new CreditCard();
$category = new Category();

// Obtener datos
$transactions = $transaction->getByUser($userId, null, 0, [
    'date_from' => $dateFrom,
    'date_to' => $dateTo
]);

$accountsPayable = $accountPayable->getByUser($userId);
$accountsReceivable = $accountReceivable->getByUser($userId);
$creditCards = $creditCard->getByUser($userId);
$categories = $category->getByUser($userId);

// Calcular totales
$totalIncome = 0;
$totalExpenses = 0;
$totalPayable = 0;
$totalReceivable = 0;
$totalCreditCardDebt = 0;

foreach ($transactions as $trans) {
    if ($trans['type'] === 'income') {
        $totalIncome += $trans['amount'];
    } else {
        $totalExpenses += $trans['amount'];
    }
}

foreach ($accountsPayable as $account) {
    $totalPayable += ($account['total_amount'] - $account['paid_amount']);
}

foreach ($accountsReceivable as $account) {
    $totalReceivable += ($account['total_amount'] - $account['received_amount']);
}

foreach ($creditCards as $card) {
    $totalCreditCardDebt += $card['current_balance'];
}

switch ($exportType) {
    case 'csv':
        exportCSV($transactions, $dateFrom, $dateTo);
        break;
    case 'excel':
        exportExcel($transactions, $accountsPayable, $accountsReceivable, $creditCards, $dateFrom, $dateTo);
        break;
    case 'pdf':
        exportPDF($transactions, $accountsPayable, $accountsReceivable, $creditCards, $totalIncome, $totalExpenses, $totalPayable, $totalReceivable, $totalCreditCardDebt, $dateFrom, $dateTo);
        break;
    default:
        header('Location: reports.php');
        exit;
}

function exportCSV($transactions, $dateFrom, $dateTo) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fputs($output, "\xEF\xBB\xBF");
    
    // Cabeceras
    fputcsv($output, ['Tipo', 'Categoría', 'Descripción', 'Monto', 'Fecha']);
    
    // Datos de transacciones
    foreach ($transactions as $trans) {
        fputcsv($output, [
            $trans['type'] === 'income' ? 'Ingreso' : 'Gasto',
            $trans['category_name'],
            $trans['description'],
            $trans['amount'],
            $trans['transaction_date']
        ]);
    }
    
    fclose($output);
}

function exportExcel($transactions, $accountsPayable, $accountsReceivable, $creditCards, $dateFrom, $dateTo) {
    // Crear archivo Excel básico usando HTML
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_' . date('Y-m-d') . '.xls"');
    
    echo "\xEF\xBB\xBF"; // BOM UTF-8
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head>';
    echo '<body>';
    
    echo '<h1>Reporte Financiero</h1>';
    echo '<p>Período: ' . formatDate($dateFrom) . ' - ' . formatDate($dateTo) . '</p>';
    
    // Transacciones
    echo '<h2>Transacciones</h2>';
    echo '<table border="1">';
    echo '<tr><th>Tipo</th><th>Categoría</th><th>Descripción</th><th>Monto</th><th>Fecha</th></tr>';
    
    foreach ($transactions as $trans) {
        echo '<tr>';
        echo '<td>' . ($trans['type'] === 'income' ? 'Ingreso' : 'Gasto') . '</td>';
        echo '<td>' . htmlspecialchars($trans['category_name']) . '</td>';
        echo '<td>' . htmlspecialchars($trans['description']) . '</td>';
        echo '<td>' . $trans['amount'] . '</td>';
        echo '<td>' . $trans['transaction_date'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // Cuentas por pagar
    echo '<h2>Cuentas por Pagar</h2>';
    echo '<table border="1">';
    echo '<tr><th>Acreedor</th><th>Descripción</th><th>Total</th><th>Pagado</th><th>Pendiente</th><th>Vencimiento</th><th>Estado</th></tr>';
    
    foreach ($accountsPayable as $account) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($account['creditor_name']) . '</td>';
        echo '<td>' . htmlspecialchars($account['description']) . '</td>';
        echo '<td>' . $account['total_amount'] . '</td>';
        echo '<td>' . $account['paid_amount'] . '</td>';
        echo '<td>' . ($account['total_amount'] - $account['paid_amount']) . '</td>';
        echo '<td>' . $account['due_date'] . '</td>';
        echo '<td>' . ucfirst($account['status']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // Cuentas por cobrar
    echo '<h2>Cuentas por Cobrar</h2>';
    echo '<table border="1">';
    echo '<tr><th>Deudor</th><th>Descripción</th><th>Total</th><th>Cobrado</th><th>Pendiente</th><th>Vencimiento</th><th>Estado</th></tr>';
    
    foreach ($accountsReceivable as $account) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($account['debtor_name']) . '</td>';
        echo '<td>' . htmlspecialchars($account['description']) . '</td>';
        echo '<td>' . $account['total_amount'] . '</td>';
        echo '<td>' . $account['received_amount'] . '</td>';
        echo '<td>' . ($account['total_amount'] - $account['received_amount']) . '</td>';
        echo '<td>' . $account['due_date'] . '</td>';
        echo '<td>' . ucfirst($account['status']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // Tarjetas de crédito
    echo '<h2>Tarjetas de Crédito</h2>';
    echo '<table border="1">';
    echo '<tr><th>Tarjeta</th><th>Límite</th><th>Balance Actual</th><th>Disponible</th><th>Día de Corte</th><th>Día de Pago</th></tr>';
    
    foreach ($creditCards as $card) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($card['card_name']) . '</td>';
        echo '<td>' . $card['credit_limit'] . '</td>';
        echo '<td>' . $card['current_balance'] . '</td>';
        echo '<td>' . ($card['credit_limit'] - $card['current_balance']) . '</td>';
        echo '<td>' . $card['cut_off_date'] . '</td>';
        echo '<td>' . $card['payment_due_date'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</body></html>';
}

function exportPDF($transactions, $accountsPayable, $accountsReceivable, $creditCards, $totalIncome, $totalExpenses, $totalPayable, $totalReceivable, $totalCreditCardDebt, $dateFrom, $dateTo) {
    // Crear PDF básico usando HTML con estilos CSS que se convierten bien a PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_' . date('Y-m-d') . '.pdf"');
    
    // Para convertir a PDF real, necesitarías una librería como TCPDF o mPDF
    // Por ahora, generamos un HTML que se puede imprimir/guardar como PDF
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte Financiero</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 20px; }
            .period { text-align: center; color: #666; margin-bottom: 30px; }
            .summary { background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            .summary-item { display: inline-block; margin-right: 30px; }
            .summary-item strong { color: #333; }
            .income { color: #28a745; }
            .expense { color: #dc3545; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f8f9fa; font-weight: bold; }
            .section-title { margin-top: 30px; margin-bottom: 15px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .status-paid { color: #28a745; }
            .status-pending { color: #ffc107; }
            .status-overdue { color: #dc3545; }
            .status-partial { color: #17a2b8; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Dev Network Solutions - Reporte Financiero</h1>
        </div>
        
        <div class="period">
            Período: <?php echo formatDate($dateFrom) . ' - ' . formatDate($dateTo); ?>
        </div>
        
        <div class="summary">
            <div class="summary-item">
                <strong>Total Ingresos:</strong> <span class="income"><?php echo formatCurrency($totalIncome); ?></span>
            </div>
            <div class="summary-item">
                <strong>Total Gastos:</strong> <span class="expense"><?php echo formatCurrency($totalExpenses); ?></span>
            </div>
            <div class="summary-item">
                <strong>Balance:</strong> <span class="<?php echo ($totalIncome - $totalExpenses) >= 0 ? 'income' : 'expense'; ?>"><?php echo formatCurrency($totalIncome - $totalExpenses); ?></span>
            </div>
        </div>
        
        <div class="summary">
            <div class="summary-item">
                <strong>Por Pagar:</strong> <span class="expense"><?php echo formatCurrency($totalPayable); ?></span>
            </div>
            <div class="summary-item">
                <strong>Por Cobrar:</strong> <span class="income"><?php echo formatCurrency($totalReceivable); ?></span>
            </div>
            <div class="summary-item">
                <strong>Deuda Tarjetas:</strong> <span class="expense"><?php echo formatCurrency($totalCreditCardDebt); ?></span>
            </div>
        </div>
        
        <h2 class="section-title">Transacciones</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $trans): ?>
                <tr>
                    <td><?php echo formatDate($trans['transaction_date']); ?></td>
                    <td><?php echo $trans['type'] === 'income' ? 'Ingreso' : 'Gasto'; ?></td>
                    <td><?php echo htmlspecialchars($trans['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($trans['description']); ?></td>
                    <td class="text-right <?php echo $trans['type'] === 'income' ? 'income' : 'expense'; ?>">
                        <?php echo formatCurrency($trans['amount']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2 class="section-title">Cuentas por Pagar</h2>
        <table>
            <thead>
                <tr>
                    <th>Acreedor</th>
                    <th>Descripción</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Pagado</th>
                    <th class="text-right">Pendiente</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accountsPayable as $account): ?>
                <tr>
                    <td><?php echo htmlspecialchars($account['creditor_name']); ?></td>
                    <td><?php echo htmlspecialchars($account['description']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($account['total_amount']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($account['paid_amount']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($account['total_amount'] - $account['paid_amount']); ?></td>
                    <td><?php echo formatDate($account['due_date']); ?></td>
                    <td class="status-<?php echo $account['status']; ?>"><?php echo ucfirst($account['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2 class="section-title">Cuentas por Cobrar</h2>
        <table>
            <thead>
                <tr>
                    <th>Deudor</th>
                    <th>Descripción</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Cobrado</th>
                    <th class="text-right">Pendiente</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accountsReceivable as $account): ?>
                <tr>
                    <td><?php echo htmlspecialchars($account['debtor_name']); ?></td>
                    <td><?php echo htmlspecialchars($account['description']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($account['total_amount']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($account['received_amount']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($account['total_amount'] - $account['received_amount']); ?></td>
                    <td><?php echo formatDate($account['due_date']); ?></td>
                    <td class="status-<?php echo $account['status']; ?>"><?php echo ucfirst($account['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2 class="section-title">Tarjetas de Crédito</h2>
        <table>
            <thead>
                <tr>
                    <th>Tarjeta</th>
                    <th class="text-right">Límite</th>
                    <th class="text-right">Balance Actual</th>
                    <th class="text-right">Disponible</th>
                    <th>Día de Corte</th>
                    <th>Día de Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($creditCards as $card): ?>
                <tr>
                    <td><?php echo htmlspecialchars($card['card_name']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($card['credit_limit']); ?></td>
                    <td class="text-right expense"><?php echo formatCurrency($card['current_balance']); ?></td>
                    <td class="text-right income"><?php echo formatCurrency($card['credit_limit'] - $card['current_balance']); ?></td>
                    <td><?php echo $card['cut_off_date']; ?></td>
                    <td><?php echo $card['payment_due_date']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">
            Generado el <?php echo date('d/m/Y H:i:s'); ?> por Dev Network Solutions
        </div>
        
        <script>
            // Auto-print cuando se abre el PDF
            window.onload = function() {
                window.print();
            };
        </script>
    </body>
    </html>
    <?php
}
?>
