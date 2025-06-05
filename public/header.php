<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
        $_SESSION['user'] = array();
    } 
if (!isset($_SESSION['user'])) {    
    header("Location: login.php");
    exit();
}
?>
<header style="background:#333; color:white; padding:10px;">
  <div style="max-width:1000px; margin:auto; display:flex; justify-content:space-between; align-items:center;">
    <a href="index.php" style="color:white; text-decoration:none; font-size:1.5em;">Renombrador de Ficheros</a>
    <div style="font-size:1em;">
      Bienvenido, <?php echo htmlspecialchars($_SESSION['user']); ?> |
      <a href="logout.php" style="color:white; text-decoration:none;">Logout</a>
    </div>
  </div>
</header>
