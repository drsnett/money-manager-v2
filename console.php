#!/usr/bin/env php
<?php
/**
 * Consola de comandos para Money Manager
 * Permite ejecutar tareas administrativas desde línea de comandos
 */

// Configurar el entorno para CLI
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/env_config.php';
require_once __DIR__ . '/classes/Migration.php';
require_once __DIR__ . '/classes/Cache.php';
require_once __DIR__ . '/classes/Notification.php';

/**
 * Clase principal de la consola
 */
class Console {
    private $commands = [];
    
    public function __construct() {
        $this->registerCommands();
    }
    
    /**
     * Registrar comandos disponibles
     */
    private function registerCommands() {
        $this->commands = [
            'migrate' => [
                'description' => 'Ejecutar migraciones pendientes',
                'method' => 'migrate'
            ],
            'migrate:rollback' => [
                'description' => 'Revertir migraciones (--steps=N para especificar cantidad)',
                'method' => 'rollback'
            ],
            'migrate:status' => [
                'description' => 'Mostrar estado de las migraciones',
                'method' => 'migrationStatus'
            ],
            'migrate:refresh' => [
                'description' => 'Refrescar la base de datos (rollback + migrate)',
                'method' => 'refreshDatabase'
            ],
            'migrate:create' => [
                'description' => 'Crear nueva migración (--name=nombre --table=tabla)',
                'method' => 'createMigration'
            ],
            'cache:clear' => [
                'description' => 'Limpiar caché',
                'method' => 'clearCache'
            ],
            'cache:stats' => [
                'description' => 'Mostrar estadísticas del caché',
                'method' => 'cacheStats'
            ],
            'notifications:generate' => [
                'description' => 'Generar notificaciones de vencimientos',
                'method' => 'generateNotifications'
            ],
            'notifications:clean' => [
                'description' => 'Limpiar notificaciones expiradas',
                'method' => 'cleanNotifications'
            ],
            'db:seed' => [
                'description' => 'Ejecutar seeders de base de datos',
                'method' => 'seedDatabase'
            ],
            'db:backup' => [
                'description' => 'Crear backup de la base de datos',
                'method' => 'backupDatabase'
            ],
            'system:check' => [
                'description' => 'Verificar estado del sistema',
                'method' => 'systemCheck'
            ],
            'help' => [
                'description' => 'Mostrar ayuda',
                'method' => 'showHelp'
            ]
        ];
    }
    
    /**
     * Ejecutar comando
     */
    public function run($argv) {
        $command = $argv[1] ?? 'help';
        $options = $this->parseOptions(array_slice($argv, 2));
        
        if (!isset($this->commands[$command])) {
            $this->error("Comando no encontrado: {$command}");
            $this->showHelp();
            return 1;
        }
        
        $method = $this->commands[$command]['method'];
        
        try {
            return $this->$method($options);
        } catch (Exception $e) {
            $this->error("Error ejecutando comando: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Parsear opciones de línea de comandos
     */
    private function parseOptions($args) {
        $options = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = isset($parts[1]) ? $parts[1] : true;
                $options[$key] = $value;
            }
        }
        
        return $options;
    }
    
    /**
     * Ejecutar migraciones
     */
    private function migrate($options) {
        $this->info('Ejecutando migraciones...');
        $migration = new Migration();
        $migration->migrate();
        return 0;
    }
    
    /**
     * Revertir migraciones
     */
    private function rollback($options) {
        $steps = isset($options['steps']) ? (int)$options['steps'] : 1;
        $this->info("Revirtiendo {$steps} migración(es)...");
        
        $migration = new Migration();
        $migration->rollback($steps);
        return 0;
    }
    
    /**
     * Estado de migraciones
     */
    private function migrationStatus($options) {
        $migration = new Migration();
        $migration->status();
        return 0;
    }
    
    /**
     * Refrescar base de datos
     */
    private function refreshDatabase($options) {
        $this->warning('¡ADVERTENCIA! Esto eliminará todos los datos de la base de datos.');
        
        if (!$this->confirm('¿Estás seguro de que quieres continuar?')) {
            $this->info('Operación cancelada.');
            return 0;
        }
        
        $migration = new Migration();
        $migration->refresh();
        return 0;
    }
    
    /**
     * Crear nueva migración
     */
    private function createMigration($options) {
        if (!isset($options['name'])) {
            $this->error('Debes especificar un nombre para la migración con --name=nombre');
            return 1;
        }
        
        $name = $options['name'];
        $table = $options['table'] ?? null;
        
        $migration = new Migration();
        $filepath = $migration->create($name, $table);
        
        $this->success("Migración creada: {$filepath}");
        return 0;
    }
    
    /**
     * Limpiar caché
     */
    private function clearCache($options) {
        $this->info('Limpiando caché...');
        
        $cache = new Cache();
        $cache->clear();
        
        $this->success('Caché limpiado exitosamente.');
        return 0;
    }
    
    /**
     * Estadísticas del caché
     */
    private function cacheStats($options) {
        $cache = new Cache();
        $stats = $cache->getStats();
        
        $this->info('Estadísticas del caché:');
        echo "\n";
        
        // Caché de archivos
        echo "Caché de archivos:\n";
        echo "  Habilitado: " . ($stats['file_cache']['enabled'] ? 'Sí' : 'No') . "\n";
        echo "  Directorio: {$stats['file_cache']['directory']}\n";
        echo "  Archivos: {$stats['file_cache']['files']}\n";
        echo "  Tamaño: " . $this->formatBytes($stats['file_cache']['size']) . "\n";
        
        // Caché APCu
        echo "\nCaché APCu:\n";
        echo "  Habilitado: " . ($stats['apcu_cache']['enabled'] ? 'Sí' : 'No') . "\n";
        
        if ($stats['apcu_cache']['enabled'] && $stats['apcu_cache']['info']) {
            $info = $stats['apcu_cache']['info'];
            echo "  Memoria total: " . $this->formatBytes($info['memory_type']) . "\n";
            echo "  Memoria usada: " . $this->formatBytes($info['memory_type']) . "\n";
        }
        
        return 0;
    }
    
    /**
     * Generar notificaciones
     */
    private function generateNotifications($options) {
        $this->info('Generando notificaciones de vencimientos...');
        
        $notification = new Notification();
        $notification->generateDueNotifications();
        
        $this->success('Notificaciones generadas exitosamente.');
        return 0;
    }
    
    /**
     * Limpiar notificaciones expiradas
     */
    private function cleanNotifications($options) {
        $this->info('Limpiando notificaciones expiradas...');
        
        $notification = new Notification();
        $count = $notification->cleanExpired();
        
        $this->success("Se eliminaron {$count} notificaciones expiradas.");
        return 0;
    }
    
    /**
     * Ejecutar seeders
     */
    private function seedDatabase($options) {
        $this->info('Ejecutando seeders...');
        
        // Aquí se pueden agregar seeders específicos
        $this->warning('No hay seeders configurados aún.');
        
        return 0;
    }
    
    /**
     * Crear backup de base de datos
     */
    private function backupDatabase($options) {
        $this->info('Creando backup de la base de datos...');
        
        $backupDir = __DIR__ . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = "{$backupDir}/backup_{$timestamp}.db";
        
        $dbPath = __DIR__ . '/data/money_manager.db';
        
        if (file_exists($dbPath)) {
            if (copy($dbPath, $backupFile)) {
                $this->success("Backup creado: {$backupFile}");
            } else {
                $this->error('Error creando el backup.');
                return 1;
            }
        } else {
            $this->error('Base de datos no encontrada.');
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Verificar estado del sistema
     */
    private function systemCheck($options) {
        $this->info('Verificando estado del sistema...');
        echo "\n";
        
        // Verificar PHP
        echo "PHP Version: " . PHP_VERSION . "\n";
        
        // Verificar extensiones
        $extensions = ['pdo', 'pdo_sqlite', 'json', 'mbstring'];
        foreach ($extensions as $ext) {
            $status = extension_loaded($ext) ? '✓' : '✗';
            echo "Extensión {$ext}: {$status}\n";
        }
        
        // Verificar permisos de directorios
        $directories = ['data', 'logs', 'uploads'];
        foreach ($directories as $dir) {
            $path = __DIR__ . '/' . $dir;
            $writable = is_writable($path) ? '✓' : '✗';
            echo "Directorio {$dir} escribible: {$writable}\n";
        }
        
        // Verificar base de datos
        try {
            $database = new Database();
            $db = $database->getConnection();
            echo "Conexión a base de datos: ✓\n";
        } catch (Exception $e) {
            echo "Conexión a base de datos: ✗ (" . $e->getMessage() . ")\n";
        }
        
        echo "\n";
        $this->success('Verificación del sistema completada.');
        
        return 0;
    }
    
    /**
     * Mostrar ayuda
     */
    private function showHelp($options = []) {
        echo "Money Manager - Consola de comandos\n";
        echo "\nUso: php console.php [comando] [opciones]\n";
        echo "\nComandos disponibles:\n";
        
        foreach ($this->commands as $command => $info) {
            echo sprintf("  %-25s %s\n", $command, $info['description']);
        }
        
        echo "\nEjemplos:\n";
        echo "  php console.php migrate\n";
        echo "  php console.php migrate:rollback --steps=2\n";
        echo "  php console.php migrate:create --name=add_users_table --table=users\n";
        echo "  php console.php cache:clear\n";
        
        return 0;
    }
    
    /**
     * Confirmar acción
     */
    private function confirm($message) {
        echo $message . ' (y/N): ';
        $handle = fopen('php://stdin', 'r');
        $line = fgets($handle);
        fclose($handle);
        return strtolower(trim($line)) === 'y';
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
     * Mostrar mensaje de información
     */
    private function info($message) {
        echo "\033[36m[INFO]\033[0m {$message}\n";
    }
    
    /**
     * Mostrar mensaje de éxito
     */
    private function success($message) {
        echo "\033[32m[SUCCESS]\033[0m {$message}\n";
    }
    
    /**
     * Mostrar mensaje de advertencia
     */
    private function warning($message) {
        echo "\033[33m[WARNING]\033[0m {$message}\n";
    }
    
    /**
     * Mostrar mensaje de error
     */
    private function error($message) {
        echo "\033[31m[ERROR]\033[0m {$message}\n";
    }
}

// Ejecutar la consola
if (php_sapi_name() === 'cli') {
    $console = new Console();
    $exitCode = $console->run($argv);
    exit($exitCode);
} else {
    echo "Este script debe ejecutarse desde línea de comandos.\n";
    exit(1);
}
?>