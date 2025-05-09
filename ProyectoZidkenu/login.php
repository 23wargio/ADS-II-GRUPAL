<?php
require './conexion/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $username = $stmt->fetch();

    if ($username && password_verify($password, $username['password'])) {
        $_SESSION['user_id'] = $username['id'];
        header("Location: ./pantallas/home_screen.php");
        exit();
    } else {
        echo "<p class='error'>Invalid credentials</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zidkenu</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="container">
        <h1>Iniciar sesión en ZIDKENU</h1>
        <a href="#" class="oauth-btn"><img src="./assets/img/google.png" alt="Google">Iniciar sesión con Google</a>
        <form method="POST">
            <label for="username">Usuario</label>
            <input type="text" name="username" id="username" placeholder="Usuario" required>

            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" placeholder="Contraseña" required>

            <a href="#">¿Olvidó su contraseña?</a>
            <button type="submit">Iniciar sesión</button>
        </form>
        <p>No tienes una cuenta? <a href="./pantallas/register.php">Regístrate aquí</a></p>
    </div>
    <script src="./javascript/main.js"></script>
</body>
</html>