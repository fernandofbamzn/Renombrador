<?php
if (session_status() !== PHP_SESSION_ACTIVE) { // Añade esta condición
    session_start();
}
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
