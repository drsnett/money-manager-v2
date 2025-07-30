<?php
/**
 * Migración para agregar campo card_color a las tarjetas de crédito
 * Permite personalizar el color de visualización de cada tarjeta
 * Fecha: 2025-01-16 12:00:00
 */

class AddCardColorToCreditCards {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function up() {
        try {
            // Verificar si la columna ya existe
            $columns = $this->db->query("PRAGMA table_info(credit_cards)")->fetchAll();
            $hasColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'card_color') {
                    $hasColumn = true;
                    break;
                }
            }
            
            if (!$hasColumn) {
                // Agregar campo card_color a la tabla credit_cards
                $this->db->exec("
                    ALTER TABLE credit_cards 
                    ADD COLUMN card_color VARCHAR(7) DEFAULT '#007bff'
                ");
                
                echo "✅ Campo 'card_color' agregado a la tabla credit_cards\n";
                
                // Asignar colores por defecto a tarjetas existentes
                $defaultColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#6f42c1', '#fd7e14'];
                $stmt = $this->db->prepare("UPDATE credit_cards SET card_color = ? WHERE id = ?");
                
                $cards = $this->db->query("SELECT id FROM credit_cards WHERE card_color IS NULL OR card_color = ''")->fetchAll();
                foreach ($cards as $index => $card) {
                    $color = $defaultColors[$index % count($defaultColors)];
                    $stmt->execute([$color, $card['id']]);
                }
                
                echo "✅ Colores asignados a " . count($cards) . " tarjetas existentes\n";
                
            } else {
                echo "ℹ️ El campo 'card_color' ya existe en la tabla credit_cards\n";
            }
            
        } catch (PDOException $e) {
            throw new Exception('Error al agregar campo card_color: ' . $e->getMessage());
        }
    }
    
    public function down() {
        try {
            // SQLite no soporta DROP COLUMN directamente
            // Crear tabla temporal sin el campo card_color
            $this->db->exec("
                CREATE TABLE credit_cards_temp AS 
                SELECT id, user_id, card_name, card_number, credit_limit, 
                       current_balance, cut_off_date, payment_due_date, 
                       minimum_payment_percentage, currency, status, created_at
                FROM credit_cards
            ");
            
            // Eliminar tabla original
            $this->db->exec("DROP TABLE credit_cards");
            
            // Renombrar tabla temporal
            $this->db->exec("ALTER TABLE credit_cards_temp RENAME TO credit_cards");
            
            echo "✅ Campo 'card_color' removido de la tabla credit_cards\n";
            
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
        
        $migration = new AddCardColorToCreditCards($db);
        $migration->up();
        
        echo "\n🎉 Migración completada exitosamente\n";
        
    } catch (Exception $e) {
        echo "❌ Error en la migración: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>