<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}require_once __DIR__ . '/../conexion/config.php'; // Ajusta según tu estructura

$avatarFile = $_SESSION['user_avatar'] ?? $user['foto_perfil'] ?? 'default.jpg';
$localPath = __DIR__ . '/' . $avatarFile;

if (!file_exists($localPath)) {
    echo "Archivo no encontrado: " . $localPath;
    $avatarFile = 'default.jpg'; // Usar imagen por defecto
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Zidkenu - Soluciones Empresariales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <img src="<?= BASE_URL ?>assets/img/zidkenu_logo.png" alt="Zidkenu Logo" class="logo-img">
            <div>
                <span class="logo-slogan">Soluciones Empresariales</span>
            </div>
        </div>
        
        <nav class="nav-links">
            <a href="<?= BASE_URL ?>./pantallas/home_screen.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="<?= BASE_URL ?>./pantallas/projects.php"><i class="fas fa-project-diagram"></i> Proyectos</a>
            <a href="<?= BASE_URL ?>./pantallas/tareas/tasks.php"><i class="fas fa-tasks"></i> Tareas</a>
            <a href="<?= BASE_URL ?>./pantallas/team.php"><i class="fas fa-users"></i> Equipo</a>
            <a href="<?= BASE_URL ?>./pantallas/reports.php"><i class="fas fa-chart-bar"></i> Reportes</a>
        </nav>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-menu">
            <span class="username"><?= htmlspecialchars($user['nombres'] ?? 'Usuario') ?></span>
            <div class="dropdown">
                <img src="<?= BASE_URL . '/' . htmlspecialchars($_SESSION['user_avatar'] ?? $user['foto_perfil'] ?? 'default.jpg') ?>" alt="Avatar" class="avatar" id="avatarBtn">

                <div class="dropdown-content" id="userDropdown">
                    <p style="padding: 12px 16px; margin: 0; color: var(--dark-color);"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <a href="<?= BASE_URL ?>pantallas/edit_perfil.php"><i class="fas fa-user-edit" style="margin-right: 8px;"></i> Editar perfil</a>
                    <a href="<?= BASE_URL ?>logout/logout.php"><i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Cerrar sesión</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </header>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarBtn = document.querySelector('.avatar');
            const usernameBtn = document.querySelector('.username');
            const dropdown = document.querySelector('.dropdown-content');

            // Función para toggle del dropdown
            function toggleDropdown() {
                dropdown.classList.toggle('show');
            }

            // Event listeners
            if (avatarBtn) {
                avatarBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDropdown();
                });
            }

            if (usernameBtn) {
                usernameBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDropdown();
                });
            }

            // Cerrar dropdown al hacer click fuera
            document.addEventListener('click', function() {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            });

            // Evitar que se cierre al hacer click dentro del dropdown
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
    <script src="./javascript/main.js"></script>
    <main class="main-content">