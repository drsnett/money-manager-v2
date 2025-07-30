<?php
/**
 * Ejemplos prácticos de uso del sistema de caché
 * Demuestra cómo integrar el caché en una aplicación real
 */

require_once '../classes/Cache.php';
require_once '../config/database.php';

// Función para simular una consulta costosa a la base de datos
function getExpensiveUserData($userId) {
    // Simular una consulta que toma tiempo
    sleep(1); // Simula 1 segundo de procesamiento
    
    return [
        'id' => $userId,
        'name' => 'Usuario ' . $userId,
        'email' => 'user' . $userId . '@example.com',
        'last_login' => date('Y-m-d H:i:s'),
        'preferences' => [
            'theme' => 'dark',
            'language' => 'es',
            'notifications' => true
        ]
    ];
}

// Función para obtener estadísticas del sistema
function getSystemStats() {
    sleep(2); // Simula una consulta compleja
    
    return [
        'total_users' => rand(1000, 5000),
        'active_sessions' => rand(50, 200),
        'total_debts' => rand(10000, 50000),
        'pending_payments' => rand(100, 1000),
        'generated_at' => date('Y-m-d H:i:s')
    ];
}

echo "<h1>Ejemplos de Uso del Sistema de Caché</h1>";

// Ejemplo 1: Caché de datos de usuario
echo "<h2>Ejemplo 1: Datos de Usuario con Caché</h2>";
$userId = 123;
$cacheKey = "user_data_{$userId}";

echo "<h3>Primera consulta (sin caché)</h3>";
$startTime = microtime(true);

$userData = cache_remember($cacheKey, function() use ($userId) {
    echo "<p style='color: orange;'>Ejecutando consulta costosa...</p>";
    return getExpensiveUserData($userId);
}, 300); // Caché por 5 minutos

$endTime = microtime(true);
echo "<p>Tiempo de ejecución: " . round(($endTime - $startTime) * 1000, 2) . " ms</p>";
echo "<pre>" . print_r($userData, true) . "</pre>";

echo "<h3>Segunda consulta (desde caché)</h3>";
$startTime = microtime(true);

$userData2 = cache_remember($cacheKey, function() use ($userId) {
    echo "<p style='color: orange;'>Esta función NO debería ejecutarse</p>";
    return getExpensiveUserData($userId);
}, 300);

$endTime = microtime(true);
echo "<p>Tiempo de ejecución: " . round(($endTime - $startTime) * 1000, 2) . " ms</p>";
echo "<pre>" . print_r($userData2, true) . "</pre>";

// Ejemplo 2: Caché de estadísticas del sistema
echo "<h2>Ejemplo 2: Estadísticas del Sistema</h2>";
$statsKey = 'system_stats';

echo "<h3>Obteniendo estadísticas (pueden estar en caché)</h3>";
$startTime = microtime(true);

$stats = cache_remember($statsKey, function() {
    echo "<p style='color: orange;'>Generando estadísticas del sistema...</p>";
    return getSystemStats();
}, 600); // Caché por 10 minutos

$endTime = microtime(true);
echo "<p>Tiempo de ejecución: " . round(($endTime - $startTime) * 1000, 2) . " ms</p>";
echo "<pre>" . print_r($stats, true) . "</pre>";

// Ejemplo 3: Contador de visitas
echo "<h2>Ejemplo 3: Contador de Visitas</h2>";
$cache = CacheManager::getInstance();
$visitKey = 'page_visits';

$visits = $cache->increment($visitKey);
echo "<p>Esta página ha sido visitada <strong>$visits</strong> veces.</p>";

// Ejemplo 4: Caché de configuración
echo "<h2>Ejemplo 4: Configuración de la Aplicación</h2>";
$configKey = 'app_config';

$config = cache_remember($configKey, function() {
    echo "<p style='color: orange;'>Cargando configuración desde archivo...</p>";
    return [
        'app_name' => 'Sistema de Gestión de Deudas',
        'version' => '1.0.0',
        'maintenance_mode' => false,
        'max_upload_size' => '10MB',
        'session_timeout' => 3600,
        'loaded_at' => date('Y-m-d H:i:s')
    ];
}, 1800); // Caché por 30 minutos

echo "<pre>" . print_r($config, true) . "</pre>";

// Ejemplo 5: Invalidación manual del caché
echo "<h2>Ejemplo 5: Gestión Manual del Caché</h2>";
echo "<p>Claves en caché:</p>";
echo "<ul>";
echo "<li>$cacheKey: " . ($cache->has($cacheKey) ? 'Existe' : 'No existe') . "</li>";
echo "<li>$statsKey: " . ($cache->has($statsKey) ? 'Existe' : 'No existe') . "</li>";
echo "<li>$configKey: " . ($cache->has($configKey) ? 'Existe' : 'No existe') . "</li>";
echo "</ul>";

// Botones para gestionar caché (en una aplicación real, estos serían formularios)
echo "<div style='margin: 20px 0;'>";
echo "<p><strong>Acciones disponibles:</strong></p>";
echo "<p>• Para limpiar una clave específica: <code>cache()->delete('clave')</code></p>";
echo "<p>• Para limpiar todo el caché: <code>cache()->clear()</code></p>";
echo "<p>• Para limpiar caché expirado: <code>cache()->clearExpired()</code></p>";
echo "</div>";

// Estadísticas finales
echo "<h2>Estadísticas del Caché</h2>";
$cacheStats = $cache->getStats();
echo "<pre>" . print_r($cacheStats, true) . "</pre>";

echo "<h2>Conclusión</h2>";
echo "<p>El sistema de caché está funcionando correctamente y puede mejorar significativamente el rendimiento de la aplicación.</p>";
echo "<p><strong>Beneficios observados:</strong></p>";
echo "<ul>";
echo "<li>Reducción drástica en el tiempo de respuesta para datos ya cacheados</li>";
echo "<li>Menor carga en la base de datos</li>";
echo "<li>Mejor experiencia de usuario</li>";
echo "<li>Sistema robusto con fallback a archivos cuando APCu no está disponible</li>";
echo "</ul>";
?>