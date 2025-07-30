<?php
/**
 * Configuración de variables de entorno para Money Manager
 * Este archivo carga y gestiona las variables de entorno desde .env
 */

class EnvConfig {
    private static $config = [];
    private static $loaded = false;
    
    /**
     * Cargar variables de entorno desde archivo .env
     */
    public static function load($envFile = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($envFile === null) {
            $envFile = __DIR__ . '/../.env';
        }
        
        // Intentar cargar .env.local primero (para configuraciones específicas)
        $localEnvFile = __DIR__ . '/../.env.local';
        if (file_exists($localEnvFile)) {
            self::parseEnvFile($localEnvFile);
        }
        
        // Cargar .env principal
        if (file_exists($envFile)) {
            self::parseEnvFile($envFile);
        }
        
        // Establecer valores por defecto si no están definidos
        self::setDefaults();
        
        self::$loaded = true;
    }
    
    /**
     * Parsear archivo .env
     */
    private static function parseEnvFile($file) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parsear línea KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                self::$config[$key] = $value;
                
                // También establecer en $_ENV para compatibilidad
                $_ENV[$key] = $value;
            }
        }
    }
    
    /**
     * Establecer valores por defecto
     */
    private static function setDefaults() {
        $defaults = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_NAME' => 'Money Manager',
            'APP_VERSION' => '2.0',
            'DB_TYPE' => 'sqlite',
            'DB_PATH' => 'data/money_manager.db',
            'SESSION_LIFETIME' => '7200',
            'CSRF_TOKEN_LIFETIME' => '3600',
            'PASSWORD_MIN_LENGTH' => '8',
            'UPLOAD_MAX_SIZE' => '5242880',
            'CURRENCY_SYMBOL' => '$',
            'CURRENCY_CODE' => 'USD',
            'CURRENCY_DECIMALS' => '2',
            'TIMEZONE' => 'America/Santo_Domingo',
            'LOG_LEVEL' => 'error',
            'LOG_MAX_FILES' => '30',
            'CACHE_ENABLED' => 'true',
            'CACHE_LIFETIME' => '3600'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset(self::$config[$key])) {
                self::$config[$key] = $value;
                $_ENV[$key] = $value;
            }
        }
    }
    
    /**
     * Obtener valor de configuración
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Obtener valor como booleano
     */
    public static function getBool($key, $default = false) {
        $value = self::get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
    }
    
    /**
     * Obtener valor como entero
     */
    public static function getInt($key, $default = 0) {
        return (int) self::get($key, $default);
    }
    
    /**
     * Obtener valor como float
     */
    public static function getFloat($key, $default = 0.0) {
        return (float) self::get($key, $default);
    }
    
    /**
     * Verificar si una clave existe
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$config[$key]);
    }
    
    /**
     * Obtener todas las configuraciones
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$config;
    }
    
    /**
     * Verificar si estamos en modo debug
     */
    public static function isDebug() {
        return self::getBool('APP_DEBUG', false);
    }
    
    /**
     * Verificar si estamos en producción
     */
    public static function isProduction() {
        return self::get('APP_ENV', 'production') === 'production';
    }
    
    /**
     * Obtener configuración de base de datos
     */
    public static function getDatabaseConfig() {
        return [
            'type' => self::get('DB_TYPE', 'sqlite'),
            'path' => self::get('DB_PATH', 'data/money_manager.db'),
            'host' => self::get('DB_HOST', 'localhost'),
            'name' => self::get('DB_NAME', 'money_manager'),
            'user' => self::get('DB_USER', 'root'),
            'pass' => self::get('DB_PASS', '')
        ];
    }
    
    /**
     * Obtener configuración de email
     */
    public static function getMailConfig() {
        return [
            'host' => self::get('MAIL_HOST', 'smtp.gmail.com'),
            'port' => self::getInt('MAIL_PORT', 587),
            'username' => self::get('MAIL_USERNAME', ''),
            'password' => self::get('MAIL_PASSWORD', ''),
            'from_address' => self::get('MAIL_FROM_ADDRESS', 'noreply@moneymanager.com'),
            'from_name' => self::get('MAIL_FROM_NAME', 'Money Manager')
        ];
    }
    
    /**
     * Obtener configuración de WhatsApp
     */
    public static function getWhatsAppConfig() {
        return [
            'enabled' => self::getBool('WHATSAPP_ENABLED', false),
            'api_url' => self::get('WHATSAPP_API_URL', 'https://api.callmebot.com/whatsapp.php'),
            'api_key' => self::get('WHATSAPP_API_KEY', ''),
            'phone' => self::get('WHATSAPP_PHONE', '')
        ];
    }
}

// Cargar configuración automáticamente
EnvConfig::load();

// Funciones de conveniencia
function env($key, $default = null) {
    return EnvConfig::get($key, $default);
}

function env_bool($key, $default = false) {
    return EnvConfig::getBool($key, $default);
}

function env_int($key, $default = 0) {
    return EnvConfig::getInt($key, $default);
}

function env_float($key, $default = 0.0) {
    return EnvConfig::getFloat($key, $default);
}
?>