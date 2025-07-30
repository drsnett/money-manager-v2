<?php
/**
 * Migración: AddColorToCreditCards
 * Creada el: 2025-07-27 03:00:00
 */

class AddColorToCreditCards {
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
            
            // Verificar si la columna ya existe antes de agregarla
            $columns = $this->db->query("PRAGMA table_info(credit_cards)")->fetchAll();
            $hasColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'card_color') {
                    $hasColumn = true;
                    break;
                }
            }
            
            if (!$hasColumn) {
                $this->db->exec("ALTER TABLE credit_cards ADD COLUMN card_color VARCHAR(7) DEFAULT '#007bff'");
                echo "Columna card_color agregada a la tabla credit_cards\n";
            } else {
                echo "La columna card_color ya existe en la tabla credit_cards\n";
            }
            
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
            
            // SQLite no soporta DROP COLUMN directamente
            // Tendríamos que recrear la tabla sin la columna
            echo "Nota: SQLite no soporta DROP COLUMN. Para revertir esta migración,\n";
            echo "sería necesario recrear la tabla sin la columna card_color.\n";
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
?>