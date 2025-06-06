<?php
require '../../conexion/config.php';
session_start();

// Mostrar mensaje flash si existe
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Eliminar el mensaje después de mostrarlo
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

// Obtener proyectos según el rol del usuario
$userId = $_SESSION['user_id'];
$userRole = $user['role'];

// Consulta de proyectos según el rol
if ($userRole == 'admin') {
    // Los administradores ven todos los proyectos
    $stmt = $pdo->query("SELECT p.*, c.name as client_name, u.nombres as manager_name, u.apellidos as manager_lastname 
                        FROM projects p 
                        LEFT JOIN clients c ON p.client_id = c.id 
                        LEFT JOIN users u ON p.manager_id = u.id 
                        ORDER BY p.start_date DESC");
    $projects = $stmt->fetchAll();
} elseif ($userRole == 'manager') {
    // Los managers ven los proyectos que gestionan
    $stmt = $pdo->prepare("SELECT p.*, c.name as client_name, u.nombres as manager_name, u.apellidos as manager_lastname 
                        FROM projects p 
                        LEFT JOIN clients c ON p.client_id = c.id 
                        LEFT JOIN users u ON p.manager_id = u.id 
                        WHERE p.manager_id = ? 
                        ORDER BY p.start_date DESC");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();
} else {
    // Los miembros ven los proyectos en los que están asignados
    $stmt = $pdo->prepare("SELECT p.*, c.name as client_name, u.nombres as manager_name, u.apellidos as manager_lastname 
                        FROM projects p 
                        LEFT JOIN clients c ON p.client_id = c.id 
                        LEFT JOIN users u ON p.manager_id = u.id 
                        INNER JOIN project_team pt ON p.id = pt.project_id 
                        WHERE pt.user_id = ? 
                        ORDER BY p.start_date DESC");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();
}

// Función para convertir status a texto en español y clase CSS
function getStatusInfo($status) {
    switch ($status) {
        case 'planning':
            return ['text' => 'Planificación', 'class' => 'status-planning'];
        case 'in_progress':
            return ['text' => 'En progreso', 'class' => 'status-progress'];
        case 'on_hold':
            return ['text' => 'En espera', 'class' => 'status-hold'];
        case 'completed':
            return ['text' => 'Completado', 'class' => 'status-completed'];
        case 'cancelled':
            return ['text' => 'Cancelado', 'class' => 'status-cancelled'];
        default:
            return ['text' => 'Desconocido', 'class' => ''];
    }
}

// Función para convertir prioridad a texto en español y clase CSS
function getPriorityInfo($priority) {
    switch ($priority) {
        case 'low':
            return ['text' => 'Baja', 'class' => 'priority-low'];
        case 'medium':
            return ['text' => 'Media', 'class' => 'priority-medium'];
        case 'high':
            return ['text' => 'Alta', 'class' => 'priority-high'];
        case 'critical':
            return ['text' => 'Crítica', 'class' => 'priority-critical'];
        default:
            return ['text' => 'Desconocida', 'class' => ''];
    }
}

// Función para formatear fecha
function formatDate($date) {
    if (!$date) return '-';
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

// Obtener lista de clientes para el formulario
$clients = [];
if ($userRole == 'admin' || $userRole == 'manager') {
    $stmt = $pdo->query("SELECT id, name FROM clients ORDER BY name");
    $clients = $stmt->fetchAll();
}

// Obtener lista de managers para el formulario
$managers = [];
if ($userRole == 'admin') {
    $stmt = $pdo->query("SELECT id, nombres, apellidos FROM users WHERE role = 'admin' OR role = 'manager' ORDER BY nombres");
    $managers = $stmt->fetchAll();
} else {
    // Si es manager, solo se puede asignar a sí mismo
    $managers[] = $user;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos - Zidkenu</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <?php include '../../estructura/header.php'; ?>
    <div id="flash-message-container" style="position: fixed; top: 200px; right: 200px; z-index: 1000;"></div>
    <h1>Bienvenido a Proyectos</h1>
    <div class="section-header">
            <?php if ($userRole == 'admin' || $userRole == 'manager'): ?>
            <a href="create_project.php"><button id="btn-new-project" class="btn-create">Nuevo Proyecto</button></a>
            <?php endif; ?>
        </div>
    <div class="container">
        <?php if (empty($projects)): ?>
        <div class="no-projects">
            <h3>No hay proyectos disponibles</h3>
            <p>Aún no tienes proyectos asignados o no se han creado proyectos en el sistema.</p>
            <?php if ($userRole == 'admin' || $userRole == 'manager'): ?>
            <p>Haz clic en "Nuevo Proyecto" para crear uno.</p>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="project-grid">
            <?php foreach ($projects as $project): 
                $statusInfo = getStatusInfo($project['status']);
                $priorityInfo = getPriorityInfo($project['priority']);
            ?>
            <div class="project-card">
                <div class="project-header">
                    <h3 class="project-name"><?= htmlspecialchars($project['name']) ?></h3>
                    <div>
                        <span class="project-status <?= $statusInfo['class'] ?>"><?= $statusInfo['text'] ?></span>
                    </div>
                </div>
                <p class="project-description"><?= htmlspecialchars($project['description'] ?: 'Sin descripción') ?></p>
                <div>
                    <span class="project-priority <?= $priorityInfo['class'] ?>"><?= $priorityInfo['text'] ?></span>
                </div>
                <div class="project-info">
                    <div class="info-row">
                        <span class="info-label">Cliente:</span>
                        <span class="info-value"><?= htmlspecialchars($project['client_name'] ?: 'N/A') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Manager:</span>
                        <span class="info-value"><?= htmlspecialchars($project['manager_name'] . ' ' . $project['manager_lastname']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha inicio:</span>
                        <span class="info-value"><?= formatDate($project['start_date']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha fin:</span>
                        <span class="info-value"><?= formatDate($project['end_date']) ?></span>
                    </div>
                    <?php if ($project['budget']): ?>
                    <div class="info-row">
                        <span class="info-label">Presupuesto:</span>
                        <span class="info-value">S/. <?= number_format($project['budget'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="project-footer">
                    <a href="edit_project.php?id=<?= $project['id'] ?>">Editar Proyecto</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php include '../../estructura/footer.php'; ?>
</body>
</html>