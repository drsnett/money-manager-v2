<?php
/**
 * Migración para agregar campo de estado a las tarjetas de crédito
 * Permite manejar estados como 'active', 'overdue', 'suspended'
 */

class AddStatusToCreditCards {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function up() {
        try {
            // Agregar campo status a la tabla credit_cards
            $this->db->exec("
                ALTER TABLE credit_cards 
                ADD COLUMN status VARCHAR(20) DEFAULT 'active' 
                CHECK (status IN ('active', 'overdue', 'suspended'))
            ");
            
            echo "✅ Campo 'status' agregado a la tabla credit_cards\n";
            
            // Actualizar todas las tarjetas existentes con estado 'active'
            $this->db->exec("UPDATE credit_cards SET status = 'active' WHERE status IS NULL");
            
            echo "✅ Estados inicializados para tarjetas existentes\n";
            
        } catch (PDOException $e) {
            // Si la columna ya existe, no es un error
            if (strpos($e->getMessage(), 'duplicate column name') !== false) {
                echo "ℹ️ El campo 'status' ya existe en la tabla credit_cards\n";
            } else {
                throw $e;
            }
        }
    }
    
    public function down() {
        try {
            // SQLite no soporta DROP COLUMN directamente
            // Crear tabla temporal sin el campo status
            $this->db->exec("
                CREATE TABLE credit_cards_temp AS 
                SELECT id, user_id, card_name, card_number, credit_limit, 
                       current_balance, cut_off_date, payment_due_date, 
                       minimum_payment_percentage, currency, created_at
                FROM credit_cards
            ");
            
            // Eliminar tabla original
            $this->db->exec("DROP TABLE credit_cards");
            
            // Renombrar tabla temporal
            $this->db->exec("ALTER TABLE credit_cards_temp RENAME TO credit_cards");
            
            echo "✅ Campo 'status' removido de la tabla credit_cards\n";
            
        } catch (PDOException $e) {
            throw new Exception('Error al revertir migración: ' . $e->getMessage());
        }
    }
}

// Ejecutar migración si se llama directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    require_once __DIR__ . '/../config/database.php';
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $migration = new AddStatusToCreditCards($db);
        $migration->up();
        
        echo "\n🎉 Migración completada exitosamente\n";
        
    } catch (Exception $e) {
        echo "❌ Error en la migración: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>