<?php
/**
 * Endpoint AJAX para gestionar notificaciones de WhatsApp
 * Permite enviar mensajes manuales y probar la configuración
 */

require_once '../config/config.php';
require_once '../classes/Notification.php';

// Verificar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Obtener acción
$action = $_POST['action'] ?? '';

// Crear instancia de notificación
$notification = new Notification();

try {
    switch ($action) {
        case 'test_config':
            // Probar configuración de WhatsApp
            $result = $notification->testWhatsAppConfiguration();
            echo json_encode($result);
            break;
            
        case 'send_manual':
            // Enviar mensaje manual
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if (empty($title) || empty($message)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Título y mensaje son requeridos'
                ]);
                break;
            }
            
            $result = $notification->sendManualWhatsAppNotification($title, $message);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Mensaje enviado exitosamente por WhatsApp'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al enviar mensaje. Revisa la configuración y logs.'
                ]);
            }
            break;
            
        case 'get_stats':
            // Obtener estadísticas de WhatsApp
            $stats = $notification->getWhatsAppStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'create_test_notification':
            // Crear notificación de prueba con prioridad alta
            $userId = getCurrentUserId();
            
            $notificationId = $notification->create(
                $userId,
                'test_whatsapp',
                'Prueba de Notificación WhatsApp',
                'Esta es una notificación de prueba con prioridad alta que se envía automáticamente por WhatsApp.',
                ['test' => true, 'timestamp' => time()],
                'high', // Prioridad alta para activar WhatsApp
                date('Y-m-d H:i:s', strtotime('+2 hours'))
            );
            
            if ($notificationId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación de prueba creada y enviada por WhatsApp',
                    'notification_id' => $notificationId
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear la notificación de prueba'
                ]);
            }
            break;
            
        case 'toggle_whatsapp':
            // Esta acción requeriría modificar el archivo .env o configuración
            // Por seguridad, solo mostramos el estado actual
            echo json_encode([
                'success' => false,
                'message' => 'Para habilitar/deshabilitar WhatsApp, modifica la configuración en el archivo .env'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false, 
                'message' => 'Acción no válida'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log('Error in WhatsApp notifications endpoint: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>