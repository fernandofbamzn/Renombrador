<?php
// rename.php
if (session_status() !== PHP_SESSION_ACTIVE) { // Añade esta condición
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/config.php';

$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $files = $_POST['files'] ?? [];
    $new_names = $_POST['new_names'] ?? [];
    $currentDir = $_POST['currentDir'] ?? '/volume1/Filmoteca/Nuevos Capitulos';
    if (count($files) !== count($new_names)) {
        $results[] = "Error: El número de archivos y nombres no coincide.";
    } else {
        foreach ($files as $i => $oldName) {
            // Asumimos que $oldName es el nombre base del archivo y que está en el directorio actual.
            // Se espera que el formulario incluya también el directorio actual en un hidden "currentDir".
            $currentDir = $_POST['currentDir'] ?? '/volume1/Filmoteca/Nuevos Capitulos';
            $oldPath = rtrim($currentDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $oldName;
            $newName = $new_names[$i];
            $newPath = rtrim($currentDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newName;
            if (rename($oldPath, $newPath)) {
                $results[] = "Renombrado: $oldName -> $newName";
            } else {
                $results[] = "Error al renombrar: $oldName";
                error_log("DEBUG: Error al renombrar '$oldPath' a '$newPath'");
            }
        }
    }
} else {
    $results[] = "Acceso inválido.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Renombrado</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include_once 'header.php'; ?>
    <h2>Resultados del Renombrado</h2>
    <ul>
    <?php foreach ($results as $line): ?>
        <li><?php echo htmlspecialchars($line); ?></li>
    <?php endforeach; ?>
    </ul>
    <p><a href="index.php">Volver al Inicio</a></p>
</body>
</html>
