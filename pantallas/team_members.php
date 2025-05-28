<?php
require '../conexion/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener datos del usuario para el header
if (isset($_SESSION['user_id'])) {
    $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
} else {
    $user = null;
}

if (!function_exists('redirect')) {
    function redirect($location) {
        header("Location: $location");
        exit();
    }
}

if (!function_exists('get_flash_message')) {
    function get_flash_message() {
        $flash_message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        return $flash_message;
    }
}

if (!function_exists('set_flash_message')) {
    function set_flash_message($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$team_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$team_id) {
    set_flash_message('error', 'ID de equipo no válido');
    redirect('team.php');
}

try {
    $stmt = $pdo->prepare("SELECT role FROM team_members WHERE team_id = ? AND user_id = ?");
    $stmt->execute([$team_id, $_SESSION['user_id']]);
    $user_membership = $stmt->fetch();

    if (!$user_membership && $_SESSION['user_role'] != 'admin') {
        set_flash_message('error', 'No tienes permisos para ver este equipo');
        redirect('team.php');
    }
} catch (PDOException $e) {
    set_flash_message('error', 'Error al verificar permisos');
    redirect('team.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'remove_member' && ($user_membership['role'] == 'leader' || $_SESSION['user_role'] == 'admin')) {
        $member_id = (int)$_POST['member_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
            $stmt->execute([$team_id, $member_id]);

            set_flash_message('success', 'Miembro eliminado del equipo');
        } catch (PDOException $e) {
            set_flash_message('error', 'Error al eliminar miembro');
        }
        redirect("team_members.php?id=$team_id");
    }
}

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.nombres as creator_name, u.apellidos as creator_lastname
        FROM teams t
        LEFT JOIN users u ON t.created_by = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch();

    if (!$team) {
        set_flash_message('error', 'Equipo no encontrado');
        redirect('team.php');
    }
} catch (PDOException $e) {
    set_flash_message('error', 'Error al cargar información del equipo');
    redirect('team.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombres, u.apellidos, u.email, u.celular, u.foto_perfil,
               tm.role, tm.joined_at
        FROM users u
        JOIN team_members tm ON u.id = tm.user_id
        WHERE tm.team_id = ?
        ORDER BY 
            CASE tm.role 
                WHEN 'leader' THEN 1 
                WHEN 'member' THEN 2 
            END,
            tm.joined_at ASC
    ");
    $stmt->execute([$team_id]);
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    $members = [];
    set_flash_message('error', 'Error al cargar miembros del equipo');
}

$flash_message = get_flash_message();

function get_role_badge($role) {
    $badges = ['leader' => 'primary', 'member' => 'secondary'];
    return $badges[$role] ?? 'light';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Miembros del Equipo - <?= htmlspecialchars($team['name']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../estructura/header.php'; ?>

    <?php if ($flash_message): ?>
        <div class="alert alert-<?= $flash_message['type'] ?> text-center">
            <?= htmlspecialchars($flash_message['message']) ?>
        </div>
    <?php endif; ?>

    <div class="container py-4 team-container">
        <!-- BOTÓN PARA VOLVER -->
        <a href="team.php" class="btn btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Volver
        </a>

        <h2 class="mb-4">Miembros del equipo: <?= htmlspecialchars($team['name']) ?></h2>
        <p><strong>Creado por:</strong> <?= htmlspecialchars($team['creator_name'] . ' ' . $team['creator_lastname']) ?></p>

        <div class="member-grid">
            <?php foreach ($members as $member): ?>
                <div class="card member-card <?= 'role-' . $member['role'] ?>">
                    <div class="card-body d-flex align-items-center">
                        <img src="<?= htmlspecialchars($member['foto_perfil'] ?? '../img/default-avatar.png') ?>" 
                             class="rounded-circle avatar-lg me-3" alt="Avatar">
                        <div>
                            <h5 class="card-title mb-0"><?= htmlspecialchars($member['nombres'] . ' ' . $member['apellidos']) ?></h5>
                            <p class="mb-1 text-muted"><?= htmlspecialchars($member['email']) ?></p>
                            <span class="badge bg-<?= get_role_badge($member['role']) ?>">
                                <?= ucfirst($member['role']) ?>
                            </span>
                        </div>
                        <?php if (($user_membership['role'] ?? '') == 'leader' || $_SESSION['user_role'] == 'admin'): ?>
                            <?php if ($member['id'] != $_SESSION['user_id']): ?>
                                <form action="" method="POST" class="ms-auto">
                                    <input type="hidden" name="action" value="remove_member">
                                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este miembro?')">
                                        <i class="fas fa-user-minus"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include '../estructura/footer.php'; ?>
</body>
</html>
