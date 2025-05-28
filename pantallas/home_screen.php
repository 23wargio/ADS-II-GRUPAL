<?php
require '../conexion/config.php';
session_start();

// Mostrar mensaje flash si existe
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Eliminar el mensaje después de mostrarlo
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
    <link rel="stylesheet" href="../css/style.css">
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
    <?php include '../estructura/header.php'; ?>
    <div id="flash-message-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>
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
    <?php include '../estructura/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($flash_message): ?>
            showFlashMessage('<?= $flash_message['type'] ?>', '<?= addslashes($flash_message['message']) ?>');
            <?php endif; ?>
        });

        function showFlashMessage(type, message) {
            const container = document.getElementById('flash-message-container');
            const messageDiv = document.createElement('div');
            
            // Estilos base
            messageDiv.style.padding = '15px 20px';
            messageDiv.style.marginBottom = '10px';
            messageDiv.style.borderRadius = '4px';
            messageDiv.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            messageDiv.style.color = 'white';
            messageDiv.style.display = 'flex';
            messageDiv.style.alignItems = 'center';
            messageDiv.style.justifyContent = 'space-between';
            messageDiv.style.minWidth = '300px';
            messageDiv.style.maxWidth = '400px';
            messageDiv.style.opacity = '0';
            messageDiv.style.transform = 'translateX(100%)';
            messageDiv.style.transition = 'all 0.3s ease';
            
            // Estilos según tipo
            if (type === 'success') {
                messageDiv.style.backgroundColor = '#28a745';
            } else if (type === 'error') {
                messageDiv.style.backgroundColor = '#dc3545';
            } else {
                messageDiv.style.backgroundColor = '#17a2b8';
            }
            
            // Contenido del mensaje
            messageDiv.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: 10px;">
                    &times;
                </button>
            `;
            
            container.appendChild(messageDiv);
            
            // Animación de entrada
            setTimeout(() => {
                messageDiv.style.opacity = '1';
                messageDiv.style.transform = 'translateX(0)';
            }, 10);
            
            // Auto-eliminación después de 5 segundos
            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => {
                    messageDiv.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>