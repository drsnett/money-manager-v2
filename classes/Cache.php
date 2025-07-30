<?php
/**
 * Sistema de caché simple para mejorar el rendimiento
 * Soporta caché en archivos y en memoria (APCu si está disponible)
 */

class Cache {
    private $cacheDir;
    private $defaultTtl;
    private $useApcu;
    
    public function __construct($cacheDir = null, $defaultTtl = 3600) {
        $this->cacheDir = $cacheDir ?: __DIR__ . '/../data/cache';
        $this->defaultTtl = $defaultTtl;
        $this->useApcu = extension_loaded('apcu') && function_exists('apcu_enabled') && apcu_enabled();
        
        // Crear directorio de caché si no existe
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        // Crear archivo .htaccess para proteger el directorio de caché
        $htaccessFile = $this->cacheDir . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, "Deny from all\n");
        }
    }
    
    /**
     * Obtener valor del caché
     */
    public function get($key, $default = null) {
        if (empty($key)) {
            return $default;
        }
        
        $key = $this->sanitizeKey($key);
        
        // Intentar primero con APCu si está disponible
        if ($this->useApcu && function_exists('apcu_fetch')) {
            $success = false;
            $value = apcu_fetch($key, $success);
            if ($success) {
                return $value;
            }
        }
        
        // Intentar con caché de archivos
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = file_get_contents($filename);
        if ($data === false) {
            return $default;
        }
        
        $cacheData = @unserialize($data);
        
        // Verificar que los datos sean válidos
        if ($cacheData === false || !is_array($cacheData) || !isset($cacheData['expires'], $cacheData['value'])) {
            $this->delete($key);
            return $default;
        }
        
        // Verificar si ha expirado
        if ($cacheData['expires'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        // Guardar en APCu para próximas consultas
        if ($this->useApcu && function_exists('apcu_store')) {
            $remainingTtl = $cacheData['expires'] - time();
            if ($remainingTtl > 0) {
                apcu_store($key, $cacheData['value'], $remainingTtl);
            }
        }
        
        return $cacheData['value'];
    }
    
    /**
     * Guardar valor en caché
     */
    public function set($key, $value, $ttl = null) {
        if (empty($key)) {
            throw new InvalidArgumentException('Cache key cannot be empty');
        }
        
        $key = $this->sanitizeKey($key);
        $ttl = $ttl ?: $this->defaultTtl;
        
        if ($ttl <= 0) {
            throw new InvalidArgumentException('TTL must be greater than 0');
        }
        
        $expires = time() + $ttl;
        
        // Guardar en APCu si está disponible
        if ($this->useApcu && function_exists('apcu_store')) {
            apcu_store($key, $value, $ttl);
        }
        
        // Guardar en archivo
        $filename = $this->getCacheFilename($key);
        $cacheData = [
            'value' => $value,
            'expires' => $expires,
            'created' => time()
        ];
        
        $data = serialize($cacheData);
        
        // Crear directorio si no existe
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($filename, $data, LOCK_EX) !== false;
    }
    
    /**
     * Verificar si existe una clave en caché
     */
    public function has($key) {
        if (empty($key)) {
            return false;
        }
        
        $key = $this->sanitizeKey($key);
        
        // Verificar en APCu primero
        if ($this->useApcu && function_exists('apcu_exists') && apcu_exists($key)) {
            return true;
        }
        
        // Verificar en archivo
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $data = file_get_contents($filename);
        if ($data === false) {
            return false;
        }
        
        $cacheData = @unserialize($data);
        
        // Verificar que los datos sean válidos
        if ($cacheData === false || !is_array($cacheData) || !isset($cacheData['expires'])) {
            $this->delete($key);
            return false;
        }
        
        // Verificar si ha expirado
        if ($cacheData['expires'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Eliminar valor del caché
     */
    public function delete($key) {
        if (empty($key)) {
            return true;
        }
        
        $key = $this->sanitizeKey($key);
        
        // Eliminar de APCu
        if ($this->useApcu && function_exists('apcu_delete')) {
            apcu_delete($key);
        }
        
        // Eliminar archivo
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Limpiar todo el caché
     */
    public function clear() {
        // Limpiar APCu
        if ($this->useApcu && function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
        
        // Limpiar archivos
        return $this->clearDirectory($this->cacheDir);
    }
    
    /**
     * Limpiar caché expirado
     */
    public function clearExpired() {
        $count = 0;
        
        if (!is_dir($this->cacheDir)) {
            return $count;
        }
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->cacheDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'cache') {
                    try {
                        $data = file_get_contents($file->getPathname());
                        if ($data !== false) {
                            $cacheData = @unserialize($data);
                            if ($cacheData && isset($cacheData['expires']) && $cacheData['expires'] < time()) {
                                if (unlink($file->getPathname())) {
                                    $count++;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Archivo corrupto, intentar eliminarlo
                        @unlink($file->getPathname());
                    }
                }
            }
        } catch (Exception $e) {
            // Error al acceder al directorio, registrar en log si está disponible
            if (function_exists('error_log')) {
                error_log('Cache clearExpired error: ' . $e->getMessage());
            }
        }
        
        return $count;
    }
    
    /**
     * Obtener o establecer valor con callback
     */
    public function remember($key, $callback, $ttl = null) {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Callback must be callable');
        }
        
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Incrementar valor numérico
     */
    public function increment($key, $value = 1) {
        $current = $this->get($key, 0);
        $new = $current + $value;
        $this->set($key, $new);
        return $new;
    }
    
    /**
     * Decrementar valor numérico
     */
    public function decrement($key, $value = 1) {
        return $this->increment($key, -$value);
    }
    
    /**
     * Obtener estadísticas del caché
     */
    public function getStats() {
        $stats = [
            'file_cache' => [
                'enabled' => true,
                'directory' => $this->cacheDir,
                'files' => 0,
                'size' => 0
            ],
            'apcu_cache' => [
                'enabled' => $this->useApcu,
                'info' => null
            ]
        ];
        
        // Estadísticas de archivos
        if (is_dir($this->cacheDir)) {
            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($this->cacheDir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'cache') {
                        $stats['file_cache']['files']++;
                        try {
                            $stats['file_cache']['size'] += $file->getSize();
                        } catch (Exception $e) {
                            // Archivo inaccesible, continuar con el siguiente
                        }
                    }
                }
            } catch (Exception $e) {
                // Error al acceder al directorio
                $stats['file_cache']['error'] = $e->getMessage();
            }
        }
        
        // Estadísticas de APCu
        if ($this->useApcu && function_exists('apcu_cache_info')) {
            $stats['apcu_cache']['info'] = apcu_cache_info();
        }
        
        return $stats;
    }
    
    /**
     * Sanitizar clave de caché
     */
    private function sanitizeKey($key) {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
    }
    
    /**
     * Obtener nombre de archivo para una clave
     */
    private function getCacheFilename($key) {
        $hash = md5($key);
        $subdir = substr($hash, 0, 2);
        return $this->cacheDir . '/' . $subdir . '/' . $hash . '.cache';
    }
    
    /**
     * Limpiar directorio recursivamente
     */
    private function clearDirectory($dir) {
        if (!is_dir($dir)) {
            return true;
        }
        
        $scanResult = @scandir($dir);
        if ($scanResult === false) {
            return false;
        }
        
        $files = array_diff($scanResult, ['.', '..', '.htaccess']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->clearDirectory($path);
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
        
        return true;
    }
}

/**
 * Clase singleton para acceso global al caché
 */
class CacheManager {
    private static $instance = null;
    private $cache;
    
    private function __construct() {
        $this->cache = new Cache();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getCache() {
        return $this->cache;
    }
    
    // Métodos de conveniencia
    public function get($key, $default = null) {
        return $this->cache->get($key, $default);
    }
    
    public function set($key, $value, $ttl = null) {
        return $this->cache->set($key, $value, $ttl);
    }
    
    public function has($key) {
        return $this->cache->has($key);
    }
    
    public function delete($key) {
        return $this->cache->delete($key);
    }
    
    public function remember($key, $callback, $ttl = null) {
        return $this->cache->remember($key, $callback, $ttl);
    }
}

// Funciones de conveniencia globales
if (!function_exists('cache')) {
    function cache($key = null, $value = null, $ttl = null) {
        $cache = CacheManager::getInstance();
        
        if ($key === null) {
            return $cache->getCache();
        }
        
        if ($value === null) {
            return $cache->get($key);
        }
        
        return $cache->set($key, $value, $ttl);
    }
}

if (!function_exists('cache_remember')) {
    function cache_remember($key, $callback, $ttl = null) {
        $cache = CacheManager::getInstance();
        return $cache->remember($key, $callback, $ttl);
    }
}
?>