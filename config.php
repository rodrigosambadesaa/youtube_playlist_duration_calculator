<?php
/**
 * Archivo de configuración para YouTube Playlist Calculator
 * Centraliza todas las configuraciones de la aplicación
 */

// Configuración de la aplicación
define('APP_NAME', 'YouTube Playlist Duration Calculator');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true); // Cambiar a false en producción

// Configuración de seguridad
define('CSRF_TOKEN_LENGTH', 32);
define('SESSION_LIFETIME', 3600); // 1 hora
define('MAX_REQUESTS_PER_MINUTE', 30);
define('MAX_PLAYLIST_ID_LENGTH', 100);

// Configuración de cache
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 900); // 15 minutos
define('CACHE_DIRECTORY', sys_get_temp_dir());

// Configuración de API de YouTube
define('YOUTUBE_API_BASE_URL', 'https://www.googleapis.com/youtube/v3/');

// Función para cargar configuración desde archivo .env
function loadEnvironmentConfig()
{
    $envFiles = [
        __DIR__ . '/.env.local',    // Prioridad más alta para desarrollo local
        __DIR__ . '/.env',          // Archivo principal
        __DIR__ . '/.env.example'   // Fallback para valores por defecto
    ];

    $config = [];

    foreach ($envFiles as $envFile) {
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0 || empty($line)) {
                    continue; // Saltar comentarios y líneas vacías
                }

                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"'"); // Remover espacios y comillas

                    if (!isset($config[$key])) { // Solo si no se ha configurado antes
                        $config[$key] = $value;
                    }
                }
            }
            break; // Usar solo el primer archivo encontrado
        }
    }

    return $config;
}

// Cargar configuración de entorno
$envConfig = loadEnvironmentConfig();

// Configurar API key con múltiples fuentes
$youtubeApiKey = '';

// Prioridad: 1. Variable de entorno del sistema
if (getenv('YOUTUBE_API_KEY')) {
    $youtubeApiKey = getenv('YOUTUBE_API_KEY');
}
// 2. Archivo .env
elseif (isset($envConfig['YOUTUBE_API_KEY']) && !empty($envConfig['YOUTUBE_API_KEY'])) {
    $youtubeApiKey = $envConfig['YOUTUBE_API_KEY'];
}

define('YOUTUBE_API_KEY', $youtubeApiKey);

// Configuración de logs
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'ERROR'); // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE', dirname(__FILE__) . '/logs/app.log');

// Configuración de base de datos (para futuras versiones)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'playlist_calculator');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Configuraciones de PHP para seguridad
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', LOG_FILE);

// Configuración de sesiones seguras
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Headers de seguridad globales
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Función para obtener configuración de forma segura
function getConfig($key, $default = null)
{
    return defined($key) ? constant($key) : $default;
}

// Función para logging seguro
function secureLog($level, $message, $context = [])
{
    if (!LOG_ENABLED)
        return;

    $allowedLevels = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];
    $currentLevelIndex = array_search(LOG_LEVEL, $allowedLevels);
    $messageLevelIndex = array_search($level, $allowedLevels);

    if ($messageLevelIndex >= $currentLevelIndex) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message $contextStr" . PHP_EOL;

        $logDir = dirname(LOG_FILE);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// Funciones de utilidad
function isProduction()
{
    return !APP_DEBUG &&
        (isset($_SERVER['SERVER_NAME']) &&
            !in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']));
}

function getClientIP()
{
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Validar entorno
function validateEnvironment()
{
    $required_extensions = ['json', 'session', 'filter'];
    $missing_extensions = [];

    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missing_extensions[] = $ext;
        }
    }

    if (!empty($missing_extensions)) {
        secureLog('ERROR', 'Missing required PHP extensions', $missing_extensions);
        die('Error: Missing required PHP extensions');
    }

    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        secureLog('ERROR', 'PHP version too old', ['current' => PHP_VERSION]);
        die('Error: PHP 7.4+ required');
    }
}

// Ejecutar validación del entorno
validateEnvironment();

// Manejar solicitudes de información de API
if (isset($_GET['action']) && $_GET['action'] === 'get_api_info') {
    header('Content-Type: application/json');

    $apiKey = getConfig('YOUTUBE_API_KEY');

    $response = [
        'api_key' => $apiKey ? substr($apiKey, 0, 10) . '...' . substr($apiKey, -4) : 'No configurada',
        'api_key_length' => $apiKey ? strlen($apiKey) : 0,
        'api_key_format_valid' => $apiKey ? strpos($apiKey, 'AIza') === 0 : false,
        'api_key_configured' => !empty($apiKey)
    ];

    echo json_encode($response);
    exit;
}

// Log de inicio de aplicación
secureLog('INFO', 'Application initialized', [
    'version' => APP_VERSION,
    'php_version' => PHP_VERSION,
    'client_ip' => getClientIP(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
]);
?>