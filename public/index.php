<?php
// index.php en la raíz: incluye el contenido principal y define DEBUG_MODE en JS
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Renombrador de Ficheros</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include_once 'contenido.php'; ?>
    <script>
      window.DEBUG_MODE = <?= DEBUG_MODE ? 'true' : 'false'; ?>;
      window.EPISODE_DIGITS = <?= EPISODE_DIGITS; ?>;
      window.SEASON_DIGITS = <?= defined('SEASON_DIGITS') ? SEASON_DIGITS : 1; ?>;
      if(window.DEBUG_MODE) {
          console.log("DEBUG_MODE activado");
          console.log("EPISODE_DIGITS: " + window.EPISODE_DIGITS);
          console.log("SEASON_DIGITS: " + window.SEASON_DIGITS);
      }
   </script>
   <script src="scripts.js"></script>
</body>
</html>

