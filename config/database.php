<?php
require_once __DIR__ . '/DatabaseException.php';

class Database {
    private $db;
    
    public function __construct() {
        $this->connect();
        $this->createTables();
    }
    
    private function connect() {
        try {
            $dbPath = __DIR__ . '/../data/money_manager.db';
            $this->db = new PDO('sqlite:' . $dbPath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configuraciones optimizadas para evitar bloqueos
            $this->db->exec('PRAGMA journal_mode=WAL');
            $this->db->exec('PRAGMA synchronous=NORMAL');
            $this->db->exec('PRAGMA cache_size=10000');
            $this->db->exec('PRAGMA temp_store=MEMORY');
            $this->db->exec('PRAGMA busy_timeout=30000');
            $this->db->exec('PRAGMA foreign_keys=ON');
            
        } catch (PDOException $e) {
            throw new DatabaseException('Error de conexión: ' . $e->getMessage(), 0, $e);
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    private function createTables() {
        $queries = [
            // Tabla de usuarios
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                is_admin BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Tabla de categorías
            "CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name VARCHAR(100) NOT NULL,
                type VARCHAR(20) NOT NULL CHECK (type IN ('income', 'expense')),
                icon VARCHAR(50) DEFAULT 'other',
                color VARCHAR(7) DEFAULT '#007bff',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            // Tabla de transacciones
            "CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                category_id INTEGER NOT NULL,
                type VARCHAR(20) NOT NULL CHECK (type IN ('income', 'expense')),
                amount DECIMAL(10,2) NOT NULL,
                description TEXT,
                payment_method VARCHAR(50) DEFAULT 'cash',
                transaction_date DATE NOT NULL,
                bank_account_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
                FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL
            )",
            
            // Tabla de cuentas por pagar
            "CREATE TABLE IF NOT EXISTS accounts_payable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                creditor_name VARCHAR(100) NOT NULL,
                description TEXT,
                total_amount DECIMAL(10,2) NOT NULL,
                paid_amount DECIMAL(10,2) DEFAULT 0,
                due_date DATE NOT NULL,
                status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'paid', 'overdue', 'partial')),
                is_recurring BOOLEAN DEFAULT 0,
                recurring_type VARCHAR(20) CHECK (recurring_type IN ('monthly', 'biweekly', 'weekly')),
                bank_account_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL
            )",
            
            // Tabla de pagos realizados
            "CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                account_payable_id INTEGER NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_date DATE NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'cash',
                notes TEXT,
                bank_account_id INTEGER,
                credit_card_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (account_payable_id) REFERENCES accounts_payable(id) ON DELETE CASCADE,
                FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL,
                FOREIGN KEY (credit_card_id) REFERENCES credit_cards(id) ON DELETE SET NULL
            )",
            
            // Tabla de cuentas por cobrar
            "CREATE TABLE IF NOT EXISTS accounts_receivable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                debtor_name VARCHAR(100) NOT NULL,
                description TEXT,
                total_amount DECIMAL(10,2) NOT NULL,
                received_amount DECIMAL(10,2) DEFAULT 0,
                due_date DATE NOT NULL,
                status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'paid', 'overdue', 'partial')),
                is_recurring BOOLEAN DEFAULT 0,
                recurring_type VARCHAR(20) CHECK (recurring_type IN ('monthly', 'biweekly', 'weekly')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            // Tabla de cobros recibidos
            "CREATE TABLE IF NOT EXISTS receipts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                account_receivable_id INTEGER NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_date DATE NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'cash',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (account_receivable_id) REFERENCES accounts_receivable(id) ON DELETE CASCADE
            )",
            
            // Tabla de tarjetas de crédito
            "CREATE TABLE IF NOT EXISTS credit_cards (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                card_name VARCHAR(100) NOT NULL,
                card_number VARCHAR(20) NOT NULL,
                credit_limit DECIMAL(10,2) NOT NULL,
                current_balance DECIMAL(10,2) DEFAULT 0,
                cut_off_date INTEGER NOT NULL CHECK (cut_off_date >= 1 AND cut_off_date <= 31),
                payment_due_date INTEGER NOT NULL CHECK (payment_due_date >= 1 AND payment_due_date <= 31),
                minimum_payment_percentage DECIMAL(5,2) DEFAULT 5.00,
                currency VARCHAR(3) DEFAULT 'USD',
                status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'overdue', 'suspended')),
                card_color VARCHAR(7) DEFAULT '#007bff',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            // Tabla de movimientos de tarjeta de crédito
            "CREATE TABLE IF NOT EXISTS credit_card_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                credit_card_id INTEGER NOT NULL,
                type VARCHAR(20) NOT NULL CHECK (type IN ('charge', 'payment')),
                amount DECIMAL(10,2) NOT NULL,
                description TEXT,
                transaction_date DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (credit_card_id) REFERENCES credit_cards(id) ON DELETE CASCADE
            )",
            
            // Tabla de deudas con interés
            "CREATE TABLE IF NOT EXISTS debts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                creditor_name VARCHAR(100) NOT NULL,
                description TEXT,
                principal_amount DECIMAL(10,2) NOT NULL,
                current_balance DECIMAL(10,2) NOT NULL,
                monthly_interest_rate DECIMAL(5,2) NOT NULL,
                start_date DATE NOT NULL,
                due_date DATE,
                status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'paid', 'suspended')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            // Tabla de pagos de deudas
            "CREATE TABLE IF NOT EXISTS debt_payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                debt_id INTEGER NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_date DATE NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'cash',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (debt_id) REFERENCES debts(id) ON DELETE CASCADE
            )",
            
            // Tabla de cuentas bancarias
            "CREATE TABLE IF NOT EXISTS bank_accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                bank_name VARCHAR(100) NOT NULL,
                account_name VARCHAR(100) NOT NULL,
                account_number VARCHAR(50) NOT NULL,
                account_type VARCHAR(30) NOT NULL CHECK (account_type IN ('checking', 'savings', 'credit', 'investment')),
                current_balance DECIMAL(10,2) DEFAULT 0,
                currency VARCHAR(3) DEFAULT 'USD',
                is_active BOOLEAN DEFAULT 1,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )"
        ];
        
        foreach ($queries as $query) {
            try {
                $this->db->exec($query);
            } catch (PDOException $e) {
                throw new DatabaseException('Error creando tablas: ' . $e->getMessage(), 0, $e);
            }
        }
        
        // Las categorías se crearán durante el proceso de instalación
    }
    
    private function insertDefaultCategories() {
        // No insertar categorías automáticamente
        // Las categorías se crearán cuando se creen los usuarios por defecto
        // mediante el script de instalación install_updated.php o install_v2.php
    }
    

    

    

    
    /**
     * Obtener información de la estructura de la base de datos
     */
    public function getDatabaseInfo() {
        $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        $info = [];
        
        foreach($tables as $table) {
            $schema = $this->db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
            $count = $this->db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            $info[$table] = [
                'columns' => $schema,
                'records' => $count
            ];
        }
        
        return $info;
    }
    
    /**
     * Verificar integridad de la base de datos
     */
    public function checkIntegrity() {
        try {
            $result = $this->db->query("PRAGMA integrity_check")->fetchColumn();
            return $result === 'ok';
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
