<?php
/**
 * Script de Backup Automatizado
 * Money Manager v2.0
 * 
 * Este script crea backups automáticos de la base de datos y archivos importantes
 */

// Verificar que se ejecute desde línea de comandos o con permisos
if (php_sapi_name() !== 'cli' && !isset($_GET['admin_backup'])) {
    die('Este script solo puede ejecutarse desde línea de comandos o con permisos de administrador.');
}

// Configuración
$baseDir = __DIR__;
$backupDir = $baseDir . '/backups';
$dataDir = $baseDir . '/data';
$uploadsDir = $baseDir . '/uploads';
$configDir = $baseDir . '/config';

// Crear directorio de backups si no existe
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Función para crear backup
function createBackup($type = 'manual') {
    global $baseDir, $backupDir, $dataDir, $uploadsDir, $configDir;
    
    $timestamp = date('Y-m-d_H-i-s');
    $backupName = "money_manager_backup_{$type}_{$timestamp}";
    $backupPath = $backupDir . '/' . $backupName;
    
    echo "\n=== INICIANDO BACKUP ===\n";
    echo "Tipo: $type\n";
    echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
    echo "Destino: $backupPath\n\n";
    
    // Crear directorio del backup
    if (!mkdir($backupPath, 0755, true)) {
        echo "❌ Error creando directorio de backup\n";
        return false;
    }
    
    $success = true;
    
    // 1. Backup de base de datos
    echo "1. Respaldando base de datos...\n";
    $dbFile = $dataDir . '/money_manager.db';
    if (file_exists($dbFile)) {
        if (copy($dbFile, $backupPath . '/money_manager.db')) {
            echo "✅ Base de datos respaldada\n";
        } else {
            echo "❌ Error respaldando base de datos\n";
            $success = false;
        }
    } else {
        echo "⚠️  Base de datos no encontrada\n";
    }
    
    // 2. Backup de archivos subidos
    echo "\n2. Respaldando archivos subidos...\n";
    if (is_dir($uploadsDir)) {
        $uploadsBackup = $backupPath . '/uploads';
        if (copyDirectory($uploadsDir, $uploadsBackup)) {
            echo "✅ Archivos subidos respaldados\n";
        } else {
            echo "❌ Error respaldando archivos subidos\n";
            $success = false;
        }
    } else {
        echo "ℹ️  Directorio uploads no encontrado\n";
    }
    
    // 3. Backup de configuración
    echo "\n3. Respaldando configuración...\n";
    $configBackup = $backupPath . '/config';
    if (copyDirectory($configDir, $configBackup)) {
        echo "✅ Configuración respaldada\n";
    } else {
        echo "❌ Error respaldando configuración\n";
        $success = false;
    }
    
    // 4. Backup de archivos importantes
    echo "\n4. Respaldando archivos importantes...\n";
    $importantFiles = [
        '.env',
        '.env.local',
        '.env.production',
        '.htaccess',
        'README.md',
        'README_PRODUCTION.md'
    ];
    
    foreach ($importantFiles as $file) {
        $sourcePath = $baseDir . '/' . $file;
        if (file_exists($sourcePath)) {
            if (copy($sourcePath, $backupPath . '/' . $file)) {
                echo "✅ $file respaldado\n";
            } else {
                echo "❌ Error respaldando $file\n";
                $success = false;
            }
        }
    }
    
    // 5. Crear archivo de información del backup
    echo "\n5. Creando información del backup...\n";
    $backupInfo = [
        'backup_date' => date('Y-m-d H:i:s'),
        'backup_type' => $type,
        'php_version' => PHP_VERSION,
        'app_version' => '2.0',
        'files_included' => [
            'database' => file_exists($backupPath . '/money_manager.db'),
            'uploads' => is_dir($backupPath . '/uploads'),
            'config' => is_dir($backupPath . '/config'),
            'important_files' => count(glob($backupPath . '/*.*'))
        ]
    ];
    
    file_put_contents($backupPath . '/backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT));
    echo "✅ Información del backup creada\n";
    
    // 6. Comprimir backup (si está disponible)
    echo "\n6. Comprimiendo backup...\n";
    if (class_exists('ZipArchive')) {
        $zipFile = $backupDir . '/' . $backupName . '.zip';
        if (createZipBackup($backupPath, $zipFile)) {
            echo "✅ Backup comprimido: " . basename($zipFile) . "\n";
            // Eliminar directorio sin comprimir
            removeDirectory($backupPath);
        } else {
            echo "❌ Error comprimiendo backup\n";
        }
    } else {
        echo "ℹ️  ZipArchive no disponible, backup sin comprimir\n";
    }
    
    echo "\n=== BACKUP COMPLETADO ===\n";
    echo "Estado: " . ($success ? "EXITOSO" : "CON ERRORES") . "\n";
    echo "Ubicación: $backupPath\n";
    
    return $success;
}

// Función para copiar directorio recursivamente
function copyDirectory($source, $destination) {
    if (!is_dir($source)) {
        return false;
    }
    
    if (!mkdir($destination, 0755, true)) {
        return false;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $subPath = $iterator->getSubPathName();
        $target = rtrim($destination, '/\\') . '/' . ltrim($subPath, '/\\');
        
        if ($item->isDir()) {
            if (!mkdir($target, 0755, true)) {
                return false;
            }
        } else {
            if (!copy($item, $target)) {
                return false;
            }
        }
    }
    
    return true;
}

// Función para crear ZIP
function createZipBackup($source, $zipFile) {
    $zip = new ZipArchive();
    
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        return false;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($source) + 1);
        
        if ($file->isDir()) {
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    return $zip->close();
}

// Función para eliminar directorio
function removeDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    
    return rmdir($dir);
}

// Función para limpiar backups antiguos
function cleanOldBackups($retentionDays = 30) {
    global $backupDir;
    
    echo "\n=== LIMPIANDO BACKUPS ANTIGUOS ===\n";
    echo "Retención: $retentionDays días\n";
    
    $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
    $backups = glob($backupDir . '/money_manager_backup_*');
    $deleted = 0;
    
    foreach ($backups as $backup) {
        if (filemtime($backup) < $cutoffTime) {
            if (is_file($backup)) {
                unlink($backup);
                $deleted++;
                echo "🗑️  Eliminado: " . basename($backup) . "\n";
            } elseif (is_dir($backup)) {
                removeDirectory($backup);
                $deleted++;
                echo "🗑️  Eliminado: " . basename($backup) . "\n";
            }
        }
    }
    
    echo "Total eliminados: $deleted\n";
}

// Función principal
function main() {
    $type = isset($argv[1]) ? $argv[1] : 'manual';
    $clean = isset($argv[2]) && $argv[2] === 'clean';
    
    // Crear backup
    createBackup($type);
    
    // Limpiar backups antiguos si se solicita
    if ($clean) {
        cleanOldBackups();
    }
    
    echo "\n📋 COMANDOS ÚTILES:\n";
    echo "Backup manual: php backup.php manual\n";
    echo "Backup automático: php backup.php auto\n";
    echo "Backup con limpieza: php backup.php auto clean\n";
    echo "\n💡 CONFIGURAR CRON (Linux/Mac):\n";
    echo "# Backup diario a las 2:00 AM\n";
    echo "0 2 * * * /usr/bin/php /path/to/money-manager/backup.php auto clean\n";
    echo "\n💡 CONFIGURAR TAREA PROGRAMADA (Windows):\n";
    echo "schtasks /create /tn \"Money Manager Backup\" /tr \"C:\\xampp\\php\\php.exe C:\\path\\to\\backup.php auto clean\" /sc daily /st 02:00\n";
}

// Ejecutar si se llama directamente
if (php_sapi_name() === 'cli') {
    main();
} elseif (isset($_GET['admin_backup'])) {
    createBackup('web');
}

?>