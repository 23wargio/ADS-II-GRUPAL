<?php
require '../conexion/config.php';

// Iniciar sesión si no se ha iniciado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde el parámetro GET
$user_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];

// Obtener los datos del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='alert alert-danger text-center'>Usuario no encontrado.</div>";
    exit();
}

// Función para asignar colores según el rol
function get_role_badge($role) {
    $badges = ['admin' => 'danger', 'manager' => 'primary', 'member' => 'secondary'];
    return $badges[$role] ?? 'light';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($user['nombres']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            width: 80%;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            text-align: center;
        }
        .avatar-xl {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .badge {
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../estructura/header.php'; ?>

    <div class="profile-container">
        <img src="<?= htmlspecialchars($user['foto_perfil']) ?>" 
             alt="Foto de <?= htmlspecialchars($user['nombres']) ?>" 
             class="avatar-xl rounded-circle mb-3">
        <h2><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></h2>
        <h4 class="text-muted">
            <span class="badge bg-<?= get_role_badge($user['role']) ?>">
                <?= ucfirst($user['role']) ?>
            </span>
        </h4>
        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user['celular']) ?></p>

        <div class="mt-3">
            <a href="team.php" class="btn btn-outline-secondary"><i class="fas fa-users"></i> Ver equipo</a>
            <?php if ($_SESSION['user_role'] == 'admin' || $_SESSION['user_id'] == $user['id']): ?>
                <a href="edit_profile.php?id=<?= $user['id'] ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Editar perfil</a>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../estructura/footer.php'; ?>
</body>
</html>
