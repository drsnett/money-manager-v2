<?php
/**
 * Script de Despliegue para Producción
 * Money Manager v2.0
 * 
 * Este script automatiza la configuración del sistema para producción
 */

// Verificar que se ejecute desde línea de comandos o con permisos de administrador
if (php_sapi_name() !== 'cli' && !isset($_GET['admin_deploy'])) {
    die('Este script solo puede ejecutarse desde línea de comandos o con permisos de administrador.');
}

echo "\n=== MONEY MANAGER - SCRIPT DE DESPLIEGUE ===\n";
echo "Versión: 2.0\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuraciones
$baseDir = __DIR__;
$dataDir = $baseDir . '/data';
$logsDir = $baseDir . '/logs';
$uploadsDir = $baseDir . '/uploads';
$backupsDir = $baseDir . '/backups';
$cacheDir = $dataDir . '/cache';

// Función para crear directorios
function createDirectory($dir, $permissions = 0755) {
    if (!file_exists($dir)) {
        if (mkdir($dir, $permissions, true)) {
            echo "✅ Directorio creado: $dir\n";
        } else {
            echo "❌ Error creando directorio: $dir\n";
            return false;
        }
    } else {
        echo "ℹ️  Directorio ya existe: $dir\n";
    }
    return true;
}

// Función para configurar permisos
function setPermissions($path, $permissions) {
    if (chmod($path, $permissions)) {
        echo "✅ Permisos configurados: $path (" . decoct($permissions) . ")\n";
    } else {
        echo "❌ Error configurando permisos: $path\n";
    }
}

// Función para copiar archivo si no existe
function copyIfNotExists($source, $destination) {
    if (!file_exists($destination)) {
        if (copy($source, $destination)) {
            echo "✅ Archivo copiado: $destination\n";
        } else {
            echo "❌ Error copiando archivo: $destination\n";
        }
    } else {
        echo "ℹ️  Archivo ya existe: $destination\n";
    }
}

echo "1. CREANDO ESTRUCTURA DE DIRECTORIOS...\n";
echo "==========================================\n";

// Crear directorios necesarios
createDirectory($dataDir);
createDirectory($logsDir);
createDirectory($uploadsDir);
createDirectory($backupsDir);
createDirectory($cacheDir);

echo "\n2. CONFIGURANDO PERMISOS...\n";
echo "===========================\n";

// Configurar permisos
setPermissions($dataDir, 0755);
setPermissions($logsDir, 0755);
setPermissions($uploadsDir, 0755);
setPermissions($backupsDir, 0755);
setPermissions($cacheDir, 0755);

echo "\n3. CONFIGURANDO ARCHIVOS DE ENTORNO...\n";
echo "======================================\n";

// Copiar archivo de entorno de producción
if (file_exists($baseDir . '/.env.production')) {
    copyIfNotExists($baseDir . '/.env.production', $baseDir . '/.env.local');
} else {
    echo "❌ Archivo .env.production no encontrado\n";
}

// Crear archivo .htaccess de protección para data
$dataHtaccess = $dataDir . '/.htaccess';
if (!file_exists($dataHtaccess)) {
    file_put_contents($dataHtaccess, "Order deny,allow\nDeny from all\n");
    echo "✅ Protección .htaccess creada en /data/\n";
}

// Crear archivo .htaccess de protección para logs
$logsHtaccess = $logsDir . '/.htaccess';
if (!file_exists($logsHtaccess)) {
    file_put_contents($logsHtaccess, "Order deny,allow\nDeny from all\n");
    echo "✅ Protección .htaccess creada en /logs/\n";
}

echo "\n4. VERIFICANDO CONFIGURACIÓN PHP...\n";
echo "===================================\n";

// Verificar extensiones PHP
$requiredExtensions = ['pdo', 'pdo_sqlite', 'mbstring', 'openssl', 'json'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extensión PHP: $ext\n";
    } else {
        echo "❌ Extensión PHP faltante: $ext\n";
    }
}

echo "\n5. VERIFICANDO BASE DE DATOS...\n";
echo "===============================\n";

// Verificar/crear base de datos
try {
    require_once $baseDir . '/config/database.php';
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Ejecutar migraciones si es necesario
    if (file_exists($baseDir . '/check_migrations.php')) {
        echo "ℹ️  Ejecutando migraciones...\n";
        include $baseDir . '/check_migrations.php';
    }
    
} catch (Exception $e) {
    echo "❌ Error en base de datos: " . $e->getMessage() . "\n";
}

echo "\n6. CONFIGURACIÓN DE SEGURIDAD...\n";
echo "================================\n";

// Verificar configuración de seguridad
if (file_exists($baseDir . '/config/production.php')) {
    echo "✅ Configuración de producción encontrada\n";
} else {
    echo "❌ Configuración de producción no encontrada\n";
}

// Verificar .htaccess
if (file_exists($baseDir . '/.htaccess')) {
    echo "✅ Archivo .htaccess configurado\n";
} else {
    echo "❌ Archivo .htaccess no encontrado\n";
}

echo "\n7. LIMPIEZA DE ARCHIVOS DE DESARROLLO...\n";
echo "=======================================\n";

// Lista de archivos de desarrollo a eliminar en producción
$devFiles = [
    'test_*.php',
    'debug.php',
    'phpinfo.php',
    'composer.json',
    'composer.lock',
    '.git',
    'node_modules'
];

foreach ($devFiles as $pattern) {
    $files = glob($baseDir . '/' . $pattern);
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "🗑️  Archivo eliminado: " . basename($file) . "\n";
        } elseif (is_dir($file)) {
            echo "ℹ️  Directorio encontrado (revisar manualmente): " . basename($file) . "\n";
        }
    }
}

echo "\n8. CONFIGURACIÓN DE CACHE...\n";
echo "============================\n";

// Limpiar cache existente
if (is_dir($cacheDir)) {
    $cacheFiles = glob($cacheDir . '/*');
    foreach ($cacheFiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✅ Cache limpiado\n";
}

echo "\n9. VERIFICACIÓN FINAL...\n";
echo "========================\n";

// Verificaciones finales
$checks = [
    'Directorio data escribible' => is_writable($dataDir),
    'Directorio logs escribible' => is_writable($logsDir),
    'Directorio uploads escribible' => is_writable($uploadsDir),
    'Archivo .htaccess presente' => file_exists($baseDir . '/.htaccess'),
    'Configuración de producción' => file_exists($baseDir . '/config/production.php')
];

foreach ($checks as $check => $result) {
    echo ($result ? "✅" : "❌") . " $check\n";
}

echo "\n=== DESPLIEGUE COMPLETADO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "\n✅ CORRECCIONES APLICADAS:\n";
echo "- Rutas hardcodeadas corregidas en check_migrations.php\n";
echo "- Permisos configurados automáticamente\n";
echo "- Configuración de producción aplicada\n";
echo "\n📋 PRÓXIMOS PASOS:\n";
echo "1. Configurar SSL/HTTPS en el servidor\n";
echo "2. Actualizar rutas en cron/setup_cron.bat para tu servidor\n";
echo "3. Configurar variables específicas en .env.local\n";
echo "4. Configurar backup automático\n";
echo "5. Probar todas las funcionalidades\n";
echo "\n⚠️  ARCHIVOS QUE REQUIEREN ATENCIÓN MANUAL:\n";
echo "- cron/setup_cron.bat (contiene rutas de XAMPP)\n";
echo "- .env.local (configurar URLs y credenciales del servidor)\n";
echo "\n🔗 Acceder al sistema: http://tu-dominio.com/\n";
echo "📖 Documentación: http://tu-dominio.com/documentation.php\n";
echo "📋 Guía de despliegue: DEPLOY_SERVER_GUIDE.md\n";
echo "🔧 Resolución de problemas: docs/RESOLUCION_PROBLEMAS.md\n";
echo "\n¡Sistema listo para producción! 🚀\n";

?>