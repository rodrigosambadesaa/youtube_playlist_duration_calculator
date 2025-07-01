<?php
// Habilitar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== TEST API YOUTUBE ===\n";

require_once __DIR__ . '/config.php';

$apiKey = YOUTUBE_API_KEY;
echo "API Key: " . substr($apiKey, 0, 20) . "...\n";

// Test directo con cURL
$playlistId = 'PLrAXtmRdnEQy8VgTl62wdkmMLKWMvOYGF';
$url = 'https://www.googleapis.com/youtube/v3/playlists?' . http_build_query([
    'part' => 'snippet,status',
    'id' => $playlistId,
    'key' => $apiKey,
    'maxResults' => 1
]);

echo "URL: " . $url . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'YouTube Playlist Calculator/1.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Accept-Encoding: gzip, deflate'
]);
curl_setopt($ch, CURLOPT_ENCODING, '');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "cURL Error: " . ($curlError ?: "Ninguno") . "\n";
echo "Response Length: " . strlen($response) . "\n\n";

if ($response) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "JSON válido\n";
        if (isset($data['error'])) {
            echo "ERROR API: " . $data['error']['message'] . "\n";
            echo "Error Code: " . $data['error']['code'] . "\n";
            echo "Error Reason: " . ($data['error']['errors'][0]['reason'] ?? 'N/A') . "\n";
        } else {
            echo "ÉXITO: Playlist encontrada\n";
            echo "Título: " . ($data['items'][0]['snippet']['title'] ?? 'N/A') . "\n";
            echo "Videos encontrados: " . count($data['items']) . "\n";
        }
    } else {
        echo "JSON inválido: " . json_last_error_msg() . "\n";
        echo "Response (primeros 500 chars): " . substr($response, 0, 500) . "\n";
    }
} else {
    echo "Sin respuesta\n";
}
?>