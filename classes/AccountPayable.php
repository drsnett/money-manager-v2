<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AccountPayable {
    private $db;
    private $hasBankAccountColumn;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();

        try {
            $columns = $this->db->query("PRAGMA table_info(accounts_payable)")->fetchAll(PDO::FETCH_ASSOC);
            $this->hasBankAccountColumn = in_array('bank_account_id', array_column($columns, 'name'));
        } catch (PDOException $e) {
            $this->hasBankAccountColumn = false;
        }
    }
    
    public function create($userId, $creditorName, $description, $totalAmount, $dueDate, $isRecurring = false, $recurringType = null, $bankAccountId = null) {
        if ($this->hasBankAccountColumn) {
            $stmt = $this->db->prepare("INSERT INTO accounts_payable (user_id, creditor_name, description, total_amount, due_date, is_recurring, recurring_type, bank_account_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $params = [$userId, $creditorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType, $bankAccountId];
        } else {
            $stmt = $this->db->prepare("INSERT INTO accounts_payable (user_id, creditor_name, description, total_amount, due_date, is_recurring, recurring_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $params = [$userId, $creditorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType];
        }
        
        try {
            $stmt->execute($params);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function getByUser($userId, $status = null) {
        if ($this->hasBankAccountColumn) {
            $sql = "SELECT ap.*, ba.bank_name, ba.account_name,
                           CASE WHEN ba.bank_name IS NOT NULL THEN (ba.bank_name || ' - ' || ba.account_name) ELSE NULL END as bank_account_display
                    FROM accounts_payable ap
                    LEFT JOIN bank_accounts ba ON ap.bank_account_id = ba.id
                    WHERE ap.user_id = ?";
        } else {
            $sql = "SELECT ap.*, NULL as bank_name, NULL as account_name, NULL as bank_account_display
                    FROM accounts_payable ap 
                    WHERE ap.user_id = ?";
        }
        
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND ap.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ap.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM accounts_payable WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $creditorName, $description, $totalAmount, $dueDate, $isRecurring = false, $recurringType = null, $bankAccountId = null, $status = null) {
        if ($this->hasBankAccountColumn) {
            $sql = "UPDATE accounts_payable SET creditor_name = ?, description = ?, total_amount = ?, due_date = ?, is_recurring = ?, recurring_type = ?, bank_account_id = ?";
            $params = [$creditorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType, $bankAccountId];
        } else {
            $sql = "UPDATE accounts_payable SET creditor_name = ?, description = ?, total_amount = ?, due_date = ?, is_recurring = ?, recurring_type = ?";
            $params = [$creditorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType];
        }

        if ($status !== null) {
            $sql .= ", status = ?";
            $params[] = $status;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM accounts_payable WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function addPayment($accountId, $amount, $paymentDate, $paymentMethod, $notes = '', $bankAccountId = null, $creditCardId = null) {
        $this->db->beginTransaction();
        
        try {
            // Agregar el pago
            $stmt = $this->db->prepare("INSERT INTO payments (account_payable_id, amount, payment_date, payment_method, notes, bank_account_id, credit_card_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$accountId, $amount, $paymentDate, $paymentMethod, $notes, $bankAccountId, $creditCardId]);
            
            // Actualizar el monto pagado
            $stmt = $this->db->prepare("UPDATE accounts_payable SET paid_amount = paid_amount + ? WHERE id = ?");
            $stmt->execute([$amount, $accountId]);
            
            // Actualizar el estado
            $this->updateStatus($accountId);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getPayments($accountId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE account_payable_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$accountId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPaymentById($paymentId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deletePayment($paymentId) {
        $this->db->beginTransaction();
        
        try {
            // Obtener información del pago
            $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$payment) {
                throw new Exception("Pago no encontrado");
            }
            
            // Eliminar el pago
            $stmt = $this->db->prepare("DELETE FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            
            // Actualizar el monto pagado
            $stmt = $this->db->prepare("UPDATE accounts_payable SET paid_amount = paid_amount - ? WHERE id = ?");
            $stmt->execute([$payment['amount'], $payment['account_payable_id']]);
            
            // Actualizar el estado
            $this->updateStatus($payment['account_payable_id']);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function updateStatus($accountId) {
        $stmt = $this->db->prepare("SELECT total_amount, paid_amount, due_date FROM accounts_payable WHERE id = ?");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) return;
        
        $status = 'pending';
        $remaining = $account['total_amount'] - $account['paid_amount'];
        
        if ($remaining <= 0) {
            $status = 'paid';
        } elseif ($account['paid_amount'] > 0) {
            $status = 'partial';
        } elseif (date('Y-m-d') > $account['due_date']) {
            $status = 'overdue';
        }
        
        $stmt = $this->db->prepare("UPDATE accounts_payable SET status = ? WHERE id = ?");
        $stmt->execute([$status, $accountId]);
    }
    
    public function getUpcoming($userId, $days = 7) {
        $stmt = $this->db->prepare("
            SELECT * FROM accounts_payable 
            WHERE user_id = ? 
            AND status IN ('pending', 'partial') 
            AND due_date BETWEEN DATE('now') AND DATE('now', '+{$days} days')
            ORDER BY due_date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getOverdue($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM accounts_payable 
            WHERE user_id = ? 
            AND status IN ('pending', 'partial') 
            AND due_date < DATE('now')
            ORDER BY due_date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateAllStatuses() {
        $stmt = $this->db->prepare("SELECT id FROM accounts_payable WHERE status != 'paid'");
        $stmt->execute();
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($accounts as $account) {
            $this->updateStatus($account['id']);
        }
    }

    private function calculateNextDueDate($currentDueDate, $recurringType) {
        $date = new DateTime($currentDueDate);

        switch ($recurringType) {
            case 'weekly':
                $date->add(new DateInterval('P7D'));
                break;
            case 'biweekly':
                $date->add(new DateInterval('P15D'));
                break;
            case 'monthly':
                $date->add(new DateInterval('P1M'));
                break;
            default:
                throw new Exception("Tipo de recurrencia no válido: {$recurringType}");
        }

        return $date->format('Y-m-d');
    }

    public function createNextRecurring($accountId) {
        $account = $this->getById($accountId);
        if (!$account || !$account['is_recurring'] || !$account['recurring_type']) {
            return null;
        }

        $nextDueDate = $this->calculateNextDueDate($account['due_date'], $account['recurring_type']);

        return $this->create(
            $account['user_id'],
            $account['creditor_name'],
            $account['description'] . ' (Recurrente)',
            $account['total_amount'],
            $nextDueDate,
            true,
            $account['recurring_type'],
            $account['bank_account_id'] ?? null
        );
    }
}
