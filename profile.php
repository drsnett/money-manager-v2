 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'classes/User.php';

requireLogin();

$user = new User();
$currentUserId = getCurrentUserId();
$currentUser = $user->getById($currentUserId);

if (!$currentUser) {
    redirect('login.php');
}

$message = '';
$error = '';

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($fullName)) {
        $error = 'El nombre completo es requerido.';
    } elseif (empty($email)) {
        $error = 'El email es requerido.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } elseif (!empty($newPassword) && empty($currentPassword)) {
        $error = 'Debes ingresar tu contraseña actual para cambiarla.';
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $error = 'Las contraseñas nuevas no coinciden.';
    } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } else {
        // Verificar contraseña actual si se quiere cambiar
        if (!empty($newPassword)) {
            if (!password_verify($currentPassword, $currentUser['password'])) {
                $error = 'La contraseña actual es incorrecta.';
            }
        }
        
        if (empty($error)) {
            // Verificar si el email ya existe (excepto el usuario actual)
            $existingUser = $user->getByEmail($email);
            if ($existingUser && $existingUser['id'] != $currentUserId) {
                $error = 'Este email ya está en uso por otro usuario.';
            } else {
                // Actualizar datos
                $updateData = [
                    'full_name' => $fullName,
                    'email' => $email
                ];
                
                if (!empty($newPassword)) {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                
                if ($user->update($currentUserId, $updateData)) {
                    $message = 'Perfil actualizado correctamente.';
                    // Recargar datos del usuario
                    $currentUser = $user->getById($currentUserId);
                } else {
                    $error = 'Error al actualizar el perfil.';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-user-edit"></i> Mi Perfil</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="profile.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Usuario</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?php echo htmlspecialchars($currentUser['username']); ?>" 
                                           readonly disabled>
                                    <div class="form-text">El nombre de usuario no se puede cambiar.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="created_at" class="form-label">Fecha de Registro</label>
                                    <input type="text" class="form-control" id="created_at" 
                                           value="<?php echo formatDate($currentUser['created_at']); ?>" 
                                           readonly disabled>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <h5 class="mb-3">Cambiar Contraseña</h5>
                        <p class="text-muted">Deja estos campos vacíos si no quieres cambiar tu contraseña.</p>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           minlength="6">
                                    <div class="form-text">Mínimo 6 caracteres.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación en tiempo real de contraseñas
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword && confirmPassword) {
        if (newPassword === confirmPassword) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});

// Validar que si se ingresa nueva contraseña, se requiera la actual
document.getElementById('new_password').addEventListener('input', function() {
    const currentPassword = document.getElementById('current_password');
    if (this.value) {
        currentPassword.required = true;
    } else {
        currentPassword.required = false;
        currentPassword.classList.remove('is-invalid');
    }
});
</script>

<?php include 'includes/footer.php'; ?>