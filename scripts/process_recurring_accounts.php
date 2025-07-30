<?php
/**
 * Script para procesar cuentas por pagar y por cobrar recurrentes
 * Este script debe ejecutarse diariamente mediante cron job
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/AccountPayable.php';
require_once __DIR__ . '/../classes/AccountReceivable.php';

class RecurringProcessor {
    private $db;
    private $accountPayable;
    private $accountReceivable;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->accountPayable = new AccountPayable();
        $this->accountReceivable = new AccountReceivable();
    }
    
    /**
     * Procesa todas las cuentas recurrentes
     */
    public function processAll() {
        $this->log("Iniciando procesamiento de cuentas recurrentes...");
        
        $processedPayable = $this->processRecurringPayable();
        $processedReceivable = $this->processRecurringReceivable();
        
        $total = $processedPayable + $processedReceivable;
        $this->log("Procesamiento completado. Total de cuentas generadas: {$total}");
        
        return $total;
    }
    
    /**
     * Procesa cuentas por pagar recurrentes
     */
    private function processRecurringPayable() {
        $sql = "SELECT * FROM accounts_payable
                WHERE is_recurring = 1
                AND status = 'paid'
                AND recurring_type IS NOT NULL
                AND due_date <= DATE('now')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $processed = 0;
        
        foreach ($accounts as $account) {
            if ($this->shouldCreateNewPayable($account)) {
                $this->createNewPayableAccount($account);
                $processed++;
            }
        }
        
        $this->log("Cuentas por pagar procesadas: {$processed}");
        return $processed;
    }
    
    /**
     * Procesa cuentas por cobrar recurrentes
     */
    private function processRecurringReceivable() {
        $sql = "SELECT * FROM accounts_receivable
                WHERE is_recurring = 1
                AND status = 'paid'
                AND recurring_type IS NOT NULL
                AND due_date <= DATE('now')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $processed = 0;
        
        foreach ($accounts as $account) {
            if ($this->shouldCreateNewReceivable($account)) {
                $this->createNewReceivableAccount($account);
                $processed++;
            }
        }
        
        $this->log("Cuentas por cobrar procesadas: {$processed}");
        return $processed;
    }
    
    /**
     * Determina si debe crear una nueva cuenta por pagar
     */
    private function shouldCreateNewPayable($account) {
        $nextDueDate = $this->calculateNextDueDate($account['due_date'], $account['recurring_type']);

        // Verificar si ya existe una cuenta para la próxima fecha
        $sql = "SELECT COUNT(*) FROM accounts_payable
                WHERE user_id = ?
                AND creditor_name = ?
                AND due_date = ?
                AND is_recurring = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$account['user_id'], $account['creditor_name'], $nextDueDate]);
        $exists = $stmt->fetchColumn() > 0;

        // Crear únicamente si no existe una cuenta con esa fecha
        return !$exists;
    }
    
    /**
     * Determina si debe crear una nueva cuenta por cobrar
     */
    private function shouldCreateNewReceivable($account) {
        $nextDueDate = $this->calculateNextDueDate($account['due_date'], $account['recurring_type']);

        // Verificar si ya existe una cuenta para la próxima fecha
        $sql = "SELECT COUNT(*) FROM accounts_receivable
                WHERE user_id = ?
                AND debtor_name = ?
                AND due_date = ?
                AND is_recurring = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$account['user_id'], $account['debtor_name'], $nextDueDate]);
        $exists = $stmt->fetchColumn() > 0;

        // Crear únicamente si no existe una cuenta con esa fecha
        return !$exists;
    }
    
    /**
     * Calcula la próxima fecha de vencimiento según el tipo de recurrencia
     */
    private function calculateNextDueDate($currentDueDate, $recurringType) {
        $date = new DateTime($currentDueDate);
        
        switch ($recurringType) {
            case 'weekly':
                $date->add(new DateInterval('P7D')); // +7 días
                break;
            case 'biweekly':
                $date->add(new DateInterval('P15D')); // +15 días
                break;
            case 'monthly':
                $date->add(new DateInterval('P1M')); // +1 mes
                break;
            default:
                throw new Exception("Tipo de recurrencia no válido: {$recurringType}");
        }
        
        return $date->format('Y-m-d');
    }
    
    /**
     * Crea una nueva cuenta por pagar recurrente
     */
    private function createNewPayableAccount($originalAccount) {
        $nextDueDate = $this->calculateNextDueDate($originalAccount['due_date'], $originalAccount['recurring_type']);
        
        try {
            $newId = $this->accountPayable->create(
                $originalAccount['user_id'],
                $originalAccount['creditor_name'],
                $originalAccount['description'] . ' (Recurrente)',
                $originalAccount['total_amount'],
                $nextDueDate,
                true, // is_recurring
                $originalAccount['recurring_type'],
                $originalAccount['bank_account_id']
            );
            
            $this->log("Nueva cuenta por pagar creada: ID {$newId}, Acreedor: {$originalAccount['creditor_name']}, Fecha: {$nextDueDate}");
            
        } catch (Exception $e) {
            $this->log("Error al crear cuenta por pagar: " . $e->getMessage());
        }
    }
    
    /**
     * Crea una nueva cuenta por cobrar recurrente
     */
    private function createNewReceivableAccount($originalAccount) {
        $nextDueDate = $this->calculateNextDueDate($originalAccount['due_date'], $originalAccount['recurring_type']);
        
        try {
            $newId = $this->accountReceivable->create(
                $originalAccount['user_id'],
                $originalAccount['debtor_name'],
                $originalAccount['description'] . ' (Recurrente)',
                $originalAccount['total_amount'],
                $nextDueDate,
                true, // is_recurring
                $originalAccount['recurring_type']
            );
            
            $this->log("Nueva cuenta por cobrar creada: ID {$newId}, Deudor: {$originalAccount['debtor_name']}, Fecha: {$nextDueDate}");
            
        } catch (Exception $e) {
            $this->log("Error al crear cuenta por cobrar: " . $e->getMessage());
        }
    }
    
    /**
     * Registra mensajes en el log
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Escribir al archivo de log
        $logFile = __DIR__ . '/../logs/recurring_process.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // También mostrar en consola si se ejecuta desde línea de comandos
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
}

// Ejecutar el procesamiento si se llama directamente
if (php_sapi_name() === 'cli' || basename($_SERVER['PHP_SELF']) === 'process_recurring_accounts.php') {
    try {
        $processor = new RecurringProcessor();
        $processed = $processor->processAll();
        
        if (php_sapi_name() !== 'cli') {
            echo json_encode([
                'success' => true,
                'processed' => $processed,
                'message' => "Procesamiento completado. {$processed} cuentas generadas."
            ]);
        }
        
    } catch (Exception $e) {
        $error = "Error en procesamiento de recurrencias: " . $e->getMessage();
        
        if (php_sapi_name() === 'cli') {
            echo $error . PHP_EOL;
        } else {
            echo json_encode([
                'success' => false,
                'error' => $error
            ]);
        }
    }
}
?>