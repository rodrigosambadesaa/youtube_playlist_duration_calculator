<?php
// Habilitar error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Iniciando test debug...\n";

// Incluir configuración
try {
    require_once __DIR__ . '/config.php';
    echo "Config cargado correctamente\n";
} catch (Exception $e) {
    echo "Error cargando config: " . $e->getMessage() . "\n";
    exit;
}

session_start();
echo "Sesión iniciada\n";

// Probar creación de API
try {
    require_once __DIR__ . '/api.php';
    echo "API incluida correctamente\n";
    
    $api = new YouTubePlaylistAPI();
    echo "API instanciada correctamente\n";
    
} catch (Exception $e) {
    echo "Error instanciando API: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
