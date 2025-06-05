<?php
if (session_status() !== PHP_SESSION_ACTIVE) { // Añade esta condición
    session_start();
}
require_once __DIR__ . '/../config/secrets.php';
$users = unserialize(USERS);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (isset($users[$username]) && $users[$username] === $password) {
        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Renombrador de Ficheros</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container" style="max-width:400px; margin:50px auto; padding:20px; background:#fff; border:1px solid #ddd; border-radius:5px;">
        <h2>Login</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <input type="submit" value="Entrar">
        </form>
    </div>
</body>
</html>
