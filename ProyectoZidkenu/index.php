<?php
// index.php - Página de inicio con redirección a login

// Configuración de cabeceras para evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirección después de 3 segundos (opcional)
header("refresh:3;url=login.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Proyecto Zidkenu</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg,rgb(83, 91, 117),rgb(3, 5, 119));
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
            text-align: center;
        }
        .welcome-container {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 90%;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .redirect-message {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <h1>¡Bienvenido a Proyecto Zidkenu!</h1>
        <p>Estamos cargando tu experiencia. Serás redirigido automáticamente a la página de inicio de sesión.</p>
        
        <div class="loader"></div>
        
        <p class="redirect-message">Si no eres redirigido en unos segundos, 
            <a href="login.php" style="color: #fff; text-decoration: underline;">haz clic aquí</a>.
        </p>
    </div>
</body>
</html>