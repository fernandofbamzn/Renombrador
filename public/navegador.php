<?php
// navegador.php
// Aseguramos la codificación UTF-8
header('Content-Type: text/html; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) { // Añade esta condición
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Definir la carpeta principal y el mapa de directorios autorizados
$filmotecaDirectory = '/volume1/Filmoteca';
$directoryMap = [
    'Nuevos Capitulos'     => $filmotecaDirectory . '/Nuevos Capitulos',
    'Series'               => $filmotecaDirectory . '/Series',
    'Peliculas'            => $filmotecaDirectory . '/Peliculas',
    'Peliculas Infantiles' => $filmotecaDirectory . '/Peliculas Infantiles'
];

// Obtener la categoría seleccionada vía GET, por defecto "Nuevos Capitulos"
$selectedKey = isset($_GET['dir']) ? $_GET['dir'] : 'Nuevos Capitulos';
if (!isset($directoryMap[$selectedKey])) {
    $selectedKey = 'Nuevos Capitulos';
}
$baseDirectory = $directoryMap[$selectedKey];

// Permitir navegar solo dentro de la carpeta base y sus subdirectorios
$subdir = isset($_GET['subdir']) ? $_GET['subdir'] : '';
$path = realpath($baseDirectory . DIRECTORY_SEPARATOR . $subdir);
if ($path === false || strpos($path, realpath($baseDirectory)) !== 0) {
    $path = realpath($baseDirectory);
    if (DEBUG_MODE) { error_log("DEBUG: Ruta inválida. Se establece la ruta base: " . $path); }
}

// Función para obtener la ruta relativa desde la carpeta base
function getRelativePath($fullPath, $basePath) {
    return ltrim(str_replace(realpath($basePath), '', realpath($fullPath)), DIRECTORY_SEPARATOR);
}

$relativePath = getRelativePath($path, $baseDirectory);

// Generar breadcrumbs a partir de la ruta relativa
$breadcrumbs = [];
if (!empty($relativePath)) {
    $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
    $accum = '';
    foreach ($parts as $part) {
        $accum .= $part . DIRECTORY_SEPARATOR;
        $breadcrumbs[] = [
            'name'   => $part,
            'subdir' => rtrim($accum, DIRECTORY_SEPARATOR)
        ];
    }
}

// Listar solo los subdirectorios (excluyendo archivos)
$items = scandir($path);
$directories = [];
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $fullItemPath = $path . DIRECTORY_SEPARATOR . $item;
    if (is_dir($fullItemPath)) {
        $directories[] = $item;
    }
}

$rootName = $selectedKey;
?>
<div id="navegador" class="navegador" style="padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
    <h2 style="margin: 0 0 10px 0; font-size: 1.2em;">Navegador de Directorios</h2>
    
    <!-- Desplegables en línea -->
    <div class="nav-dropdowns" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Selección de categoría -->
        <form method="get" action="" style="display: inline-block; margin: 0;">
            <label for="dir" style="font-weight: bold;">Raíz:</label>
            <select name="dir" id="dir" onchange="this.form.submit()" style="padding: 4px;">
                <?php foreach ($directoryMap as $key => $dirPath): ?>
                    <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($key === $selectedKey) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($key); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- Se envía el subdirectorio y currentDir actual -->
            <input type="hidden" name="subdir" value="<?php echo htmlspecialchars($subdir); ?>">
            <input type="hidden" name="currentDir" id="currentDirInput" value="<?php echo htmlspecialchars($path); ?>">
        </form>
        
        <!-- Selección de subdirectorio -->
        <form method="get" action="" style="display: inline-block; margin: 0;">
            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($selectedKey); ?>">
            <input type="hidden" name="currentDir" id="currentDirInput2" value="<?php echo htmlspecialchars($path); ?>">
            <label for="subdirSelect" style="font-weight: bold;">Subdirectorios:</label>
            <select name="subdir" id="subdirSelect" onchange="updateCurrentDir(this)" style="padding: 4px;">
                <option value="">(Raíz)</option>
                <?php foreach ($directories as $dirName): 
                    $newSubdir = trim(getRelativePath($path . DIRECTORY_SEPARATOR . $dirName, $baseDirectory));
                ?>
                    <option value="<?php echo htmlspecialchars($newSubdir); ?>">
                        <?php echo htmlspecialchars($dirName); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <!-- Breadcrumbs -->
    <p style="margin: 10px 0 0 0; font-size: 0.9em;">
        <strong>Ruta:</strong>
        <a href="?dir=<?php echo urlencode($selectedKey); ?>&currentDir=<?php echo urlencode($path); ?>" style="color: #3498db; text-decoration: none;">
            <?php echo htmlspecialchars($rootName); ?>
        </a>
        <?php
        $currentSubdir = '';
        foreach ($breadcrumbs as $crumb):
            $currentSubdir = $crumb['subdir'];
        ?>
            / <a href="?dir=<?php echo urlencode($selectedKey); ?>&subdir=<?php echo urlencode($currentSubdir); ?>&currentDir=<?php echo urlencode($path); ?>" style="color: #3498db; text-decoration: none;">
                <?php echo htmlspecialchars($crumb['name']); ?>
            </a>
        <?php endforeach; ?>
    </p>
</div>
<script>
function updateCurrentDir(select) {
    // Obtenemos la base de directorio desde una variable embebida
    const baseDir = "<?php echo htmlspecialchars($baseDirectory); ?>";
    const selectedSubdir = select.value;
    // Construir la nueva ruta: si se selecciona raíz, es baseDir; si no, baseDir + "/" + subdirectorio
    const newPath = selectedSubdir ? baseDir + "/" + selectedSubdir : baseDir;
    document.getElementById('currentDirInput').value = newPath;
    document.getElementById('currentDirInput2').value = newPath;
    select.form.submit();
}
</script>
