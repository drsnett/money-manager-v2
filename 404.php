<?php
require_once 'config/config.php';

$title = 'Página no encontrada - Error 404';
http_response_code(404);

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="display-4 text-muted">404</h1>
                    <h2 class="mb-3">Página no encontrada</h2>
                    <p class="lead mb-4">
                        Lo sentimos, la página que estás buscando no existe o ha sido movida.
                    </p>
                    
                    <div class="mb-4">
                        <p class="text-muted">
                            <strong>URL solicitada:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?>
                        </p>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="<?php echo url(); ?>" class="btn btn-primary btn-lg me-md-2">
                            <i class="bi bi-house-fill"></i> Ir al Inicio
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Volver Atrás
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row text-start">
                        <div class="col-md-6">
                            <h5><i class="bi bi-lightbulb"></i> Sugerencias:</h5>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> Verifica la URL en la barra de direcciones</li>
                                <li><i class="bi bi-check-circle text-success"></i> Usa el menú de navegación</li>
                                <li><i class="bi bi-check-circle text-success"></i> Regresa a la página principal</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="bi bi-link-45deg"></i> Enlaces útiles:</h5>
                            <ul class="list-unstyled">
                                <?php if (isLoggedIn()): ?>
                                <li><a href="<?php echo url('dashboard.php'); ?>" class="text-decoration-none"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                <li><a href="<?php echo url('transactions.php'); ?>" class="text-decoration-none"><i class="bi bi-list-ul"></i> Transacciones</a></li>
                                <li><a href="<?php echo url('reports.php'); ?>" class="text-decoration-none"><i class="bi bi-graph-up"></i> Reportes</a></li>
                                <?php else: ?>
                                <li><a href="<?php echo url('login.php'); ?>" class="text-decoration-none"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</a></li>
                                <!-- ENLACE DE REGISTRO DESHABILITADO -->
                                <!-- <li><a href="<?php echo url('register.php'); ?>" class="text-decoration-none"><i class="bi bi-person-plus"></i> Registrarse</a></li> -->
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>