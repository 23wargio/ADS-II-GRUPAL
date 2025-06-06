<?php
require '../../conexion/config.php';
session_start();

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

// Verificar si se proporcionó un ID de tarea
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID de tarea inválido.'];
    header("Location: tasks.php");
    exit();
}

$taskId = $_GET['id'];

// Obtener la tarea
$stmt = $pdo->prepare("SELECT t.*, p.name as project_name, p.manager_id 
                       FROM tasks t 
                       JOIN projects p ON t.project_id = p.id 
                       WHERE t.id = ?");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

if (!$task) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Tarea no encontrada.'];
    header("Location: tasks.php");
    exit();
}

// Verificar permisos para editar la tarea
$canEdit = false;
if ($userRole == 'admin') {
    $canEdit = true;
} elseif ($task['created_by'] == $userId) {
    $canEdit = true;
} elseif ($task['manager_id'] == $userId) {
    $canEdit = true;
}

if (!$canEdit) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para editar esta tarea.'];
    header("Location: tasks.php");
    exit();
}

// Obtener lista de proyectos
$projectQuery = "SELECT id, name FROM projects";
if ($userRole != 'admin') {
    $projectQuery .= " WHERE manager_id = ? OR id IN (SELECT project_id FROM project_team WHERE user_id = ?)";
}

$stmtProjects = $pdo->prepare($projectQuery);
if ($userRole != 'admin') {
    $stmtProjects->execute([$userId, $userId]);
} else {
    $stmtProjects->execute();
}
$projects = $stmtProjects->fetchAll();

// Obtener lista de usuarios para asignar
$userQuery = "SELECT id, nombres, apellidos FROM users";
if ($userRole != 'admin') {
    $userQuery .= " WHERE id IN (
        SELECT DISTINCT u.id FROM users u
        JOIN project_team pt ON u.id = pt.user_id
        WHERE pt.project_id = ?
        UNION
        SELECT id FROM users WHERE id = ?
    )";
}

$stmtUsers = $pdo->prepare($userQuery);
if ($userRole != 'admin') {
    $stmtUsers->execute([$task['project_id'], $userId]);
} else {
    $stmtUsers->execute();
}
$users = $stmtUsers->fetchAll();

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $project_id = $_POST['project_id'];
    $assigned_to = $_POST['assigned_to'] ?: null;
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $progress = $_POST['progress'];
    $due_date = $_POST['due_date'] ?: null;
    
    // Validaciones
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'El título es obligatorio.';
    }
    
    if (empty($project_id)) {
        $errors[] = 'Debe seleccionar un proyecto.';
    }
    
    if ($progress < 0 || $progress > 100) {
        $errors[] = 'El progreso debe estar entre 0 y 100.';
    }
    
    // Verificar que el proyecto existe y el usuario tiene acceso
    $checkProjectStmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $checkProjectStmt->execute([$project_id]);
    $project = $checkProjectStmt->fetch();
    
    if (!$project) {
        $errors[] = 'Proyecto no encontrado.';
    } elseif ($userRole != 'admin' && $project['manager_id'] != $userId) {
        // Verificar si es miembro del proyecto
        $checkMemberStmt = $pdo->prepare("SELECT * FROM project_team WHERE project_id = ? AND user_id = ?");
        $checkMemberStmt->execute([$project_id, $userId]);
        if ($checkMemberStmt->rowCount() == 0) {
            $errors[] = 'No tienes acceso a este proyecto.';
        }
    }
    
    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE tasks SET 
                                        title = ?, 
                                        description = ?, 
                                        project_id = ?, 
                                        assigned_to = ?, 
                                        priority = ?, 
                                        status = ?, 
                                        progress = ?, 
                                        due_date = ?, 
                                        updated_at = NOW() 
                                        WHERE id = ?");
            
            if ($updateStmt->execute([$title, $description, $project_id, $assigned_to, $priority, $status, $progress, $due_date, $taskId])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Tarea actualizada con éxito.'];
                header("Location: tasks.php");
                exit();
            } else {
                $errors[] = 'Error al actualizar la tarea.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error de base de datos: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tarea - Zidkenu</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <?php include '../../estructura/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <div class="page-header">
                <h1>Editar Tarea</h1>
                <p>Proyecto: <?= htmlspecialchars($task['project_name']) ?></p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label for="title">Título <span class="required">*</span></label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" placeholder="Descripción detallada de la tarea..."><?= htmlspecialchars($task['description']) ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="project_id">Proyecto <span class="required">*</span></label>
                        <select id="project_id" name="project_id" required>
                            <option value="">Seleccionar proyecto</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>" <?= $task['project_id'] == $project['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="assigned_to">Asignar a</label>
                        <select id="assigned_to" name="assigned_to">
                            <option value="">Sin asignar</option>
                            <?php foreach ($users as $assignUser): ?>
                                <option value="<?= $assignUser['id'] ?>" <?= $task['assigned_to'] == $assignUser['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($assignUser['nombres'] . ' ' . $assignUser['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="priority">Prioridad</label>
                        <select id="priority" name="priority">
                            <option value="low" <?= $task['priority'] == 'low' ? 'selected' : '' ?>>Baja</option>
                            <option value="medium" <?= $task['priority'] == 'medium' ? 'selected' : '' ?>>Media</option>
                            <option value="high" <?= $task['priority'] == 'high' ? 'selected' : '' ?>>Alta</option>
                            <option value="critical" <?= $task['priority'] == 'critical' ? 'selected' : '' ?>>Crítica</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select id="status" name="status">
                            <option value="not_started" <?= $task['status'] == 'not_started' ? 'selected' : '' ?>>No iniciada</option>
                            <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                            <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completada</option>
                            <option value="deferred" <?= $task['status'] == 'deferred' ? 'selected' : '' ?>>Pospuesta</option>
                            <option value="cancelled" <?= $task['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="progress">Progreso (%)</label>
                        <input type="number" id="progress" name="progress" min="0" max="100" value="<?= $task['progress'] ?>" oninput="updateProgressBar(this.value)">
                        <div class="progress-preview">
                            <div class="progress-bar-container">
                                <div id="progress-bar" class="progress-bar" style="width: <?= $task['progress'] ?>%;"></div>
                            </div>
                            <small id="progress-text"><?= $task['progress'] ?>% completado</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Fecha límite</label>
                        <input type="date" id="due_date" name="due_date" value="<?= $task['due_date'] ?>">
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Actualizar Tarea</button>
                    <a href="tasks.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../../estructura/footer.php'; ?>
    
    <script>
        function updateProgressBar(value) {
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            
            progressBar.style.width = value + '%';
            progressText.textContent = value + '% completado';
            
            // Cambiar color según el progreso
            if (value < 30) {
                progressBar.style.backgroundColor = '#dc3545'; // Rojo
            } else if (value < 70) {
                progressBar.style.backgroundColor = '#ffc107'; // Amarillo
            } else {
                progressBar.style.backgroundColor = '#28a745'; // Verde
            }
        }
        
        // Inicializar la barra de progreso al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const progressInput = document.getElementById('progress');
            updateProgressBar(progressInput.value);
        });
        
        // Auto-actualizar progreso basado en el estado
        document.getElementById('status').addEventListener('change', function() {
            const progressInput = document.getElementById('progress');
            
            switch(this.value) {
                case 'not_started':
                    progressInput.value = 0;
                    break;
                case 'completed':
                    progressInput.value = 100;
                    break;
                case 'cancelled':
                    // No cambiar el progreso
                    break;
            }
            
            updateProgressBar(progressInput.value);
        });
    </script>
</body>
</html>