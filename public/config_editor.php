<?php
// config_editor.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/config.php';

// Determinar el valor de las extensiones
$videoExtensionsValue = is_array(VIDEO_EXTENSIONS) ? implode(', ', VIDEO_EXTENSIONS) : VIDEO_EXTENSIONS;

// Leer el contenido actual del archivo de configuración
$filename = __DIR__ . '/../config/config.php';
$configContents = file_get_contents($filename);
$message = '';

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tmdb_api_url = $_POST['tmdb_api_url'] ?? 'https://api.themoviedb.org/3';
    $video_extensions = $_POST['video_extensions'] ?? $videoExtensionsValue;
    $episode_digits = $_POST['episode_digits'] ?? EPISODE_DIGITS;
    $season_digits = $_POST['season_digits'] ?? SEASON_DIGITS;
    $debug_mode = (isset($_POST['debug_mode']) && $_POST['debug_mode'] === 'true') ? 'true' : 'false';

    // Reemplazar las definiciones de las constantes usando expresiones regulares
    $configContents = preg_replace(
        "/define\('TMDB_API_URL',\s*'.*?'\);/",
        "define('TMDB_API_URL', '$tmdb_api_url');",
        $configContents
    );

    // Reconstruir VIDEO_EXTENSIONS: se espera una cadena separada por comas
    $extensionsArray = "['" . implode("', '", array_map('trim', explode(',', $video_extensions))) . "']";
    $configContents = preg_replace(
        "/define\('VIDEO_EXTENSIONS',\s*\[.*?\]\);/",
        "define('VIDEO_EXTENSIONS', $extensionsArray);",
        $configContents
    );

    $configContents = preg_replace(
        "/define\('EPISODE_DIGITS',\s*\d+\);/",
        "define('EPISODE_DIGITS', $episode_digits);",
        $configContents
    );
    $configContents = preg_replace(
        "/define\('SEASON_DIGITS',\s*\d+\);/",
        "define('SEASON_DIGITS', $season_digits);",
        $configContents
    );
    $configContents = preg_replace(
        "/define\('DEBUG_MODE',\s*(true|false)\);/",
        "define('DEBUG_MODE', $debug_mode);",
        $configContents
    );

    // Intentar escribir los cambios en config.php
    if (file_put_contents($filename, $configContents) !== false) {
        $message = "Configuración actualizada correctamente.";
    } else {
        $message = "Error al actualizar la configuración. Por favor, verifique los permisos del archivo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editor de Configuración</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include_once 'header.php'; ?>
    <h2>Editor de Configuración</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="tmdb_api_url">TMDB API URL:</label><br>
        <input type="text" id="tmdb_api_url" name="tmdb_api_url" value="<?php echo TMDB_API_URL; ?>" style="width:100%;"><br><br>

        <label for="video_extensions">Video Extensions (separados por coma):</label><br>
        <input type="text" id="video_extensions" name="video_extensions" value="<?php echo htmlspecialchars($videoExtensionsValue); ?>" style="width:100%;"><br><br>

        <label for="episode_digits">Episode Digits (mínimo 2):</label><br>
        <input type="number" id="episode_digits" name="episode_digits" value="<?php echo EPISODE_DIGITS; ?>" min="1"><br><br>

        <label for="season_digits">Season Digits (mínimo 1):</label><br>
        <input type="number" id="season_digits" name="season_digits" value="<?php echo SEASON_DIGITS; ?>" min="1"><br><br>

        <label for="debug_mode">Debug Mode:</label><br>
        <select name="debug_mode" id="debug_mode">
            <option value="true" <?php if(DEBUG_MODE) echo "selected"; ?>>true</option>
            <option value="false" <?php if(!DEBUG_MODE) echo "selected"; ?>>false</option>
        </select><br><br>

        <input type="submit" value="Actualizar Configuración">
    </form>
    <p><a href="index.php">Volver al Inicio</a></p>
</body>
</html>
