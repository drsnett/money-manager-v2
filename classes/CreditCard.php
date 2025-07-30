<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class CreditCard {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function create($userId, $cardName, $cardNumber, $creditLimit, $cutOffDate, $paymentDueDate, $minimumPaymentPercentage = 5.00, $currency = 'USD', $cardColor = '#007bff') {
        $stmt = $this->db->prepare("INSERT INTO credit_cards (user_id, card_name, card_number, credit_limit, cut_off_date, payment_due_date, minimum_payment_percentage, currency, card_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([$userId, $cardName, $cardNumber, $creditLimit, $cutOffDate, $paymentDueDate, $minimumPaymentPercentage, $currency, $cardColor]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM credit_cards WHERE user_id = ? ORDER BY card_name");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM credit_cards WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $cardName, $cardNumber, $creditLimit, $cutOffDate, $paymentDueDate, $minimumPaymentPercentage, $currency = 'USD', $cardColor = '#007bff') {
        $stmt = $this->db->prepare("UPDATE credit_cards SET card_name = ?, card_number = ?, credit_limit = ?, cut_off_date = ?, payment_due_date = ?, minimum_payment_percentage = ?, currency = ?, card_color = ? WHERE id = ?");
        
        try {
            $stmt->execute([$cardName, $cardNumber, $creditLimit, $cutOffDate, $paymentDueDate, $minimumPaymentPercentage, $currency, $cardColor, $id]);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function updateColor($id, $cardColor) {
        $stmt = $this->db->prepare("UPDATE credit_cards SET card_color = ? WHERE id = ?");
        
        try {
            $stmt->execute([$cardColor, $id]);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM credit_cards WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function addTransaction($cardId, $type, $amount, $description, $transactionDate) {
        $this->db->beginTransaction();
        
        try {
            // Agregar la transacción
            $stmt = $this->db->prepare("INSERT INTO credit_card_transactions (credit_card_id, type, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$cardId, $type, $amount, $description, $transactionDate]);
            
            // Actualizar el balance
            if ($type === 'charge') {
                $stmt = $this->db->prepare("UPDATE credit_cards SET current_balance = current_balance + ? WHERE id = ?");
            } else {
                $stmt = $this->db->prepare("UPDATE credit_cards SET current_balance = current_balance - ? WHERE id = ?");
            }
            $stmt->execute([$amount, $cardId]);
            
            // Si es un pago, marcar la tarjeta como activa (quitar estado vencido)
            if ($type === 'payment') {
                $stmt = $this->db->prepare("UPDATE credit_cards SET status = 'active' WHERE id = ? AND status = 'overdue'");
                $stmt->execute([$cardId]);
            }
            
            $this->db->commit();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getTransactions($cardId, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM credit_card_transactions WHERE credit_card_id = ? ORDER BY transaction_date DESC, created_at DESC";
        $params = [$cardId];
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTransactionById($transactionId) {
        $stmt = $this->db->prepare("SELECT * FROM credit_card_transactions WHERE id = ?");
        $stmt->execute([$transactionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deleteTransaction($transactionId) {
        $this->db->beginTransaction();
        
        try {
            // Obtener información de la transacción
            $stmt = $this->db->prepare("SELECT * FROM credit_card_transactions WHERE id = ?");
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transaction) {
                throw new Exception("Transacción no encontrada");
            }
            
            // Eliminar la transacción
            $stmt = $this->db->prepare("DELETE FROM credit_card_transactions WHERE id = ?");
            $stmt->execute([$transactionId]);
            
            // Actualizar el balance
            if ($transaction['type'] === 'charge') {
                $stmt = $this->db->prepare("UPDATE credit_cards SET current_balance = current_balance - ? WHERE id = ?");
            } else {
                $stmt = $this->db->prepare("UPDATE credit_cards SET current_balance = current_balance + ? WHERE id = ?");
            }
            $stmt->execute([$transaction['amount'], $transaction['credit_card_id']]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getAvailableCredit($cardId) {
        $stmt = $this->db->prepare("SELECT credit_limit, current_balance FROM credit_cards WHERE id = ?");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$card) return 0;
        
        return $card['credit_limit'] - $card['current_balance'];
    }
    
    public function getMinimumPayment($cardId) {
        $stmt = $this->db->prepare("SELECT current_balance, minimum_payment_percentage FROM credit_cards WHERE id = ?");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$card) return 0;
        
        return ($card['current_balance'] * $card['minimum_payment_percentage']) / 100;
    }
    
    public function getNextPaymentDate($cardId) {
        $stmt = $this->db->prepare("SELECT payment_due_date FROM credit_cards WHERE id = ?");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$card) return null;
        
        $currentDate = new DateTime();
        $paymentDay = $card['payment_due_date'];
        
        // Si el día del mes ya pasó, calcular para el próximo mes
        if ($currentDate->format('d') >= $paymentDay) {
            $nextPayment = new DateTime('first day of next month');
        } else {
            $nextPayment = new DateTime('first day of this month');
        }
        
        $nextPayment->setDate($nextPayment->format('Y'), $nextPayment->format('m'), $paymentDay);
        
        return $nextPayment->format('Y-m-d');
    }
    
    public function getNextCutOffDate($cardId) {
        $stmt = $this->db->prepare("SELECT cut_off_date FROM credit_cards WHERE id = ?");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$card) return null;
        
        $currentDate = new DateTime();
        $cutOffDay = $card['cut_off_date'];
        
        // Si el día del mes ya pasó, calcular para el próximo mes
        if ($currentDate->format('d') >= $cutOffDay) {
            $nextCutOff = new DateTime('first day of next month');
        } else {
            $nextCutOff = new DateTime('first day of this month');
        }
        
        $nextCutOff->setDate($nextCutOff->format('Y'), $nextCutOff->format('m'), $cutOffDay);
        
        return $nextCutOff->format('Y-m-d');
    }
    
    public function getMonthlyStatement($cardId, $year, $month) {
        $stmt = $this->db->prepare("
            SELECT 
                type,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count
            FROM credit_card_transactions 
            WHERE credit_card_id = ? 
            AND strftime('%Y', transaction_date) = ? 
            AND strftime('%m', transaction_date) = ?
            GROUP BY type
        ");
        $stmt->execute([$cardId, $year, sprintf('%02d', $month)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCardsWithPaymentsDue($userId, $days = 7) {
        $stmt = $this->db->prepare("
            SELECT 
                cc.*,
                CASE 
                    WHEN CAST(strftime('%d', 'now') AS INTEGER) >= payment_due_date THEN
                        DATE('now', 'start of month', '+1 month', '+' || (payment_due_date - 1) || ' days')
                    ELSE
                        DATE('now', 'start of month', '+' || (payment_due_date - 1) || ' days')
                END as next_payment_date
            FROM credit_cards cc
            WHERE cc.user_id = ? 
            AND cc.current_balance > 0
            AND (
                CASE 
                    WHEN CAST(strftime('%d', 'now') AS INTEGER) >= payment_due_date THEN
                        DATE('now', 'start of month', '+1 month', '+' || (payment_due_date - 1) || ' days')
                    ELSE
                        DATE('now', 'start of month', '+' || (payment_due_date - 1) || ' days')
                END
            ) BETWEEN DATE('now') AND DATE('now', '+' || ? || ' days')
            ORDER BY next_payment_date
        ");
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar el estado de las tarjetas de crédito a 'overdue' si han vencido
     */
    public function updateOverdueStatus($userId = null) {
        $sql = "
            UPDATE credit_cards 
            SET status = 'overdue' 
            WHERE current_balance > 0 
            AND status != 'overdue'
            AND (
                CASE 
                    WHEN CAST(strftime('%d', 'now') AS INTEGER) >= payment_due_date THEN
                        DATE('now', 'start of month', '+' || (payment_due_date - 1) || ' days')
                    ELSE
                        DATE('now', 'start of month', '-1 month', '+' || (payment_due_date - 1) || ' days')
                END
            ) < DATE('now')
        ";
        
        $params = [];
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Obtener tarjetas vencidas
     */
    public function getOverdueCards($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                cc.*,
                CASE 
                    WHEN CAST(strftime('%d', 'now') AS INTEGER) >= payment_due_date THEN
                        DATE('now', 'start of month', '+' || (payment_due_date - 1) || ' days')
                    ELSE
                        DATE('now', 'start of month', '-1 month', '+' || (payment_due_date - 1) || ' days')
                END as last_payment_date,
                julianday('now') - julianday(
                    CASE 
                        WHEN CAST(strftime('%d', 'now') AS INTEGER) >= payment_due_date THEN
                            DATE('now', 'start of month', '+' || (payment_due_date - 1) || ' days')
                        ELSE
                            DATE('now', 'start of month', '-1 month', '+' || (payment_due_date - 1) || ' days')
                    END
                ) as days_overdue
            FROM credit_cards cc
            WHERE cc.user_id = ? 
            AND cc.current_balance > 0
            AND cc.status = 'overdue'
            ORDER BY days_overdue DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Marcar una tarjeta como activa (cuando se realiza un pago)
     */
    public function markAsActive($cardId) {
        $stmt = $this->db->prepare("UPDATE credit_cards SET status = 'active' WHERE id = ?");
        $stmt->execute([$cardId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtener el estado de una tarjeta
     */
    public function getCardStatus($cardId) {
        $stmt = $this->db->prepare("SELECT status FROM credit_cards WHERE id = ?");
        $stmt->execute([$cardId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['status'] : null;
    }
    
    /**
     * Determinar el estado dinámico de una tarjeta basado en fechas y pagos
     */
    public function getDynamicCardStatus($cardId) {
        $stmt = $this->db->prepare("
            SELECT 
                cc.*,
                CASE 
                    WHEN CAST(strftime('%d', 'now') AS INTEGER) >= cc.cut_off_date THEN
                        DATE('now', 'start of month', '+' || (cc.cut_off_date - 1) || ' days')
                    ELSE
                        DATE('now', 'start of month', '-1 month', '+' || (cc.cut_off_date - 1) || ' days')
                END as last_cut_off_date,
                CASE 
                    WHEN CAST(strftime('%d', 'now') AS INTEGER) >= cc.payment_due_date THEN
                        DATE('now', 'start of month', '+' || (cc.payment_due_date - 1) || ' days')
                    ELSE
                        DATE('now', 'start of month', '-1 month', '+' || (cc.payment_due_date - 1) || ' days')
                END as last_payment_due_date
            FROM credit_cards cc
            WHERE cc.id = ?
        ");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$card) return 'unknown';
        
        $currentDate = new DateTime();
        $lastCutOffDate = new DateTime($card['last_cut_off_date']);
        $lastPaymentDueDate = new DateTime($card['last_payment_due_date']);
        
        // Obtener el balance al momento del último corte
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN type = 'charge' THEN amount ELSE -amount END) as balance_at_cutoff
            FROM credit_card_transactions 
            WHERE credit_card_id = ? 
            AND transaction_date <= ?
        ");
        $stmt->execute([$cardId, $card['last_cut_off_date']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $balanceAtCutOff = $result['balance_at_cutoff'] ?? 0;
        
        // Verificar si hubo pagos después del corte
        $stmt = $this->db->prepare("
            SELECT 
                SUM(amount) as payments_after_cutoff
            FROM credit_card_transactions 
            WHERE credit_card_id = ? 
            AND type = 'payment'
            AND transaction_date > ?
        ");
        $stmt->execute([$cardId, $card['last_cut_off_date']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $paymentsAfterCutOff = $result['payments_after_cutoff'] ?? 0;
        
        // Determinar el estado
        if ($balanceAtCutOff <= 0) {
            // No hay deuda del corte anterior
            return 'paid';
        }
        
        if ($currentDate > $lastPaymentDueDate && $paymentsAfterCutOff < $balanceAtCutOff) {
            // Pasó la fecha de pago y no se pagó completamente
            return 'overdue';
        }
        
        if ($paymentsAfterCutOff >= $balanceAtCutOff) {
            // Se realizó el pago completo
            return 'paid';
        }
        
        if ($currentDate >= $lastCutOffDate && $paymentsAfterCutOff < $balanceAtCutOff) {
            // Se realizó el corte pero aún no se paga
            return 'pending';
        }
        
        return 'active';
    }
    
    public function getCutOffPayment($cardId) {
        $stmt = $this->db->prepare("SELECT cut_off_date, payment_due_date FROM credit_cards WHERE id = ?");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$card) return 0;
        
        $currentDate = new DateTime();
        $cutOffDay = $card['cut_off_date'];
        
        // Calcular la fecha de corte actual o anterior
        if ($currentDate->format('d') >= $cutOffDay) {
            // Ya pasó el corte de este mes, usar el corte actual
            $cutOffDate = new DateTime('first day of this month');
        } else {
            // No ha llegado el corte, usar el corte del mes anterior
            $cutOffDate = new DateTime('first day of last month');
        }
        
        $cutOffDate->setDate($cutOffDate->format('Y'), $cutOffDate->format('m'), $cutOffDay);
        
        // Calcular el balance al momento del corte
        // Obtener todas las transacciones hasta la fecha de corte
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN type = 'charge' THEN amount ELSE -amount END) as balance_at_cutoff
            FROM credit_card_transactions 
            WHERE credit_card_id = ? 
            AND transaction_date <= ?
        ");
        $stmt->execute([$cardId, $cutOffDate->format('Y-m-d')]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $balanceAtCutOff = $result['balance_at_cutoff'] ?? 0;
        
        // Si hay balance positivo al corte, ese es el pago requerido
        return max(0, $balanceAtCutOff);
    }
    
    public function getCutOffDate($cardId) {
        $stmt = $this->db->prepare("SELECT cut_off_date FROM credit_cards WHERE id = ?");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$card) return null;
        
        $currentDate = new DateTime();
        $cutOffDay = $card['cut_off_date'];
        
        // Calcular la fecha de corte actual o anterior
        if ($currentDate->format('d') >= $cutOffDay) {
            // Ya pasó el corte de este mes, usar el corte actual
            $cutOffDate = new DateTime('first day of this month');
        } else {
            // No ha llegado el corte, usar el corte del mes anterior
            $cutOffDate = new DateTime('first day of last month');
        }
        
        $cutOffDate->setDate($cutOffDate->format('Y'), $cutOffDate->format('m'), $cutOffDay);
        
        return $cutOffDate->format('Y-m-d');
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
