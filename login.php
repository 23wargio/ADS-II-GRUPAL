<?php
require './conexion/config.php';
session_start();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: ./pantallas/home_screen.php");
        exit();
    } else {
        $error_message = "Usuario o contraseña incorrectos. Por favor, inténtelo de nuevo.";
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
    <style>
        .error-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 400px;
            animation: slideIn 0.5s, fadeOut 0.5s 4.5s forwards;
            z-index: 1000;
        }
        
        .error-alert .close-btn {
            background: none;
            border: none;
            color: #c62828;
            font-size: 18px;
            cursor: pointer;
            margin-left: 15px;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body>
    <?php if (!empty($error_message)): ?>
    <div class="error-alert" id="errorAlert">
        <span><?php echo htmlspecialchars($error_message); ?></span>
        <button class="close-btn" onclick="document.getElementById('errorAlert').style.display='none'">&times;</button>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <div>
            <img class="logo" src="./assets/img/zidkenu_logov2.jfif" alt="">
        </div>
        <h1>Iniciar sesión en ZIDKENU</h1>
        <a href="#" class="oauth-btn"><img src="./assets/img/google.png" alt="Google">Iniciar sesión con Google</a>
        <form method="POST">
            <label for="username">Usuario</label>
            <input type="text" name="username" id="username" placeholder="Usuario" required>

            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" placeholder="Contraseña" required>

            <a href="./pantallas/olvido_contraseña.php">¿Olvidó su contraseña?</a>
            <button type="submit">Iniciar sesión</button>
        </form>
        <p>No tienes una cuenta? <a href="./pantallas/register.php">Regístrate aquí</a></p>
    </div>
    <script src="./javascript/main.js"></script>
    <script>
        // Cierra automáticamente la alerta después de 5 segundos
        setTimeout(() => {
            const alert = document.getElementById('errorAlert');
            if (alert) alert.style.display = 'none';
        }, 5000);
    </script>
</body>
</html>
