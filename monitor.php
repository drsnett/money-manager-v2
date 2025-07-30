<?php
/**
 * Script de Monitoreo del Sistema
 * Money Manager v2.0
 * 
 * Este script verifica el estado del sistema y genera reportes de salud
 */

// Verificar acceso
if (!isset($_GET['monitor_key']) || $_GET['monitor_key'] !== 'mm2024monitor') {
    if (php_sapi_name() !== 'cli') {
        http_response_code(403);
        die('Acceso denegado');
    }
}

// Configuración
$baseDir = __DIR__;
$dataDir = $baseDir . '/data';
$logsDir = $baseDir . '/logs';
$uploadsDir = $baseDir . '/uploads';
$backupsDir = $baseDir . '/backups';

/**
 * Clase de Monitoreo del Sistema
 */
class SystemMonitor {
    private $checks = [];
    private $warnings = [];
    private $errors = [];
    private $info = [];
    
    public function __construct() {
        $this->runAllChecks();
    }
    
    /**
     * Ejecutar todas las verificaciones
     */
    private function runAllChecks() {
        $this->checkPHPVersion();
        $this->checkPHPExtensions();
        $this->checkDirectories();
        $this->checkPermissions();
        $this->checkDatabase();
        $this->checkDiskSpace();
        $this->checkMemoryUsage();
        $this->checkLogFiles();
        $this->checkBackups();
        $this->checkSecurity();
        $this->checkPerformance();
    }
    
    /**
     * Verificar versión de PHP
     */
    private function checkPHPVersion() {
        $version = PHP_VERSION;
        $minVersion = '7.4.0';
        
        if (version_compare($version, $minVersion, '>=')) {
            $this->addCheck('PHP Version', "✅ PHP $version (OK)", 'success');
        } else {
            $this->addCheck('PHP Version', "❌ PHP $version (Mínimo requerido: $minVersion)", 'error');
        }
        
        // Verificar si es una versión soportada
        if (version_compare($version, '8.0.0', '>=')) {
            $this->addInfo('PHP 8.0+ detectado - Rendimiento optimizado');
        }
    }
    
    /**
     * Verificar extensiones de PHP
     */
    private function checkPHPExtensions() {
        $required = ['pdo', 'pdo_sqlite', 'json', 'mbstring', 'openssl'];
        $optional = ['zip', 'curl', 'gd', 'fileinfo'];
        
        foreach ($required as $ext) {
            if (extension_loaded($ext)) {
                $this->addCheck('Extension', "✅ $ext (requerida)", 'success');
            } else {
                $this->addCheck('Extension', "❌ $ext (REQUERIDA - FALTANTE)", 'error');
            }
        }
        
        foreach ($optional as $ext) {
            if (extension_loaded($ext)) {
                $this->addCheck('Extension', "✅ $ext (opcional)", 'success');
            } else {
                $this->addCheck('Extension', "⚠️  $ext (opcional - recomendada)", 'warning');
            }
        }
    }
    
    /**
     * Verificar directorios
     */
    private function checkDirectories() {
        global $dataDir, $logsDir, $uploadsDir, $backupsDir;
        
        $dirs = [
            'data' => $dataDir,
            'logs' => $logsDir,
            'uploads' => $uploadsDir,
            'backups' => $backupsDir
        ];
        
        foreach ($dirs as $name => $path) {
            if (is_dir($path)) {
                $this->addCheck('Directory', "✅ $name ($path)", 'success');
            } else {
                $this->addCheck('Directory', "❌ $name ($path) - NO EXISTE", 'error');
            }
        }
    }
    
    /**
     * Verificar permisos
     */
    private function checkPermissions() {
        global $dataDir, $logsDir, $uploadsDir, $backupsDir;
        
        $dirs = [$dataDir, $logsDir, $uploadsDir, $backupsDir];
        
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    $this->addCheck('Permissions', "✅ " . basename($dir) . " (escribible)", 'success');
                } else {
                    $this->addCheck('Permissions', "❌ " . basename($dir) . " (NO escribible)", 'error');
                }
            }
        }
    }
    
    /**
     * Verificar base de datos
     */
    private function checkDatabase() {
        global $dataDir;
        
        $dbFile = $dataDir . '/money_manager.db';
        
        if (file_exists($dbFile)) {
            $size = filesize($dbFile);
            $sizeFormatted = $this->formatBytes($size);
            
            try {
                $pdo = new PDO('sqlite:' . $dbFile);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Verificar integridad
                $stmt = $pdo->query('PRAGMA integrity_check');
                $result = $stmt->fetchColumn();
                
                if ($result === 'ok') {
                    $this->addCheck('Database', "✅ Integridad OK ($sizeFormatted)", 'success');
                } else {
                    $this->addCheck('Database', "❌ Problemas de integridad: $result", 'error');
                }
                
                // Verificar tablas principales
                $tables = ['users', 'transactions', 'categories', 'credit_cards'];
                foreach ($tables as $table) {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    $this->addInfo("Tabla $table: $count registros");
                }
                
            } catch (Exception $e) {
                $this->addCheck('Database', "❌ Error de conexión: " . $e->getMessage(), 'error');
            }
        } else {
            $this->addCheck('Database', "❌ Base de datos no encontrada", 'error');
        }
    }
    
    /**
     * Verificar espacio en disco
     */
    private function checkDiskSpace() {
        $freeBytes = disk_free_space('.');
        $totalBytes = disk_total_space('.');
        
        if ($freeBytes !== false && $totalBytes !== false) {
            $freeFormatted = $this->formatBytes($freeBytes);
            $totalFormatted = $this->formatBytes($totalBytes);
            $percentUsed = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 2);
            
            if ($percentUsed < 80) {
                $this->addCheck('Disk Space', "✅ $freeFormatted libres de $totalFormatted ($percentUsed% usado)", 'success');
            } elseif ($percentUsed < 90) {
                $this->addCheck('Disk Space', "⚠️  $freeFormatted libres de $totalFormatted ($percentUsed% usado)", 'warning');
            } else {
                $this->addCheck('Disk Space', "❌ $freeFormatted libres de $totalFormatted ($percentUsed% usado)", 'error');
            }
        }
    }
    
    /**
     * Verificar uso de memoria
     */
    private function checkMemoryUsage() {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        $usageFormatted = $this->formatBytes($memoryUsage);
        $peakFormatted = $this->formatBytes($memoryPeak);
        
        $this->addCheck('Memory', "✅ Uso actual: $usageFormatted, Pico: $peakFormatted, Límite: $memoryLimit", 'success');
    }
    
    /**
     * Verificar archivos de log
     */
    private function checkLogFiles() {
        global $logsDir;
        
        if (is_dir($logsDir)) {
            $logFiles = glob($logsDir . '/*.log');
            
            if (empty($logFiles)) {
                $this->addCheck('Logs', "ℹ️  No hay archivos de log", 'info');
            } else {
                foreach ($logFiles as $logFile) {
                    $size = filesize($logFile);
                    $sizeFormatted = $this->formatBytes($size);
                    $name = basename($logFile);
                    
                    if ($size > 10 * 1024 * 1024) { // 10MB
                        $this->addCheck('Logs', "⚠️  $name ($sizeFormatted) - Archivo grande", 'warning');
                    } else {
                        $this->addCheck('Logs', "✅ $name ($sizeFormatted)", 'success');
                    }
                }
            }
        }
    }
    
    /**
     * Verificar backups
     */
    private function checkBackups() {
        global $backupsDir;
        
        if (is_dir($backupsDir)) {
            $backups = array_merge(
                glob($backupsDir . '/money_manager_backup_*.zip'),
                glob($backupsDir . '/money_manager_backup_*')
            );
            
            if (empty($backups)) {
                $this->addCheck('Backups', "⚠️  No hay backups disponibles", 'warning');
            } else {
                // Encontrar el backup más reciente
                $latestBackup = '';
                $latestTime = 0;
                
                foreach ($backups as $backup) {
                    $time = filemtime($backup);
                    if ($time > $latestTime) {
                        $latestTime = $time;
                        $latestBackup = $backup;
                    }
                }
                
                $daysSince = floor((time() - $latestTime) / (24 * 60 * 60));
                $backupName = basename($latestBackup);
                
                if ($daysSince <= 1) {
                    $this->addCheck('Backups', "✅ Último backup: $backupName (hace $daysSince días)", 'success');
                } elseif ($daysSince <= 7) {
                    $this->addCheck('Backups', "⚠️  Último backup: $backupName (hace $daysSince días)", 'warning');
                } else {
                    $this->addCheck('Backups', "❌ Último backup: $backupName (hace $daysSince días)", 'error');
                }
                
                $this->addInfo("Total de backups: " . count($backups));
            }
        }
    }
    
    /**
     * Verificar configuración de seguridad
     */
    private function checkSecurity() {
        // Verificar display_errors
        if (ini_get('display_errors')) {
            $this->addCheck('Security', "❌ display_errors está habilitado", 'error');
        } else {
            $this->addCheck('Security', "✅ display_errors está deshabilitado", 'success');
        }
        
        // Verificar expose_php
        if (ini_get('expose_php')) {
            $this->addCheck('Security', "⚠️  expose_php está habilitado", 'warning');
        } else {
            $this->addCheck('Security', "✅ expose_php está deshabilitado", 'success');
        }
        
        // Verificar HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->addCheck('Security', "✅ HTTPS habilitado", 'success');
        } else {
            $this->addCheck('Security', "⚠️  HTTPS no detectado", 'warning');
        }
    }
    
    /**
     * Verificar rendimiento
     */
    private function checkPerformance() {
        // Verificar OPcache
        if (extension_loaded('opcache') && ini_get('opcache.enable')) {
            $this->addCheck('Performance', "✅ OPcache habilitado", 'success');
        } else {
            $this->addCheck('Performance', "⚠️  OPcache no habilitado", 'warning');
        }
        
        // Verificar límites de PHP
        $maxExecutionTime = ini_get('max_execution_time');
        $maxInputVars = ini_get('max_input_vars');
        $postMaxSize = ini_get('post_max_size');
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        
        $this->addInfo("Tiempo máximo de ejecución: {$maxExecutionTime}s");
        $this->addInfo("Variables de entrada máximas: $maxInputVars");
        $this->addInfo("Tamaño máximo POST: $postMaxSize");
        $this->addInfo("Tamaño máximo de archivo: $uploadMaxFilesize");
    }
    
    /**
     * Agregar verificación
     */
    private function addCheck($category, $message, $type) {
        $this->checks[] = [
            'category' => $category,
            'message' => $message,
            'type' => $type,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($type === 'error') {
            $this->errors[] = $message;
        } elseif ($type === 'warning') {
            $this->warnings[] = $message;
        }
    }
    
    /**
     * Agregar información
     */
    private function addInfo($message) {
        $this->info[] = $message;
    }
    
    /**
     * Formatear bytes
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Generar reporte
     */
    public function generateReport($format = 'html') {
        if ($format === 'json') {
            return $this->generateJSONReport();
        } else {
            return $this->generateHTMLReport();
        }
    }
    
    /**
     * Generar reporte JSON
     */
    private function generateJSONReport() {
        return json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => empty($this->errors) ? (empty($this->warnings) ? 'healthy' : 'warning') : 'error',
            'summary' => [
                'total_checks' => count($this->checks),
                'errors' => count($this->errors),
                'warnings' => count($this->warnings),
                'info_items' => count($this->info)
            ],
            'checks' => $this->checks,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'info' => $this->info
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * Generar reporte HTML
     */
    private function generateHTMLReport() {
        $status = empty($this->errors) ? (empty($this->warnings) ? 'healthy' : 'warning') : 'error';
        $statusColor = $status === 'healthy' ? '#28a745' : ($status === 'warning' ? '#ffc107' : '#dc3545');
        $statusText = $status === 'healthy' ? 'SALUDABLE' : ($status === 'warning' ? 'ADVERTENCIAS' : 'ERRORES');
        
        $html = "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Monitor del Sistema - Money Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; color: white; font-weight: bold; }
        .checks { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .check-category { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        .check-item { margin: 5px 0; padding: 5px; border-radius: 3px; }
        .success { background: #d4edda; color: #155724; }
        .warning { background: #fff3cd; color: #856404; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .summary-item { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .timestamp { text-align: center; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 Monitor del Sistema</h1>
            <h2>Money Manager v2.0</h2>
        </div>
        
        <div class='status' style='background-color: $statusColor;'>
            Estado del Sistema: $statusText
        </div>
        
        <div class='summary'>
            <div class='summary-item'>
                <h3>" . count($this->checks) . "</h3>
                <p>Verificaciones Totales</p>
            </div>
            <div class='summary-item'>
                <h3 style='color: #dc3545;'>" . count($this->errors) . "</h3>
                <p>Errores</p>
            </div>
            <div class='summary-item'>
                <h3 style='color: #ffc107;'>" . count($this->warnings) . "</h3>
                <p>Advertencias</p>
            </div>
            <div class='summary-item'>
                <h3 style='color: #17a2b8;'>" . count($this->info) . "</h3>
                <p>Información</p>
            </div>
        </div>";
        
        // Agrupar verificaciones por categoría
        $categories = [];
        foreach ($this->checks as $check) {
            $categories[$check['category']][] = $check;
        }
        
        $html .= "<div class='checks'>";
        foreach ($categories as $category => $checks) {
            $html .= "<div class='check-category'>
                <h3>$category</h3>";
            
            foreach ($checks as $check) {
                $html .= "<div class='check-item {$check['type']}'>{$check['message']}</div>";
            }
            
            $html .= "</div>";
        }
        $html .= "</div>";
        
        // Información adicional
        if (!empty($this->info)) {
            $html .= "<div class='check-category'>
                <h3>Información del Sistema</h3>";
            foreach ($this->info as $info) {
                $html .= "<div class='check-item info'>ℹ️  $info</div>";
            }
            $html .= "</div>";
        }
        
        $html .= "<div class='timestamp'>
            Generado el: " . date('Y-m-d H:i:s') . "
        </div>
    </div>
</body>
</html>";
        
        return $html;
    }
}

// Ejecutar monitoreo
$monitor = new SystemMonitor();

// Determinar formato de salida
$format = isset($_GET['format']) ? $_GET['format'] : (php_sapi_name() === 'cli' ? 'text' : 'html');

if ($format === 'json') {
    header('Content-Type: application/json');
    echo $monitor->generateReport('json');
} elseif ($format === 'text') {
    // Salida para línea de comandos
    $report = json_decode($monitor->generateReport('json'), true);
    
    echo "\n=== MONITOR DEL SISTEMA - MONEY MANAGER ===\n";
    echo "Fecha: " . $report['timestamp'] . "\n";
    echo "Estado: " . strtoupper($report['status']) . "\n";
    echo "Verificaciones: " . $report['summary']['total_checks'] . "\n";
    echo "Errores: " . $report['summary']['errors'] . "\n";
    echo "Advertencias: " . $report['summary']['warnings'] . "\n\n";
    
    if (!empty($report['errors'])) {
        echo "❌ ERRORES:\n";
        foreach ($report['errors'] as $error) {
            echo "  - $error\n";
        }
        echo "\n";
    }
    
    if (!empty($report['warnings'])) {
        echo "⚠️  ADVERTENCIAS:\n";
        foreach ($report['warnings'] as $warning) {
            echo "  - $warning\n";
        }
        echo "\n";
    }
    
    echo "💡 Para ver el reporte completo en HTML:\n";
    echo "   http://tu-dominio.com/monitor.php?monitor_key=mm2024monitor\n";
} else {
    echo $monitor->generateReport('html');
}

?>