<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Dev Network Solutions'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Validation Script -->
    <script src="js/validation.js"></script>
    
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #007bff;
        }

        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }

        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            min-height: 100vh;
            padding: 1rem;
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .dashboard-stat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .dashboard-stat.income {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .dashboard-stat.expense {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .dashboard-stat.balance {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .dashboard-stat.debt {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }

        .bank-stat {
            background: linear-gradient(135deg, #4834d4, #686de0);
            color: white;
        }

        .dashboard-stat h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin: 1rem 0;
        }

        .form-control, .form-select {
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .alert {
            border-radius: 0.5rem;
            border: none;
        }

        .pagination {
            justify-content: center;
        }

        .pagination .page-link {
            border-radius: 0.375rem;
            margin: 0 0.25rem;
            border: 1px solid #dee2e6;
        }

        .pagination .page-link:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .modal-content {
            border-radius: 0.5rem;
            border: none;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .text-income {
            color: var(--success-color);
        }

        .text-expense {
            color: var(--danger-color);
        }

        .loading {
            text-align: center;
            padding: 2rem;
        }

        .loading .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s;
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .dashboard-stat h3 {
                font-size: 1.5rem;
            }
            
            .navbar-nav-mobile {
                flex-direction: row !important;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }
            
            .dropdown-menu {
                position: absolute !important;
                right: 0 !important;
                left: auto !important;
                transform: none !important;
            }
            
            .navbar-brand {
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            /* Badge más visible en móviles */
            #notificationBadge {
                min-width: 22px !important;
                height: 22px !important;
                line-height: 22px !important;
                font-size: 0.8rem !important;
                border: 3px solid #fff !important;
                box-shadow: 0 3px 8px rgba(0,0,0,0.4) !important;
            }
        }

        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
            margin-top: auto;
        }

        .footer .text-muted {
            color: white !important;
        }

        /* Estructura para footer sticky */
        html, body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .container-fluid {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .row {
            flex: 1;
        }
        
        /* Estilos para notificaciones móviles */
        @media (max-width: 991.98px) {
            #notificationButton {
                padding: 0.5rem 0.75rem !important;
            }
            
            #notificationBadge {
                font-size: 0.65rem !important;
                min-width: 16px !important;
                height: 16px !important;
                line-height: 16px !important;
            }
            
            .dropdown-menu {
                margin-top: 0.5rem !important;
            }
        }
        
        /* Mejoras para el badge de notificaciones */
        #notificationBadge {
            z-index: 1050;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: 700 !important;
            color: #fff !important;
            background-color: #dc3545 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        /* Animación para nuevas notificaciones */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .notification-pulse {
            animation: pulse 1s ease-in-out 3;
        }
        
        /* Estilos para navegación móvil mejorada */
        .navbar-nav-mobile {
            width: 100%;
        }
        
        @media (max-width: 991.98px) {
            .navbar-nav {
                flex-direction: row;
                justify-content: flex-end;
                align-items: center;
                gap: 0.5rem;
            }
            
            .nav-item.dropdown .dropdown-menu {
                position: absolute;
                right: 0;
                left: auto;
                transform: translateX(0);
            }
            
            .navbar-brand {
                flex: 1;
            }
            
            /* Ocultar texto en móviles para ahorrar espacio */
            .nav-link .d-none-mobile {
                display: none;
            }
        }
        
        @media (min-width: 992px) {
            .navbar-nav-mobile {
                display: flex !important;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <?php if (isLoggedIn()): ?>
                <button class="btn btn-outline-light me-2 d-lg-none" type="button" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            <?php endif; ?>
            
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="bi bi-cash-coin"></i> Dev Network Solutions
            </a>
            
            <div class="navbar-nav-mobile d-block" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <!-- Botón de notificaciones -->
                        <li class="nav-item dropdown me-2 me-lg-3" id="notificationDropdown">
                            <a class="nav-link position-relative d-flex align-items-center" href="#" id="notificationButton" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none; font-size: 0.75rem; min-width: 20px; height: 20px; line-height: 20px; font-weight: bold; text-align: center; padding: 0;">
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="width: min(350px, 90vw); max-height: 70vh; overflow-y: auto;" id="notificationList">
                                <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notificaciones</span>
                                    <button class="btn btn-sm btn-outline-primary" onclick="notificationManager.markAllAsRead()" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                        Marcar todas
                                    </button>
                                </h6>
                                <div id="notificationItems">
                                    <div class="text-center p-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <div class="text-center p-2">
                                    <button class="btn btn-sm btn-outline-secondary" id="view-all-notifications" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                        Ver todas las notificaciones
                                    </button>
                                </div>
                            </div>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <span class="d-none d-lg-inline"><?php echo getCurrentUser()['full_name'] ?? 'Usuario'; ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>settings.php">
                                        <i class="bi bi-gear"></i> Configuración
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>login.php">
                                <i class="bi bi-box-arrow-in-right"></i> <span class="d-none d-lg-inline">Iniciar Sesión</span>
                            </a>
                        </li>
                        <?php /* Registro deshabilitado
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>register.php">
                                <i class="bi bi-person-plus"></i> Registrarse
                            </a>
                        </li>
                        */ ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar móvil overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="container-fluid">
        <div class="row">
            <?php if (isLoggedIn()): ?>
                <!-- Sidebar -->
                <nav class="col-lg-3 col-xl-2 sidebar" id="sidebar">
                    <div class="d-flex flex-column">
                        <div class="text-center mb-3">
                            <h5><i class="bi bi-person-circle"></i> <?php echo getCurrentUser()['username'] ?? 'Usuario'; ?></h5>
                            <small class="text-light">Bienvenid@ de vuelta</small>
                        </div>
                        
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>transactions.php">
                                    <i class="bi bi-arrow-left-right"></i> Transacciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>accounts-payable.php">
                                    <i class="bi bi-credit-card"></i> Cuentas por Pagar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>accounts-receivable.php">
                                    <i class="bi bi-cash-stack"></i> Cuentas por Cobrar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>credit-cards.php">
                                    <i class="bi bi-credit-card-2-front"></i> Tarjetas de Crédito
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>debts.php">
                                    <i class="bi bi-exclamation-triangle"></i> Deudas con Interés
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>bank_accounts.php">
                                    <i class="bi bi-bank"></i> Cuentas Bancarias
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>categories.php">
                                    <i class="bi bi-tags"></i> Categorías
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>reports.php">
                                    <i class="bi bi-graph-up"></i> Reportes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>documentation.php">
                                    <i class="bi bi-book"></i> Manual de Usuario
                                </a>
                            </li>
                            <?php if (isAdmin()): ?>
                                <li class="nav-item">
                                    <hr class="text-light">
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>whatsapp_admin.php">
                                        <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>admin/users.php">
                                        <i class="bi bi-people"></i> Usuarios
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>admin/backend-documentation.php">
                                        <i class="bi bi-code-slash"></i> Documentación Backend
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>
                
                <!-- Contenido principal -->
                <main class="col-lg-9 col-xl-10 ms-sm-auto px-4 py-3">
            <?php else: ?>
                <!-- Contenido sin sidebar -->
                <main class="col-12 px-4 py-3">
            <?php endif; ?>
