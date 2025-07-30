<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Debt {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function create($userId, $creditorName, $description, $principalAmount, $monthlyInterestRate, $startDate, $dueDate = null) {
        $stmt = $this->db->prepare("
            INSERT INTO debts (user_id, creditor_name, description, principal_amount, monthly_interest_rate, start_date, due_date, current_balance) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([$userId, $creditorName, $description, $principalAmount, $monthlyInterestRate, $startDate, $dueDate, $principalAmount]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function getByUser($userId, $status = null) {
        $sql = "SELECT * FROM debts WHERE user_id = ?";
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM debts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $creditorName, $description, $principalAmount, $monthlyInterestRate, $startDate, $dueDate = null) {
        $stmt = $this->db->prepare("
            UPDATE debts SET 
                creditor_name = ?, 
                description = ?, 
                principal_amount = ?, 
                monthly_interest_rate = ?, 
                start_date = ?, 
                due_date = ?
            WHERE id = ?
        ");
        
        try {
            $stmt->execute([$creditorName, $description, $principalAmount, $monthlyInterestRate, $startDate, $dueDate, $id]);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM debts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function calculateCurrentBalance($debtId) {
        $debt = $this->getById($debtId);
        if (!$debt) return 0;
        
        // Obtener pagos realizados
        $totalPayments = $this->getTotalPayments($debtId);
        
        // Para deudas simples: Balance = Capital inicial - Pagos realizados
        // Si hay interés mensual, se puede calcular el interés acumulado
        $startDate = new DateTime($debt['start_date']);
        $currentDate = new DateTime();
        $monthsDiff = $startDate->diff($currentDate)->m + ($startDate->diff($currentDate)->y * 12);
        
        $currentBalance = $debt['principal_amount'];
        
        // Si hay interés y han pasado meses, calcular interés acumulado
        if ($debt['monthly_interest_rate'] > 0 && $monthsDiff > 0) {
            // Calcular interés compuesto sobre el balance restante
            $monthlyRate = $debt['monthly_interest_rate'] / 100;
            $currentBalance = $debt['principal_amount'] * pow(1 + $monthlyRate, $monthsDiff);
        }
        
        // Restar pagos realizados
        $currentBalance = $currentBalance - $totalPayments;
        
        // El balance no puede ser negativo
        $currentBalance = max(0, $currentBalance);
        
        // Actualizar en la base de datos
        $this->updateCurrentBalance($debtId, $currentBalance);
        
        return $currentBalance;
    }
    
    public function updateCurrentBalance($debtId, $balance) {
        $stmt = $this->db->prepare("UPDATE debts SET current_balance = ? WHERE id = ?");
        $stmt->execute([$balance, $debtId]);
    }
    
    public function getTotalPayments($debtId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM debt_payments WHERE debt_id = ?");
        $stmt->execute([$debtId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function addPayment($debtId, $amount, $paymentDate, $paymentMethod = 'cash', $notes = '') {
        $stmt = $this->db->prepare("
            INSERT INTO debt_payments (debt_id, amount, payment_date, payment_method, notes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([$debtId, $amount, $paymentDate, $paymentMethod, $notes]);
            
            // Recalcular balance actual
            $newBalance = $this->calculateCurrentBalance($debtId);
            
            // Actualizar estado basado en el balance calculado
            if ($newBalance <= 0) {
                $this->updateStatus($debtId, 'paid');
            } else {
                $this->updateStatus($debtId, 'active');
            }
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function getPayments($debtId) {
        $stmt = $this->db->prepare("SELECT * FROM debt_payments WHERE debt_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$debtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deletePayment($paymentId) {
        // Obtener información del pago antes de eliminarlo
        $stmt = $this->db->prepare("SELECT debt_id FROM debt_payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) return false;
        
        // Eliminar el pago
        $stmt = $this->db->prepare("DELETE FROM debt_payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        
        // Recalcular balance y actualizar estado
        $newBalance = $this->calculateCurrentBalance($payment['debt_id']);
        
        // Actualizar estado basado en el balance calculado
        if ($newBalance <= 0) {
            $this->updateStatus($payment['debt_id'], 'paid');
        } else {
            $this->updateStatus($payment['debt_id'], 'active');
        }
        
        return $stmt->rowCount() > 0;
    }
    
    public function updateStatus($debtId, $status) {
        $stmt = $this->db->prepare("UPDATE debts SET status = ? WHERE id = ?");
        $stmt->execute([$status, $debtId]);
    }
    
    public function getOverdue($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM debts 
            WHERE user_id = ? AND due_date < DATE('now') AND status = 'active'
            ORDER BY due_date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUpcoming($userId, $days = 30) {
        $stmt = $this->db->prepare("
            SELECT * FROM debts 
            WHERE user_id = ? AND due_date BETWEEN DATE('now') AND DATE('now', '+' || ? || ' days') AND status = 'active'
            ORDER BY due_date ASC
        ");
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalDebtsByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN status = 'active' THEN current_balance ELSE 0 END), 0) as total_active_debt,
                COALESCE(SUM(principal_amount), 0) as total_principal,
                COUNT(*) as total_debts,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_debts
            FROM debts 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function calculateAmortization($principal, $monthlyInterestRate, $months) {
        if ($months <= 0) return [];
        
        $monthlyRate = $monthlyInterestRate / 100;
        $monthlyPayment = $this->calculateMonthlyPayment($principal, $monthlyRate, $months);
        
        $amortization = [];
        $remainingBalance = $principal;
        
        for ($i = 1; $i <= $months; $i++) {
            $interestPayment = $remainingBalance * $monthlyRate;
            $principalPayment = $monthlyPayment - $interestPayment;
            $remainingBalance -= $principalPayment;
            
            // Ajustar último pago para evitar saldos negativos por redondeo
            if ($i == $months && $remainingBalance < 0.01) {
                $principalPayment += $remainingBalance;
                $remainingBalance = 0;
                $monthlyPayment = $principalPayment + $interestPayment;
            }
            
            $amortization[] = [
                'month' => $i,
                'monthly_payment' => round($monthlyPayment, 2),
                'principal_payment' => round($principalPayment, 2),
                'interest_payment' => round($interestPayment, 2),
                'remaining_balance' => round(max(0, $remainingBalance), 2),
                'date' => date('Y-m-d', strtotime("+{$i} months"))
            ];
        }
        
        return $amortization;
    }
    
    public function calculateMonthlyPayment($principal, $monthlyRate, $months) {
        if ($monthlyRate == 0) {
            return $principal / $months;
        }
        
        return $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
    }
    
    public function getAmortizationTable($debtId, $customMonths = null) {
        $debt = $this->getById($debtId);
        if (!$debt) return [];
        
        $months = $customMonths ?: 12; // Por defecto 12 meses si no se especifica
        
        // Usar el balance actual actualizado en lugar del monto principal original
        $currentBalance = $this->calculateCurrentBalance($debtId);
        
        // Si el balance actual es 0 o negativo, no hay nada que amortizar
        if ($currentBalance <= 0) {
            return [];
        }
        
        return $this->calculateAmortization(
            $currentBalance, 
            $debt['monthly_interest_rate'], 
            $months
        );
    }
    
    public function getMonthlyInterestProjection($debtId, $months = 12) {
        return $this->getAmortizationTable($debtId, $months);
    }
    
    public function updateAllBalances($userId) {
        $debts = $this->getByUser($userId, 'active');
        foreach ($debts as $debt) {
            $this->calculateCurrentBalance($debt['id']);
        }
    }
    
    // Métodos adicionales para compatibilidad con dashboard
    public function getOverdueDebts($userId) {
        return $this->getOverdue($userId);
    }
    
    public function getUpcomingDebts($userId, $days = 7) {
        return $this->getUpcoming($userId, $days);
    }
    
    public function getDebtStats($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
                COALESCE(SUM(CASE WHEN status = 'active' THEN current_balance ELSE 0 END), 0) as total_current_balance,
                COALESCE(SUM(CASE WHEN status = 'active' THEN principal_amount ELSE 0 END), 0) as total_principal,
                COALESCE(SUM(CASE WHEN status = 'active' THEN (current_balance * monthly_interest_rate / 100) ELSE 0 END), 0) as monthly_interest,
                COUNT(*) as total_count
            FROM debts 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>