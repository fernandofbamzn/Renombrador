<?php
// renombrador.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/FileNavigator.php';

// Directorio actual: se obtiene vía GET "currentDir" o se usa por defecto
$currentDirectory = $_GET['currentDir'] ?? '/volume1/Filmoteca/Nuevos Capitulos';
$navigator = new FileNavigator();
$ficheros = $navigator->listFiles($currentDirectory, VIDEO_EXTENSIONS, false);
if (!$ficheros) {
  $ficheros = [];
} else {
    // Mostrar solo el nombre del archivo (sin la ruta)
  $ficheros = array_map('basename', $ficheros);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Renombrador de Ficheros</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Estilos mínimos para pestañas y tablas */
    .tabs { margin: 20px 0; }
    .tab-links { list-style: none; padding: 0; display: flex; border-bottom: 1px solid #ddd; margin-bottom: 10px; }
    .tab-links li { margin-right: 10px; }
    .tab-links li a { text-decoration: none; padding: 8px 12px; display: block; background: #f2f2f2; color: #333; border-radius: 4px 4px 0 0; }
    .tab-links li.active a, .tab-links li a:hover { background: #3498db; color: #fff; }
    .tab-content .tab { display: none; }
    .tab-content .tab.active { display: block; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
    th { background: #f2f2f2; }
    button { padding: 4px 8px; font-size: 0.9em; margin: 2px 0; }
    input[type="text"] { box-sizing: border-box; }
    .new-name { width: 100%; padding: 8px; font-size: 1.1em; }
    .action-buttons { display: flex; flex-direction: column; gap: 4px; }
  </style>
</head>
<body>
  <?php include_once 'header.php'; ?>
  <div id="renombrador" class="renombrador" style="padding:10px; background:#fff; border:1px solid #ddd; border-radius:4px;">
    <h2>Renombrador de Ficheros</h2>
    
    <!-- Panel de configuración del patrón y tokens -->
    <div id="pattern-config" class="pattern-config" style="margin-bottom:20px;">
      <h3>Configurar Patrón</h3>
      <!-- El usuario puede modificar el patrón; se usan los tokens {serie}, {temporada}, {episodio}, {titulo} y {extension} -->
      <input type="text" id="pattern" value="{serie} {temporada}x{episodio} - {titulo}.{extension}" style="width:80%; padding:5px;">
      <div id="tokenContainer" style="margin-top:5px;">
        <button type="button" class="tokenBtn" data-token="{serie}">[Serie]</button>
        <button type="button" class="tokenBtn" data-token="{temporada}">[Temporada]</button>
        <button type="button" class="tokenBtn" data-token="{episodio}">[Episodio]</button>
        <button type="button" class="tokenBtn" data-token="{titulo}">[Título]</button>
        <button type="button" class="tokenBtn" data-token="{extension}">[Extensión]</button>
      </div>
    </div>
    
    <!-- Interfaz en pestañas -->
    <div class="tabs">
      <ul class="tab-links">
        <li class="active"><a href="#tab1">Carga de Info</a></li>
        <li><a href="#tab2">Generación de Nombres</a></li>
      </ul>
      
      <div class="tab-content">
        <!-- Pestaña 1: Carga de Info -->
        <div id="tab1" class="tab active">
          <h3>Ficheros Encontrados y Detección</h3>
          <button type="button" id="selectAllBtn">Seleccionar Todos</button>
          &nbsp;&nbsp;
          <button type="button" id="detectAllBtn">Detectar Info en Seleccionados</button>
          <button type="button" class="api-btn" onclick="retrieveApiInfoBatch()">Recuperar Info de API</button>
          <br><br>
          <form id="infoForm">
            <table id="fileList">
              <thead>
                <tr>
                  <th>Seleccionar</th>
                  <th>Fichero Original</th>
                  <th>Serie/Película</th>
                  <th>Temporada</th>
                  <th>Nº Capítulo</th>
                  <th>Nombre Capítulo</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ficheros as $fichero): ?>
                  <tr>
                    <td><input type="checkbox" class="file-checkbox" value="<?= htmlspecialchars($fichero) ?>"></td>
                    <td class="original-name"><?= htmlspecialchars($fichero) ?></td>
                    <td><input type="text" class="detected-series" value="" style="width:100%;"></td>
                    <td><input type="text" class="detected-season" value="" style="width:100%;"></td>
                    <td><input type="text" class="detected-episode" value="" style="width:100%;"></td>
                    <td><input type="text" class="detected-title" value="" style="width:100%;"></td>
                    <td class="action-buttons">
                      <button type="button" class="detect-btn" onclick="detectFileInfo(this)">Detectar</button>
                      <button type="button" class="api-btn" onclick="retrieveApiInfo(this)">Recuperar Info de API</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </form>
        </div>
        
        <!-- Pestaña 2: Generación de Nombres -->
        <div id="tab2" class="tab">
          <h3>Generación de Nombres</h3>
          <form id="renameNamesForm" action="rename.php" method="post">
            <input type="hidden" name="currentDir" value="<?php echo htmlspecialchars($currentDirectory); ?>">
            <table id="newNameList">
              <thead>
                <tr>
                  <th>Fichero Original</th>
                  <th>Nuevo Nombre Propuesto</th>
                </tr>
              </thead>
              <tbody>
                <!-- Se llenará dinámicamente con los ficheros seleccionados -->
              </tbody>
            </table>
            <br>
            <button type="button" id="generatePatternButton">Generar Nombres</button>
            <button type="submit">Renombrar Ficheros</button>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Sección de Configuración de Tipos de Fichero -->
    <div id="file-types-config" class="file-types-config" style="margin-top:20px; padding:10px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px;">
      <h3>Tipos de Fichero a Listar</h3>
      <p>Actualmente se muestran: <?= implode(', ', VIDEO_EXTENSIONS) ?></p>
      <p><a href="config_editor.php" target="_blank">Editar Configuración</a></p>
    </div>
  </div>
</body>
</html>
