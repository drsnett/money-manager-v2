<?php
/**
 * Script para generar notificaciones automáticas
 * Debe ejecutarse diariamente mediante cron job o tarea programada
 */

// Configurar el entorno
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/notifications.log');

// Incluir archivos necesarios
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../classes/CreditCard.php';
require_once __DIR__ . '/../config/server_config.php';

/**
 * Función para escribir logs
 */
function writeLog($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    $logFile = __DIR__ . '/../logs/notifications.log';
    
    // Crear directorio de logs si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // También mostrar en consola si se ejecuta desde línea de comandos
    if (php_sapi_name() === 'cli') {
        echo $logMessage;
    }
}

/**
 * Función principal
 */
function generateNotifications() {
    try {
        writeLog('Iniciando generación de notificaciones automáticas');
        
        $notification = new Notification();
        
        // Limpiar notificaciones expiradas primero
        writeLog('Limpiando notificaciones expiradas...');
        $deletedCount = $notification->cleanExpired();
        writeLog("Se eliminaron {$deletedCount} notificaciones expiradas");
        
        // Actualizar estado de tarjetas vencidas
        writeLog('Actualizando estado de tarjetas vencidas...');
        updateOverdueCreditCards();
        
        // Generar nuevas notificaciones de vencimientos
        writeLog('Generando notificaciones de vencimientos...');
        $notification->generateDueNotifications();
        writeLog('Notificaciones de vencimientos generadas exitosamente');
        
        // Generar notificaciones de límites de gastos
        writeLog('Verificando límites de gastos...');
        generateSpendingLimitNotifications();
        
        writeLog('Proceso de generación de notificaciones completado exitosamente');
        
    } catch (Exception $e) {
        writeLog('Error en la generación de notificaciones: ' . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

/**
 * Actualizar estado de tarjetas de crédito vencidas
 */
function updateOverdueCreditCards() {
    try {
        $creditCard = new CreditCard();
        
        // Actualizar todas las tarjetas vencidas
        $updatedCount = $creditCard->updateOverdueStatus();
        
        writeLog("Se actualizaron {$updatedCount} tarjetas de crédito a estado 'overdue'");
        
        // Generar notificaciones para tarjetas recién marcadas como vencidas
        if ($updatedCount > 0) {
            generateOverdueCardNotifications();
        }
        
    } catch (Exception $e) {
        writeLog('Error actualizando estado de tarjetas vencidas: ' . $e->getMessage(), 'ERROR');
    }
}

/**
 * Generar notificaciones para tarjetas recién marcadas como vencidas
 */
function generateOverdueCardNotifications() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $notification = new Notification();
        
        // Obtener todas las tarjetas vencidas
        $sql = "SELECT cc.*, u.id as user_id 
                FROM credit_cards cc
                JOIN users u ON cc.user_id = u.id
                WHERE cc.status = 'overdue'
                AND cc.current_balance > 0";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $overdueCards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($overdueCards as $card) {
            // Verificar si ya existe una notificación de vencimiento para esta tarjeta
            $existingNotification = $db->prepare(
                "SELECT id FROM notifications 
                 WHERE user_id = ? 
                 AND type = 'credit_card_overdue' 
                 AND JSON_EXTRACT(data, '$.card_id') = ?
                 AND created_at > datetime('now', '-7 days')"
            );
            $existingNotification->execute([$card['user_id'], $card['id']]);
            
            if (!$existingNotification->fetch()) {
                $title = "Tarjeta de Crédito Vencida";
                $message = "Su tarjeta {$card['card_name']} tiene un pago vencido de $" . number_format($card['current_balance'], 2) . ". Por favor, realice el pago lo antes posible para evitar cargos adicionales.";
                
                $notification->create(
                    $card['user_id'],
                    'credit_card_overdue',
                    $title,
                    $message,
                    ['card_id' => $card['id'], 'balance' => $card['current_balance']],
                    'high',
                    date('Y-m-d H:i:s', strtotime('+30 days'))
                );
                
                writeLog("Notificación de vencimiento creada para tarjeta {$card['card_name']} (ID: {$card['id']})");
            }
        }
        
    } catch (Exception $e) {
        writeLog('Error generando notificaciones de tarjetas vencidas: ' . $e->getMessage(), 'ERROR');
    }
}

/**
 * Generar notificaciones de límites de gastos
 */
function generateSpendingLimitNotifications() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $notification = new Notification();
        
        // Obtener usuarios con límites de gastos configurados
        $sql = "SELECT DISTINCT user_id FROM user_settings 
                WHERE setting_name = 'spending_limit' 
                AND setting_value IS NOT NULL 
                AND setting_value != ''";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($users as $userId) {
            checkUserSpendingLimits($userId, $db, $notification);
        }
        
        writeLog('Verificación de límites de gastos completada');
        
    } catch (Exception $e) {
        writeLog('Error verificando límites de gastos: ' . $e->getMessage(), 'ERROR');
    }
}

/**
 * Verificar límites de gastos para un usuario específico
 */
function checkUserSpendingLimits($userId, $db, $notification) {
    try {
        // Obtener límites de gastos del usuario
        $sql = "SELECT setting_value FROM user_settings 
                WHERE user_id = ? AND setting_name = 'spending_limit'";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $limitsJson = $stmt->fetchColumn();
        
        if (!$limitsJson) {
            return;
        }
        
        $limits = json_decode($limitsJson, true);
        if (!$limits) {
            return;
        }
        
        $currentMonth = date('Y-m');
        
        foreach ($limits as $categoryId => $limit) {
            if ($limit <= 0) {
                continue;
            }
            
            // Calcular gastos del mes actual para esta categoría
            $sql = "SELECT COALESCE(SUM(amount), 0) as total_spent
                    FROM transactions 
                    WHERE user_id = ? 
                    AND category_id = ? 
                    AND type = 'expense'
                    AND DATE_FORMAT(date, '%Y-%m') = ?
                    AND deleted_at IS NULL";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $categoryId, $currentMonth]);
            $totalSpent = $stmt->fetchColumn();
            
            // Verificar si se ha alcanzado el 80% o 100% del límite
            $percentage = ($totalSpent / $limit) * 100;
            
            if ($percentage >= 80) {
                // Verificar si ya se envió una notificación similar hoy
                $sql = "SELECT COUNT(*) FROM notifications 
                        WHERE user_id = ? 
                        AND type = 'spending_limit'
                        AND json_extract(data, '$.category_id') = ?
                        AND date(created_at) = date('now')";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([$userId, $categoryId]);
                
                if ($stmt->fetchColumn() == 0) {
                    $notification->generateSpendingLimitNotification($userId, $categoryId, $totalSpent, $limit);
                    writeLog("Notificación de límite de gastos generada para usuario {$userId}, categoría {$categoryId}");
                }
            }
        }
        
    } catch (Exception $e) {
        writeLog("Error verificando límites para usuario {$userId}: " . $e->getMessage(), 'ERROR');
    }
}

/**
 * Función para enviar notificaciones por email (opcional)
 */
function sendEmailNotifications() {
    try {
        writeLog('Enviando notificaciones por email...');
        
        $database = new Database();
        $db = $database->getConnection();
        
        // Obtener notificaciones de alta prioridad no enviadas por email
        $sql = "SELECT n.*, u.email, u.name 
                FROM notifications n
                JOIN users u ON n.user_id = u.id
                WHERE n.priority = 'high'
                AND n.is_read = 0
                AND n.created_at > datetime('now', '-1 hour')
                AND (n.data IS NULL OR json_extract(n.data, '$.email_sent') IS NULL)
                ORDER BY n.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($notifications as $notif) {
            if (sendNotificationEmail($notif)) {
                // Marcar como enviada por email
                $data = $notif['data'] ? json_decode($notif['data'], true) : [];
                $data['email_sent'] = date('Y-m-d H:i:s');
                
                $updateSql = "UPDATE notifications SET data = ? WHERE id = ?";
                $updateStmt = $db->prepare($updateSql);
                $updateStmt->execute([json_encode($data), $notif['id']]);
                
                writeLog("Email enviado para notificación {$notif['id']} a {$notif['email']}");
            }
        }
        
        writeLog('Envío de emails completado');
        
    } catch (Exception $e) {
        writeLog('Error enviando emails: ' . $e->getMessage(), 'ERROR');
    }
}

/**
 * Enviar email de notificación individual
 */
function sendNotificationEmail($notification) {
    // Implementar envío de email aquí
    // Por ahora solo simular el envío
    return true;
}

// Ejecutar el script
try {
    // Verificar que el script no se esté ejecutando ya
    $lockFile = __DIR__ . '/../data/notifications.lock';
    
    if (file_exists($lockFile)) {
        $lockTime = filemtime($lockFile);
        if (time() - $lockTime < 3600) { // 1 hora
            writeLog('El script ya se está ejecutando. Saliendo...', 'WARNING');
            exit(1);
        } else {
            // Lock file muy antiguo, eliminarlo
            unlink($lockFile);
        }
    }
    
    // Crear lock file
    touch($lockFile);
    
    // Ejecutar generación de notificaciones
    generateNotifications();
    
    // Enviar emails si está habilitado
    if (env('NOTIFICATIONS_EMAIL_ENABLED', false)) {
        sendEmailNotifications();
    }
    
    // Eliminar lock file
    unlink($lockFile);
    
    writeLog('Script ejecutado exitosamente');
    exit(0);
    
} catch (Exception $e) {
    writeLog('Error fatal: ' . $e->getMessage(), 'ERROR');
    
    // Eliminar lock file en caso de error
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
    
    exit(1);
}
?>