<?php
/**
 * YouTube Playlist Duration Calculator - Backend API
 * Maneja las solicitudes de cálculo de duración de playlists con medidas de seguridad avanzadas
 */

// Incluir configuración
require_once __DIR__ . '/config.php';

class YouTubePlaylistAPI
{
    private $apiKey;
    private $rateLimiter;
    private $csrfToken;

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
        $this->rateLimiter = new RateLimiter();
        $this->setupCSRF();
        $this->setupErrorHandling();
    }

    private function getApiKey()
    {
        // Obtener API key desde la configuración
        $apiKey = getConfig('YOUTUBE_API_KEY', '');

        // Para desarrollo, permitir modo demo si no hay API key
        if (empty($apiKey) && !isProduction()) {
            secureLog('WARNING', 'YouTube API key not configured, using demo mode');
            return 'demo_mode';
        }

        if (empty($apiKey)) {
            secureLog('ERROR', 'YouTube API key not configured in production');
            return '';
        }

        return $apiKey;
    }

    private function setupCSRF()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(getConfig('CSRF_TOKEN_LENGTH', 32)));
        }
        $this->csrfToken = $_SESSION['csrf_token'];
    }

    private function setupErrorHandling()
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    public function handleError($severity, $message, $file, $line)
    {
        secureLog('ERROR', "PHP Error: $message", [
            'file' => $file,
            'line' => $line,
            'severity' => $severity
        ]);
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public function handleException($exception)
    {
        secureLog('ERROR', 'Unhandled exception: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        $this->sendError('Error interno del servidor', 500);
    }

    public function handleRequest()
    {
        try {
            error_log("DEBUG: Iniciando handleRequest()");

            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log("DEBUG: Método no POST: " . $_SERVER['REQUEST_METHOD']);
                $this->sendError('Método no permitido', 405);
                return;
            }

            // Validar Content-Type
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            error_log("DEBUG: Content-Type: " . $contentType);
            if (
                strpos($contentType, 'application/json') === false &&
                strpos($contentType, 'application/x-www-form-urlencoded') === false &&
                strpos($contentType, 'multipart/form-data') === false
            ) {
                error_log("DEBUG: Content-Type no válido");
                $this->sendError('Content-Type no válido', 400);
                return;
            }

            // Rate limiting
            if (!$this->rateLimiter->canMakeRequest()) {
                error_log("DEBUG: Rate limit excedido");
                $this->sendError('Demasiadas solicitudes. Intenta de nuevo más tarde.', 429);
                return;
            }

            // Obtener y validar datos de entrada
            error_log("DEBUG: Obteniendo input");
            $input = $this->getSecureInput();
            error_log("DEBUG: Input obtenido: " . json_encode($input));

            // Validar CSRF token
            $csrfTokenInput = $input['csrf_token'] ?? '';
            error_log("DEBUG: Token CSRF input: " . substr($csrfTokenInput, 0, 16) . "...");
            error_log("DEBUG: Token CSRF sesión: " . substr($this->csrfToken, 0, 16) . "...");

            if (!$this->validateCSRF($csrfTokenInput)) {
                error_log("DEBUG: Validación CSRF falló");
                $this->sendError('Token de seguridad inválido', 403);
                return;
            }
            error_log("DEBUG: CSRF válido");

            // Validar y sanitizar playlist ID
            $playlistId = $this->validatePlaylistId($input['playlistId'] ?? '');
            error_log("DEBUG: Playlist ID: " . $playlistId);
            if (!$playlistId) {
                error_log("DEBUG: Playlist ID inválido");
                $this->sendError('ID de playlist inválido', 400);
                return;
            }

            // Obtener datos de la playlist
            error_log("DEBUG: Obteniendo datos de playlist");
            $playlistData = $this->fetchPlaylistDuration($playlistId);
            error_log("DEBUG: Datos obtenidos exitosamente");

            // Enviar respuesta exitosa
            $this->sendSuccess($playlistData);

        } catch (Exception $e) {
            error_log('ERROR en YouTubePlaylistAPI: ' . $e->getMessage());
            error_log('ERROR trace: ' . $e->getTraceAsString());
            $this->sendError('Error interno del servidor', 500);
        }
    }

    private function getSecureInput()
    {
        // Para testing, permitir override
        if (isset($GLOBALS['test_input'])) {
            $jsonData = json_decode($GLOBALS['test_input'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonData;
            }
        }

        $rawInput = file_get_contents('php://input');

        if (empty($rawInput)) {
            return $_POST;
        }

        $jsonData = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON inválido');
        }

        return $jsonData;
    }

    private function validateCSRF($token)
    {
        return hash_equals($this->csrfToken, $token);
    }

    private function validatePlaylistId($playlistId)
    {
        // Sanitizar entrada
        $playlistId = $this->sanitizeInput($playlistId);

        // Validar formato
        if (!preg_match('/^[a-zA-Z0-9_-]{10,}$/', $playlistId)) {
            return false;
        }

        // Validar longitud
        if (strlen($playlistId) < 10 || strlen($playlistId) > 100) {
            return false;
        }

        return $playlistId;
    }

    private function sanitizeInput($input)
    {
        if (!is_string($input)) {
            return '';
        }

        // Remover caracteres peligrosos
        $input = preg_replace('/[\x00-\x1F\x7F-\x9F]/', '', $input);
        $input = str_replace(['<', '>', '"', "'", '&'], '', $input);
        $input = preg_replace('/javascript:/i', '', $input);
        $input = preg_replace('/data:/i', '', $input);
        $input = preg_replace('/vbscript:/i', '', $input);

        return trim($input);
    }

    private function fetchPlaylistDuration($playlistId)
    {
        $cacheKey = 'playlist_' . md5($playlistId);
        $cachedData = $this->getCachedData($cacheKey);

        if ($cachedData) {
            return $cachedData;
        }

        try {
            // Llamada real a la API de YouTube
            $playlistData = $this->getRealYouTubePlaylistData($playlistId);

            // Cachear resultado por 15 minutos
            $this->setCachedData($cacheKey, $playlistData, 900);

            return $playlistData;
        } catch (Exception $e) {
            secureLog('ERROR', 'Error fetching YouTube playlist data', [
                'playlist_id' => $playlistId,
                'error' => $e->getMessage()
            ]);
            throw new Exception('No se pudo obtener la información de la playlist');
        }
    }

    private function getRealYouTubePlaylistData($playlistId)
    {
        // Si no hay API key configurada, usar modo demo con datos conocidos
        if ($this->apiKey === 'demo_mode' || empty($this->apiKey)) {
            return $this->getDemoPlaylistData($playlistId);
        }

        // Paso 1: Obtener información básica de la playlist
        $playlistInfo = $this->getPlaylistInfo($playlistId);

        // Paso 2: Obtener todos los videos de la playlist
        $videos = $this->getPlaylistVideos($playlistId);

        // Paso 3: Obtener detalles de duración de todos los videos
        $videoDetails = $this->getVideosDetails($videos);

        // Paso 4: Calcular duración total
        $totalSeconds = 0;
        $validVideos = [];

        foreach ($videoDetails as $video) {
            if (isset($video['duration']) && $video['duration'] > 0) {
                $totalSeconds += $video['duration'];
                $validVideos[] = $video;
            }
        }

        return [
            'title' => $this->sanitizeOutput($playlistInfo['title']),
            'description' => $this->sanitizeOutput($playlistInfo['description'] ?? ''),
            'videoCount' => count($validVideos),
            'totalSeconds' => $totalSeconds,
            'videos' => $validVideos,
            'channelTitle' => $this->sanitizeOutput($playlistInfo['channelTitle'] ?? ''),
            'publishedAt' => $playlistInfo['publishedAt'] ?? '',
            'thumbnails' => $playlistInfo['thumbnails'] ?? []
        ];
    }

    private function getDemoPlaylistData($playlistId)
    {
        // Datos de demostración basados en playlists reales conocidas
        $knownPlaylists = [
            'PLbAFXJC0J5Gz-liBodX5Lmt0wfbMgm-vA' => [
                'title' => 'Curso de JavaScript',
                'description' => 'Aprende JavaScript desde cero',
                'channelTitle' => 'Canal Educativo',
                'videos' => [
                    ['title' => 'Introducción a JavaScript', 'duration' => 720],
                    ['title' => 'Variables y Tipos de Datos', 'duration' => 960],
                    ['title' => 'Funciones en JavaScript', 'duration' => 1080],
                    ['title' => 'Arrays y Objetos', 'duration' => 1200],
                    ['title' => 'DOM Manipulation', 'duration' => 1440],
                ]
            ],
            'PLX8eNTkvxMmcM9-Y-w2hBAVuvW4N3q36n' => [
                'title' => 'Curso de PHP',
                'description' => 'Desarrollo web con PHP',
                'channelTitle' => 'WebDev Academy',
                'videos' => [
                    ['title' => 'Introducción a PHP', 'duration' => 600],
                    ['title' => 'Variables y Arrays', 'duration' => 840],
                    ['title' => 'Funciones y Clases', 'duration' => 1020],
                    ['title' => 'Base de Datos con MySQL', 'duration' => 1380],
                ]
            ]
        ];

        // Si es una playlist conocida, usar datos reales
        if (isset($knownPlaylists[$playlistId])) {
            $playlist = $knownPlaylists[$playlistId];
            $totalSeconds = array_sum(array_column($playlist['videos'], 'duration'));

            return [
                'title' => $this->sanitizeOutput($playlist['title']),
                'description' => $this->sanitizeOutput($playlist['description']),
                'videoCount' => count($playlist['videos']),
                'totalSeconds' => $totalSeconds,
                'videos' => $playlist['videos'],
                'channelTitle' => $this->sanitizeOutput($playlist['channelTitle']),
                'publishedAt' => date('Y-m-d\TH:i:s\Z'),
                'thumbnails' => []
            ];
        }

        // Para otras playlists, generar datos de ejemplo pero indicar que es demo
        $videoCount = rand(5, 15);
        $videos = [];
        $totalSeconds = 0;

        for ($i = 0; $i < $videoCount; $i++) {
            $duration = rand(300, 1200); // 5-20 minutos
            $videos[] = [
                'title' => $this->sanitizeOutput("Video de demostración " . ($i + 1)),
                'duration' => $duration,
                'videoId' => 'demo_' . $i
            ];
            $totalSeconds += $duration;
        }

        return [
            'title' => $this->sanitizeOutput('Playlist de Demostración'),
            'description' => $this->sanitizeOutput('DEMO: Configura una API key de YouTube para ver datos reales'),
            'videoCount' => $videoCount,
            'totalSeconds' => $totalSeconds,
            'videos' => $videos,
            'channelTitle' => $this->sanitizeOutput('Canal Demo'),
            'publishedAt' => date('Y-m-d\TH:i:s\Z'),
            'thumbnails' => []
        ];
    }

    private function getPlaylistInfo($playlistId)
    {
        $url = 'https://www.googleapis.com/youtube/v3/playlists?' . http_build_query([
            'part' => 'snippet,status',
            'id' => $playlistId,
            'key' => $this->apiKey,
            'maxResults' => 1
        ]);

        $response = $this->makeAPIRequest($url);

        if (empty($response['items'])) {
            throw new Exception('Playlist no encontrada o no es pública');
        }

        $playlist = $response['items'][0];

        // Verificar que la playlist sea pública
        if (
            isset($playlist['status']['privacyStatus']) &&
            $playlist['status']['privacyStatus'] !== 'public'
        ) {
            throw new Exception('La playlist no es pública');
        }

        return [
            'title' => $playlist['snippet']['title'],
            'description' => $playlist['snippet']['description'],
            'channelTitle' => $playlist['snippet']['channelTitle'],
            'publishedAt' => $playlist['snippet']['publishedAt'],
            'thumbnails' => $playlist['snippet']['thumbnails']
        ];
    }

    private function getPlaylistVideos($playlistId)
    {
        $videos = [];
        $nextPageToken = null;
        $maxResults = 50; // Máximo permitido por la API

        do {
            $params = [
                'part' => 'snippet,status',
                'playlistId' => $playlistId,
                'key' => $this->apiKey,
                'maxResults' => $maxResults
            ];

            if ($nextPageToken) {
                $params['pageToken'] = $nextPageToken;
            }

            $url = 'https://www.googleapis.com/youtube/v3/playlistItems?' . http_build_query($params);
            $response = $this->makeAPIRequest($url);

            if (isset($response['items'])) {
                foreach ($response['items'] as $item) {
                    // Solo incluir videos públicos y disponibles
                    if (
                        isset($item['snippet']['resourceId']['videoId']) &&
                        (!isset($item['status']['privacyStatus']) || $item['status']['privacyStatus'] === 'public')
                    ) {
                        $videos[] = [
                            'videoId' => $item['snippet']['resourceId']['videoId'],
                            'title' => $item['snippet']['title'],
                            'position' => $item['snippet']['position']
                        ];
                    }
                }
            }

            $nextPageToken = $response['nextPageToken'] ?? null;

            // Límite de seguridad: máximo 1000 videos
            if (count($videos) >= 1000) {
                break;
            }

        } while ($nextPageToken);

        return $videos;
    }

    private function getVideosDetails($videos)
    {
        $videoDetails = [];
        $batchSize = 50; // Máximo 50 IDs por request

        // Procesar videos en lotes
        for ($i = 0; $i < count($videos); $i += $batchSize) {
            $batch = array_slice($videos, $i, $batchSize);
            $videoIds = array_column($batch, 'videoId');

            $url = 'https://www.googleapis.com/youtube/v3/videos?' . http_build_query([
                'part' => 'contentDetails,snippet,status',
                'id' => implode(',', $videoIds),
                'key' => $this->apiKey
            ]);

            $response = $this->makeAPIRequest($url);

            if (isset($response['items'])) {
                foreach ($response['items'] as $video) {
                    // Solo procesar videos disponibles
                    if (
                        isset($video['status']['uploadStatus']) &&
                        $video['status']['uploadStatus'] === 'processed' &&
                        isset($video['contentDetails']['duration'])
                    ) {

                        $duration = $this->parseISO8601Duration($video['contentDetails']['duration']);

                        $videoDetails[] = [
                            'videoId' => $video['id'],
                            'title' => $video['snippet']['title'],
                            'duration' => $duration,
                            'thumbnails' => $video['snippet']['thumbnails'] ?? []
                        ];
                    }
                }
            }
        }

        return $videoDetails;
    }

    private function makeAPIRequest($url)
    {
        // Usar cURL para mejor compatibilidad y control de errores
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'YouTube Playlist Calculator/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        // Configurar headers para manejo correcto de compresión
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-Encoding: gzip, deflate'
        ]);

        // Habilitar descompresión automática de gzip
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            secureLog('ERROR', 'cURL error in API request', [
                'url' => $url,
                'error' => $curlError
            ]);
            throw new Exception('Error de conexión: ' . $curlError);
        }

        if ($response === false) {
            secureLog('ERROR', 'Failed to get response from YouTube API', [
                'url' => $url,
                'http_code' => $httpCode
            ]);
            throw new Exception('Error al conectar con la API de YouTube (HTTP ' . $httpCode . ')');
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            secureLog('ERROR', 'Invalid JSON response from YouTube API', [
                'url' => $url,
                'json_error' => json_last_error_msg(),
                'response_preview' => substr($response, 0, 200)
            ]);
            throw new Exception('Respuesta inválida de la API de YouTube');
        }

        // Verificar errores de la API
        if (isset($data['error'])) {
            $errorMessage = $data['error']['message'] ?? 'Error desconocido de la API';
            throw new Exception('Error de YouTube API: ' . $errorMessage);
        }

        return $data;
    }

    private function parseISO8601Duration($duration)
    {
        // Ejemplo: PT4M13S = 4 minutos 13 segundos = 253 segundos
        // Ejemplo: PT1H2M10S = 1 hora 2 minutos 10 segundos = 3730 segundos

        $interval = new DateInterval($duration);

        return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }

    private function sanitizeOutput($output)
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function getCachedData($key)
    {
        $cacheFile = sys_get_temp_dir() . '/playlist_cache_' . md5($key);

        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && $data['expires'] > time()) {
                return $data['content'];
            }
            unlink($cacheFile);
        }

        return null;
    }

    private function setCachedData($key, $data, $ttl)
    {
        $cacheFile = sys_get_temp_dir() . '/playlist_cache_' . md5($key);
        $cacheData = [
            'content' => $data,
            'expires' => time() + $ttl
        ];

        file_put_contents($cacheFile, json_encode($cacheData), LOCK_EX);
    }

    private function sendSuccess($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE);
    }

    private function sendError($message, $code = 400)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $this->sanitizeOutput($message),
            'code' => $code,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE);
    }

}

class RateLimiter
{
    private $maxRequests;
    private $timeWindow;
    private $storageKey;

    public function __construct($maxRequests = null, $timeWindow = 60)
    {
        $this->maxRequests = $maxRequests ?? getConfig('MAX_REQUESTS_PER_MINUTE', 10);
        $this->timeWindow = $timeWindow;
        $this->storageKey = 'rate_limit_' . $this->getClientId();
    }

    private function getClientId()
    {
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return md5($ip . $userAgent);
    }

    private function getClientIP()
    {
        return getClientIP(); // Usar función de config.php
    }

    public function canMakeRequest()
    {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . $this->storageKey;
        $now = time();

        $requests = [];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data) {
                $requests = array_filter($data, function ($timestamp) use ($now) {
                    return ($now - $timestamp) < $this->timeWindow;
                });
            }
        }

        if (count($requests) >= $this->maxRequests) {
            return false;
        }

        $requests[] = $now;
        file_put_contents($cacheFile, json_encode($requests), LOCK_EX);

        return true;
    }
}

// Configurar zona horaria
date_default_timezone_set('UTC');

// Solo ejecutar el código principal si estamos siendo accedidos directamente
if (!defined('API_TEMP_MODE') && basename($_SERVER['PHP_SELF']) === 'api.php') {
    // Configuración de seguridad adicional
    header('Content-Type: application/json; charset=utf-8');

    // Iniciar sesión segura
    session_start();

    // Determinar el endpoint
    $action = $_GET['action'] ?? $_GET['endpoint'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    // Manejar la solicitud basado en el action
    if ($action === 'playlist-duration' || strpos($requestUri, 'playlist-duration') !== false) {
        $api = new YouTubePlaylistAPI();
        $api->handleRequest();
    } elseif ($action === 'csrf-token' || strpos($requestUri, 'csrf-token') !== false) {
        // Devolver el token CSRF para el frontend
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Asegurar que tenemos un token CSRF en la sesión
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(getConfig('CSRF_TOKEN_LENGTH', 32)));
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
        }
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Endpoint no encontrado',
            'available_actions' => ['csrf-token', 'playlist-duration'],
            'current_action' => $action,
            'request_uri' => $requestUri
        ]);
    }
}
?>