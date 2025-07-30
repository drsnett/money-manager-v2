<?php
require_once '../config/config.php';
require_once '../classes/User.php';

requireLogin();

// Verificar que el usuario sea administrador
$currentUser = getCurrentUser();
if (!$currentUser || !$currentUser['is_admin']) {
    header('Location: ../dashboard.php');
    exit;
}

$user = new User();
$message = '';
$messageType = '';

// Manejo de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_user') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $isAdmin = isset($_POST['is_admin']);
        
        if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
            $message = 'Todos los campos son obligatorios.';
            $messageType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'El formato del email no es válido.';
            $messageType = 'danger';
        } elseif (strlen($password) < 6) {
            $message = 'La contraseña debe tener al menos 6 caracteres.';
            $messageType = 'danger';
        } else {
            try {
                $userId = $user->register($username, $email, $password, $fullName);
                if ($userId && $isAdmin) {
                    $user->setAdminStatus($userId, true);
                }
                $message = 'Usuario creado exitosamente.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'toggle_admin') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $isAdmin = isset($_POST['is_admin']);
        
        if ($userId && $userId !== getCurrentUserId()) {
            try {
                $result = $user->setAdminStatus($userId, $isAdmin);
                if ($result) {
                    $message = 'Estado de administrador actualizado.';
                    $messageType = 'success';
                } else {
                    $message = 'Error al actualizar el estado.';
                    $messageType = 'danger';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } else {
            $message = 'No puede cambiar su propio estado de administrador.';
            $messageType = 'warning';
        }
    } elseif ($action === 'change_password') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        
        if ($userId && !empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $message = 'La contraseña debe tener al menos 6 caracteres.';
                $messageType = 'danger';
            } else {
                try {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $result = $user->update($userId, ['password' => $hashedPassword]);
                    if ($result) {
                        $message = 'Contraseña cambiada exitosamente.';
                        $messageType = 'success';
                    } else {
                        $message = 'Error al cambiar la contraseña.';
                        $messageType = 'danger';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            }
        } else {
            $message = 'Datos inválidos para cambiar contraseña.';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        
        if ($userId && $userId !== getCurrentUserId()) {
            try {
                $result = $user->deleteUser($userId);
                if ($result) {
                    $message = 'Usuario eliminado exitosamente.';
                    $messageType = 'success';
                } else {
                    $message = 'Error al eliminar el usuario.';
                    $messageType = 'danger';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } else {
            $message = 'No puede eliminar su propia cuenta.';
            $messageType = 'warning';
        }
    }
}

// Obtener lista de usuarios
$users = $user->getAllUsers();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people"></i> Gestión de Usuarios</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus"></i> Nuevo Usuario
            </button>
            <a href="../dashboard.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Usuarios del Sistema</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Fecha Registro</th>
                        <th>Última Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $userItem): ?>
                    <tr>
                        <td><?php echo $userItem['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($userItem['username']); ?></strong>
                            <?php if ($userItem['id'] == getCurrentUserId()): ?>
                                <span class="badge bg-info">Tú</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($userItem['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($userItem['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $userItem['is_admin'] ? 'danger' : 'primary'; ?>">
                                <?php echo $userItem['is_admin'] ? 'Administrador' : 'Usuario'; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($userItem['created_at']); ?></td>
                        <td><?php echo isset($userItem['updated_at']) ? formatDate($userItem['updated_at']) : 'No disponible'; ?></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="../profile.php?user_id=<?php echo $userItem['id']; ?>" class="btn btn-outline-info" title="Ver Perfil">
                                    <i class="bi bi-person-circle"></i>
                                </a>
                                <?php if ($userItem['id'] !== getCurrentUserId()): ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="changePassword(<?php echo $userItem['id']; ?>, '<?php echo htmlspecialchars($userItem['username']); ?>')" title="Cambiar Contraseña">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAdmin(<?php echo $userItem['id']; ?>, <?php echo $userItem['is_admin'] ? 'false' : 'true'; ?>)" title="<?php echo $userItem['is_admin'] ? 'Quitar Admin' : 'Hacer Admin'; ?>">
                                        <i class="bi bi-<?php echo $userItem['is_admin'] ? 'person-dash' : 'person-check'; ?>"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteUser(<?php echo $userItem['id']; ?>, '<?php echo htmlspecialchars($userItem['username']); ?>')" title="Eliminar Usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="changePassword(<?php echo $userItem['id']; ?>, '<?php echo htmlspecialchars($userItem['username']); ?>')" title="Cambiar Mi Contraseña">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <span class="text-muted small ms-2">Tu cuenta</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-bar-chart"></i> Estadísticas</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary"><?php echo count($users); ?></h3>
                            <p class="card-text">Total Usuarios</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger"><?php echo count(array_filter($users, function($u) { return $u['is_admin']; })); ?></h3>
                            <p class="card-text">Administradores</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success"><?php echo count(array_filter($users, function($u) { return !$u['is_admin']; })); ?></h3>
                            <p class="card-text">Usuarios Regulares</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info"><?php echo count(array_filter($users, function($u) { return date('Y-m-d', strtotime($u['created_at'])) === date('Y-m-d'); })); ?></h3>
                            <p class="card-text">Nuevos Hoy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_user">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin">
                            <label class="form-check-label" for="is_admin">
                                Administrador
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para cambiar contraseña -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="changePasswordForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="user_id" id="change_password_user_id">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Cambiando contraseña para: <strong id="change_password_username"></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_new_password" required minlength="6">
                        <div class="invalid-feedback">Las contraseñas no coinciden</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="changePasswordBtn">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formularios ocultos para acciones -->
<form id="toggleAdminForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="toggle_admin">
    <input type="hidden" name="user_id" id="toggle_user_id">
    <input type="hidden" name="is_admin" id="toggle_is_admin">
</form>

<form id="deleteUserForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<script>
function toggleAdmin(userId, isAdmin) {
    if (confirm('¿Está seguro de que desea cambiar el estado de administrador?')) {
        document.getElementById('toggle_user_id').value = userId;
        if (isAdmin) {
            document.getElementById('toggle_is_admin').value = '1';
        } else {
            document.getElementById('toggle_is_admin').removeAttribute('value');
        }
        document.getElementById('toggleAdminForm').submit();
    }
}

function deleteUser(userId, username) {
    if (confirm('¿Está seguro de que desea eliminar al usuario "' + username + '"?\n\nEsta acción no se puede deshacer y eliminará todos los datos asociados.')) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('deleteUserForm').submit();
    }
}

function changePassword(userId, username) {
    document.getElementById('change_password_user_id').value = userId;
    document.getElementById('change_password_username').textContent = username;
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_new_password').value = '';
    document.getElementById('confirm_new_password').classList.remove('is-valid', 'is-invalid');
    document.getElementById('changePasswordBtn').disabled = false;
    
    var modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

// Validación en tiempo real de contraseñas
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_new_password');
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    
    function validatePasswords() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length > 0) {
            if (newPassword === confirmPassword && newPassword.length >= 6) {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
                changePasswordBtn.disabled = false;
            } else {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
                changePasswordBtn.disabled = true;
            }
        } else {
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
            changePasswordBtn.disabled = newPassword.length < 6;
        }
    }
    
    newPasswordInput.addEventListener('input', validatePasswords);
    confirmPasswordInput.addEventListener('input', validatePasswords);
    
    // Validar antes de enviar
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Las contraseñas no coinciden.');
            return false;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 6 caracteres.');
            return false;
        }
        
        return confirm('¿Está seguro de que desea cambiar la contraseña de este usuario?');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
