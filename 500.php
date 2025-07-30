<?php
// Página de error 500 personalizada para panel.drsnet.ovh/reg
http_response_code(500);

// Detectar la ruta base automáticamente
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = dirname($scriptName);
    
    if ($scriptDir === '/' || $scriptDir === '\\') {
        return '/';
    }
    
    return rtrim($scriptDir, '/\\') . '/';
}

$baseUrl = getBasePath();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Error 500 - Dev Network Solutions</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .error-container { 
            max-width: 700px; 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
            text-align: center;
        }
        h1 { 
            color: #dc3545; 
            font-size: 48px; 
            margin-bottom: 20px;
            font-weight: 300;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
            margin: 20px 0;
            opacity: 0.3;
        }
        p { 
            color: #666; 
            font-size: 16px; 
            line-height: 1.6; 
            margin-bottom: 30px;
        }
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 6px; 
            margin: 8px; 
            transition: all 0.3s;
            font-weight: 500;
        }
        .btn:hover { 
            background: #0056b3; 
            transform: translateY(-2px);
        }
        .btn-danger { 
            background: #dc3545; 
        }
        .btn-danger:hover { 
            background: #c82333; 
        }
        .btn-success { 
            background: #28a745; 
        }
        .btn-success:hover { 
            background: #218838; 
        }
        .btn-warning { 
            background: #ffc107; 
            color: #212529;
        }
        .btn-warning:hover { 
            background: #e0a800; 
        }
        .diagnostic-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        .diagnostic-section h3 {
            color: #495057;
            margin-top: 0;
        }
        .server-info {
            font-size: 12px;
            color: #999;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">🚨</div>
        <div class="error-code">500</div>
        <h1>Error Interno del Servidor</h1>
        
        <p>Lo sentimos, ha ocurrido un error interno en el servidor de Dev Network Solutions.</p>
        <p>Nuestro equipo ha sido notificado automáticamente y está trabajando para resolver el problema.</p>
        
        <div class="diagnostic-section">
            <h3>🔧 Herramientas de Diagnóstico</h3>
            <p>Si eres administrador, consulta nuestra <a href="<?php echo $baseUrl; ?>documentation.php">documentación</a> para opciones de diagnóstico.</p>

            <!--
            <a href="<?php echo $baseUrl; ?>emergency_fix.php" class="btn btn-danger">🚨 Reparación de Emergencia</a>
            <a href="<?php echo $baseUrl; ?>simple_test.php" class="btn btn-warning">🔍 Test Simple</a>
            <a href="<?php echo $baseUrl; ?>fix_panel_drsnet.php" class="btn btn-success">🛠️ Diagnóstico Completo</a>
            -->
        </div>
        
        <h3>🔗 Opciones de Navegación</h3>
        <a href="<?php echo $baseUrl; ?>index.php" class="btn">🏠 Volver al Inicio</a>
        <a href="<?php echo $baseUrl; ?>login.php" class="btn">🔐 Ir al Login</a>
        <a href="<?php echo $baseUrl; ?>dashboard.php" class="btn">📊 Dashboard</a>
        
        <div class="diagnostic-section">
            <h3>📋 Información del Error</h3>
            <p><strong>Tiempo:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>Servidor:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'Desconocido'; ?></p>
            <p><strong>Ruta:</strong> <?php echo $_SERVER['REQUEST_URI'] ?? 'Desconocida'; ?></p>
            <p><strong>IP del Cliente:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Desconocida'; ?></p>
            <p><strong>User Agent:</strong> <?php echo substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100); ?></p>
        </div>
        
        <div class="diagnostic-section">
            <h3>💡 Posibles Causas</h3>
            <ul style="text-align: left; color: #666;">
                <li>Configuración incorrecta de rutas (BASE_URL)</li>
                <li>Problemas en el archivo .htaccess</li>
                <li>Permisos insuficientes en directorios</li>
                <li>Base de datos bloqueada o corrupta</li>
                <li>Extensiones PHP faltantes</li>
                <li>Errores en el código PHP</li>
            </ul>
        </div>
        
        <div class="server-info">
            <p>Error 500 - Dev Network Solutions | PHP <?php echo phpversion(); ?> | <?php echo date('Y-m-d H:i:s'); ?></p>
            <?php if ($isProduction): ?>
                <p>Entorno: Producción (panel.drsnet.ovh/reg)</p>
            <?php else: ?>
                <p>Entorno: Desarrollo Local</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>