<?php
/**
 * Script para diagnosticar y resolver problemas de bloqueo de SQLite
 * Dev Network Solutions - Money Manager
 */

require_once 'config/config.php';

// Función para mostrar mensajes con estilo
function showMessage($message, $type = 'info') {
    $colors = [
        'success' => '#28a745',
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8'
    ];
    
    echo "<div style='padding: 10px; margin: 10px 0; border-left: 4px solid {$colors[$type]}; background: #f8f9fa;'>";
    echo "<strong>" . ucfirst($type) . ":</strong> $message";
    echo "</div>";
}

// Función para verificar y reparar la base de datos
function fixDatabaseLock() {
    $dbPath = __DIR__ . '/data/money_manager.db';
    $journalPath = $dbPath . '-journal';
    $walPath = $dbPath . '-wal';
    $shmPath = $dbPath . '-shm';
    
    showMessage("Iniciando diagnóstico de la base de datos SQLite...", 'info');
    
    // Verificar si la base de datos existe
    if (!file_exists($dbPath)) {
        showMessage("La base de datos no existe. Se creará automáticamente.", 'warning');
        return true;
    }
    
    // Verificar permisos del directorio
    $dataDir = dirname($dbPath);
    if (!is_writable($dataDir)) {
        showMessage("El directorio de datos no tiene permisos de escritura: $dataDir", 'error');
        return false;
    }
    
    // Verificar permisos de la base de datos
    if (!is_writable($dbPath)) {
        showMessage("La base de datos no tiene permisos de escritura: $dbPath", 'error');
        return false;
    }
    
    // Eliminar archivos de journal y WAL si existen
    $filesToRemove = [$journalPath, $walPath, $shmPath];
    foreach ($filesToRemove as $file) {
        if (file_exists($file)) {
            if (unlink($file)) {
                showMessage("Archivo eliminado: " . basename($file), 'success');
            } else {
                showMessage("No se pudo eliminar: " . basename($file), 'error');
            }
        }
    }
    
    // Intentar conectar y verificar la integridad
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Configurar SQLite para evitar bloqueos
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA synchronous=NORMAL');
        $pdo->exec('PRAGMA cache_size=10000');
        $pdo->exec('PRAGMA temp_store=MEMORY');
        $pdo->exec('PRAGMA busy_timeout=30000');
        
        showMessage("Conexión a la base de datos establecida correctamente.", 'success');
        
        // Verificar integridad
        $stmt = $pdo->query('PRAGMA integrity_check');
        $result = $stmt->fetchColumn();
        
        if ($result === 'ok') {
            showMessage("Integridad de la base de datos: OK", 'success');
        } else {
            showMessage("Problema de integridad detectado: $result", 'error');
            return false;
        }
        
        // Optimizar la base de datos
        $pdo->exec('VACUUM');
        $pdo->exec('ANALYZE');
        showMessage("Base de datos optimizada correctamente.", 'success');
        
        return true;
        
    } catch (PDOException $e) {
        showMessage("Error de conexión: " . $e->getMessage(), 'error');
        return false;
    }
}

// Función para crear configuración optimizada de SQLite
function createOptimizedConfig() {
    $configContent = '<?php
/**
 * Configuración optimizada para SQLite
 * Generado automáticamente por fix_database_lock.php
 */

class OptimizedDatabase {
    private $db;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dbPath = __DIR__ . "/../data/money_manager.db";
            $this->db = new PDO("sqlite:" . $dbPath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configuraciones optimizadas para evitar bloqueos
            $this->db->exec("PRAGMA journal_mode=WAL");
            $this->db->exec("PRAGMA synchronous=NORMAL");
            $this->db->exec("PRAGMA cache_size=10000");
            $this->db->exec("PRAGMA temp_store=MEMORY");
            $this->db->exec("PRAGMA busy_timeout=30000");
            $this->db->exec("PRAGMA foreign_keys=ON");
            
        } catch (PDOException $e) {
            throw new Exception("Error de conexión optimizada: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
}
?>';
    
    $configPath = __DIR__ . '/config/optimized_database.php';
    if (file_put_contents($configPath, $configContent)) {
        showMessage("Configuración optimizada creada: $configPath", 'success');
        return true;
    } else {
        showMessage("No se pudo crear la configuración optimizada", 'error');
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reparación de Base de Datos - Money Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .btn-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            margin: 5px;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tools"></i> Reparación de Base de Datos</h1>
            <p class="lead">Herramienta de diagnóstico y reparación para SQLite</p>
        </div>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'fix':
                        echo "<h3>🔧 Ejecutando reparación...</h3>";
                        if (fixDatabaseLock()) {
                            showMessage("¡Reparación completada exitosamente!", 'success');
                            echo "<p><a href='index.php' class='btn btn-custom'>🏠 Volver al inicio</a></p>";
                        } else {
                            showMessage("La reparación no se completó correctamente. Revise los errores anteriores.", 'error');
                        }
                        break;
                        
                    case 'optimize':
                        echo "<h3>⚡ Creando configuración optimizada...</h3>";
                        if (createOptimizedConfig()) {
                            showMessage("Configuración optimizada creada correctamente.", 'success');
                        }
                        break;
                        
                    case 'info':
                        echo "<h3>📊 Información de la base de datos</h3>";
                        try {
                            require_once 'config/database.php';
                            $db = new Database();
                            $info = $db->getDatabaseInfo();
                            
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped'>";
                            echo "<thead><tr><th>Tabla</th><th>Registros</th><th>Columnas</th></tr></thead>";
                            echo "<tbody>";
                            
                            foreach ($info as $table => $data) {
                                echo "<tr>";
                                echo "<td><strong>$table</strong></td>";
                                echo "<td>{$data['records']}</td>";
                                echo "<td>" . count($data['columns']) . "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                            showMessage("Información de la base de datos cargada correctamente.", 'success');
                            
                        } catch (Exception $e) {
                            showMessage("Error al obtener información: " . $e->getMessage(), 'error');
                        }
                        break;
                }
            }
        } else {
        ?>
        
        <div class="row">
            <div class="col-md-12">
                <h3>🔍 Diagnóstico Disponible</h3>
                <p>Seleccione una acción para diagnosticar y reparar problemas de la base de datos:</p>
                
                <form method="POST" style="margin: 20px 0;">
                    <button type="submit" name="action" value="fix" class="btn btn-custom">
                        🔧 Reparar Base de Datos
                    </button>
                    <button type="submit" name="action" value="optimize" class="btn btn-custom">
                        ⚡ Crear Configuración Optimizada
                    </button>
                    <button type="submit" name="action" value="info" class="btn btn-custom">
                        📊 Ver Información de BD
                    </button>
                </form>
                
                <div class="alert alert-info">
                    <h5>💡 Problemas Comunes y Soluciones:</h5>
                    <ul>
                        <li><strong>Database is locked:</strong> Archivos de journal corruptos</li>
                        <li><strong>Permission denied:</strong> Permisos incorrectos en directorio</li>
                        <li><strong>Slow queries:</strong> Base de datos no optimizada</li>
                    </ul>
                </div>
                
                <p>
                    <a href="index.php" class="btn btn-secondary">🏠 Volver al inicio</a>
                    <a href="dashboard.php" class="btn btn-primary">📊 Ir al Dashboard</a>
                </p>
            </div>
        </div>
        
        <?php } ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>