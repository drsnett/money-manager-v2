<?php
/**
 * Endpoint AJAX para gestionar notificaciones
 * Permite obtener, marcar como leídas y eliminar notificaciones
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

$notification = new Notification();
$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_notifications':
            $unreadOnly = isset($_POST['unread_only']) && $_POST['unread_only'] === 'true';
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 50;
            
            $notifications = $notification->getByUser($userId, $unreadOnly, $limit);
            $unreadCount = $notification->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            break;
            
        case 'mark_as_read':
            $notificationId = $_POST['notification_id'] ?? null;
            
            if (!$notificationId) {
                throw new Exception('ID de notificación requerido');
            }
            
            $result = $notification->markAsRead($notificationId, $userId);
            
            if ($result) {
                $unreadCount = $notification->getUnreadCount($userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación marcada como leída',
                    'unread_count' => $unreadCount
                ]);
            } else {
                throw new Exception('Error al marcar notificación como leída');
            }
            break;
            
        case 'mark_all_as_read':
            $result = $notification->markAllAsRead($userId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Todas las notificaciones marcadas como leídas',
                    'unread_count' => 0
                ]);
            } else {
                throw new Exception('Error al marcar todas las notificaciones como leídas');
            }
            break;
            
        case 'delete_notification':
            $notificationId = $_POST['notification_id'] ?? null;
            
            if (!$notificationId) {
                throw new Exception('ID de notificación requerido');
            }
            
            $result = $notification->delete($notificationId, $userId);
            
            if ($result) {
                $unreadCount = $notification->getUnreadCount($userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación eliminada',
                    'unread_count' => $unreadCount
                ]);
            } else {
                throw new Exception('Error al eliminar notificación');
            }
            break;
            
        case 'get_unread_count':
            $unreadCount = $notification->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
            break;
            
        case 'generate_due_notifications':
            // Solo permitir a administradores generar notificaciones manualmente
            if ($_SESSION['role'] !== 'admin') {
                throw new Exception('Acción no permitida');
            }
            
            $notification->generateDueNotifications();
            
            echo json_encode([
                'success' => true,
                'message' => 'Notificaciones de vencimientos generadas'
            ]);
            break;
            
        case 'clean_expired':
            // Solo permitir a administradores limpiar notificaciones expiradas
            if ($_SESSION['role'] !== 'admin') {
                throw new Exception('Acción no permitida');
            }
            
            $deletedCount = $notification->cleanExpired();
            
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} notificaciones expiradas"
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error in notifications.php: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>