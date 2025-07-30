<?php
/**
 * Clase para gestionar notificaciones del sistema
 * Maneja alertas de vencimientos, límites de gastos y recordatorios
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/env_config.php';

class Notification {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->createNotificationsTable();
    }
    
    /**
     * Crear tabla de notificaciones si no existe
     */
    private function createNotificationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            data TEXT,
            is_read BOOLEAN DEFAULT 0,
            priority VARCHAR(20) DEFAULT 'normal',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $this->db->exec($sql);
        
        // Crear índices para mejor rendimiento
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_type ON notifications(type)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at)");
    }
    
    /**
     * Crear una nueva notificación
     */
    public function create($userId, $type, $title, $message, $data = null, $priority = 'normal', $expiresAt = null, $sendWhatsApp = true) {
        try {
            $sql = "INSERT INTO notifications (user_id, type, title, message, data, priority, expires_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $type,
                $title,
                $message,
                $data ? json_encode($data) : null,
                $priority,
                $expiresAt
            ]);
            
            $notificationId = $this->db->lastInsertId();
            
            // Enviar notificación por WhatsApp si está habilitado
            if ($sendWhatsApp && WHATSAPP_ENABLED && $priority === 'high') {
                $this->sendWhatsAppNotification($title, $message);
            }
            
            return $notificationId;
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener notificaciones de un usuario
     */
    public function getByUser($userId, $unreadOnly = false, $limit = 50) {
        try {
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = ? 
                    AND (expires_at IS NULL OR expires_at > datetime('now'))";
            
            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }
            
            $sql .= " ORDER BY priority DESC, created_at DESC LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $limit]);
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar datos JSON
            foreach ($notifications as &$notification) {
                if ($notification['data']) {
                    $notification['data'] = json_decode($notification['data'], true);
                }
            }
            
            return $notifications;
        } catch (PDOException $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $sql = "UPDATE notifications SET is_read = 1 
                    WHERE id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId, $userId]);
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar notificación
     */
    public function delete($notificationId, $userId) {
        try {
            $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId, $userId]);
        } catch (PDOException $e) {
            error_log("Error deleting notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar notificaciones no leídas
     */
    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE user_id = ? AND is_read = 0 
                    AND (expires_at IS NULL OR expires_at > datetime('now'))";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Limpiar notificaciones expiradas
     */
    public function cleanExpired() {
        try {
            $sql = "DELETE FROM notifications 
                    WHERE expires_at IS NOT NULL 
                    AND expires_at <= datetime('now')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error cleaning expired notifications: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Generar notificaciones automáticas para vencimientos próximos
     */
    public function generateDueNotifications() {
        $this->generateAccountPayableDueNotifications();
        $this->generateAccountReceivableDueNotifications();
        $this->generateDebtDueNotifications();
        $this->generateCreditCardDueNotifications();
    }
    
    /**
     * Generar notificaciones para cuentas por pagar próximas a vencer
     */
    private function generateAccountPayableDueNotifications() {
        try {
            // Buscar cuentas que vencen en los próximos 3 días
            $sql = "SELECT ap.*, u.id as user_id 
                    FROM accounts_payable ap
                    JOIN users u ON ap.user_id = u.id
                    WHERE ap.status = 'pending'
                    AND ap.due_date BETWEEN date('now') AND date('now', '+3 days')
                    AND NOT EXISTS (
                        SELECT 1 FROM notifications n 
                        WHERE n.user_id = ap.user_id 
                        AND n.type = 'account_payable_due'
                        AND json_extract(n.data, '$.account_id') = ap.id
                        AND datetime(n.created_at) > datetime('now', '-1 day')
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($accounts as $account) {
                $daysUntilDue = (new DateTime($account['due_date']))->diff(new DateTime())->days;
                
                $title = "Cuenta por pagar próxima a vencer";
                $message = "La cuenta '{$account['description']}' vence en {$daysUntilDue} día(s). Monto: " . formatCurrency($account['total_amount'] - $account['paid_amount']);
                
                $this->create(
                    $account['user_id'],
                    'account_payable_due',
                    $title,
                    $message,
                    ['account_id' => $account['id'], 'due_date' => $account['due_date']],
                    $daysUntilDue <= 1 ? 'high' : 'normal',
                    date('Y-m-d H:i:s', strtotime('+7 days'))
                );
            }
        } catch (PDOException $e) {
            error_log("Error generating account payable due notifications: " . $e->getMessage());
        }
    }
    
    /**
     * Generar notificaciones para cuentas por cobrar próximas a vencer
     */
    private function generateAccountReceivableDueNotifications() {
        try {
            $sql = "SELECT ar.*, u.id as user_id 
                    FROM accounts_receivable ar
                    JOIN users u ON ar.user_id = u.id
                    WHERE ar.status = 'pending'
                    AND ar.due_date BETWEEN date('now') AND date('now', '+3 days')
                    AND NOT EXISTS (
                        SELECT 1 FROM notifications n 
                        WHERE n.user_id = ar.user_id 
                        AND n.type = 'account_receivable_due'
                        AND json_extract(n.data, '$.account_id') = ar.id
                        AND datetime(n.created_at) > datetime('now', '-1 day')
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($accounts as $account) {
                $daysUntilDue = (new DateTime($account['due_date']))->diff(new DateTime())->days;
                
                $title = "Cuenta por cobrar próxima a vencer";
                $message = "La cuenta '{$account['description']}' vence en {$daysUntilDue} día(s). Monto: " . formatCurrency($account['total_amount'] - $account['received_amount']);
                
                $this->create(
                    $account['user_id'],
                    'account_receivable_due',
                    $title,
                    $message,
                    ['account_id' => $account['id'], 'due_date' => $account['due_date']],
                    'normal',
                    date('Y-m-d H:i:s', strtotime('+7 days'))
                );
            }
        } catch (PDOException $e) {
            error_log("Error generating account receivable due notifications: " . $e->getMessage());
        }
    }
    
    /**
     * Generar notificaciones para deudas próximas a vencer
     */
    private function generateDebtDueNotifications() {
        try {
            $sql = "SELECT d.*, u.id as user_id 
                    FROM debts d
                    JOIN users u ON d.user_id = u.id
                    WHERE d.status = 'active'
                    AND d.due_date IS NOT NULL
                    AND d.due_date BETWEEN date('now') AND date('now', '+3 days')
                    AND NOT EXISTS (
                        SELECT 1 FROM notifications n 
                        WHERE n.user_id = d.user_id 
                        AND n.type = 'debt_payment_due'
                        AND json_extract(n.data, '$.debt_id') = d.id
                        AND datetime(n.created_at) > datetime('now', '-1 day')
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $debts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($debts as $debt) {
                $daysUntilDue = (new DateTime($debt['due_date']))->diff(new DateTime())->days;
                
                $title = "Pago de deuda próximo a vencer";
                $message = "El pago de la deuda '{$debt['description']}' vence en {$daysUntilDue} día(s). Balance actual: " . formatCurrency($debt['current_balance']);
                
                $this->create(
                    $debt['user_id'],
                    'debt_payment_due',
                    $title,
                    $message,
                    ['debt_id' => $debt['id'], 'due_date' => $debt['due_date']],
                    $daysUntilDue <= 1 ? 'high' : 'normal',
                    date('Y-m-d H:i:s', strtotime('+7 days'))
                );
            }
        } catch (PDOException $e) {
            error_log("Error generating debt due notifications: " . $e->getMessage());
        }
    }
    
    /**
     * Generar notificaciones para tarjetas de crédito próximas a vencer
     */
    private function generateCreditCardDueNotifications() {
        try {
            // Obtener todas las tarjetas con balance > 0
            $sql = "SELECT cc.*, u.id as user_id 
                    FROM credit_cards cc
                    JOIN users u ON cc.user_id = u.id
                    WHERE cc.current_balance > 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Instanciar la clase CreditCard para usar sus métodos
            require_once 'CreditCard.php';
            $creditCardClass = new CreditCard($this->db);
            
            foreach ($cards as $card) {
                // Calcular la próxima fecha de pago usando el método de la clase CreditCard
                $nextPaymentDate = $creditCardClass->getNextPaymentDate($card['id']);
                
                if (!$nextPaymentDate) continue;
                
                // Calcular días hasta el vencimiento
                $currentDate = new DateTime();
                $paymentDate = new DateTime($nextPaymentDate);
                $daysUntilDue = $currentDate->diff($paymentDate)->days;
                
                // Solo procesar si vence en los próximos 3 días
                if ($daysUntilDue > 3) continue;
                
                // Verificar si ya existe una notificación reciente
                $checkSql = "SELECT COUNT(*) FROM notifications 
                            WHERE user_id = ? 
                            AND type = 'credit_card_due'
                            AND json_extract(data, '$.card_id') = ?
                            AND datetime(created_at) > datetime('now', '-1 day')";
                
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->execute([$card['user_id'], $card['id']]);
                
                if ($checkStmt->fetchColumn() > 0) {
                    continue; // Ya existe una notificación reciente
                }
                
                $minimumPayment = $card['current_balance'] * 0.05; // 5% del balance
                
                $title = "Pago de tarjeta de crédito próximo a vencer";
                $message = "El pago de la tarjeta '{$card['card_name']}' vence en {$daysUntilDue} día(s). Pago mínimo: " . formatCurrency($minimumPayment);
                
                // Enviar notificación de WhatsApp si faltan exactamente 2 días
                if ($daysUntilDue == 2) {
                    $whatsappTitle = "⚠️ Recordatorio de Pago - Tarjeta de Crédito";
                    $whatsappMessage = "Tu tarjeta '{$card['card_name']}' vence en 2 días (" . date('d/m/Y', strtotime($nextPaymentDate)) . ").\n\n" .
                                     "💳 Balance actual: " . formatCurrency($card['current_balance']) . "\n" .
                                     "💰 Pago mínimo: " . formatCurrency($minimumPayment) . "\n\n" .
                                     "¡No olvides realizar tu pago a tiempo para evitar intereses!";
                    
                    $this->sendWhatsAppNotification($whatsappTitle, $whatsappMessage);
                }
                
                $this->create(
                    $card['user_id'],
                    'credit_card_due',
                    $title,
                    $message,
                    ['card_id' => $card['id'], 'due_date' => $nextPaymentDate],
                    $daysUntilDue <= 1 ? 'high' : 'normal',
                    date('Y-m-d H:i:s', strtotime('+7 days'))
                );
            }
        } catch (PDOException $e) {
            error_log("Error generating credit card due notifications: " . $e->getMessage());
        }
    }
    
    /**
     * Generar notificación de límite de gastos
     */
    public function generateSpendingLimitNotification($userId, $categoryId, $currentAmount, $limit) {
        try {
            // Verificar si ya existe una notificación similar reciente
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE user_id = ? 
                    AND type = 'spending_limit'
                    AND json_extract(data, '$.category_id') = ?
                    AND datetime(created_at) > datetime('now', '-1 day')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $categoryId]);
            
            if ($stmt->fetchColumn() > 0) {
                return; // Ya existe una notificación reciente
            }
            
            $percentage = ($currentAmount / $limit) * 100;
            $title = "Límite de gastos alcanzado";
            $message = "Has alcanzado el {$percentage}% de tu límite de gastos. Actual: " . formatCurrency($currentAmount) . " / Límite: " . formatCurrency($limit);
            
            $this->create(
                $userId,
                'spending_limit',
                $title,
                $message,
                ['category_id' => $categoryId, 'current_amount' => $currentAmount, 'limit' => $limit],
                $percentage >= 100 ? 'high' : 'normal',
                date('Y-m-d H:i:s', strtotime('+3 days'))
            );
        } catch (PDOException $e) {
            error_log("Error generating spending limit notification: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar notificación por WhatsApp usando CallMeBot API
     */
    private function sendWhatsAppNotification($title, $message) {
        if (!WHATSAPP_ENABLED) {
            return false;
        }
        
        try {
            // Formatear el mensaje para WhatsApp
            $whatsappMessage = "🔔 *" . $title . "*\n\n" . $message . "\n\n_Money Manager - " . date('d/m/Y H:i') . "_";
            
            // Preparar los parámetros para la API
            $params = [
                'phone' => WHATSAPP_PHONE,
                'text' => $whatsappMessage,
                'apikey' => WHATSAPP_API_KEY
            ];
            
            // Construir la URL con los parámetros
            $url = WHATSAPP_API_URL . '?' . http_build_query($params);
            
            // Enviar la petición usando cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Money Manager WhatsApp Bot');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                error_log("WhatsApp notification sent successfully: " . $title);
                return true;
            } else {
                error_log("WhatsApp notification failed. HTTP Code: " . $httpCode . ", Response: " . $response);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error sending WhatsApp notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación manual por WhatsApp
     */
    public function sendManualWhatsAppNotification($title, $message) {
        return $this->sendWhatsAppNotification($title, $message);
    }
    
    /**
     * Probar la configuración de WhatsApp
     */
    public function testWhatsAppConfiguration() {
        if (!WHATSAPP_ENABLED) {
            return ['success' => false, 'message' => 'WhatsApp está deshabilitado en la configuración'];
        }
        
        if (empty(WHATSAPP_API_KEY)) {
            return ['success' => false, 'message' => 'API Key de WhatsApp no configurada'];
        }
        
        if (empty(WHATSAPP_PHONE)) {
            return ['success' => false, 'message' => 'Número de teléfono de WhatsApp no configurado'];
        }
        
        // Enviar mensaje de prueba
        $testResult = $this->sendWhatsAppNotification(
            'Prueba de Configuración',
            'Este es un mensaje de prueba para verificar que la integración con WhatsApp funciona correctamente.'
        );
        
        if ($testResult) {
            return ['success' => true, 'message' => 'Configuración de WhatsApp funcionando correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al enviar mensaje de prueba. Revisa los logs para más detalles.'];
        }
    }
    
    /**
     * Obtener estadísticas de notificaciones WhatsApp
     */
    public function getWhatsAppStats() {
        return [
            'enabled' => WHATSAPP_ENABLED,
            'api_url' => WHATSAPP_API_URL,
            'phone' => WHATSAPP_PHONE,
            'api_key_configured' => !empty(WHATSAPP_API_KEY)
        ];
    }
}
?>