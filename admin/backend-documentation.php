<?php
require_once '../config/config.php';
require_once '../classes/User.php';

// Verificar autenticación y permisos de administrador
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user = new User();
$userData = $user->getById($_SESSION['user_id']);

if (!$userData || !$userData['is_admin']) {
    header('Location: ../dashboard.php');
    exit;
}

include '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-code"></i>
                        Documentación del Backend - Sistema de Gestión Financiera
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Navegación de secciones -->
                    <ul class="nav nav-tabs" id="docTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                                <i class="fas fa-info-circle"></i> Visión General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="structure-tab" data-bs-toggle="tab" data-bs-target="#structure" type="button" role="tab">
                                <i class="fas fa-sitemap"></i> Estructura
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button" role="tab">
                                <i class="fas fa-cube"></i> Clases
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="creditcard-tab" data-bs-toggle="tab" data-bs-target="#creditcard" type="button" role="tab">
                                <i class="fas fa-credit-card"></i> CreditCard
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab">
                                <i class="fas fa-cog"></i> Configuración
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ajax-tab" data-bs-toggle="tab" data-bs-target="#ajax" type="button" role="tab">
                                <i class="fas fa-exchange-alt"></i> AJAX
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cache-tab" data-bs-toggle="tab" data-bs-target="#cache" type="button" role="tab">
                                <i class="fas fa-tachometer-alt"></i> Sistema de Caché
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab">
                                <i class="fas fa-database"></i> Base de Datos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="production-tab" data-bs-toggle="tab" data-bs-target="#production" type="button" role="tab">
                                <i class="fas fa-rocket"></i> Producción
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                                <i class="fas fa-save"></i> Backup
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="monitoring-tab" data-bs-toggle="tab" data-bs-target="#monitoring" type="button" role="tab">
                                <i class="fas fa-chart-line"></i> Monitoreo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt"></i> Seguridad
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="docTabsContent">
                        <!-- Visión General -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <h5><i class="fas fa-info-circle text-primary"></i> Visión General del Sistema</h5>
                            <div class="alert alert-success">
                                <strong>Money Manager v2.0 - Sistema de Gestión Financiera Personal</strong><br>
                                Aplicación web completa desarrollada en PHP con SQLite para la gestión integral de finanzas personales, <strong>lista para producción</strong> con herramientas avanzadas de despliegue, backup y monitoreo.
                            </div>
                            
                            <h6>Características Principales:</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Gestión de transacciones (ingresos y gastos)</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Cuentas por pagar y por cobrar</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Gestión de tarjetas de crédito con estados dinámicos</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Control de deudas con intereses</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Cuentas bancarias múltiples</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Reportes y análisis financiero avanzado</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Sistema de usuarios con roles</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Sistema de caché avanzado para rendimiento</li>
                                <li class="list-group-item"><i class="fas fa-rocket text-warning"></i> <strong>Scripts de despliegue automatizado</strong></li>
                                <li class="list-group-item"><i class="fas fa-save text-warning"></i> <strong>Sistema de backup automático</strong></li>
                                <li class="list-group-item"><i class="fas fa-chart-line text-warning"></i> <strong>Monitoreo del sistema en tiempo real</strong></li>
                                <li class="list-group-item"><i class="fas fa-shield-alt text-warning"></i> <strong>Configuraciones de seguridad avanzadas</strong></li>
                            </ul>

                            <h6 class="mt-4">Tecnologías Utilizadas:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fab fa-php text-primary"></i> <strong>PHP 8.0+</strong> - Backend con PDO</li>
                                        <li><i class="fas fa-database text-info"></i> <strong>SQLite 3</strong> - Base de datos</li>
                                        <li><i class="fab fa-bootstrap text-purple"></i> <strong>Bootstrap 5</strong> - Framework CSS</li>
                                        <li><i class="fas fa-server text-secondary"></i> <strong>Apache/Nginx</strong> - Servidor web</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fab fa-js text-warning"></i> <strong>JavaScript ES6+</strong> - Interactividad</li>
                                        <li><i class="fas fa-chart-bar text-success"></i> <strong>Chart.js</strong> - Gráficos dinámicos</li>
                                        <li><i class="fas fa-icons text-danger"></i> <strong>Font Awesome 6</strong> - Iconos</li>
                                        <li><i class="fas fa-lock text-dark"></i> <strong>HTTPS/SSL</strong> - Seguridad</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="alert alert-info mt-4">
                                <i class="fas fa-rocket"></i>
                                <strong>¡Listo para Producción!</strong> El sistema incluye todas las herramientas necesarias para un despliegue seguro y eficiente en entornos de producción, con scripts automatizados (<code>deploy.php</code>, <code>backup.php</code>, <code>monitor.php</code>), configuraciones de seguridad optimizadas y documentación técnica completa.
                            </div>
                        </div>

                        <!-- Estructura del Proyecto -->
                        <div class="tab-pane fade" id="structure" role="tabpanel">
                            <h5><i class="fas fa-sitemap text-primary"></i> Estructura del Proyecto</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Archivos Principales:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-dark">
                                                <tr><th>Archivo</th><th>Función</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr><td><code>index.php</code></td><td>Página de inicio/redirección</td></tr>
                                                <tr><td><code>login.php</code></td><td>Autenticación de usuarios</td></tr>
                                                <tr><td><code>register.php</code></td><td>Registro de usuarios (deshabilitado)</td></tr>
                                                <tr><td><code>dashboard.php</code></td><td>Panel principal del usuario</td></tr>
                                                <tr><td><code>logout.php</code></td><td>Cierre de sesión</td></tr>
                                                <tr><td><code>install.php</code></td><td>Instalación inicial del sistema</td></tr>
                                                <tr><td><code>install_process.php</code></td><td>Procesamiento de instalación</td></tr>
                                                <tr><td><code>deploy.php</code></td><td>Script de despliegue a producción</td></tr>
                                                <tr><td><code>backup.php</code></td><td>Sistema de backup automatizado</td></tr>
                                                <tr><td><code>monitor.php</code></td><td>Monitoreo del sistema</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Módulos Funcionales:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-dark">
                                                <tr><th>Archivo</th><th>Función</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr><td><code>transactions.php</code></td><td>Gestión de transacciones</td></tr>
                                                <tr><td><code>accounts-payable.php</code></td><td>Cuentas por pagar</td></tr>
                                                <tr><td><code>accounts-receivable.php</code></td><td>Cuentas por cobrar</td></tr>
                                                <tr><td><code>credit-cards.php</code></td><td>Tarjetas de crédito</td></tr>
                                                <tr><td><code>debts.php</code></td><td>Gestión de deudas</td></tr>
                                                <tr><td><code>bank-accounts.php</code></td><td>Cuentas bancarias</td></tr>
                                                <tr><td><code>categories.php</code></td><td>Categorías de transacciones</td></tr>
                                                <tr><td><code>reports.php</code></td><td>Reportes financieros</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">Directorios:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <i class="fas fa-folder"></i> /classes/
                                        </div>
                                        <div class="card-body">
                                            <small>Clases PHP del modelo de datos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <i class="fas fa-folder"></i> /config/
                                        </div>
                                        <div class="card-body">
                                            <small>Archivos de configuración</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <i class="fas fa-folder"></i> /ajax/
                                        </div>
                                        <div class="card-body">
                                            <small>Endpoints AJAX</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-folder"></i> /backups/
                                        </div>
                                        <div class="card-body">
                                            <small>Backups automáticos del sistema</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <div class="card">
                                        <div class="card-header bg-warning text-white">
                                            <i class="fas fa-folder"></i> /logs/
                                        </div>
                                        <div class="card-body">
                                            <small>Logs del sistema y errores</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <i class="fas fa-file"></i> Archivos de Configuración
                                        </div>
                                        <div class="card-body">
                                            <small>.env.production, server-config.md, PRODUCTION_CHECKLIST.md</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clases del Sistema -->
                        <div class="tab-pane fade" id="classes" role="tabpanel">
                            <h5><i class="fas fa-cube text-primary"></i> Clases del Sistema</h5>
                            
                            <div class="accordion" id="classesAccordion">
                                <!-- User Class -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="userClass">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUser">
                                            <i class="fas fa-user text-primary me-2"></i> User.php
                                        </button>
                                    </h2>
                                    <div id="collapseUser" class="accordion-collapse collapse show" data-bs-parent="#classesAccordion">
                                        <div class="accordion-body">
                                            <strong>Función:</strong> Gestión de usuarios del sistema<br>
                                            <strong>Métodos principales:</strong>
                                            <ul>
                                                <li><code>create()</code> - Crear nuevo usuario</li>
                                                <li><code>authenticate()</code> - Autenticar credenciales</li>
                                                <li><code>getById()</code> - Obtener usuario por ID</li>
                                                <li><code>update()</code> - Actualizar datos del usuario</li>
                                                <li><code>changePassword()</code> - Cambiar contraseña</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Class -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="transactionClass">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTransaction">
                                            <i class="fas fa-exchange-alt text-success me-2"></i> Transaction.php
                                        </button>
                                    </h2>
                                    <div id="collapseTransaction" class="accordion-collapse collapse" data-bs-parent="#classesAccordion">
                                        <div class="accordion-body">
                                            <strong>Función:</strong> Gestión de transacciones financieras<br>
                                            <strong>Métodos principales:</strong>
                                            <ul>
                                                <li><code>create()</code> - Crear nueva transacción</li>
                                                <li><code>getByUser()</code> - Obtener transacciones por usuario</li>
                                                <li><code>getById()</code> - Obtener transacción específica</li>
                                                <li><code>update()</code> - Actualizar transacción</li>
                                                <li><code>delete()</code> - Eliminar transacción</li>
                                                <li><code>getBalance()</code> - Calcular balance</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- AccountPayable Class -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="payableClass">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayable">
                                            <i class="fas fa-file-invoice-dollar text-danger me-2"></i> AccountPayable.php
                                        </button>
                                    </h2>
                                    <div id="collapsePayable" class="accordion-collapse collapse" data-bs-parent="#classesAccordion">
                                        <div class="accordion-body">
                                            <strong>Función:</strong> Gestión de cuentas por pagar<br>
                                            <strong>Métodos principales:</strong>
                                            <ul>
                                                <li><code>create()</code> - Crear cuenta por pagar</li>
                                                <li><code>addPayment()</code> - Registrar pago</li>
                                                <li><code>getUpcoming()</code> - Pagos próximos</li>
                                                <li><code>getOverdue()</code> - Pagos vencidos</li>
                                                <li><code>updateStatus()</code> - Actualizar estado</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Otras clases -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="otherClasses">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOther">
                                            <i class="fas fa-cubes text-info me-2"></i> Otras Clases
                                        </button>
                                    </h2>
                                    <div id="collapseOther" class="accordion-collapse collapse" data-bs-parent="#classesAccordion">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                    <h6>AccountReceivable.php</h6>
                                    <p><small>Gestión de cuentas por cobrar</small></p>
                                    
                                    <h6>BankAccount.php</h6>
                                    <p><small>Gestión de cuentas bancarias</small></p>
                                    
                                    <h6>Category.php</h6>
                                    <p><small>Categorías de transacciones</small></p>
                                    
                                    <h6>Cache.php</h6>
                                    <p><small>Sistema de caché dual (archivos + APCu)</small></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>CreditCard.php</h6>
                                    <p><small>Gestión avanzada de tarjetas de crédito con estados dinámicos</small></p>
                                    <div class="mt-2">
                                        <span class="badge bg-success me-1">Estados Dinámicos</span>
                                        <span class="badge bg-info me-1">Cálculos Automáticos</span>
                                        <span class="badge bg-warning me-1">Personalización</span>
                                    </div>
                                    
                                    <h6>Debt.php</h6>
                                    <p><small>Gestión de deudas con intereses</small></p>
                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clase CreditCard -->
                        <div class="tab-pane fade" id="creditcard" role="tabpanel">
                            <h5><i class="fas fa-credit-card text-primary"></i> Clase CreditCard</h5>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-star"></i>
                                <strong>Sistema de Estados Dinámicos:</strong> Implementación avanzada que calcula automáticamente 
                                el estado de las tarjetas basado en fechas de corte, balances y pagos realizados.
                            </div>

                            <!-- Estados Dinámicos -->
                            <h6>Estados Dinámicos Automáticos:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-check-circle"></i> Estados Disponibles
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-circle text-success"></i> <strong>Pagada</strong> - Balance en cero</li>
                                                <li><i class="fas fa-circle text-warning"></i> <strong>Pendiente</strong> - Dentro del período de gracia</li>
                                                <li><i class="fas fa-circle text-danger"></i> <strong>Vencida</strong> - Fuera del período de gracia</li>
                                                <li><i class="fas fa-circle text-primary"></i> <strong>Activa</strong> - Estado por defecto</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <i class="fas fa-calculator"></i> Lógica de Cálculo
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success"></i> <strong>Fecha de Corte:</strong> Cálculo automático mensual</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Balance:</strong> Suma de transacciones menos pagos</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Período de Gracia:</strong> 25 días después del corte</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Actualización:</strong> En tiempo real</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Métodos Principales -->
                            <h6 class="mt-4">Métodos Principales:</h6>
                            <div class="accordion" id="creditCardAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="creditCardMethods">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCreditCardMethods">
                                            <i class="fas fa-code text-primary me-2"></i> Métodos de Estados y Cálculos
                                        </button>
                                    </h2>
                                    <div id="collapseCreditCardMethods" class="accordion-collapse collapse show" data-bs-parent="#creditCardAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Método</th>
                                                            <th>Descripción</th>
                                                            <th>Retorno</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><code>getDynamicCardStatus($cardId)</code></td>
                                                            <td>Calcula el estado dinámico de la tarjeta</td>
                                                            <td>string (paid, pending, overdue, active)</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>getCardBalance($cardId)</code></td>
                                                            <td>Obtiene el balance actual de la tarjeta</td>
                                                            <td>float</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>getCreditUtilization($cardId)</code></td>
                                                            <td>Calcula el porcentaje de utilización del crédito</td>
                                                            <td>float (0-100)</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>getLastCutOffDate($cardId)</code></td>
                                                            <td>Calcula la última fecha de corte</td>
                                                            <td>DateTime</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>getNextCutOffDate($cardId)</code></td>
                                                            <td>Calcula la próxima fecha de corte</td>
                                                            <td>DateTime</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>getPaymentDueDate($cardId)</code></td>
                                                            <td>Calcula la fecha límite de pago</td>
                                                            <td>DateTime</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="creditCardCrud">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCreditCardCrud">
                                            <i class="fas fa-database text-info me-2"></i> Operaciones CRUD
                                        </button>
                                    </h2>
                                    <div id="collapseCreditCardCrud" class="accordion-collapse collapse" data-bs-parent="#creditCardAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Método</th>
                                                            <th>Descripción</th>
                                                            <th>Parámetros</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><code>create($data)</code></td>
                                                            <td>Crear nueva tarjeta de crédito</td>
                                                            <td>array con datos de la tarjeta</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>getAll($userId)</code></td>
                                                            <td>Obtener todas las tarjetas del usuario</td>
                                                            <td>int userId</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>getById($id)</code></td>
                                                            <td>Obtener tarjeta específica por ID</td>
                                                            <td>int id</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>update($id, $data)</code></td>
                                                            <td>Actualizar datos de la tarjeta</td>
                                                            <td>int id, array data</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>delete($id)</code></td>
                                                            <td>Eliminar tarjeta (con validaciones)</td>
                                                            <td>int id</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>addTransaction($cardId, $data)</code></td>
                                                            <td>Agregar transacción a la tarjeta</td>
                                                            <td>int cardId, array data</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personalización -->
                            <h6 class="mt-4">Sistema de Personalización:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <i class="fas fa-palette"></i> Colores de Tarjetas
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-circle text-primary"></i> <strong>Azul:</strong> #007bff (por defecto)</li>
                                                <li><i class="fas fa-circle text-success"></i> <strong>Verde:</strong> #28a745</li>
                                                <li><i class="fas fa-circle text-danger"></i> <strong>Rojo:</strong> #dc3545</li>
                                                <li><i class="fas fa-circle text-warning"></i> <strong>Amarillo:</strong> #ffc107</li>
                                                <li><i class="fas fa-circle text-info"></i> <strong>Cian:</strong> #17a2b8</li>
                                                <li><i class="fas fa-circle text-dark"></i> <strong>Negro:</strong> #343a40</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-secondary">
                                        <div class="card-header bg-secondary text-white">
                                            <i class="fas fa-eye"></i> Visualización
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success"></i> <strong>Badges de Estado:</strong> Colores dinámicos</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Iconos:</strong> Representación visual del estado</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Tarjetas:</strong> Colores personalizables</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Responsive:</strong> Adaptable a dispositivos</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seguridad y Validaciones -->
                            <div class="alert alert-warning mt-4">
                                <i class="fas fa-shield-alt"></i>
                                <strong>Seguridad y Validaciones:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Validación de Usuario:</strong> Todas las operaciones verifican la propiedad de la tarjeta</li>
                                    <li><strong>Transacciones Seguras:</strong> Uso de transacciones de base de datos para operaciones críticas</li>
                                    <li><strong>Sanitización:</strong> Todos los datos de entrada son sanitizados y validados</li>
                                    <li><strong>Eliminación Segura:</strong> Verificación de dependencias antes de eliminar</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="tab-pane fade" id="config" role="tabpanel">
                            <h5><i class="fas fa-cog text-primary"></i> Archivos de Configuración</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Archivo</th>
                                            <th>Función</th>
                                            <th>Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>config/config.php</code></td>
                                            <td>Configuración principal</td>
                                            <td>Configuración de sesiones, zona horaria y constantes globales</td>
                                        </tr>
                                        <tr>
                                            <td><code>config/database.php</code></td>
                                            <td>Clase Database</td>
                                            <td>Conexión SQLite, creación de tablas y gestión de la base de datos</td>
                                        </tr>
                                        <tr>
                                            <td><code>config/auto_config.php</code></td>
                                            <td>Configuración automática</td>
                                            <td>Detección automática del entorno y configuración</td>
                                        </tr>
                                        <tr>
                                            <td><code>config/production.php</code></td>
                                            <td>Configuración de producción</td>
                                            <td>Configuraciones específicas para el entorno de producción</td>
                                        </tr>
                                        <tr>
                                            <td><code>config/server_config.php</code></td>
                                            <td>Configuración del servidor</td>
                                            <td>Configuraciones específicas del servidor web</td>
                                        </tr>
                                        <tr class="table-success">
                                            <td><code>.env.production</code></td>
                                            <td>Variables de entorno de producción</td>
                                            <td>Configuración optimizada para producción con seguridad avanzada</td>
                                        </tr>
                                        <tr class="table-info">
                                            <td><code>server-config.md</code></td>
                                            <td>Guía de configuración del servidor</td>
                                            <td>Documentación completa para Apache, Nginx, PHP y seguridad</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><code>PRODUCTION_CHECKLIST.md</code></td>
                                            <td>Lista de verificación de producción</td>
                                            <td>Checklist completo para despliegue seguro en producción</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-success mt-4">
                                <i class="fas fa-shield-alt"></i>
                                <strong>Configuración de Producción:</strong> El sistema incluye configuraciones específicas 
                                para producción con medidas de seguridad avanzadas, monitoreo y backup automatizado.
                            </div>

                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> Los archivos de configuración contienen información sensible. 
                                Asegúrese de que estén protegidos y no sean accesibles públicamente.
                            </div>
                        </div>

                        <!-- AJAX Endpoints -->
                        <div class="tab-pane fade" id="ajax" role="tabpanel">
                            <h5><i class="fas fa-exchange-alt text-primary"></i> Endpoints AJAX</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Endpoint</th>
                                            <th>Método</th>
                                            <th>Función</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>ajax/get_payments.php</code></td>
                                            <td>GET</td>
                                            <td>Obtener lista de pagos realizados</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/delete_payment.php</code></td>
                                            <td>POST</td>
                                            <td>Eliminar un pago específico</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/get_receipts.php</code></td>
                                            <td>GET</td>
                                            <td>Obtener lista de cobros recibidos</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/delete_receipt.php</code></td>
                                            <td>POST</td>
                                            <td>Eliminar un cobro específico</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/get_credit_card_transactions.php</code></td>
                                            <td>GET</td>
                                            <td>Obtener transacciones de tarjeta de crédito</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/delete_credit_card_transaction.php</code></td>
                                            <td>POST</td>
                                            <td>Eliminar transacción de tarjeta (con validaciones de seguridad)</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/update_card_color.php</code></td>
                                            <td>POST</td>
                                            <td>Actualizar color personalizado de tarjeta</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/get_card_status.php</code></td>
                                            <td>GET</td>
                                            <td>Obtener estado dinámico de tarjeta</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/get_debt_details.php</code></td>
                                            <td>GET</td>
                                            <td>Obtener detalles de una deuda</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/get_amortization.php</code></td>
                                            <td>GET</td>
                                            <td>Calcular tabla de amortización</td>
                                        </tr>
                                        <tr>
                                            <td><code>ajax/delete_debt_payment.php</code></td>
                                            <td>POST</td>
                                            <td>Eliminar pago de deuda</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle"></i>
                                <strong>Nota:</strong> Todos los endpoints AJAX requieren autenticación de usuario y 
                                devuelven respuestas en formato JSON.
                            </div>
                        </div>

                        <!-- Sistema de Caché -->
                        <div class="tab-pane fade" id="cache" role="tabpanel">
                            <h5><i class="fas fa-tachometer-alt text-primary"></i> Sistema de Caché</h5>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-rocket"></i>
                                <strong>Sistema de Caché Dual:</strong> Implementación avanzada con soporte para archivos y APCu 
                                que mejora significativamente el rendimiento del sistema.
                            </div>

                            <!-- Características Técnicas -->
                            <h6>Características Técnicas:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <i class="fas fa-cogs"></i> Arquitectura
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success"></i> <strong>Patrón Singleton</strong> - Instancia única</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Caché Dual</strong> - Archivos + APCu</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Serialización</strong> - Soporte completo de tipos</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>TTL Configurable</strong> - Expiración automática</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <i class="fas fa-tools"></i> Funcionalidades
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success"></i> <strong>Operaciones Numéricas</strong> - increment/decrement</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Estadísticas</strong> - Monitoreo completo</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Limpieza Automática</strong> - Archivos expirados</li>
                                                <li><i class="fas fa-check text-success"></i> <strong>Manejo de Errores</strong> - Robusto y confiable</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Clase Cache -->
                            <h6 class="mt-4">Clase Cache.php:</h6>
                            <div class="accordion" id="cacheAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="cacheClass">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCacheClass">
                                            <i class="fas fa-cube text-primary me-2"></i> Métodos Principales
                                        </button>
                                    </h2>
                                    <div id="collapseCacheClass" class="accordion-collapse collapse show" data-bs-parent="#cacheAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Método</th>
                                                            <th>Descripción</th>
                                                            <th>Parámetros</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><code>set($key, $value, $ttl)</code></td>
                                                            <td>Almacenar valor en caché</td>
                                                            <td>key, value, ttl (opcional)</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>get($key, $default)</code></td>
                                                            <td>Obtener valor del caché</td>
                                                            <td>key, default (opcional)</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>has($key)</code></td>
                                                            <td>Verificar existencia de clave</td>
                                                            <td>key</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>delete($key)</code></td>
                                                            <td>Eliminar entrada específica</td>
                                                            <td>key</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>remember($key, $callback, $ttl)</code></td>
                                                            <td>Caché con callback automático</td>
                                                            <td>key, callback, ttl (opcional)</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>increment($key, $value)</code></td>
                                                            <td>Incrementar valor numérico</td>
                                                            <td>key, value (default: 1)</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>decrement($key, $value)</code></td>
                                                            <td>Decrementar valor numérico</td>
                                                            <td>key, value (default: 1)</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="cacheManagement">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCacheManagement">
                                            <i class="fas fa-tools text-info me-2"></i> Gestión y Mantenimiento
                                        </button>
                                    </h2>
                                    <div id="collapseCacheManagement" class="accordion-collapse collapse" data-bs-parent="#cacheAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Método</th>
                                                            <th>Descripción</th>
                                                            <th>Uso</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><code>getStats()</code></td>
                                                            <td>Estadísticas detalladas del caché</td>
                                                            <td>Monitoreo y análisis</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>clear()</code></td>
                                                            <td>Limpiar todo el caché</td>
                                                            <td>Mantenimiento completo</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>clearExpired()</code></td>
                                                            <td>Limpiar solo archivos expirados</td>
                                                            <td>Mantenimiento automático</td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>clearDirectory($dir)</code></td>
                                                            <td>Limpiar directorio específico</td>
                                                            <td>Limpieza selectiva</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Funciones Globales -->
                            <h6 class="mt-4">Funciones Globales:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-code"></i> cache()
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Función:</strong> Acceso rápido al caché</p>
                                            <code>cache($key, $value = null, $ttl = null)</code>
                                            <ul class="mt-2 mb-0">
                                                <li>Sin parámetros: retorna instancia Cache</li>
                                                <li>Solo $key: obtiene valor</li>
                                                <li>$key + $value: almacena valor</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <i class="fas fa-code"></i> cache_remember()
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Función:</strong> Caché con callback</p>
                                            <code>cache_remember($key, $callback, $ttl = null)</code>
                                            <ul class="mt-2 mb-0">
                                                <li>Busca en caché primero</li>
                                                <li>Ejecuta callback si no existe</li>
                                                <li>Almacena resultado automáticamente</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración -->
                            <h6 class="mt-4">Configuración del Sistema:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Configuración</th>
                                            <th>Valor por Defecto</th>
                                            <th>Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>CACHE_DIR</code></td>
                                            <td><code>cache/</code></td>
                                            <td>Directorio de archivos de caché</td>
                                        </tr>
                                        <tr>
                                            <td><code>DEFAULT_TTL</code></td>
                                            <td><code>3600 segundos</code></td>
                                            <td>Tiempo de vida por defecto</td>
                                        </tr>
                                        <tr>
                                            <td><code>APCu Support</code></td>
                                            <td><code>Auto-detectado</code></td>
                                            <td>Caché en memoria si está disponible</td>
                                        </tr>
                                        <tr>
                                            <td><code>File Format</code></td>
                                            <td><code>Serializado</code></td>
                                            <td>Formato de almacenamiento en archivos</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Comandos CLI -->
                            <h6 class="mt-4">Comandos de Consola:</h6>
                            <div class="alert alert-light">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-terminal"></i> cache:clear</strong><br>
                                        <small>Limpiar todo el caché del sistema</small>
                                    </div>
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-terminal"></i> cache:stats</strong><br>
                                        <small>Ver estadísticas detalladas del caché</small>
                                    </div>
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-terminal"></i> cache:expired</strong><br>
                                        <small>Limpiar solo archivos expirados</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Beneficios de Rendimiento -->
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-chart-line"></i>
                                <strong>Beneficios de Rendimiento:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Dashboard:</strong> Carga de estadísticas hasta 10x más rápida</li>
                                    <li><strong>Reportes:</strong> Consultas complejas cacheadas automáticamente</li>
                                    <li><strong>Búsquedas:</strong> Resultados frecuentes almacenados en memoria</li>
                                    <li><strong>Cálculos:</strong> Operaciones matemáticas complejas optimizadas</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Base de Datos -->
                        <div class="tab-pane fade" id="database" role="tabpanel">
                            <h5><i class="fas fa-database text-primary"></i> Estructura de la Base de Datos</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Tablas Principales:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>users</strong>
                                            <span class="badge bg-primary rounded-pill">Usuarios del sistema</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>categories</strong>
                                            <span class="badge bg-secondary rounded-pill">Categorías</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>transactions</strong>
                                            <span class="badge bg-success rounded-pill">Transacciones</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>bank_accounts</strong>
                                            <span class="badge bg-info rounded-pill">Cuentas bancarias</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Tablas de Gestión:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>accounts_payable</strong>
                                            <span class="badge bg-danger rounded-pill">Cuentas por pagar</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>accounts_receivable</strong>
                                            <span class="badge bg-warning rounded-pill">Cuentas por cobrar</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>credit_cards</strong>
                                            <span class="badge bg-dark rounded-pill">Tarjetas de crédito</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>credit_card_transactions</strong>
                                            <span class="badge bg-secondary rounded-pill">Transacciones de tarjetas</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>debts</strong>
                                            <span class="badge bg-danger rounded-pill">Deudas</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <h6 class="mt-4">Relaciones Importantes:</h6>
                            <div class="alert alert-light">
                                <ul class="mb-0">
                                    <li><strong>transactions.bank_account_id</strong> → bank_accounts.id</li>
                                    <li><strong>accounts_payable.bank_account_id</strong> → bank_accounts.id</li>
                                    <li><strong>payments.bank_account_id</strong> → bank_accounts.id</li>
                                    <li><strong>payments.credit_card_id</strong> → credit_cards.id</li>
                                    <li>Todas las tablas principales tienen <strong>user_id</strong> → users.id</li>
                                </ul>
                            </div>

                            <h6 class="mt-4">Campos Nuevos en Tarjetas de Crédito:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Campo</th>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Valor por Defecto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>status</code></td>
                                            <td>VARCHAR(20)</td>
                                            <td>Estado de la tarjeta (active, overdue, suspended)</td>
                                            <td>active</td>
                                        </tr>
                                        <tr>
                                            <td><code>card_color</code></td>
                                            <td>VARCHAR(7)</td>
                                            <td>Color personalizado de la tarjeta (formato hex)</td>
                                            <td>#007bff</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-success mt-4">
                                <i class="fas fa-check-circle"></i>
                                <strong>Base de Datos Actualizada:</strong> El sistema incluye soporte completo para 
                                estados dinámicos de tarjetas, personalización de colores y cuentas bancarias 
                                en transacciones y pagos, con integridad referencial.
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i>
                                <strong>Migraciones Disponibles:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><code>2025_01_15_140000_add_status_to_credit_cards.php</code> - Agrega campo status</li>
                                    <li><code>2025_01_16_120000_add_card_color_to_credit_cards.php</code> - Agrega campo card_color</li>
                                    <li><code>2025_07_27_030000_add_color_to_credit_cards.php</code> - Migración alternativa de colores</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Producción -->
                        <div class="tab-pane fade" id="production" role="tabpanel">
                            <h5><i class="fas fa-rocket text-primary"></i> Configuración de Producción</h5>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Sistema Listo para Producción:</strong> Money Manager v2.0 incluye configuraciones optimizadas y scripts automatizados para despliegue en producción.
                            </div>

                            <h6>Scripts de Despliegue:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-cog"></i> deploy.php</h6>
                                            <p class="card-text">Script automatizado de despliegue que configura el entorno de producción.</p>
                                            <ul class="list-unstyled small">
                                                <li>✓ Creación de directorios</li>
                                                <li>✓ Configuración de permisos</li>
                                                <li>✓ Verificación de extensiones PHP</li>
                                                <li>✓ Limpieza de archivos de desarrollo</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-save"></i> backup.php</h6>
                                            <p class="card-text">Sistema automatizado de respaldos con compresión y limpieza.</p>
                                            <ul class="list-unstyled small">
                                                <li>✓ Backup de base de datos</li>
                                                <li>✓ Backup de archivos subidos</li>
                                                <li>✓ Backup de configuración</li>
                                                <li>✓ Compresión automática</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-chart-line"></i> monitor.php</h6>
                                            <p class="card-text">Sistema de monitoreo del estado del servidor y aplicación.</p>
                                            <ul class="list-unstyled small">
                                                <li>✓ Estado del sistema</li>
                                                <li>✓ Verificación de seguridad</li>
                                                <li>✓ Reportes en HTML/JSON</li>
                                                <li>✓ Alertas automáticas</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">Archivos de Configuración:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Archivo</th>
                                            <th>Propósito</th>
                                            <th>Características</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>.env.production</code></td>
                                            <td>Configuración optimizada para producción</td>
                                            <td>Seguridad mejorada, logs, caché, HTTPS</td>
                                        </tr>
                                        <tr>
                                            <td><code>server-config.md</code></td>
                                            <td>Guía de configuración del servidor</td>
                                            <td>Apache, Nginx, PHP, SSL, seguridad</td>
                                        </tr>
                                        <tr>
                                            <td><code>PRODUCTION_CHECKLIST.md</code></td>
                                            <td>Lista de verificación de despliegue</td>
                                            <td>Pasos detallados, verificaciones, pruebas</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle"></i>
                                <strong>Comando de Despliegue:</strong>
                                <code>php deploy.php</code> - Ejecuta la configuración automática de producción
                            </div>
                        </div>

                        <!-- Backup -->
                        <div class="tab-pane fade" id="backup" role="tabpanel">
                            <h5><i class="fas fa-save text-primary"></i> Sistema de Backup</h5>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> El sistema de backup está diseñado para ejecutarse automáticamente y mantener la integridad de los datos.
                            </div>

                            <h6>Características del Sistema:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-database"></i> Backup de Base de Datos:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">✓ Exportación completa de SQLite</li>
                                        <li class="list-group-item">✓ Verificación de integridad</li>
                                        <li class="list-group-item">✓ Compresión automática</li>
                                        <li class="list-group-item">✓ Nomenclatura con timestamp</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-folder"></i> Backup de Archivos:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">✓ Archivos subidos (uploads/)</li>
                                        <li class="list-group-item">✓ Configuraciones (.env, .htaccess)</li>
                                        <li class="list-group-item">✓ Documentación (README.md)</li>
                                        <li class="list-group-item">✓ Logs importantes</li>
                                    </ul>
                                </div>
                            </div>

                            <h6 class="mt-4">Comandos Disponibles:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Comando</th>
                                            <th>Descripción</th>
                                            <th>Uso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>php backup.php</code></td>
                                            <td>Backup manual completo</td>
                                            <td>Ejecutar cuando sea necesario</td>
                                        </tr>
                                        <tr>
                                            <td><code>php backup.php --auto</code></td>
                                            <td>Backup automático (cron)</td>
                                            <td>Para tareas programadas</td>
                                        </tr>
                                        <tr>
                                            <td><code>php backup.php --clean</code></td>
                                            <td>Limpiar backups antiguos</td>
                                            <td>Mantener solo los últimos 30 días</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="mt-4">Configuración de Cron (Recomendado):</h6>
                            <div class="alert alert-light">
                                <strong>Backup Diario:</strong><br>
                                <code>0 2 * * * /usr/bin/php /path/to/backup.php --auto</code><br><br>
                                <strong>Limpieza Semanal:</strong><br>
                                <code>0 3 * * 0 /usr/bin/php /path/to/backup.php --clean</code>
                            </div>

                            <div class="alert alert-success mt-4">
                                <i class="fas fa-check-circle"></i>
                                <strong>Ubicación de Backups:</strong> <code>backups/</code> - Los archivos se almacenan con formato <code>backup_YYYY-MM-DD_HH-MM-SS</code>
                            </div>
                        </div>

                        <!-- Monitoreo -->
                        <div class="tab-pane fade" id="monitoring" role="tabpanel">
                            <h5><i class="fas fa-chart-line text-primary"></i> Sistema de Monitoreo</h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Monitoreo Integral:</strong> El sistema verifica automáticamente el estado del servidor, aplicación y seguridad.
                            </div>

                            <h6>Verificaciones del Sistema:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <i class="fas fa-server"></i> Sistema
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li>✓ Versión de PHP</li>
                                                <li>✓ Extensiones requeridas</li>
                                                <li>✓ Permisos de directorios</li>
                                                <li>✓ Espacio en disco</li>
                                                <li>✓ Uso de memoria</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-database"></i> Base de Datos
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li>✓ Conexión a SQLite</li>
                                                <li>✓ Integridad de tablas</li>
                                                <li>✓ Tamaño de la base</li>
                                                <li>✓ Últimas transacciones</li>
                                                <li>✓ Índices optimizados</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <i class="fas fa-shield-alt"></i> Seguridad
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li>✓ Configuración PHP</li>
                                                <li>✓ Archivos protegidos</li>
                                                <li>✓ HTTPS habilitado</li>
                                                <li>✓ Cabeceras de seguridad</li>
                                                <li>✓ Logs de acceso</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">Formatos de Reporte:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Comando</th>
                                            <th>Formato</th>
                                            <th>Uso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>php monitor.php</code></td>
                                            <td>HTML (navegador)</td>
                                            <td>Visualización completa con gráficos</td>
                                        </tr>
                                        <tr>
                                            <td><code>php monitor.php --json</code></td>
                                            <td>JSON (API)</td>
                                            <td>Integración con sistemas externos</td>
                                        </tr>
                                        <tr>
                                            <td><code>php monitor.php --cli</code></td>
                                            <td>Texto (consola)</td>
                                            <td>Verificaciones rápidas por terminal</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="mt-4">Estados del Sistema:</h6>
                            <div class="alert alert-light">
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="badge bg-success">OK</span> - Sistema funcionando correctamente
                                    </div>
                                    <div class="col-md-4">
                                        <span class="badge bg-warning">WARNING</span> - Advertencias menores detectadas
                                    </div>
                                    <div class="col-md-4">
                                        <span class="badge bg-danger">ERROR</span> - Problemas críticos encontrados
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-success mt-4">
                                <i class="fas fa-key"></i>
                                <strong>Acceso Protegido:</strong> El monitoreo requiere una clave de acceso configurada en <code>.env</code> para mayor seguridad.
                            </div>
                        </div>

                        <!-- Seguridad -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <h5><i class="fas fa-shield-alt text-primary"></i> Configuraciones de Seguridad</h5>
                            
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Crítico:</strong> Todas las configuraciones de seguridad están implementadas y activas en producción.
                            </div>

                            <h6>Protección de Archivos (.htaccess):</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-ban"></i> Archivos Protegidos:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">🔒 .env (configuración)</li>
                                        <li class="list-group-item">🔒 .db (base de datos)</li>
                                        <li class="list-group-item">🔒 .log (archivos de log)</li>
                                        <li class="list-group-item">🔒 .md (documentación)</li>
                                        <li class="list-group-item">🔒 .json, .sql, .bak</li>
                                        <li class="list-group-item">🔒 composer.json/lock</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-folder-minus"></i> Directorios Protegidos:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">🔒 config/ (configuraciones)</li>
                                        <li class="list-group-item">🔒 classes/ (código fuente)</li>
                                        <li class="list-group-item">🔒 scripts/ (scripts internos)</li>
                                        <li class="list-group-item">🔒 data/ (datos sensibles)</li>
                                        <li class="list-group-item">🔒 logs/ (archivos de log)</li>
                                    </ul>
                                </div>
                            </div>

                            <h6 class="mt-4">Cabeceras de Seguridad HTTP:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Cabecera</th>
                                            <th>Valor</th>
                                            <th>Protección</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>X-Content-Type-Options</code></td>
                                            <td>nosniff</td>
                                            <td>Previene ataques MIME-type</td>
                                        </tr>
                                        <tr>
                                            <td><code>X-XSS-Protection</code></td>
                                            <td>1; mode=block</td>
                                            <td>Protección contra XSS</td>
                                        </tr>
                                        <tr>
                                            <td><code>X-Frame-Options</code></td>
                                            <td>DENY</td>
                                            <td>Previene clickjacking</td>
                                        </tr>
                                        <tr>
                                            <td><code>Referrer-Policy</code></td>
                                            <td>strict-origin-when-cross-origin</td>
                                            <td>Control de referrer</td>
                                        </tr>
                                        <tr>
                                            <td><code>Content-Security-Policy</code></td>
                                            <td>default-src 'self'</td>
                                            <td>Previene inyección de código</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="mt-4">Configuraciones PHP Seguras:</h6>
                            <div class="alert alert-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Configuraciones Deshabilitadas:</strong>
                                        <ul class="mb-0">
                                            <li><code>display_errors = Off</code></li>
                                            <li><code>expose_php = Off</code></li>
                                            <li><code>allow_url_fopen = Off</code></li>
                                            <li><code>allow_url_include = Off</code></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Límites de Seguridad:</strong>
                                        <ul class="mb-0">
                                            <li><code>max_execution_time = 30</code></li>
                                            <li><code>max_input_time = 60</code></li>
                                            <li><code>memory_limit = 256M</code></li>
                                            <li><code>post_max_size = 50M</code></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">Funciones de Seguridad Implementadas:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-user-shield"></i> Autenticación
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li>✓ Sesiones seguras</li>
                                                <li>✓ Tokens CSRF</li>
                                                <li>✓ Validación de entrada</li>
                                                <li>✓ Sanitización de datos</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <i class="fas fa-database"></i> Base de Datos
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li>✓ Consultas preparadas (PDO)</li>
                                                <li>✓ Escape de caracteres</li>
                                                <li>✓ Validación de tipos</li>
                                                <li>✓ Transacciones seguras</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <i class="fas fa-file-upload"></i> Archivos
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li>✓ Validación de tipos MIME</li>
                                                <li>✓ Límites de tamaño</li>
                                                <li>✓ Nombres seguros</li>
                                                <li>✓ Directorios protegidos</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-success mt-4">
                                <i class="fas fa-certificate"></i>
                                <strong>Certificación de Seguridad:</strong> El sistema cumple con las mejores prácticas de seguridad para aplicaciones web PHP y está preparado para entornos de producción.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color: #1976d2;
}

.nav-tabs .nav-link {
    color: #495057;
}

.nav-tabs .nav-link.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.table th {
    border-top: none;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>

<?php include '../includes/footer.php'; ?>