<?php
require_once 'config/config.php';
require_once 'classes/User.php';

$title = 'Registrarse';
$error = '';
$success = '';

// REGISTRO DESHABILITADO
$registrationDisabled = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $fullName = sanitize($_POST['full_name']);
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($fullName)) {
        $error = 'Por favor, complete todos los campos.';
    } elseif (!validateEmail($email)) {
        $error = 'El email no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $user = new User();
        $userId = $user->register($username, $email, $password, $fullName);
        
        if ($userId) {
            $success = 'Registro exitoso. Ya puede iniciar sesión.';
            // Limpiar campos
            $username = $email = $fullName = '';
        } else {
            $error = 'El usuario o email ya existe.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="bi bi-person-plus"></i> Registrarse</h4>
                </div>
                <div class="card-body">
                    <?php if ($registrationDisabled): ?>
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <h5 class="mt-2">Registro Deshabilitado</h5>
                            <p class="mb-0">El registro de nuevos usuarios está temporalmente deshabilitado.</p>
                            <p class="mb-0">Contacte al administrador para crear una cuenta.</p>
                        </div>
                        
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Ir a Iniciar Sesión
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- FORMULARIO DE REGISTRO COMENTADO -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($fullName ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">Mínimo 6 caracteres</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Registrarse
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">¿Ya tiene cuenta? <a href="login.php">Iniciar sesión aquí</a></p>
                    <!-- Enlace de registro deshabilitado cuando $registrationDisabled = true -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (form && password && confirmPassword) {
        form.addEventListener('submit', function(e) {
            const requiredFields = ['full_name', 'username', 'email', 'password', 'confirm_password'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                isValid = false;
            } else {
                confirmPassword.classList.remove('is-invalid');
            }
            
            if (password.value.length < 6) {
                password.classList.add('is-invalid');
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        confirmPassword.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.classList.remove('is-invalid');
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
