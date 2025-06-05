<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../lib/CacheManager.php';
require_once __DIR__ . '/../config/config.php';

if (!isset($_GET['series_id']) || !isset($_GET['season']) || !isset($_GET['episode'])) {
    echo json_encode(['error' => 'Faltan parámetros (series_id, season o episode)']);
    exit;
}

$seriesId = $_GET['series_id'];
$season = $_GET['season'];
$episode = $_GET['episode'];

$cacheManager = new CacheManager();
$cacheKey = "episode_{$seriesId}_{$season}_{$episode}";
$cached = $cacheManager->get($cacheKey);
if ($cached !== null) {
    if (DEBUG_MODE) { error_log("DEBUG: get_episode_info.php - Usando caché para: " . $cacheKey); }
    ob_clean();
    echo json_encode($cached);
    exit;
}

$endpoint = "/tv/{$seriesId}/season/{$season}/episode/{$episode}";
$params = [
  'language' => 'es-ES',
  'api_key'  => TMDB_API_KEY
];
$url = TMDB_API_URL . $endpoint . '?' . http_build_query($params);

if (DEBUG_MODE) { error_log("DEBUG: get_episode_info.php URL: " . $url); }

$response = @file_get_contents($url);
if ($response === FALSE) {
    echo json_encode(['error' => 'Error al realizar la petición a TMDB']);
    exit;
}

$data = json_decode($response, true);
if (isset($data['status_code'])) {
    echo json_encode(['error' => $data['status_message']]);
} else {
    $cacheManager->set($cacheKey, $data, 86400);
    echo json_encode($data);
}
?>
