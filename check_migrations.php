<?php
try {
    $dbPath = __DIR__ . '/data/money_manager.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Verificando migraciones ejecutadas ===\n";
    
    // Verificar si existe la tabla migrations
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'");
    if ($result->fetch()) {
        echo "Tabla migrations existe\n";
        
        // Buscar migraciones relacionadas con bank_account_id
        $stmt = $db->query("SELECT * FROM migrations WHERE migration LIKE '%add_bank_account_id%' ORDER BY executed_at DESC");
        $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($migrations)) {
            echo "No se encontró la migración add_bank_account_id_to_tables\n";
        } else {
            echo "Migraciones encontradas:\n";
            foreach ($migrations as $migration) {
                echo "- {$migration['migration']} (ejecutada: {$migration['executed_at']})\n";
            }
        }
        
        // Verificar estructura de la tabla transactions
        echo "\n=== Estructura de tabla transactions ===\n";
        $result = $db->query("PRAGMA table_info(transactions)");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['name']} ({$column['type']})\n";
        }
        
        // Verificar estructura de la tabla accounts_payable
        echo "\n=== Estructura de tabla accounts_payable ===\n";
        $result = $db->query("PRAGMA table_info(accounts_payable)");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['name']} ({$column['type']})\n";
        }
        
    } else {
        echo "La tabla migrations no existe\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>