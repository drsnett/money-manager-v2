/**
 * Sistema de notificaciones del frontend
 * Maneja la visualización, actualización y gestión de notificaciones
 */

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.updateInterval = null;
        this.init();
    }
    
    /**
     * Inicializar el sistema de notificaciones
     */
    init() {
        this.createNotificationElements();
        this.bindEvents();
        this.loadNotifications(true); // Solo cargar no leídas por defecto
        this.startAutoUpdate();
    }
    
    /**
     * Crear elementos HTML para las notificaciones
     */
    createNotificationElements() {
        // Los elementos ya existen en header.php, no agregar elementos duplicados
        
        // Crear contenedor para notificaciones toast
        if (!document.getElementById('notification-toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.id = 'notification-toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
    }
    
    /**
     * Vincular eventos
     */
    bindEvents() {
        // Marcar todas como leídas - usar el botón del header.php
        const markAllBtn = document.querySelector('#notificationList button[onclick*="markAllAsRead"]');
        if (markAllBtn) {
            // Remover el onclick existente y agregar el event listener
            markAllBtn.removeAttribute('onclick');
            markAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });
        }
        
        // Ver todas las notificaciones
        const viewAllBtn = document.getElementById('view-all-notifications');
        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.showAllNotifications();
            });
        }
    }
    
    /**
     * Cargar notificaciones del servidor
     */
    async loadNotifications(unreadOnly = true) {
        try {
            const formData = new FormData();
            formData.append('action', 'get_notifications');
            formData.append('unread_only', unreadOnly.toString());
            formData.append('limit', '10');
            formData.append('csrf_token', window.csrfToken || '');
            
            const response = await fetch('ajax/notifications.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
                this.updateUI();
            } else {
                console.error('Error loading notifications:', data.message);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    
    /**
     * Actualizar la interfaz de usuario
     */
    updateUI() {
        this.updateBadge();
        this.updateNotificationList();
    }
    
    /**
     * Actualizar el badge de notificaciones
     */
    updateBadge() {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            const previousCount = parseInt(badge.textContent) || 0;
            
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'inline-block';
                
                // Agregar animación si hay nuevas notificaciones
                if (this.unreadCount > previousCount && previousCount > 0) {
                    badge.classList.add('pulse-animation');
                    setTimeout(() => {
                        badge.classList.remove('pulse-animation');
                    }, 2000);
                }
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    /**
     * Actualizar la lista de notificaciones
     */
    updateNotificationList() {
        const list = document.getElementById('notificationItems');
        if (!list) return;
        
        if (this.notifications.length === 0) {
            list.innerHTML = '<div class="text-center p-3"><span class="text-muted">No hay notificaciones</span></div>';
            return;
        }
        
        const notificationsHtml = this.notifications.map(notification => {
            const isUnread = !notification.is_read;
            const priorityClass = this.getPriorityClass(notification.priority);
            const timeAgo = this.getTimeAgo(notification.created_at);
            
            return `
                <div class="dropdown-item notification-item ${isUnread ? 'unread' : ''}" 
                     data-notification-id="${notification.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 ${priorityClass}">
                                ${isUnread ? '<i class="fas fa-circle text-primary me-1" style="font-size: 0.5rem;"></i>' : ''}
                                ${this.escapeHtml(notification.title)}
                            </h6>
                            <p class="mb-1 small">${this.escapeHtml(notification.message)}</p>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                    type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                ${isUnread ? `
                                    <li><a class="dropdown-item" href="#" 
                                           onclick="notificationManager.markAsRead(${notification.id})">
                                        <i class="fas fa-check"></i> Marcar como leída
                                    </a></li>
                                ` : ''}
                                <li><a class="dropdown-item text-danger" href="#" 
                                       onclick="notificationManager.deleteNotification(${notification.id})">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        list.innerHTML = notificationsHtml;
    }
    
    /**
     * Obtener clase CSS para la prioridad
     */
    getPriorityClass(priority) {
        switch (priority) {
            case 'high':
                return 'text-danger';
            case 'normal':
                return 'text-dark';
            default:
                return 'text-muted';
        }
    }
    
    /**
     * Calcular tiempo transcurrido
     */
    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Hace un momento';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `Hace ${days} día${days > 1 ? 's' : ''}`;
        }
    }
    
    /**
     * Escapar HTML para prevenir XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Marcar notificación como leída
     */
    async markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_as_read');
            formData.append('notification_id', notificationId);
            formData.append('csrf_token', window.csrfToken || '');
            
            const response = await fetch('ajax/notifications.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = data.unread_count;
                this.updateBadge(); // Actualizar badge inmediatamente
                this.loadNotifications(true); // Recargar solo no leídas
            } else {
                this.showToast('Error', data.message, 'error');
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
            this.showToast('Error', 'Error al marcar notificación como leída', 'error');
        }
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     */
    async markAllAsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_all_as_read');
            formData.append('csrf_token', window.csrfToken || '');
            
            const response = await fetch('ajax/notifications.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = 0;
                this.updateBadge(); // Actualizar badge inmediatamente
                this.loadNotifications(true); // Recargar solo no leídas
                this.showToast('Éxito', data.message, 'success');
            } else {
                this.showToast('Error', data.message, 'error');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            this.showToast('Error', 'Error al marcar todas las notificaciones como leídas', 'error');
        }
    }
    
    /**
     * Eliminar notificación
     */
    async deleteNotification(notificationId) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete_notification');
            formData.append('notification_id', notificationId);
            formData.append('csrf_token', window.csrfToken || '');
            
            const response = await fetch('ajax/notifications.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = data.unread_count;
                this.updateBadge(); // Actualizar badge inmediatamente
                this.loadNotifications(true); // Recargar solo no leídas
                this.showToast('Éxito', data.message, 'success');
            } else {
                this.showToast('Error', data.message, 'error');
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
            this.showToast('Error', 'Error al eliminar notificación', 'error');
        }
    }
    
    /**
     * Mostrar todas las notificaciones en un modal
     */
    showAllNotifications() {
        // Cargar todas las notificaciones (leídas y no leídas)
        this.loadNotifications(false);
    }
    
    /**
     * Mostrar notificación toast
     */
    showToast(title, message, type = 'info') {
        const container = document.getElementById('notification-toast-container');
        if (!container) return;
        
        const toastId = 'toast-' + Date.now();
        const iconClass = {
            'success': 'fas fa-check-circle text-success',
            'error': 'fas fa-exclamation-circle text-danger',
            'warning': 'fas fa-exclamation-triangle text-warning',
            'info': 'fas fa-info-circle text-info'
        }[type] || 'fas fa-info-circle text-info';
        
        const toastHtml = `
            <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="${iconClass} me-2"></i>
                    <strong class="me-auto">${this.escapeHtml(title)}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${this.escapeHtml(message)}
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        
        toast.show();
        
        // Eliminar el toast del DOM después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
    
    /**
     * Iniciar actualización automática
     */
    startAutoUpdate() {
        // Actualizar cada 30 segundos
        this.updateInterval = setInterval(() => {
            this.loadNotifications(true); // Recargar solo no leídas
        }, 30000);
    }
    
    /**
     * Detener actualización automática
     */
    stopAutoUpdate() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }
    
    /**
     * Destruir el manager de notificaciones
     */
    destroy() {
        this.stopAutoUpdate();
    }
}

// Inicializar el sistema de notificaciones cuando el DOM esté listo
let notificationManager;

document.addEventListener('DOMContentLoaded', function() {
    notificationManager = new NotificationManager();
});

// Limpiar al salir de la página
window.addEventListener('beforeunload', function() {
    if (notificationManager) {
        notificationManager.destroy();
    }
});