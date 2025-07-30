<?php
require_once 'config/config.php';
require_once 'classes/User.php';

// Verificar si existe la base de datos
$dbPath = __DIR__ . '/data/money_manager.db';
if (!file_exists($dbPath)) {
    redirect('install.php');
}

// Verificar si la base de datos tiene usuarios
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        redirect('install.php');
    }
} catch (Exception $e) {
    // Si hay error al acceder a la base de datos, redireccionar a instalación
    redirect('install.php');
}

$title = 'Iniciar Sesión';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        $user = new User();
        if ($user->login($username, $password)) {
            redirect('dashboard.php');
        } else {
            $error = 'Credenciales incorrectas. Intente nuevamente.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</h4>
                </div>
                <div class="card-body">
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
                            <label for="username" class="form-label">Usuario o Email</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <!-- ENLACE DE REGISTRO DESHABILITADO -->
                    <!-- <p class="mb-0">¿No tiene cuenta? <a href="register.php">Registrarse aquí</a></p> -->
                    <p class="mb-0 text-muted">Contacte al administrador para obtener una cuenta</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        if (!username || !password) {
            e.preventDefault();
            showAlert('Por favor, complete todos los campos.', 'danger');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
