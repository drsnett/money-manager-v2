<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Transaction {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function create($userId, $categoryId, $type, $amount, $description, $paymentMethod, $transactionDate, $bankAccountId = null) {
        $stmt = $this->db->prepare("INSERT INTO transactions (user_id, category_id, type, amount, description, payment_method, transaction_date, bank_account_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([$userId, $categoryId, $type, $amount, $description, $paymentMethod, $transactionDate, $bankAccountId]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function getByUser($userId, $limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT t.*, c.name as category_name, c.color as category_color, 
                       ba.bank_name, ba.account_name, 
                       CASE WHEN ba.bank_name IS NOT NULL THEN (ba.bank_name || ' - ' || ba.account_name) ELSE NULL END as bank_account_display
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                LEFT JOIN bank_accounts ba ON t.bank_account_id = ba.id
                WHERE t.user_id = ?";
        
        $params = [$userId];
        
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['payment_method'])) {
            $sql .= " AND t.payment_method = ?";
            $params[] = $filters['payment_method'];
        }
        
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT t.*, c.name as category_name FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $categoryId, $type, $amount, $description, $paymentMethod, $transactionDate, $bankAccountId = null) {
        $stmt = $this->db->prepare("UPDATE transactions SET category_id = ?, type = ?, amount = ?, description = ?, payment_method = ?, transaction_date = ?, bank_account_id = ? WHERE id = ?");
        
        try {
            $stmt->execute([$categoryId, $type, $amount, $description, $paymentMethod, $transactionDate, $bankAccountId, $id]);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function getBalance($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expenses
            FROM transactions 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_income' => $result['total_income'],
            'total_expenses' => $result['total_expenses'],
            'balance' => $result['total_income'] - $result['total_expenses']
        ];
    }
    
    public function getMonthlyStats($userId, $year, $month) {
        $stmt = $this->db->prepare("
            SELECT 
                type,
                COALESCE(SUM(amount), 0) as total
            FROM transactions 
            WHERE user_id = ? AND strftime('%Y', transaction_date) = ? AND strftime('%m', transaction_date) = ?
            GROUP BY type
        ");
        $stmt->execute([$userId, $year, sprintf('%02d', $month)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategoryStats($userId, $dateFrom = null, $dateTo = null) {
        $sql = "
            SELECT 
                c.name as category_name,
                c.color as category_color,
                t.type,
                COALESCE(SUM(t.amount), 0) as total
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ?
        ";
        
        $params = [$userId];
        
        if ($dateFrom) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " GROUP BY c.id, c.name, c.color, t.type ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function count($userId, $filters = []) {
        $sql = "SELECT COUNT(*) FROM transactions WHERE user_id = ?";
        $params = [$userId];
        
        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND transaction_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND transaction_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
?>
