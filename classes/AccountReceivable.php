<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AccountReceivable {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function create($userId, $debtorName, $description, $totalAmount, $dueDate, $isRecurring = false, $recurringType = null) {
        $stmt = $this->db->prepare("INSERT INTO accounts_receivable (user_id, debtor_name, description, total_amount, due_date, is_recurring, recurring_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([$userId, $debtorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function getByUser($userId, $status = null) {
        $sql = "SELECT * FROM accounts_receivable WHERE user_id = ?";
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM accounts_receivable WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $debtorName, $description, $totalAmount, $dueDate, $isRecurring = false, $recurringType = null, $status = null) {
        $sql = "UPDATE accounts_receivable SET debtor_name = ?, description = ?, total_amount = ?, due_date = ?, is_recurring = ?, recurring_type = ?";
        $params = [$debtorName, $description, $totalAmount, $dueDate, $isRecurring, $recurringType];

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
        $stmt = $this->db->prepare("DELETE FROM accounts_receivable WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function addReceipt($accountId, $amount, $paymentDate, $paymentMethod, $notes = '') {
        $this->db->beginTransaction();
        
        try {
            // Agregar el cobro
            $stmt = $this->db->prepare("INSERT INTO receipts (account_receivable_id, amount, payment_date, payment_method, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$accountId, $amount, $paymentDate, $paymentMethod, $notes]);
            
            // Actualizar el monto cobrado
            $stmt = $this->db->prepare("UPDATE accounts_receivable SET received_amount = received_amount + ? WHERE id = ?");
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
    
    public function getReceipts($accountId) {
        $stmt = $this->db->prepare("SELECT * FROM receipts WHERE account_receivable_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$accountId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getReceiptById($receiptId) {
        $stmt = $this->db->prepare("SELECT * FROM receipts WHERE id = ?");
        $stmt->execute([$receiptId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deleteReceipt($receiptId) {
        $this->db->beginTransaction();
        
        try {
            // Obtener información del cobro
            $stmt = $this->db->prepare("SELECT * FROM receipts WHERE id = ?");
            $stmt->execute([$receiptId]);
            $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$receipt) {
                throw new Exception("Cobro no encontrado");
            }
            
            // Eliminar el cobro
            $stmt = $this->db->prepare("DELETE FROM receipts WHERE id = ?");
            $stmt->execute([$receiptId]);
            
            // Actualizar el monto cobrado
            $stmt = $this->db->prepare("UPDATE accounts_receivable SET received_amount = received_amount - ? WHERE id = ?");
            $stmt->execute([$receipt['amount'], $receipt['account_receivable_id']]);
            
            // Actualizar el estado
            $this->updateStatus($receipt['account_receivable_id']);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function updateStatus($accountId) {
        $stmt = $this->db->prepare("SELECT total_amount, received_amount, due_date FROM accounts_receivable WHERE id = ?");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) return;
        
        $status = 'pending';
        $remaining = $account['total_amount'] - $account['received_amount'];
        
        if ($remaining <= 0) {
            $status = 'paid';
        } elseif ($account['received_amount'] > 0) {
            $status = 'partial';
        } elseif (date('Y-m-d') > $account['due_date']) {
            $status = 'overdue';
        }
        
        $stmt = $this->db->prepare("UPDATE accounts_receivable SET status = ? WHERE id = ?");
        $stmt->execute([$status, $accountId]);
    }
    
    public function getUpcoming($userId, $days = 7) {
        $stmt = $this->db->prepare("
            SELECT * FROM accounts_receivable 
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
            SELECT * FROM accounts_receivable 
            WHERE user_id = ? 
            AND status IN ('pending', 'partial') 
            AND due_date < DATE('now')
            ORDER BY due_date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateAllStatuses() {
        $stmt = $this->db->prepare("SELECT id FROM accounts_receivable WHERE status != 'paid'");
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
            $account['debtor_name'],
            $account['description'] . ' (Recurrente)',
            $account['total_amount'],
            $nextDueDate,
            true,
            $account['recurring_type']
        );
    }
}
