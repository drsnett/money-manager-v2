<?php
require_once 'config/config.php';
requireLogin();

$title = 'Manual de Usuario';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-book"></i> Manual de Usuario</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <!-- Índice de navegación -->
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-list"></i> Índice</h6>
            </div>
            <div class="card-body p-0">
                <nav class="nav nav-pills flex-column">
                    <a class="nav-link" href="#introduccion">1. Introducción</a>
                    <a class="nav-link" href="#dashboard">2. Dashboard</a>
                    <a class="nav-link" href="#transacciones">3. Transacciones</a>
                    <a class="nav-link" href="#cuentas-bancarias">4. Cuentas Bancarias</a>
                    <a class="nav-link" href="#tarjetas-credito">5. Tarjetas de Crédito</a>
                    <a class="nav-link" href="#cuentas-pagar">6. Cuentas por Pagar</a>
                    <a class="nav-link" href="#cuentas-cobrar">7. Cuentas por Cobrar</a>
                    <a class="nav-link" href="#deudas">8. Gestión de Deudas</a>
                    <a class="nav-link" href="#categorias">9. Categorías</a>
                    <a class="nav-link" href="#reportes">10. Reportes</a>
                    <a class="nav-link" href="#configuracion">11. Configuración</a>
                    <a class="nav-link" href="#rendimiento">12. Rendimiento y Caché</a>
                    <a class="nav-link" href="#consejos">13. Consejos y Trucos</a>
                </nav>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <!-- Contenido de la documentación -->
        <div class="documentation-content">
            
            <!-- 1. Introducción -->
            <section id="introduccion" class="mb-5">
                <h2><i class="bi bi-info-circle"></i> 1. Introducción</h2>
                <div class="alert alert-info">
                    <h5>¡Bienvenido al Sistema de Gestión Financiera!</h5>
                    <p>Este sistema te permite gestionar de manera integral tus finanzas personales, incluyendo ingresos, gastos, cuentas bancarias, tarjetas de crédito, deudas y mucho más.</p>
                </div>
                
                <h4>Características Principales:</h4>
                <ul>
                    <li><strong>Dashboard Interactivo:</strong> Visualiza tu situación financiera de un vistazo</li>
                    <li><strong>Gestión de Transacciones:</strong> Registra ingresos y gastos con categorización</li>
                    <li><strong>Cuentas Bancarias:</strong> Administra múltiples cuentas con diferentes monedas</li>
                    <li><strong>Tarjetas de Crédito:</strong> Control de límites, fechas de corte y pagos</li>
                    <li><strong>Cuentas por Pagar/Cobrar:</strong> Gestión de compromisos financieros</li>
                    <li><strong>Gestión de Deudas:</strong> Seguimiento y amortización de préstamos</li>
                    <li><strong>Reportes Avanzados:</strong> Análisis detallado de tu situación financiera</li>
                </ul>
            </section>
            
            <!-- 2. Dashboard -->
            <section id="dashboard" class="mb-5">
                <h2><i class="bi bi-speedometer2"></i> 2. Dashboard</h2>
                <p>El Dashboard es tu centro de control financiero. Aquí encontrarás un resumen completo de tu situación actual.</p>
                
                <h4>Estadísticas Principales:</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-arrow-up-circle text-success"></i> Ingresos Totales</h6>
                                <p>Muestra la suma de todos tus ingresos registrados.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-arrow-down-circle text-danger"></i> Gastos Totales</h6>
                                <p>Suma de todos los gastos registrados en el sistema.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>Alertas y Recordatorios:</h4>
                <ul>
                    <li><strong>Pagos Próximos:</strong> Cuentas por pagar que vencen en los próximos 7 días</li>
                    <li><strong>Cobros Pendientes:</strong> Dinero que esperás recibir próximamente</li>
                    <li><strong>Deudas Vencidas:</strong> Compromisos que requieren atención inmediata</li>
                    <li><strong>Tarjetas de Crédito:</strong> Fechas de corte y pagos mínimos</li>
                </ul>
                
                <div class="alert alert-success">
                    <h6><i class="bi bi-lightbulb"></i> Consejo:</h6>
                    <p>Revisa tu dashboard diariamente para mantener el control de tus finanzas y no perder fechas importantes.</p>
                </div>
            </section>
            
            <!-- 3. Transacciones -->
            <section id="transacciones" class="mb-5">
                <h2><i class="bi bi-arrow-left-right"></i> 3. Transacciones</h2>
                <p>El módulo de transacciones te permite registrar todos tus movimientos financieros de manera detallada.</p>
                
                <h4>Cómo Registrar una Transacción:</h4>
                <ol>
                    <li>Ve a <strong>Transacciones</strong> en el menú principal</li>
                    <li>Haz clic en <strong>"Agregar Transacción"</strong></li>
                    <li>Completa los campos requeridos:</li>
                    <ul>
                        <li><strong>Tipo:</strong> Ingreso o Gasto</li>
                        <li><strong>Monto:</strong> Cantidad en tu moneda local</li>
                        <li><strong>Descripción:</strong> Detalle de la transacción</li>
                        <li><strong>Categoría:</strong> Clasifica tu movimiento</li>
                        <li><strong>Fecha:</strong> Cuándo ocurrió la transacción</li>
                        <li><strong>Cuenta Bancaria:</strong> (Opcional) Asocia a una cuenta específica</li>
                    </ul>
                    <li>Haz clic en <strong>"Guardar"</strong></li>
                </ol>
                
                <h4>Ejemplo Práctico:</h4>
                <div class="card">
                    <div class="card-body">
                        <h6>Registrar un Gasto de Supermercado:</h6>
                        <ul>
                            <li><strong>Tipo:</strong> Gasto</li>
                            <li><strong>Monto:</strong> $45.50</li>
                            <li><strong>Descripción:</strong> "Compras semanales en Walmart"</li>
                            <li><strong>Categoría:</strong> Alimentación</li>
                            <li><strong>Fecha:</strong> Hoy</li>
                            <li><strong>Cuenta:</strong> Cuenta Corriente Principal</li>
                        </ul>
                    </div>
                </div>
                
                <h4>Filtros y Búsqueda:</h4>
                <ul>
                    <li><strong>Por Fecha:</strong> Filtra transacciones por rango de fechas</li>
                    <li><strong>Por Tipo:</strong> Solo ingresos o solo gastos</li>
                    <li><strong>Por Categoría:</strong> Visualiza movimientos de categorías específicas</li>
                    <li><strong>Por Cuenta:</strong> Transacciones de una cuenta bancaria particular</li>
                </ul>
            </section>
            
            <!-- 4. Cuentas Bancarias -->
            <section id="cuentas-bancarias" class="mb-5">
                <h2><i class="bi bi-bank"></i> 4. Cuentas Bancarias</h2>
                <p>Administra todas tus cuentas bancarias desde un solo lugar, con soporte para múltiples monedas y tipos de cuenta.</p>
                
                <h4>Crear una Nueva Cuenta:</h4>
                <ol>
                    <li>Ve a <strong>Cuentas Bancarias</strong></li>
                    <li>Haz clic en <strong>"Agregar Cuenta"</strong></li>
                    <li>Completa la información:</li>
                    <ul>
                        <li><strong>Nombre:</strong> Identificación de la cuenta</li>
                        <li><strong>Banco:</strong> Nombre de la institución financiera</li>
                        <li><strong>Número de Cuenta:</strong> Últimos dígitos para identificación</li>
                        <li><strong>Tipo:</strong> Ahorro, Corriente, Nómina, Inversión, etc.</li>
                        <li><strong>Moneda:</strong> USD, EUR, MXN, etc.</li>
                        <li><strong>Balance Inicial:</strong> Saldo actual de la cuenta</li>
                    </ul>
                </ol>
                
                <h4>Tipos de Cuenta Disponibles:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li><strong>Ahorro:</strong> Para guardar dinero</li>
                            <li><strong>Corriente:</strong> Para gastos diarios</li>
                            <li><strong>Nómina:</strong> Donde recibes tu salario</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul>
                            <li><strong>Inversión:</strong> Para inversiones</li>
                            <li><strong>Empresarial:</strong> Para negocios</li>
                            <li><strong>Otro:</strong> Tipos especiales</li>
                        </ul>
                    </div>
                </div>
                
                <h4>Gestión de Balances:</h4>
                <p>El sistema actualiza automáticamente los balances cuando:</p>
                <ul>
                    <li>Registras transacciones asociadas a la cuenta</li>
                    <li>Realizas pagos desde la cuenta</li>
                    <li>Recibes transferencias o depósitos</li>
                </ul>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Importante:</h6>
                    <p>Puedes activar/desactivar cuentas sin eliminarlas. Las cuentas inactivas no aparecen en los formularios pero mantienen su historial.</p>
                </div>
            </section>
            
            <!-- 5. Tarjetas de Crédito -->
            <section id="tarjetas-credito" class="mb-5">
                <h2><i class="bi bi-credit-card"></i> 5. Tarjetas de Crédito</h2>
                <p>Controla tus tarjetas de crédito con un sistema inteligente de estados dinámicos, límites, fechas de corte y pagos de manera eficiente.</p>
                
                <h4>Registrar una Tarjeta:</h4>
                <ol>
                    <li>Ve a <strong>Tarjetas de Crédito</strong></li>
                    <li>Haz clic en <strong>"Agregar Tarjeta"</strong></li>
                    <li>Ingresa los datos:</li>
                    <ul>
                        <li><strong>Nombre:</strong> Identificación de la tarjeta</li>
                        <li><strong>Banco:</strong> Institución emisora</li>
                        <li><strong>Últimos 4 dígitos:</strong> Para identificación</li>
                        <li><strong>Límite de Crédito:</strong> Monto máximo disponible</li>
                        <li><strong>Fecha de Corte:</strong> Día del mes que cierra el período</li>
                        <li><strong>Días para Pago:</strong> Días después del corte para pagar</li>
                        <li><strong>Moneda:</strong> Divisa de la tarjeta</li>
                        <li><strong>Color:</strong> Personaliza el color de visualización</li>
                    </ul>
                </ol>
                
                <h4>🎯 Sistema de Estados Dinámicos:</h4>
                <p>El sistema calcula automáticamente el estado de cada tarjeta basándose en fechas de corte, pagos y balances:</p>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6><i class="bi bi-check-circle"></i> 🟢 Pagada</h6>
                            <p>El balance del último corte ha sido pagado completamente.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-clock"></i> 🟡 Pendiente</h6>
                            <p>Hay balance pendiente pero aún no ha vencido el plazo de pago.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="alert alert-danger">
                            <h6><i class="bi bi-exclamation-triangle"></i> 🔴 Vencida</h6>
                            <p>Se pasó la fecha de pago sin cubrir el balance del corte.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-primary">
                            <h6><i class="bi bi-credit-card"></i> 🔵 Activa</h6>
                            <p>Tarjeta en uso normal sin balance de corte pendiente.</p>
                        </div>
                    </div>
                </div>
                
                <h4>Funcionalidades Clave:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-calculator"></i> Cálculos Automáticos</h6>
                                <ul>
                                    <li>Crédito disponible</li>
                                    <li>Porcentaje de utilización</li>
                                    <li>Pago mínimo</li>
                                    <li>Estados dinámicos inteligentes</li>
                                    <li>Próxima fecha de pago</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-graph-up"></i> Seguimiento Avanzado</h6>
                                <ul>
                                    <li>Historial completo de transacciones</li>
                                    <li>Eliminación segura de movimientos</li>
                                    <li>Estados de cuenta mensuales</li>
                                    <li>Alertas de vencimiento automáticas</li>
                                    <li>Personalización visual por colores</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>Gestión de Transacciones:</h4>
                <div class="card mb-3">
                    <div class="card-body">
                        <h6><i class="bi bi-arrow-left-right"></i> Tipos de Movimientos:</h6>
                        <ul>
                            <li><strong>Cargos:</strong> Compras y consumos realizados con la tarjeta</li>
                            <li><strong>Pagos:</strong> Abonos realizados para reducir el balance</li>
                        </ul>
                        <p><strong>Nota:</strong> Puedes eliminar transacciones erróneas de forma segura. El sistema actualiza automáticamente los balances y recalcula los estados.</p>
                    </div>
                </div>
                
                <h4>Ejemplo de Uso:</h4>
                <div class="card">
                    <div class="card-body">
                        <h6>Tarjeta Visa Principal:</h6>
                        <ul>
                            <li><strong>Límite:</strong> $5,000</li>
                            <li><strong>Utilizado:</strong> $1,200 (24%)</li>
                            <li><strong>Disponible:</strong> $3,800</li>
                            <li><strong>Corte:</strong> Día 15 de cada mes</li>
                            <li><strong>Pago:</strong> Hasta el día 5 del mes siguiente</li>
                            <li><strong>Estado:</strong> <span class="badge bg-warning">🟡 Pendiente</span></li>
                            <li><strong>Color:</strong> <span style="color: #007bff;">Azul personalizado</span></li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb"></i> Consejo Avanzado:</h6>
                    <p>Los estados se actualizan automáticamente cada vez que agregas transacciones o cuando cambian las fechas. No necesitas hacer nada manual para mantener la información actualizada.</p>
                </div>

                <h4>Mejoras Técnicas Recientes:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <i class="bi bi-bug"></i> Correcciones de Errores
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success"></i> <strong>deleteTransaction is not defined:</strong> Función JavaScript corregida</li>
                                    <li><i class="bi bi-check-circle text-success"></i> <strong>aria-hidden:</strong> Problemas de accesibilidad resueltos</li>
                                    <li><i class="bi bi-check-circle text-success"></i> <strong>Duplicación de funciones:</strong> Código JavaScript optimizado</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <i class="bi bi-plus-circle"></i> Nuevas Funcionalidades
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success"></i> <strong>Estados Dinámicos:</strong> Cálculo automático en tiempo real</li>
                                    <li><i class="bi bi-check-circle text-success"></i> <strong>Personalización:</strong> Colores de tarjetas configurables</li>
                                    <li><i class="bi bi-check-circle text-success"></i> <strong>Seguridad:</strong> Validaciones mejoradas en eliminación</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mt-3">
                    <h6><i class="bi bi-info-circle"></i> Nota para Desarrolladores:</h6>
                    <p>Estas mejoras están documentadas en detalle en el manual técnico (<code>backend-documentation.php</code>) y en el archivo <code>README.md</code> del proyecto.</p>
                </div>
            </section>
            
            <!-- 6. Cuentas por Pagar -->
            <section id="cuentas-pagar" class="mb-5">
                <h2><i class="bi bi-file-earmark-minus"></i> 6. Cuentas por Pagar</h2>
                <p>Gestiona todas tus obligaciones financieras y mantén un control preciso de tus compromisos de pago.</p>
                
                <h4>Crear una Cuenta por Pagar:</h4>
                <ol>
                    <li>Ve a <strong>Cuentas por Pagar</strong></li>
                    <li>Haz clic en <strong>"Agregar Cuenta"</strong></li>
                    <li>Completa los campos:</li>
                    <ul>
                        <li><strong>Acreedor:</strong> A quién le debes</li>
                        <li><strong>Descripción:</strong> Concepto de la deuda</li>
                        <li><strong>Monto Total:</strong> Cantidad total a pagar</li>
                        <li><strong>Fecha de Vencimiento:</strong> Cuándo debes pagar</li>
                        <li><strong>Cuenta Bancaria:</strong> (Opcional) Cuenta para el pago</li>
                    </ul>
                </ol>
                
                <h4>Estados de las Cuentas:</h4>
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>Pendiente</strong><br>
                            Sin pagos realizados
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>Pagado Parcial</strong><br>
                            Pagos parciales realizados
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>Pagado</strong><br>
                            Completamente liquidado
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-danger">
                            <strong>Vencido</strong><br>
                            Pasó la fecha límite
                        </div>
                    </div>
                </div>
                
                <h4>Registrar Pagos:</h4>
                <p>Para cada cuenta por pagar puedes registrar múltiples pagos:</p>
                <ol>
                    <li>Haz clic en <strong>"Agregar Pago"</strong> en la cuenta correspondiente</li>
                    <li>Ingresa el monto del pago</li>
                    <li>Selecciona la fecha del pago</li>
                    <li>Elige el método de pago:</li>
                    <ul>
                        <li><strong>Cuenta Bancaria:</strong> Pago desde una cuenta específica</li>
                        <li><strong>Tarjeta de Crédito:</strong> Pago con tarjeta</li>
                        <li><strong>Efectivo:</strong> Pago en efectivo</li>
                    </ul>
                    <li>Agrega notas si es necesario</li>
                </ol>
                
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> Funcionalidad Avanzada:</h6>
                    <p>El sistema actualiza automáticamente el estado de la cuenta según los pagos realizados y calcula el saldo pendiente.</p>
                </div>
            </section>
            
            <!-- 7. Cuentas por Cobrar -->
            <section id="cuentas-cobrar" class="mb-5">
                <h2><i class="bi bi-file-earmark-plus"></i> 7. Cuentas por Cobrar</h2>
                <p>Administra el dinero que otras personas o entidades te deben, con seguimiento completo de cobros.</p>
                
                <h4>Crear una Cuenta por Cobrar:</h4>
                <ol>
                    <li>Ve a <strong>Cuentas por Cobrar</strong></li>
                    <li>Haz clic en <strong>"Agregar Cuenta"</strong></li>
                    <li>Ingresa la información:</li>
                    <ul>
                        <li><strong>Deudor:</strong> Quién te debe</li>
                        <li><strong>Descripción:</strong> Concepto del préstamo/venta</li>
                        <li><strong>Monto Total:</strong> Cantidad total a cobrar</li>
                        <li><strong>Fecha de Vencimiento:</strong> Cuándo deben pagarte</li>
                    </ul>
                </ol>
                
                <h4>Gestión de Cobros:</h4>
                <p>Similar a las cuentas por pagar, puedes registrar cobros parciales:</p>
                <ul>
                    <li><strong>Monto recibido:</strong> Cantidad del cobro</li>
                    <li><strong>Fecha de cobro:</strong> Cuándo recibiste el pago</li>
                    <li><strong>Método:</strong> Cómo recibiste el dinero</li>
                    <li><strong>Notas:</strong> Observaciones adicionales</li>
                </ul>
                
                <h4>Ejemplo Práctico:</h4>
                <div class="card">
                    <div class="card-body">
                        <h6>Préstamo a un Amigo:</h6>
                        <ul>
                            <li><strong>Deudor:</strong> Juan Pérez</li>
                            <li><strong>Descripción:</strong> "Préstamo personal"</li>
                            <li><strong>Monto:</strong> $500</li>
                            <li><strong>Vencimiento:</strong> 30/12/2024</li>
                            <li><strong>Estado:</strong> Pendiente</li>
                        </ul>
                    </div>
                </div>
            </section>
            
            <!-- 8. Gestión de Deudas -->
            <section id="deudas" class="mb-5">
                <h2><i class="bi bi-graph-down"></i> 8. Gestión de Deudas</h2>
                <p>Módulo especializado para el manejo de préstamos con intereses, amortización y cálculos automáticos.</p>
                
                <h4>Crear una Deuda:</h4>
                <ol>
                    <li>Ve a <strong>Gestión de Deudas</strong></li>
                    <li>Haz clic en <strong>"Agregar Deuda"</strong></li>
                    <li>Completa los datos del préstamo:</li>
                    <ul>
                        <li><strong>Acreedor:</strong> Banco o prestamista</li>
                        <li><strong>Descripción:</strong> Tipo de préstamo</li>
                        <li><strong>Monto Original:</strong> Cantidad inicial prestada</li>
                        <li><strong>Tasa de Interés:</strong> Porcentaje mensual</li>
                        <li><strong>Fecha de Inicio:</strong> Cuándo comenzó el préstamo</li>
                        <li><strong>Fecha de Vencimiento:</strong> Fecha límite de pago</li>
                    </ul>
                </ol>
                
                <h4>Funcionalidades Avanzadas:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-calculator"></i> Cálculos Automáticos</h6>
                                <ul>
                                    <li>Balance actual con intereses</li>
                                    <li>Interés mensual acumulado</li>
                                    <li>Proyección de pagos</li>
                                    <li>Tabla de amortización</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-graph-up"></i> Seguimiento</h6>
                                <ul>
                                    <li>Historial de pagos</li>
                                    <li>Reducción del capital</li>
                                    <li>Intereses pagados</li>
                                    <li>Tiempo restante</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>Calculadora de Amortización:</h4>
                <p>El sistema incluye una calculadora que te ayuda a:</p>
                <ul>
                    <li>Planificar pagos mensuales</li>
                    <li>Ver el impacto de pagos adicionales</li>
                    <li>Comparar diferentes estrategias de pago</li>
                    <li>Calcular el ahorro en intereses</li>
                </ul>
                
                <div class="alert alert-success">
                    <h6><i class="bi bi-lightbulb"></i> Consejo Financiero:</h6>
                    <p>Realiza pagos adicionales al capital para reducir significativamente el tiempo de pago y los intereses totales.</p>
                </div>
            </section>
            
            <!-- 9. Categorías -->
            <section id="categorias" class="mb-5">
                <h2><i class="bi bi-tags"></i> 9. Categorías</h2>
                <p>Organiza tus transacciones con un sistema de categorías personalizable y codificado por colores.</p>
                
                <h4>Gestión de Categorías:</h4>
                <ol>
                    <li>Ve a <strong>Categorías</strong></li>
                    <li>Para crear una nueva categoría:</li>
                    <ul>
                        <li><strong>Nombre:</strong> Identificación de la categoría</li>
                        <li><strong>Descripción:</strong> Detalle del uso</li>
                        <li><strong>Color:</strong> Para identificación visual</li>
                        <li><strong>Tipo:</strong> Ingreso o Gasto</li>
                    </ul>
                </ol>
                
                <h4>Categorías Sugeridas:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Gastos:</h6>
                        <ul>
                            <li>🍽️ Alimentación</li>
                            <li>🏠 Vivienda</li>
                            <li>🚗 Transporte</li>
                            <li>👕 Ropa</li>
                            <li>🎮 Entretenimiento</li>
                            <li>💊 Salud</li>
                            <li>📚 Educación</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Ingresos:</h6>
                        <ul>
                            <li>💼 Salario</li>
                            <li>💰 Bonos</li>
                            <li>🏦 Inversiones</li>
                            <li>🎁 Regalos</li>
                            <li>💸 Ventas</li>
                            <li>🏘️ Rentas</li>
                            <li>📈 Dividendos</li>
                        </ul>
                    </div>
                </div>
                
                <h4>Beneficios de Categorizar:</h4>
                <ul>
                    <li><strong>Análisis de Gastos:</strong> Identifica en qué gastas más</li>
                    <li><strong>Presupuestos:</strong> Establece límites por categoría</li>
                    <li><strong>Reportes:</strong> Genera informes detallados</li>
                    <li><strong>Tendencias:</strong> Observa patrones de gasto</li>
                </ul>
            </section>
            
            <!-- 10. Reportes -->
            <section id="reportes" class="mb-5">
                <h2><i class="bi bi-graph-up"></i> 10. Reportes</h2>
                <p>Genera análisis detallados de tu situación financiera con múltiples formatos de exportación.</p>
                
                <h4>Tipos de Reportes Disponibles:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-bar-chart"></i> Reporte de Transacciones</h6>
                                <ul>
                                    <li>Filtros por fecha</li>
                                    <li>Agrupación por categoría</li>
                                    <li>Comparación de períodos</li>
                                    <li>Gráficos interactivos</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-pie-chart"></i> Análisis por Categorías</h6>
                                <ul>
                                    <li>Distribución de gastos</li>
                                    <li>Tendencias mensuales</li>
                                    <li>Comparación año anterior</li>
                                    <li>Identificación de patrones</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>Formatos de Exportación:</h4>
                <ul>
                    <li><strong>CSV:</strong> Para análisis en Excel</li>
                    <li><strong>Excel:</strong> Formato nativo de Microsoft</li>
                    <li><strong>PDF:</strong> Para impresión y archivo</li>
                    <li><strong>Gráficos:</strong> Visualizaciones interactivas</li>
                </ul>
                
                <h4>Cómo Generar un Reporte:</h4>
                <ol>
                    <li>Ve a <strong>Reportes</strong></li>
                    <li>Selecciona el tipo de reporte</li>
                    <li>Configura los filtros:</li>
                    <ul>
                        <li>Rango de fechas</li>
                        <li>Categorías específicas</li>
                        <li>Cuentas bancarias</li>
                        <li>Tipo de transacción</li>
                    </ul>
                    <li>Elige el formato de salida</li>
                    <li>Haz clic en <strong>"Generar Reporte"</strong></li>
                </ol>
                
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> Tip Profesional:</h6>
                    <p>Genera reportes mensuales para revisar tu progreso financiero y ajustar tu presupuesto según sea necesario.</p>
                </div>
            </section>
            
            <!-- 11. Configuración -->
            <section id="configuracion" class="mb-5">
                <h2><i class="bi bi-gear"></i> 11. Configuración</h2>
                <p>Personaliza el sistema según tus necesidades y preferencias.</p>
                
                <h4>Configuraciones Disponibles:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-person"></i> Perfil de Usuario</h6>
                                <ul>
                                    <li>Información personal</li>
                                    <li>Cambio de contraseña</li>
                                    <li>Preferencias de notificación</li>
                                    <li>Zona horaria</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="bi bi-currency-dollar"></i> Configuración Financiera</h6>
                                <ul>
                                    <li>Moneda principal</li>
                                    <li>Formato de números</li>
                                    <li>Decimales a mostrar</li>
                                    <li>Símbolo de moneda</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>Seguridad:</h4>
                <ul>
                    <li><strong>Cambio de Contraseña:</strong> Actualiza tu contraseña regularmente</li>
                    <li><strong>Sesiones:</strong> Cierra sesión en dispositivos no utilizados</li>
                    <li><strong>Respaldos:</strong> El sistema guarda automáticamente tus datos</li>
                </ul>
                
                <h4>Personalización:</h4>
                <ul>
                    <li><strong>Tema:</strong> Claro u oscuro (si está disponible)</li>
                    <li><strong>Idioma:</strong> Selecciona tu idioma preferido</li>
                    <li><strong>Dashboard:</strong> Configura qué widgets mostrar</li>
                </ul>
            </section>
            
            <!-- 12. Rendimiento y Caché -->
            <section id="rendimiento" class="mb-5">
                <h2><i class="bi bi-speedometer"></i> 12. Rendimiento y Caché</h2>
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> Sistema de Optimización Automática</h5>
                    <p>El sistema incluye un avanzado sistema de caché que mejora automáticamente el rendimiento, reduciendo los tiempos de carga y optimizando las consultas a la base de datos.</p>
                </div>
                
                <h4>🚀 Características del Sistema de Caché:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="text-primary"><i class="bi bi-lightning"></i> Caché Automático</h6>
                                <ul>
                                    <li>Datos del dashboard se cargan más rápido</li>
                                    <li>Estadísticas financieras optimizadas</li>
                                    <li>Consultas complejas aceleradas</li>
                                    <li>Reducción del uso de recursos</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-success"><i class="bi bi-shield-check"></i> Gestión Inteligente</h6>
                                <ul>
                                    <li>Limpieza automática de datos obsoletos</li>
                                    <li>Actualización en tiempo real</li>
                                    <li>Manejo robusto de errores</li>
                                    <li>Monitoreo de rendimiento</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>📊 Beneficios para el Usuario:</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Área</th>
                                <th>Mejora</th>
                                <th>Beneficio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="bi bi-speedometer2"></i> Dashboard</td>
                                <td>Carga 3-5x más rápida</td>
                                <td>Acceso inmediato a tus estadísticas</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-graph-up"></i> Reportes</td>
                                <td>Generación optimizada</td>
                                <td>Análisis financiero sin esperas</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-search"></i> Búsquedas</td>
                                <td>Resultados instantáneos</td>
                                <td>Encuentra información al momento</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-calculator"></i> Cálculos</td>
                                <td>Procesamiento acelerado</td>
                                <td>Balances y totales actualizados</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h4>🔧 Funcionamiento Transparente:</h4>
                <p>El sistema de caché opera completamente en segundo plano, sin requerir intervención del usuario:</p>
                
                <div class="accordion" id="cacheAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cache1">
                                <i class="bi bi-cpu me-2"></i> Optimización Automática
                            </button>
                        </h2>
                        <div id="cache1" class="accordion-collapse collapse" data-bs-parent="#cacheAccordion">
                            <div class="accordion-body">
                                <p><strong>¿Qué se optimiza automáticamente?</strong></p>
                                <ul>
                                    <li>Estadísticas del dashboard (balance total, ingresos, gastos)</li>
                                    <li>Listas de transacciones recientes</li>
                                    <li>Cálculos de cuentas por pagar y cobrar</li>
                                    <li>Información de tarjetas de crédito</li>
                                    <li>Datos de reportes financieros</li>
                                </ul>
                                <p><strong>Resultado:</strong> Las páginas que antes tardaban 2-3 segundos ahora cargan en menos de 1 segundo.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cache2">
                                <i class="bi bi-arrow-clockwise me-2"></i> Actualización Inteligente
                            </button>
                        </h2>
                        <div id="cache2" class="accordion-collapse collapse" data-bs-parent="#cacheAccordion">
                            <div class="accordion-body">
                                <p><strong>¿Cuándo se actualiza la información?</strong></p>
                                <ul>
                                    <li>Inmediatamente al registrar nuevas transacciones</li>
                                    <li>Al modificar cuentas bancarias o tarjetas</li>
                                    <li>Cuando se actualizan balances</li>
                                    <li>Al cambiar configuraciones del sistema</li>
                                </ul>
                                <p><strong>Ventaja:</strong> Siempre ves información actualizada sin sacrificar velocidad.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cache3">
                                <i class="bi bi-shield-check me-2"></i> Mantenimiento Automático
                            </button>
                        </h2>
                        <div id="cache3" class="accordion-collapse collapse" data-bs-parent="#cacheAccordion">
                            <div class="accordion-body">
                                <p><strong>¿Cómo se mantiene el sistema?</strong></p>
                                <ul>
                                    <li>Limpieza automática de datos obsoletos</li>
                                    <li>Recuperación automática de errores</li>
                                    <li>Optimización del espacio de almacenamiento</li>
                                    <li>Monitoreo continuo del rendimiento</li>
                                </ul>
                                <p><strong>Beneficio:</strong> El sistema se mantiene rápido y eficiente sin intervención manual.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>💡 Indicadores de Rendimiento:</h4>
                <div class="alert alert-success">
                    <h6><i class="bi bi-check-circle"></i> Señales de que el caché está funcionando:</h6>
                    <ul class="mb-0">
                        <li>El dashboard carga instantáneamente después de la primera visita</li>
                        <li>Los reportes se generan más rápidamente</li>
                        <li>Las búsquedas muestran resultados al momento</li>
                        <li>La navegación entre páginas es más fluida</li>
                        <li>Los cálculos complejos se procesan sin demoras</li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> Nota Técnica:</h6>
                    <p class="mb-0">El sistema utiliza tecnología de caché dual (archivos + memoria) que se adapta automáticamente a las capacidades de tu servidor, garantizando el mejor rendimiento posible en cualquier entorno.</p>
                </div>
            </section>
            
            <!-- 13. Consejos y Trucos -->
            <section id="consejos" class="mb-5">
                <h2><i class="bi bi-lightbulb"></i> 13. Consejos y Trucos</h2>
                
                <h4>💡 Mejores Prácticas Financieras:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-success"><i class="bi bi-check-circle"></i> Hábitos Recomendados</h6>
                                <ul>
                                    <li>Registra transacciones diariamente</li>
                                    <li>Revisa tu dashboard cada mañana</li>
                                    <li>Categoriza todas las transacciones</li>
                                    <li>Establece recordatorios de pago</li>
                                    <li>Genera reportes mensuales</li>
                                    <li>Mantén actualizados los balances</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-warning"><i class="bi bi-exclamation-triangle"></i> Evita Estos Errores</h6>
                                <ul>
                                    <li>No registrar gastos pequeños</li>
                                    <li>Olvidar actualizar balances</li>
                                    <li>No categorizar transacciones</li>
                                    <li>Ignorar las alertas de vencimiento</li>
                                    <li>No hacer respaldos regulares</li>
                                    <li>Usar categorías muy generales</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>🚀 Funcionalidades Avanzadas:</h4>
                <div class="accordion" id="advancedTips">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tip1">
                                <i class="bi bi-bank me-2"></i> Gestión Multi-Cuenta
                            </button>
                        </h2>
                        <div id="tip1" class="accordion-collapse collapse" data-bs-parent="#advancedTips">
                            <div class="accordion-body">
                                <p>Asocia cada transacción a una cuenta bancaria específica para:</p>
                                <ul>
                                    <li>Mantener balances precisos por cuenta</li>
                                    <li>Identificar patrones de uso</li>
                                    <li>Facilitar la conciliación bancaria</li>
                                    <li>Generar reportes por cuenta</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tip2">
                                <i class="bi bi-credit-card me-2"></i> Control de Tarjetas de Crédito
                            </button>
                        </h2>
                        <div id="tip2" class="accordion-collapse collapse" data-bs-parent="#advancedTips">
                            <div class="accordion-body">
                                <p>Mantén un control estricto de tus tarjetas:</p>
                                <ul>
                                    <li>No excedas el 30% del límite de crédito</li>
                                    <li>Paga siempre más del mínimo</li>
                                    <li>Registra todas las transacciones inmediatamente</li>
                                    <li>Configura alertas para fechas de corte</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tip3">
                                <i class="bi bi-graph-up me-2"></i> Análisis de Tendencias
                            </button>
                        </h2>
                        <div id="tip3" class="accordion-collapse collapse" data-bs-parent="#advancedTips">
                            <div class="accordion-body">
                                <p>Utiliza los reportes para identificar:</p>
                                <ul>
                                    <li>Categorías con mayor gasto</li>
                                    <li>Tendencias estacionales</li>
                                    <li>Oportunidades de ahorro</li>
                                    <li>Patrones de ingreso</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>📱 Atajos de Teclado:</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Acción</th>
                                <th>Atajo</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nueva Transacción</td>
                                <td><kbd>Ctrl</kbd> + <kbd>N</kbd></td>
                                <td>Abre el formulario de nueva transacción</td>
                            </tr>
                            <tr>
                                <td>Buscar</td>
                                <td><kbd>Ctrl</kbd> + <kbd>F</kbd></td>
                                <td>Activa la búsqueda en la página actual</td>
                            </tr>
                            <tr>
                                <td>Dashboard</td>
                                <td><kbd>Alt</kbd> + <kbd>D</kbd></td>
                                <td>Regresa al dashboard principal</td>
                            </tr>
                            <tr>
                                <td>Actualizar</td>
                                <td><kbd>F5</kbd></td>
                                <td>Actualiza los datos de la página</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-success">
                    <h5><i class="bi bi-trophy"></i> ¡Felicitaciones!</h5>
                    <p>Has completado el manual de usuario. Con estas herramientas y conocimientos, estás listo para tomar el control total de tus finanzas personales.</p>
                    <p><strong>Recuerda:</strong> La consistencia es clave. Usa el sistema regularmente para obtener los mejores resultados.</p>
                </div>
            </section>
            
        </div>
    </div>
</div>

<style>
.documentation-content {
    font-size: 1rem;
    line-height: 1.6;
}

.documentation-content h2 {
    color: #0d6efd;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
    margin-top: 2rem;
}

.documentation-content h4 {
    color: #495057;
    margin-top: 1.5rem;
}

.documentation-content .card {
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
}

.documentation-content .alert {
    margin: 1rem 0;
}

.documentation-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.documentation-content kbd {
    background-color: #212529;
    color: white;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.nav-pills .nav-link {
    color: #6c757d;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.nav-pills .nav-link:hover {
    background-color: #e9ecef;
}

@media print {
    .col-md-3 {
        display: none;
    }
    .col-md-9 {
        width: 100%;
    }
}
</style>

<script>
// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling para los enlaces del índice de documentación
    document.querySelectorAll('.card .nav-link[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Actualizar enlace activo solo en la documentación
                document.querySelectorAll('.card .nav-link[href^="#"]').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
    
    // Highlight del enlace activo en el scroll
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.card .nav-link[href^="#"]');
        
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
    
    // Activar el primer enlace por defecto
    const firstDocLink = document.querySelector('.card .nav-link[href^="#"]');
    if (firstDocLink) {
        firstDocLink.classList.add('active');
    }
});
</script>

<?php include 'includes/footer.php'; ?>