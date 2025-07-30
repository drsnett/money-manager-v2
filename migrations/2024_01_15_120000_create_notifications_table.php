<?php
/**
 * Migración: CreateNotificationsTable
 * Creada el: 2024-01-15 12:00:00
 */

class CreateNotificationsTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ejecutar la migración
     */
    public function up() {
        try {
            $this->db->beginTransaction();
            
            // Crear tabla de notificaciones
            $sql = "CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                data TEXT,
                is_read BOOLEAN DEFAULT 0,
                priority VARCHAR(20) DEFAULT 'normal',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->db->exec($sql);
            
            // Crear índices para mejor rendimiento
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_type ON notifications(type)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_priority ON notifications(priority)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notifications_expires_at ON notifications(expires_at)");
            
            // Crear tabla de configuraciones de usuario
            $sql = "CREATE TABLE IF NOT EXISTS user_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                setting_name VARCHAR(100) NOT NULL,
                setting_value TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(user_id, setting_name)
            )";
            $this->db->exec($sql);
            
            // Crear índices para configuraciones de usuario
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_settings_user_id ON user_settings(user_id)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_settings_name ON user_settings(setting_name)");
            
            // Crear tabla de logs del sistema
            $sql = "CREATE TABLE IF NOT EXISTS system_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                level VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                context TEXT,
                user_id INTEGER,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->db->exec($sql);
            
            // Crear índices para logs
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_system_logs_level ON system_logs(level)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_system_logs_created_at ON system_logs(created_at)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_system_logs_user_id ON system_logs(user_id)");
            
            // Crear tabla de sesiones (para mejor gestión de sesiones)
            $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
                id VARCHAR(128) PRIMARY KEY,
                user_id INTEGER,
                ip_address VARCHAR(45),
                user_agent TEXT,
                payload TEXT,
                last_activity INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->db->exec($sql);
            
            // Crear índices para sesiones
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_sessions_last_activity ON user_sessions(last_activity)");
            
            // Agregar columnas adicionales a la tabla de usuarios si no existen
            $this->addColumnIfNotExists('users', 'email_verified_at', 'DATETIME');
            $this->addColumnIfNotExists('users', 'two_factor_secret', 'VARCHAR(255)');
            $this->addColumnIfNotExists('users', 'two_factor_recovery_codes', 'TEXT');
            $this->addColumnIfNotExists('users', 'remember_token', 'VARCHAR(100)');
            $this->addColumnIfNotExists('users', 'last_login_at', 'DATETIME');
            $this->addColumnIfNotExists('users', 'last_login_ip', 'VARCHAR(45)');
            $this->addColumnIfNotExists('users', 'login_attempts', 'INTEGER DEFAULT 0');
            $this->addColumnIfNotExists('users', 'locked_until', 'DATETIME');
            
            // Agregar configuraciones por defecto
            $this->insertDefaultSettings();
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Revertir la migración
     */
    public function down() {
        try {
            $this->db->beginTransaction();
            
            // Eliminar tablas en orden inverso
            $this->db->exec("DROP TABLE IF EXISTS user_sessions");
            $this->db->exec("DROP TABLE IF EXISTS system_logs");
            $this->db->exec("DROP TABLE IF EXISTS user_settings");
            $this->db->exec("DROP TABLE IF EXISTS notifications");
            
            // Eliminar columnas agregadas a users (SQLite no soporta DROP COLUMN directamente)
            // En un entorno de producción, esto requeriría recrear la tabla
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Agregar columna si no existe
     */
    private function addColumnIfNotExists($table, $column, $type) {
        try {
            // Verificar si la columna ya existe
            $sql = "PRAGMA table_info({$table})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $columnExists = false;
            foreach ($columns as $col) {
                if ($col['name'] === $column) {
                    $columnExists = true;
                    break;
                }
            }
            
            if (!$columnExists) {
                $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$type}";
                $this->db->exec($sql);
            }
        } catch (Exception $e) {
            // Ignorar errores si la columna ya existe
        }
    }
    
    /**
     * Insertar configuraciones por defecto
     */
    private function insertDefaultSettings() {
        // Obtener todos los usuarios existentes
        $sql = "SELECT id FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Configuraciones por defecto
        $defaultSettings = [
            'notifications_enabled' => 'true',
            'email_notifications' => 'true',
            'notification_frequency' => 'daily',
            'currency_format' => 'USD',
            'date_format' => 'Y-m-d',
            'timezone' => 'America/Mexico_City',
            'dashboard_widgets' => json_encode([
                'balance_summary' => true,
                'recent_transactions' => true,
                'upcoming_payments' => true,
                'spending_chart' => true
            ])
        ];
        
        foreach ($users as $userId) {
            foreach ($defaultSettings as $setting => $value) {
                $sql = "INSERT OR IGNORE INTO user_settings (user_id, setting_name, setting_value) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId, $setting, $value]);
            }
        }
    }
}
?>