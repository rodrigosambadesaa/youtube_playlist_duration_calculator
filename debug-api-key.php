<?php
// Habilitar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== DEBUG API KEY ===\n";

require_once __DIR__ . '/config.php';
echo "Config cargado\n";

// Verificar API key
$apiKey = '';

// Prioridad: 1. Variable de entorno del sistema
if (getenv('YOUTUBE_API_KEY')) {
    $apiKey = getenv('YOUTUBE_API_KEY');
    echo "API Key encontrada en variable de entorno del sistema\n";
} elseif (isset($envConfig['YOUTUBE_API_KEY']) && !empty($envConfig['YOUTUBE_API_KEY'])) {
    $apiKey = $envConfig['YOUTUBE_API_KEY'];
    echo "API Key encontrada en archivo .env\n";
} else {
    echo "NO se encontró API Key\n";
}

echo "API Key (primeros 20 chars): " . substr($apiKey, 0, 20) . "...\n";
echo "YOUTUBE_API_KEY constante: " . (defined('YOUTUBE_API_KEY') ? substr(YOUTUBE_API_KEY, 0, 20) . "..." : "NO DEFINIDA") . "\n";

// Probar si es demo mode
if ($apiKey === 'demo_mode' || empty($apiKey)) {
    echo "MODO: DEMO\n";
} else {
    echo "MODO: API REAL\n";
}

// Verificar archivo .env
echo "\n=== CONTENIDO .env ===\n";
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (strpos($line, 'YOUTUBE_API_KEY') !== false) {
            echo "Línea .env encontrada: " . $line . "\n";
        }
    }
} else {
    echo "Archivo .env NO existe\n";
}
?>