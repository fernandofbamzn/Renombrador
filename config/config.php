<?php
// Crear la carpeta "logs" si no existe
$logsDir = __DIR__ . '/../logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0777, true);
}
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', $logsDir . '/error.log');

require_once __DIR__ . '/secrets.php';

// Configuración global
define('TMDB_API_KEY', TMDB_API_KEY_SECRET);
define('TMDB_API_URL', 'https://api.themoviedb.org/3');
define('CACHE_DIR', __DIR__ . '/../cache'); // Directorio de caché

// Extensiones de video permitidas (se podrán editar desde el editor de configuración)
define('VIDEO_EXTENSIONS', ['mp4', 'avi', 'mkv', 'mov', 'mpeg']);

// Para episodios siempre mínimo 2 dígitos (por ejemplo, "03")
define('EPISODE_DIGITS', 2);
// Para temporadas siempre mínimo 1 dígito (por ejemplo, "3")
define('SEASON_DIGITS', 1);

// Modo de ejecución: true = debug, false = release
define('DEBUG_MODE', false);
?>
