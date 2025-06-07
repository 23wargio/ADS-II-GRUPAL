<?php
require '../../conexion/config.php';
session_start();

// Mostrar mensaje flash si existe
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$userId = $_SESSION['user_id'];
$userRole = $user['role'];

// Consultar lista de clientes (todos los roles pueden ver)
$stmt = $pdo->query("SELECT * FROM clients ORDER BY created_at DESC");
$clients = $stmt->fetchAll();

// Función para formatear fecha
function formatDate($date) {
    if (!$date) return '-';
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes - Zidkenu</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <?php include '../../estructura/header.php'; ?>

    <?php if ($flash_message): ?>
        <div class="flash-message"><?= htmlspecialchars($flash_message) ?></div>
    <?php endif; ?>

    <h1>Listado de Clientes</h1>

    <div class="section-header">
        <?php if ($userRole === 'admin' || $userRole === 'manager'): ?>
            <a href="create_client.php"><button class="btn-create">Nuevo Cliente</button></a>
        <?php endif; ?>
    </div>

    <div class="container">
        <?php if (empty($clients)): ?>
            <div class="no-projects">
                <h3>No hay clientes registrados</h3>
                <p>Haz clic en "Nuevo Cliente" para agregar uno.</p>
            </div>
        <?php else: ?>
            <div class="project-grid">
                <?php foreach ($clients as $client): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <h3 class="project-name"><?= htmlspecialchars($client['name']) ?></h3>
                        </div>
                        <div class="project-info">
                            <?php if ($client['contact_person']): ?>
                                <div class="info-row"><strong>Contacto:</strong> <?= htmlspecialchars($client['contact_person']) ?></div>
                            <?php endif; ?>
                            <?php if ($client['email']): ?>
                                <div class="info-row"><strong>Email:</strong> <?= htmlspecialchars($client['email']) ?></div>
                            <?php endif; ?>
                            <?php if ($client['phone']): ?>
                                <div class="info-row"><strong>Teléfono:</strong> <?= htmlspecialchars($client['phone']) ?></div>
                            <?php endif; ?>
                            <?php if ($client['address']): ?>
                                <div class="info-row"><strong>Dirección:</strong> <?= htmlspecialchars($client['address']) ?></div>
                            <?php endif; ?>
                            <?php if ($client['tax_id']): ?>
                                <div class="info-row"><strong>RUC/DNI:</strong> <?= htmlspecialchars($client['tax_id']) ?></div>
                            <?php endif; ?>
                            <div class="info-row"><strong>Registrado:</strong> <?= formatDate($client['created_at']) ?></div>
                        </div>
                        <br>
                        <a href="view_client.php?id=<?= $client['id'] ?>" class="btn btn-sm">Ver Cliente</a>

                        <?php if ($userRole === 'admin' || $userRole === 'manager'): ?>
                            <a href="edit_client.php?id=<?= $client['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="eliminar_client.php?id=<?= $client['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?');">Eliminar</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../../estructura/footer.php'; ?>
</body>
</html>
