<?php
// Habilitar error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

header('Content-Type: application/json');

// Simular el comportamiento del endpoint CSRF
$action = $_GET['action'] ?? '';

if ($action === 'csrf-token') {
    // Generar token si no existe
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    echo json_encode([
        'success' => true,
        'csrf_token' => $_SESSION['csrf_token']
    ]);
    exit;
}

if ($action === 'playlist-duration') {
    // Obtener datos POST
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    echo json_encode([
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
            'raw_input' => $rawInput,
            'parsed_input' => $input,
            'session_token' => $_SESSION['csrf_token'] ?? 'NO_TOKEN',
            'input_token' => $input['csrf_token'] ?? 'NO_INPUT_TOKEN',
            'tokens_match' => hash_equals($_SESSION['csrf_token'] ?? '', $input['csrf_token'] ?? '')
        ]
    ]);
    exit;
}

echo json_encode(['error' => 'Action not found']);
?>
