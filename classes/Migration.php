<?php
/**
 * Sistema de migración de base de datos
 * Permite gestionar cambios en el esquema de la base de datos de forma controlada
 */

require_once __DIR__ . '/../config/database.php';

class Migration {
    private $db;
    private $migrationsDir;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->migrationsDir = __DIR__ . '/../migrations';
        
        $this->createMigrationsTable();
        $this->ensureMigrationsDirectory();
    }
    
    /**
     * Crear tabla de migraciones si no existe
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INTEGER NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->exec($sql);
    }
    
    /**
     * Asegurar que el directorio de migraciones existe
     */
    private function ensureMigrationsDirectory() {
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir, 0755, true);
        }
    }
    
    /**
     * Ejecutar migraciones pendientes
     */
    public function migrate() {
        $pendingMigrations = $this->getPendingMigrations();
        
        if (empty($pendingMigrations)) {
            echo "No hay migraciones pendientes.\n";
            return true;
        }
        
        $batch = $this->getNextBatchNumber();
        $executed = 0;
        
        foreach ($pendingMigrations as $migration) {
            try {
                echo "Ejecutando migración: {$migration}\n";
                
                $this->executeMigration($migration, 'up');
                $this->recordMigration($migration, $batch);
                
                $executed++;
                echo "✓ Migración {$migration} ejecutada exitosamente\n";
                
            } catch (Exception $e) {
                echo "✗ Error ejecutando migración {$migration}: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
        
        echo "\nSe ejecutaron {$executed} migraciones exitosamente.\n";
        return true;
    }
    
    /**
     * Revertir la última migración o un número específico de migraciones
     */
    public function rollback($steps = 1) {
        $migrationsToRollback = $this->getMigrationsToRollback($steps);
        
        if (empty($migrationsToRollback)) {
            echo "No hay migraciones para revertir.\n";
            return true;
        }
        
        $reverted = 0;
        
        foreach ($migrationsToRollback as $migration) {
            try {
                echo "Revirtiendo migración: {$migration['migration']}\n";
                
                $this->executeMigration($migration['migration'], 'down');
                $this->removeMigrationRecord($migration['migration']);
                
                $reverted++;
                echo "✓ Migración {$migration['migration']} revertida exitosamente\n";
                
            } catch (Exception $e) {
                echo "✗ Error revirtiendo migración {$migration['migration']}: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
        
        echo "\nSe revirtieron {$reverted} migraciones exitosamente.\n";
        return true;
    }
    
    /**
     * Crear una nueva migración
     */
    public function create($name, $table = null) {
        $timestamp = date('Y_m_d_His');
        $className = $this->studlyCase($name);
        $filename = "{$timestamp}_{$name}.php";
        $filepath = $this->migrationsDir . '/' . $filename;
        
        $template = $this->getMigrationTemplate($className, $table);
        
        if (file_put_contents($filepath, $template)) {
            echo "Migración creada: {$filename}\n";
            return $filepath;
        } else {
            throw new Exception("Error creando la migración: {$filename}");
        }
    }
    
    /**
     * Obtener estado de las migraciones
     */
    public function status() {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        echo "Estado de las migraciones:\n";
        echo str_repeat('-', 80) . "\n";
        echo sprintf("%-50s %-10s %-20s\n", 'Migración', 'Estado', 'Batch');
        echo str_repeat('-', 80) . "\n";
        
        foreach ($allMigrations as $migration) {
            $migrationName = pathinfo($migration, PATHINFO_FILENAME);
            $executed = isset($executedMigrations[$migrationName]);
            $status = $executed ? 'Ejecutada' : 'Pendiente';
            $batch = $executed ? $executedMigrations[$migrationName]['batch'] : '-';
            
            echo sprintf("%-50s %-10s %-20s\n", $migrationName, $status, $batch);
        }
        
        echo str_repeat('-', 80) . "\n";
    }
    
    /**
     * Refrescar la base de datos (rollback completo + migrate)
     */
    public function refresh() {
        echo "Refrescando la base de datos...\n";
        
        // Revertir todas las migraciones
        $this->rollbackAll();
        
        // Ejecutar todas las migraciones
        $this->migrate();
        
        echo "Base de datos refrescada exitosamente.\n";
    }
    
    /**
     * Revertir todas las migraciones
     */
    public function rollbackAll() {
        $sql = "SELECT migration FROM migrations ORDER BY batch DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($migrations as $migration) {
            try {
                echo "Revirtiendo migración: {$migration}\n";
                $this->executeMigration($migration, 'down');
                $this->removeMigrationRecord($migration);
                echo "✓ Migración {$migration} revertida exitosamente\n";
            } catch (Exception $e) {
                echo "✗ Error revirtiendo migración {$migration}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Obtener migraciones pendientes
     */
    private function getPendingMigrations() {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = array_keys($this->getExecutedMigrations());
        
        $pending = [];
        foreach ($allMigrations as $migration) {
            $migrationName = pathinfo($migration, PATHINFO_FILENAME);
            if (!in_array($migrationName, $executedMigrations)) {
                $pending[] = $migrationName;
            }
        }
        
        sort($pending);
        return $pending;
    }
    
    /**
     * Obtener todas las migraciones ejecutadas
     */
    private function getExecutedMigrations() {
        $sql = "SELECT migration, batch FROM migrations ORDER BY migration";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $migrations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $migrations[$row['migration']] = $row;
        }
        
        return $migrations;
    }
    
    /**
     * Obtener todos los archivos de migración
     */
    private function getAllMigrationFiles() {
        $files = glob($this->migrationsDir . '/*.php');
        return array_map('basename', $files);
    }
    
    /**
     * Obtener migraciones para revertir
     */
    private function getMigrationsToRollback($steps) {
        $sql = "SELECT migration, batch FROM migrations 
                ORDER BY batch DESC, id DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$steps]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ejecutar una migración
     */
    private function executeMigration($migrationName, $direction) {
        $filepath = $this->migrationsDir . '/' . $migrationName . '.php';
        
        if (!file_exists($filepath)) {
            throw new Exception("Archivo de migración no encontrado: {$filepath}");
        }
        
        // Incluir el archivo de migración
        require_once $filepath;
        
        // Obtener el nombre de la clase
        $className = $this->getMigrationClassName($migrationName);
        
        if (!class_exists($className)) {
            throw new Exception("Clase de migración no encontrada: {$className}");
        }
        
        // Instanciar y ejecutar la migración
        $migration = new $className($this->db);
        
        if (!method_exists($migration, $direction)) {
            throw new Exception("Método {$direction} no encontrado en la migración {$className}");
        }
        
        $migration->$direction();
    }
    
    /**
     * Registrar migración ejecutada
     */
    private function recordMigration($migration, $batch) {
        $sql = "INSERT INTO migrations (migration, batch) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$migration, $batch]);
    }
    
    /**
     * Eliminar registro de migración
     */
    private function removeMigrationRecord($migration) {
        $sql = "DELETE FROM migrations WHERE migration = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$migration]);
    }
    
    /**
     * Obtener siguiente número de batch
     */
    private function getNextBatchNumber() {
        $sql = "SELECT MAX(batch) as max_batch FROM migrations";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['max_batch'] ?? 0) + 1;
    }
    
    /**
     * Obtener nombre de clase de migración
     */
    private function getMigrationClassName($migrationName) {
        // Remover timestamp del nombre
        $parts = explode('_', $migrationName);
        if (count($parts) >= 4) {
            // Remover las primeras 4 partes (año_mes_día_hora)
            $nameParts = array_slice($parts, 4);
            $name = implode('_', $nameParts);
        } else {
            $name = $migrationName;
        }
        
        return $this->studlyCase($name);
    }
    
    /**
     * Convertir a StudlyCase
     */
    private function studlyCase($value) {
        $value = str_replace(['-', '_'], ' ', $value);
        return str_replace(' ', '', ucwords($value));
    }
    
    /**
     * Obtener template de migración
     */
    private function getMigrationTemplate($className, $table = null) {
        $tableOperations = '';
        
        if ($table) {
            $tableOperations = "
        // Crear tabla {$table}
        \$sql = \"CREATE TABLE IF NOT EXISTS {$table} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )\";
        \$this->db->exec(\$sql);
        
        // Crear índices si es necesario
        // \$this->db->exec(\"CREATE INDEX IF NOT EXISTS idx_{$table}_created_at ON {$table}(created_at)\");
";
        }
        
        return "<?php
/**
 * Migración: {$className}
 * Creada el: " . date('Y-m-d H:i:s') . "
 */

class {$className} {
    private \$db;
    
    public function __construct(\$db) {
        \$this->db = \$db;
    }
    
    /**
     * Ejecutar la migración
     */
    public function up() {
        try {
            \$this->db->beginTransaction();
            {$tableOperations}
            \$this->db->commit();
        } catch (Exception \$e) {
            \$this->db->rollBack();
            throw \$e;
        }
    }
    
    /**
     * Revertir la migración
     */
    public function down() {
        try {
            \$this->db->beginTransaction();
            
            // Revertir cambios aquí
            " . ($table ? "// \$this->db->exec(\"DROP TABLE IF EXISTS {$table}\");" : "// Código para revertir cambios") . "
            
            \$this->db->commit();
        } catch (Exception \$e) {
            \$this->db->rollBack();
            throw \$e;
        }
    }
}
?>";
    }
}
?>