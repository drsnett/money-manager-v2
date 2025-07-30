<?php
/**
 * Script para limpiar notificaciones duplicadas
 * Ejecutar una sola vez para eliminar duplicados existentes
 */

// Configurar el entorno
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/cleanup.log');

// Incluir archivos necesarios
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/env_config.php';

/**
 * Escribir en el log
 */
function writeLog($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    // Escribir en archivo de log
    file_put_contents(__DIR__ . '/../logs/cleanup.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    // También mostrar en consola si se ejecuta desde línea de comandos
    if (php_sapi_name() === 'cli') {
        echo $logMessage;
    }
}

/**
 * Limpiar notificaciones duplicadas
 */
function cleanDuplicateNotifications() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        writeLog('Iniciando limpieza de notificaciones duplicadas');
        
        // Encontrar y eliminar duplicados basados en user_id, type, y data
        $sql = "DELETE FROM notifications 
                WHERE id NOT IN (
                    SELECT MIN(id) 
                    FROM notifications 
                    GROUP BY user_id, type, data, DATE(created_at)
                )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $deletedCount = $stmt->rowCount();
        
        writeLog("Se eliminaron {$deletedCount} notificaciones duplicadas");
        
        // Limpiar notificaciones muy antiguas (más de 30 días)
        $sql = "DELETE FROM notifications 
                WHERE created_at < datetime('now', '-30 days')
                AND is_read = 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $oldCount = $stmt->rowCount();
        
        writeLog("Se eliminaron {$oldCount} notificaciones antiguas leídas");
        
        // Optimizar la base de datos
        $db->exec('VACUUM');
        writeLog('Base de datos optimizada');
        
        writeLog('Limpieza completada exitosamente');
        
    } catch (Exception $e) {
        writeLog('Error en la limpieza: ' . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

// Ejecutar el script
try {
    // Verificar que el script no se esté ejecutando ya
    $lockFile = __DIR__ . '/../data/cleanup.lock';
    
    if (file_exists($lockFile)) {
        $lockTime = filemtime($lockFile);
        if (time() - $lockTime < 1800) { // 30 minutos
            writeLog('El script ya se está ejecutando. Saliendo...', 'WARNING');
            exit(1);
        } else {
            // Lock file muy antiguo, eliminarlo
            unlink($lockFile);
        }
    }
    
    // Crear lock file
    touch($lockFile);
    
    // Ejecutar limpieza
    cleanDuplicateNotifications();
    
    // Eliminar lock file
    unlink($lockFile);
    
    writeLog('Script ejecutado exitosamente');
    exit(0);
    
} catch (Exception $e) {
    writeLog('Error fatal: ' . $e->getMessage(), 'ERROR');
    
    // Eliminar lock file en caso de error
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
    
    exit(1);
}
?>