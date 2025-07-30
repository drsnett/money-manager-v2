<?php
/**
 * Panel de administración de WhatsApp
 * Permite gestionar la configuración y envío de notificaciones por WhatsApp
 */

require_once 'config/config.php';
require_once 'classes/Notification.php';

// Verificar autenticación
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Administración WhatsApp';
$currentPage = 'whatsapp';

// Crear instancia de notificación
$notification = new Notification();

// Obtener estadísticas de WhatsApp
$whatsappStats = $notification->getWhatsAppStats();

// Obtener notificaciones recientes con prioridad alta
$recentHighPriorityNotifications = $notification->getByUser(getCurrentUserId(), false, 10);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-whatsapp text-success"></i> Administración WhatsApp</h1>
                <div>
                    <button type="button" class="btn btn-info" onclick="testWhatsAppConfig()">
                        <i class="fas fa-check-circle"></i> Probar Configuración
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                        <i class="fas fa-paper-plane"></i> Enviar Mensaje
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-<?php echo $whatsappStats['enabled'] ? 'success' : 'danger'; ?> text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Estado</h5>
                            <h3><?php echo $whatsappStats['enabled'] ? 'Habilitado' : 'Deshabilitado'; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-<?php echo $whatsappStats['enabled'] ? 'check-circle' : 'times-circle'; ?> fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">API URL</h5>
                            <small><?php echo $whatsappStats['api_url'] ? 'Configurada' : 'No configurada'; ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-link fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">API Key</h5>
                            <small><?php echo $whatsappStats['api_key'] ? 'Configurada' : 'No configurada'; ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-key fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Teléfono</h5>
                            <small><?php echo $whatsappStats['phone'] ? substr($whatsappStats['phone'], 0, 8) . '...' : 'No configurado'; ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-phone fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Configuración actual -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cog"></i> Configuración Actual</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $whatsappStats['enabled'] ? 'success' : 'danger'; ?>">
                                    <?php echo $whatsappStats['enabled'] ? 'Habilitado' : 'Deshabilitado'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>API URL:</strong></td>
                            <td><code><?php echo htmlspecialchars($whatsappStats['api_url']); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>API Key:</strong></td>
                            <td><code><?php echo $whatsappStats['api_key'] ? str_repeat('*', strlen($whatsappStats['api_key']) - 4) . substr($whatsappStats['api_key'], -4) : 'No configurada'; ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Teléfono:</strong></td>
                            <td><code><?php echo htmlspecialchars($whatsappStats['phone']); ?></code></td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> Para modificar la configuración, edita el archivo <code>.env</code> en la raíz del proyecto.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones rápidas -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info" onclick="testWhatsAppConfig()">
                            <i class="fas fa-check-circle"></i> Probar Configuración
                        </button>
                        
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                            <i class="fas fa-paper-plane"></i> Enviar Mensaje Manual
                        </button>
                        
                        <button type="button" class="btn btn-outline-success" onclick="createTestNotification()">
                            <i class="fas fa-bell"></i> Crear Notificación de Prueba
                        </button>
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshStats()">
                            <i class="fas fa-sync-alt"></i> Actualizar Estadísticas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notificaciones recientes con prioridad alta -->
    <?php if (!empty($recentHighPriorityNotifications)): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle text-warning"></i> Notificaciones Recientes (Prioridad Alta)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Título</th>
                                    <th>Mensaje</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentHighPriorityNotifications as $notif): ?>
                                <tr>
                                    <td><?php echo formatDate($notif['created_at']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($notif['type']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($notif['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($notif['message'], 0, 50)) . (strlen($notif['message']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $notif['is_read'] ? 'success' : 'warning'; ?>">
                                            <?php echo $notif['is_read'] ? 'Leída' : 'No leída'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal para enviar mensaje manual -->
<div class="modal fade" id="sendMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Enviar Mensaje por WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendMessageForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="messageTitle" class="form-label">Título</label>
                        <input type="text" class="form-control" id="messageTitle" name="title" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="messageContent" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="messageContent" name="message" rows="4" required maxlength="500"></textarea>
                        <div class="form-text">Máximo 500 caracteres</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Probar configuración de WhatsApp
function testWhatsAppConfig() {
    showLoading('Probando configuración de WhatsApp...');
    
    fetch('ajax/whatsapp_notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'test_config',
            'csrf_token': '<?php echo generateCSRFToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', 'Configuración válida', data.message);
        } else {
            showAlert('danger', 'Error en configuración', data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('danger', 'Error', 'Error al probar la configuración');
        console.error('Error:', error);
    });
}

// Crear notificación de prueba
function createTestNotification() {
    if (!confirm('¿Crear una notificación de prueba que se enviará por WhatsApp?')) {
        return;
    }
    
    showLoading('Creando notificación de prueba...');
    
    fetch('ajax/whatsapp_notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'create_test_notification',
            'csrf_token': '<?php echo generateCSRFToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', 'Notificación creada', data.message);
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('danger', 'Error', data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('danger', 'Error', 'Error al crear la notificación');
        console.error('Error:', error);
    });
}

// Actualizar estadísticas
function refreshStats() {
    location.reload();
}

// Manejar envío del formulario de mensaje manual
document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'send_manual');
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    showLoading(submitBtn);
    
    fetch('ajax/whatsapp_notifications.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Respuesta inválida del servidor');
            }
        });
    })
    .then(data => {
        console.log('Parsed data:', data);
        hideLoading(submitBtn, originalText);
        
        if (data.success) {
            showAlert(data.message, 'success');
            this.reset();
            const modal = bootstrap.Modal.getInstance(document.getElementById('sendMessageModal'));
            if (modal) {
                modal.hide();
            }
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        hideLoading(submitBtn, originalText);
        console.error('Error completo:', error);
        showAlert('Error al enviar el mensaje: ' + error.message, 'danger');
    });
});
</script>

<?php include 'includes/footer.php'; ?>