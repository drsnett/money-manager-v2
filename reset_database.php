<?php
/**
 * Script para reinicializar completamente la base de datos
 * Dev Network Solutions - Money Manager
 * ⚠️ ADVERTENCIA: Este script eliminará TODOS los datos
 */

// Función para mostrar mensajes
function showMessage($message, $type = 'info') {
    $colors = [
        'success' => '#28a745',
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8'
    ];
    
    echo "<div style='padding: 15px; margin: 10px 0; border-left: 5px solid {$colors[$type]}; background: #f8f9fa; border-radius: 5px;'>";
    echo "<strong>" . ucfirst($type) . ":</strong> $message";
    echo "</div>";
}

// Función para resetear la base de datos
function resetDatabase() {
    $dbPath = __DIR__ . '/data/money_manager.db';
    $backupPath = __DIR__ . '/data/money_manager_backup_' . date('Y-m-d_H-i-s') . '.db';
    
    try {
        // Crear backup si la base de datos existe
        if (file_exists($dbPath)) {
            if (copy($dbPath, $backupPath)) {
                showMessage("Backup creado: " . basename($backupPath), 'success');
            } else {
                showMessage("No se pudo crear backup", 'warning');
            }
            
            // Eliminar base de datos actual
            if (unlink($dbPath)) {
                showMessage("Base de datos anterior eliminada", 'success');
            } else {
                showMessage("No se pudo eliminar la base de datos anterior", 'error');
                return false;
            }
        }
        
        // Eliminar archivos relacionados
        $relatedFiles = [
            $dbPath . '-journal',
            $dbPath . '-wal',
            $dbPath . '-shm'
        ];
        
        foreach ($relatedFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
                showMessage("Archivo eliminado: " . basename($file), 'info');
            }
        }
        
        // Crear nueva base de datos
        require_once 'config/database.php';
        $database = new Database();
        
        showMessage("Nueva base de datos creada e inicializada", 'success');
        
        // Verificar que se crearon las tablas
        $connection = $database->getConnection();
        $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        showMessage("Tablas creadas: " . count($tables) . " (" . implode(', ', $tables) . ")", 'success');
        
        // Verificar usuarios por defecto
        $stmt = $connection->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        
        if ($userCount > 0) {
            showMessage("Usuarios por defecto creados: $userCount", 'success');
            
            // Mostrar usuarios creados
            $stmt = $connection->query("SELECT username, email, full_name, is_admin FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div class='table-responsive mt-3'>";
            echo "<table class='table table-striped table-sm'>";
            echo "<thead><tr><th>Usuario</th><th>Email</th><th>Nombre</th><th>Admin</th></tr></thead>";
            echo "<tbody>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td><strong>{$user['username']}</strong></td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['full_name']}</td>";
                echo "<td>" . ($user['is_admin'] ? '✅ Sí' : '❌ No') . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table></div>";
        }
        
        // Verificar categorías
        $stmt = $connection->query("SELECT COUNT(*) FROM categories");
        $categoryCount = $stmt->fetchColumn();
        
        if ($categoryCount > 0) {
            showMessage("Categorías por defecto creadas: $categoryCount", 'success');
        }
        
        return true;
        
    } catch (Exception $e) {
        showMessage("Error durante el reset: " . $e->getMessage(), 'error');
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset de Base de Datos - Money Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .btn-danger-custom {
            background: linear-gradient(135deg, #dc3545, #6f42c1);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            margin: 5px;
            font-weight: bold;
        }
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
        .btn-safe {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Reset de Base de Datos</h1>
            <p class="lead">Reinicialización completa del sistema</p>
        </div>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset']) && $_POST['confirm_reset'] === 'YES_DELETE_ALL') {
            echo "<h3>🔄 Ejecutando reset completo...</h3>";
            
            if (resetDatabase()) {
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Reset completado exitosamente</h4>";
                echo "<p>La base de datos ha sido reinicializada completamente.</p>";
                echo "<p><strong>Credenciales por defecto:</strong></p>";
                echo "<ul>";
                echo "<li><strong>Admin:</strong> usuario: admin, contraseña: admin123</li>";
                echo "<li><strong>Demo:</strong> usuario: demo, contraseña: demo123</li>";
                echo "</ul>";
                echo "</div>";
                
                echo "<div class='text-center'>";
                echo "<a href='login.php' class='btn btn-safe'>🔐 Ir al Login</a>";
                echo "<a href='test_database_connection.php' class='btn btn-safe'>🧪 Verificar Conexión</a>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-danger'>";
                echo "<h4>❌ Error durante el reset</h4>";
                echo "<p>Revise los mensajes de error anteriores.</p>";
                echo "</div>";
            }
        } else {
        ?>
        
        <div class="warning-box">
            <h3>⚠️ ADVERTENCIA IMPORTANTE</h3>
            <p><strong>Este script eliminará TODOS los datos de la base de datos:</strong></p>
            <ul>
                <li>Todos los usuarios (excepto los por defecto)</li>
                <li>Todas las transacciones</li>
                <li>Todas las cuentas por pagar y cobrar</li>
                <li>Todas las tarjetas de crédito</li>
                <li>Todas las deudas y pagos</li>
                <li>Todas las cuentas bancarias</li>
                <li>Todas las categorías personalizadas</li>
            </ul>
            <p><strong>Se creará un backup automático antes del reset.</strong></p>
        </div>
        
        <div class="alert alert-info">
            <h5>🔧 ¿Cuándo usar este reset?</h5>
            <ul>
                <li>Cuando la base de datos esté corrupta</li>
                <li>Para resolver problemas de bloqueo persistentes</li>
                <li>Para volver al estado inicial del sistema</li>
                <li>Para limpiar datos de prueba</li>
            </ul>
        </div>
        
        <form method="POST" onsubmit="return confirmReset()">
            <div class="text-center">
                <h4>Para confirmar el reset, escriba: <code>YES_DELETE_ALL</code></h4>
                <input type="text" name="confirm_reset" class="form-control text-center" 
                       placeholder="Escriba: YES_DELETE_ALL" 
                       style="max-width: 300px; margin: 20px auto; font-weight: bold;" required>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-danger-custom">
                        🗑️ EJECUTAR RESET COMPLETO
                    </button>
                </div>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <h5>🛡️ Opciones más seguras:</h5>
            <a href="fix_database_lock.php" class="btn btn-safe">🔧 Reparar Base de Datos</a>
            <a href="test_database_connection.php" class="btn btn-safe">🧪 Test de Conexión</a>
            <a href="index.php" class="btn btn-safe">🏠 Volver al Inicio</a>
        </div>
        
        <?php } ?>
    </div>
    
    <script>
        function confirmReset() {
            const input = document.querySelector('input[name="confirm_reset"]').value;
            if (input !== 'YES_DELETE_ALL') {
                alert('Debe escribir exactamente: YES_DELETE_ALL');
                return false;
            }
            
            return confirm('¿Está ABSOLUTAMENTE SEGURO de que desea eliminar TODOS los datos?\n\nEsta acción NO se puede deshacer.\n\nSe creará un backup automático.');
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>