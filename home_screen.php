<?php
require 'config.php';
session_start();

if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (!isset($_SESSION['user_id'])) {
   header("Location: login.php");
   exit();
}

// Obtener datos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Verificar si la foto de perfil existe, si no usar la default
$fotoPerfil = file_exists($user['foto_perfil']) ? $user['foto_perfil'] : 'assets/foto_perfil/default.jpg';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Zidkenu</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .avatar, .profile-img {
            border-radius: 50%;
            object-fit: cover;
            width: 40px;
            height: 40px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            margin-top: 20px;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown-content.show {
            display: block;
        }
    </style>
</head>
<body>
    <?php include './estructura/header.php'; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
    <div class="container">
        <h1>Bienvenido, <?= htmlspecialchars($user['nombres']) ?>!</h1>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>DNI:</strong> <?= htmlspecialchars($user['dni']) ?></p>
        <p><strong>Celular:</strong> <?= htmlspecialchars($user['celular']) ?></p>
        <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($user['fecha_nacimiento']) ?></p>
        
        <!-- Foto de perfil grande -->
        <div class="profile-picture">
            <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" class="profile-img">
        </div>
    </div>
    <?php include './estructura/footer.php'; ?>
</body>
</html>