<?php
/**
 * Migración: AddBankAccountIdToTables
 * Creada el: 2025-07-26 21:03:25
 */

class AddBankAccountIdToTables {
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
            
            // Verificar si las columnas ya existen antes de agregarlas
            
            // Agregar bank_account_id a transactions si no existe
            $columns = $this->db->query("PRAGMA table_info(transactions)")->fetchAll();
            $hasColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'bank_account_id') {
                    $hasColumn = true;
                    break;
                }
            }
            if (!$hasColumn) {
                $this->db->exec("ALTER TABLE transactions ADD COLUMN bank_account_id INTEGER");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_transactions_bank_account_id ON transactions(bank_account_id)");
            }
            
            // Agregar bank_account_id a accounts_payable si no existe
            $columns = $this->db->query("PRAGMA table_info(accounts_payable)")->fetchAll();
            $hasColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'bank_account_id') {
                    $hasColumn = true;
                    break;
                }
            }
            if (!$hasColumn) {
                $this->db->exec("ALTER TABLE accounts_payable ADD COLUMN bank_account_id INTEGER");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_accounts_payable_bank_account_id ON accounts_payable(bank_account_id)");
            }
            
            // Agregar bank_account_id a payments si no existe
            $columns = $this->db->query("PRAGMA table_info(payments)")->fetchAll();
            $hasColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'bank_account_id') {
                    $hasColumn = true;
                    break;
                }
            }
            if (!$hasColumn) {
                $this->db->exec("ALTER TABLE payments ADD COLUMN bank_account_id INTEGER");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_payments_bank_account_id ON payments(bank_account_id)");
            }
            
            // Agregar bank_account_id a receipts si no existe
            $columns = $this->db->query("PRAGMA table_info(receipts)")->fetchAll();
            $hasColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'bank_account_id') {
                    $hasColumn = true;
                    break;
                }
            }
            if (!$hasColumn) {
                $this->db->exec("ALTER TABLE receipts ADD COLUMN bank_account_id INTEGER");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_receipts_bank_account_id ON receipts(bank_account_id)");
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
            // En un entorno de producción, esto requeriría recrear las tablas
            // Por ahora, solo eliminamos los índices
            
            $this->db->exec("DROP INDEX IF EXISTS idx_transactions_bank_account_id");
            $this->db->exec("DROP INDEX IF EXISTS idx_accounts_payable_bank_account_id");
            $this->db->exec("DROP INDEX IF EXISTS idx_payments_bank_account_id");
            $this->db->exec("DROP INDEX IF EXISTS idx_receipts_bank_account_id");
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
?>