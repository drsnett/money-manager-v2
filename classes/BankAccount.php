<?php
require_once 'config/database.php';

class BankAccount {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Crear nueva cuenta bancaria
    public function create($userId, $bankName, $accountName, $accountNumber, $accountType, $currentBalance = 0, $currency = 'USD', $notes = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO bank_accounts (user_id, bank_name, account_name, account_number, account_type, current_balance, currency, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([$userId, $bankName, $accountName, $accountNumber, $accountType, $currentBalance, $currency, $notes]);
        } catch (PDOException $e) {
            error_log("Error creating bank account: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener todas las cuentas bancarias de un usuario
    public function getByUserId($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM bank_accounts 
                WHERE user_id = ? 
                ORDER BY is_active DESC, bank_name ASC, account_name ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching bank accounts: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtener cuenta bancaria por ID
    public function getById($id, $userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM bank_accounts 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching bank account: " . $e->getMessage());
            return false;
        }
    }
    
    // Actualizar cuenta bancaria
    public function update($id, $userId, $bankName, $accountName, $accountNumber, $accountType, $currentBalance, $currency, $notes, $isActive) {
        try {
            $stmt = $this->db->prepare("
                UPDATE bank_accounts 
                SET bank_name = ?, account_name = ?, account_number = ?, account_type = ?, 
                    current_balance = ?, currency = ?, notes = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?
            ");
            
            return $stmt->execute([$bankName, $accountName, $accountNumber, $accountType, $currentBalance, $currency, $notes, $isActive, $id, $userId]);
        } catch (PDOException $e) {
            error_log("Error updating bank account: " . $e->getMessage());
            return false;
        }
    }
    
    // Eliminar cuenta bancaria
    public function delete($id, $userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM bank_accounts WHERE id = ? AND user_id = ?");
            return $stmt->execute([$id, $userId]);
        } catch (PDOException $e) {
            error_log("Error deleting bank account: " . $e->getMessage());
            return false;
        }
    }
    
    // Actualizar balance de cuenta
    public function updateBalance($id, $userId, $newBalance) {
        try {
            $stmt = $this->db->prepare("
                UPDATE bank_accounts 
                SET current_balance = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$newBalance, $id, $userId]);
        } catch (PDOException $e) {
            error_log("Error updating bank account balance: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener estadísticas de cuentas bancarias
    public function getStats($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_accounts,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_accounts,
                    SUM(CASE WHEN is_active = 1 THEN current_balance ELSE 0 END) as total_balance,
                    AVG(CASE WHEN is_active = 1 THEN current_balance ELSE NULL END) as average_balance
                FROM bank_accounts 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching bank account stats: " . $e->getMessage());
            return [
                'total_accounts' => 0,
                'active_accounts' => 0,
                'total_balance' => 0,
                'average_balance' => 0
            ];
        }
    }
    
    // Obtener cuentas por tipo
    public function getByType($userId, $accountType) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM bank_accounts 
                WHERE user_id = ? AND account_type = ? AND is_active = 1
                ORDER BY bank_name ASC, account_name ASC
            ");
            $stmt->execute([$userId, $accountType]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching bank accounts by type: " . $e->getMessage());
            return [];
        }
    }
    
    // Verificar si el número de cuenta ya existe para el usuario
    public function accountNumberExists($userId, $accountNumber, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM bank_accounts WHERE user_id = ? AND account_number = ?";
            $params = [$userId, $accountNumber];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking account number: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener tipos de cuenta disponibles
    public function getAccountTypes() {
        return [
            'checking' => 'Cuenta Corriente',
            'savings' => 'Cuenta de Ahorros',
            'credit' => 'Línea de Crédito',
            'investment' => 'Cuenta de Inversión'
        ];
    }
    
    // Obtener monedas disponibles
    public function getCurrencies() {
        return [
            'USD' => 'Dólar Estadounidense (USD)',
            'DOP' => 'Peso Dominicano (DOP)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'Libra Esterlina (GBP)',
            'CAD' => 'Dólar Canadiense (CAD)',
            'AUD' => 'Dólar Australiano (AUD)',
            'JPY' => 'Yen Japonés (JPY)',
            'CHF' => 'Franco Suizo (CHF)',
            'CNY' => 'Yuan Chino (CNY)',
            'MXN' => 'Peso Mexicano (MXN)',
            'BRL' => 'Real Brasileño (BRL)'
        ];
    }
}
?>