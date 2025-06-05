<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) { // Añade esta condición
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../lib/TmdbTv.php';
require_once __DIR__ . '/../lib/CacheManager.php';
require_once __DIR__ . '/../config/config.php';

if (!isset($_GET['series_id']) || !isset($_GET['season'])) {
    echo json_encode(['error' => 'Faltan parámetros (series_id o season)']);
    exit;
}

$seriesId = $_GET['series_id'];
$season = $_GET['season'];
$cacheManager = new CacheManager();
$cacheKey = "season_{$seriesId}_{$season}";
$cached = $cacheManager->get($cacheKey);
if ($cached !== null) {
    if (DEBUG_MODE) { error_log("DEBUG: get_season_info.php - Usando caché para: " . $cacheKey); }
    ob_clean();
    echo json_encode($cached);
    exit;
}

$tmdbTv = new TmdbTv();
if (DEBUG_MODE) {
    error_log("DEBUG: get_season_info.php: series_id=$seriesId, season=$season");
}

try {
    $result = $tmdbTv->getSeasonDetails($seriesId, $season, ['language' => 'es-ES']);
    $cacheManager->set($cacheKey, $result, 86400);
    ob_clean();
    echo json_encode($result);
} catch (Exception $e) {
    ob_clean();
    if (DEBUG_MODE) { error_log("DEBUG: get_season_info.php error: " . $e->getMessage()); }
    echo json_encode(['error' => $e->getMessage()]);
}
?>
