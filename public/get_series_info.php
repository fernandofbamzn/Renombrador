<?php
header('Content-Type: application/json; charset=utf-8');
ob_start(); // Inicia el buffer para evitar salida accidental

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

if (!isset($_GET['series']) || empty($_GET['series'])) {
    echo json_encode(['error' => 'No se ha proporcionado el nombre de la serie']);
    exit;
}

$seriesName = $_GET['series'];
$cacheManager = new CacheManager();
$cacheKey = "series_" . md5($seriesName);
$cached = $cacheManager->get($cacheKey);
if ($cached !== null) {
    if (DEBUG_MODE) { error_log("DEBUG: get_series_info.php - Usando caché para: " . $seriesName); }
    ob_clean();
    echo json_encode($cached);
    exit;
}

$tmdbTv = new TmdbTv();
if (DEBUG_MODE) {
    error_log("DEBUG: get_series_info.php: series=" . $seriesName);
}

try {
    $result = $tmdbTv->search($seriesName, ['language' => 'es-ES']);
    $cacheManager->set($cacheKey, $result, 86400); // Cache por 24 horas
    ob_clean();
    echo json_encode($result);
} catch (Exception $e) {
    ob_clean();
    if (DEBUG_MODE) {
        error_log("DEBUG: get_series_info.php error: " . $e->getMessage());
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>
