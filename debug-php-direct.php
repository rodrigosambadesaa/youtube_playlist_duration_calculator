<?php
// Habilitar todos los errores y mostrarlos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Crear un log personalizado
function logError($message)
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents(__DIR__ . '/debug.log', $logMessage, FILE_APPEND | LOCK_EX);
    echo "LOG: $message\n";
}

logError("=== INICIO DEBUG ===");

try {
    logError("Incluyendo config.php...");
    require_once __DIR__ . '/config.php';
    logError("Config incluido correctamente");

    logError("Iniciando sesión...");
    session_start();
    logError("Sesión iniciada");

    // Generar token CSRF si no existe
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    logError("Token CSRF de sesión: " . $_SESSION['csrf_token']);

    logError("Incluyendo api.php...");
    require_once __DIR__ . '/api.php';
    logError("API incluida correctamente");

    logError("Instanciando YouTubePlaylistAPI...");
    $api = new YouTubePlaylistAPI();
    logError("API instanciada correctamente");

    logError("Simulando entrada POST...");
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';

    // Simular input JSON con el token correcto
    $testInput = json_encode([
        'playlistId' => 'PLrAXtmRdnEQy8VgTl62wdkmMLKWMvOYGF',
        'csrf_token' => $_SESSION['csrf_token']  // Usar el token de la sesión actual
    ]);

    logError("Input simulado: " . $testInput);

    // Override php://input para testing
    $GLOBALS['test_input'] = $testInput;

    logError("Llamando handleRequest()...");
    ob_start();
    $api->handleRequest();
    $output = ob_get_clean();

    logError("Output: " . $output);

} catch (Exception $e) {
    logError("EXCEPCIÓN CAPTURADA: " . $e->getMessage());
    logError("Archivo: " . $e->getFile());
    logError("Línea: " . $e->getLine());
    logError("Trace: " . $e->getTraceAsString());
} catch (Error $e) {
    logError("ERROR FATAL CAPTURADO: " . $e->getMessage());
    logError("Archivo: " . $e->getFile());
    logError("Línea: " . $e->getLine());
    logError("Trace: " . $e->getTraceAsString());
}

logError("=== FIN DEBUG ===");

// Mostrar el log
echo "\n\n=== CONTENIDO DEL LOG ===\n";
if (file_exists(__DIR__ . '/debug.log')) {
    echo file_get_contents(__DIR__ . '/debug.log');
}
?>