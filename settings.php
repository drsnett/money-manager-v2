<?php
require_once 'config/config.php';
require_once 'classes/User.php';

requireLogin();

// Verificar que el usuario sea administrador
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$user = new User();
$currentUser = $user->getById(getCurrentUserId());

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Configuraciones por defecto (en una implementación real, esto vendría de la base de datos)
$settings = [
    'currency_symbol' => CURRENCY_SYMBOL ?? '$',
    'currency_decimals' => CURRENCY_DECIMALS ?? 2,
    'date_format' => 'd/m/Y',
    'timezone' => date_default_timezone_get(),
    'dashboard_items' => 6,
    'notifications_enabled' => true,
    'email_notifications' => false,
    'auto_backup' => false,
    'theme' => 'light'
];

// Manejo de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_preferences') {
        // En una implementación real, aquí guardarías las preferencias en la base de datos
        $message = 'Preferencias actualizadas exitosamente. Nota: Las configuraciones se aplicarán en la próxima actualización del sistema.';
        $messageType = 'success';
        
        // Actualizar configuraciones temporalmente
        $settings['currency_symbol'] = $_POST['currency_symbol'] ?? '$';
        $settings['date_format'] = $_POST['date_format'] ?? 'd/m/Y';
        $settings['dashboard_items'] = (int)($_POST['dashboard_items'] ?? 6);
        $settings['notifications_enabled'] = isset($_POST['notifications_enabled']);
        $settings['email_notifications'] = isset($_POST['email_notifications']);
        $settings['auto_backup'] = isset($_POST['auto_backup']);
        $settings['theme'] = $_POST['theme'] ?? 'light';
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-gear"></i> Configuraciones</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-sliders"></i> Preferencias del Usuario</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_preferences">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Formato y Visualización</h6>
                            
                            <div class="mb-3">
                                <label for="currency_symbol" class="form-label">Símbolo de Moneda</label>
                                <select class="form-select" id="currency_symbol" name="currency_symbol">
                                    <option value="$" <?php echo $settings['currency_symbol'] === '$' ? 'selected' : ''; ?>>$ (Dólar)</option>
                                    <option value="€" <?php echo $settings['currency_symbol'] === '€' ? 'selected' : ''; ?>>€ (Euro)</option>
                                    <option value="£" <?php echo $settings['currency_symbol'] === '£' ? 'selected' : ''; ?>>£ (Libra)</option>
                                    <option value="¥" <?php echo $settings['currency_symbol'] === '¥' ? 'selected' : ''; ?>>¥ (Yen/Yuan)</option>
                                    <option value="₹" <?php echo $settings['currency_symbol'] === '₹' ? 'selected' : ''; ?>>₹ (Rupia)</option>
                                    <option value="₩" <?php echo $settings['currency_symbol'] === '₩' ? 'selected' : ''; ?>>₩ (Won)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_format" class="form-label">Formato de Fecha</label>
                                <select class="form-select" id="date_format" name="date_format">
                                    <option value="d/m/Y" <?php echo $settings['date_format'] === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                    <option value="m/d/Y" <?php echo $settings['date_format'] === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                    <option value="Y-m-d" <?php echo $settings['date_format'] === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                    <option value="d-M-Y" <?php echo $settings['date_format'] === 'd-M-Y' ? 'selected' : ''; ?>>DD-MMM-YYYY</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dashboard_items" class="form-label">Elementos en Dashboard</label>
                                <select class="form-select" id="dashboard_items" name="dashboard_items">
                                    <option value="4" <?php echo $settings['dashboard_items'] === 4 ? 'selected' : ''; ?>>4 elementos</option>
                                    <option value="6" <?php echo $settings['dashboard_items'] === 6 ? 'selected' : ''; ?>>6 elementos</option>
                                    <option value="8" <?php echo $settings['dashboard_items'] === 8 ? 'selected' : ''; ?>>8 elementos</option>
                                    <option value="10" <?php echo $settings['dashboard_items'] === 10 ? 'selected' : ''; ?>>10 elementos</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="theme" class="form-label">Tema de la Interfaz</label>
                                <select class="form-select" id="theme" name="theme">
                                    <option value="light" <?php echo $settings['theme'] === 'light' ? 'selected' : ''; ?>>Claro</option>
                                    <option value="dark" <?php echo $settings['theme'] === 'dark' ? 'selected' : ''; ?>>Oscuro</option>
                                    <option value="auto" <?php echo $settings['theme'] === 'auto' ? 'selected' : ''; ?>>Automático</option>
                                </select>
                                <div class="form-text">El tema oscuro estará disponible en futuras versiones.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="mb-3">Notificaciones y Alertas</h6>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notifications_enabled" name="notifications_enabled" <?php echo $settings['notifications_enabled'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notifications_enabled">
                                        Habilitar Notificaciones
                                    </label>
                                </div>
                                <div class="form-text">Mostrar alertas de vencimientos y recordatorios.</div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">
                                        Notificaciones por Email
                                    </label>
                                </div>
                                <div class="form-text">Funcionalidad disponible en futuras versiones.</div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto_backup" name="auto_backup" <?php echo $settings['auto_backup'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto_backup">
                                        Respaldo Automático
                                    </label>
                                </div>
                                <div class="form-text">Crear respaldos automáticos de la base de datos.</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Configuraciones
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Restablecer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Información del Sistema</h5>
            </div>
            <div class="card-body">
                <p><strong>Versión:</strong> 1.0.0</p>
                <p><strong>PHP:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Base de Datos:</strong> SQLite</p>
                <p><strong>Zona Horaria:</strong> <?php echo $settings['timezone']; ?></p>
                <p><strong>Usuario Actual:</strong> <?php echo htmlspecialchars($currentUser['username']); ?></p>
                <p><strong>Tipo:</strong> 
                    <span class="badge bg-<?php echo $currentUser['is_admin'] ? 'danger' : 'primary'; ?>">
                        <?php echo $currentUser['is_admin'] ? 'Admin' : 'Usuario'; ?>
                    </span>
                </p>
            </div>
        </div>
        
       <!-- <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-tools"></i> Herramientas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="test.php" class="btn btn-outline-info btn-sm" target="_blank">
                        <i class="bi bi-clipboard-check"></i> Verificar Sistema
                    </a>
                    <a href="troubleshoot.php" class="btn btn-outline-warning btn-sm" target="_blank">
                        <i class="bi bi-wrench"></i> Diagnóstico
                    </a>
                    <?php if ($currentUser['is_admin']): ?>
                        <a href="admin/users.php" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-people"></i> Gestión de Usuarios
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>-->
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-question-circle"></i> Ayuda</h5>
            </div>
            <div class="card-body">
                <p class="small">Para obtener ayuda adicional:</p>
                <ul class="small">
                    <li><a href="README.md" target="_blank">Documentación</a></li>
                    <li><a href="DEPLOYMENT.md" target="_blank">Guía de Despliegue</a></li>
                    <li><a href="DATABASE_TROUBLESHOOTING.md" target="_blank">Solución de Problemas</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
