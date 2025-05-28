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

// Determinar las tareas que debe ver el usuario según su rol
$userId = $_SESSION['user_id'];
$userRole = $user['role'];

// Filtros para tareas
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$project_filter = isset($_GET['project_id']) ? $_GET['project_id'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';

// Construir la consulta base
$query = "SELECT t.*, p.name as project_name, u_assigned.nombres as assigned_name, 
          u_assigned.apellidos as assigned_lastname, u_created.nombres as created_name, 
          u_created.apellidos as created_lastname 
          FROM tasks t 
          JOIN projects p ON t.project_id = p.id 
          LEFT JOIN users u_assigned ON t.assigned_to = u_assigned.id
          JOIN users u_created ON t.created_by = u_created.id
          WHERE 1=1";

$params = [];

// Aplicar filtros según el rol
if ($userRole == 'admin') {
    // Admin ve todas las tareas
} elseif ($userRole == 'manager') {
    // Manager ve tareas de sus proyectos
    $query .= " AND (p.manager_id = ? OR t.created_by = ? OR t.assigned_to = ?)";
    $params = array_merge($params, [$userId, $userId, $userId]);
} else {
    // Miembro ve solo sus tareas asignadas
    $query .= " AND t.assigned_to = ?";
    $params[] = $userId;
}

// Aplicar filtros adicionales
if ($status_filter) {
    $query .= " AND t.status = ?";
    $params[] = $status_filter;
}

if ($project_filter) {
    $query .= " AND t.project_id = ?";
    $params[] = $project_filter;
}

if ($priority_filter) {
    $query .= " AND t.priority = ?";
    $params[] = $priority_filter;
}

$query .= " ORDER BY t.due_date ASC, t.priority DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Obtener lista de proyectos para el filtro
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

// Manejar la actualización del estado de la tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];
    $new_progress = $_POST['progress'];
    
    // Verificar que el usuario tenga permiso para actualizar esta tarea
    $canUpdate = false;
    
    if ($userRole == 'admin') {
        $canUpdate = true;
    } else {
        $checkStmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $checkStmt->execute([$task_id]);
        $task = $checkStmt->fetch();
        
        if ($task && ($task['assigned_to'] == $userId || $task['created_by'] == $userId)) {
            $canUpdate = true;
        } else {
            // Verificar si es manager del proyecto
            $checkManagerStmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND manager_id = ?");
            $checkManagerStmt->execute([$task['project_id'], $userId]);
            if ($checkManagerStmt->rowCount() > 0) {
                $canUpdate = true;
            }
        }
    }
    
    if ($canUpdate) {
        $updateStmt = $pdo->prepare("UPDATE tasks SET status = ?, progress = ?, updated_at = NOW() WHERE id = ?");
        if ($updateStmt->execute([$new_status, $new_progress, $task_id])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Tarea actualizada con éxito.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al actualizar la tarea.'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para actualizar esta tarea.'];
    }
    
    header("Location: tasks.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas - Zidkenu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../estructura/header.php'; ?>
    <div id="flash-message-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>
    
    <div class="container">
        <h1>Gestión de Tareas</h1>
        
        <!-- Filtros -->
        <div class="filters">
            <form action="" method="GET" class="filter-form">
                <select name="status" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="not_started" <?= $status_filter == 'not_started' ? 'selected' : '' ?>>No iniciada</option>
                    <option value="in_progress" <?= $status_filter == 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                    <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completada</option>
                    <option value="deferred" <?= $status_filter == 'deferred' ? 'selected' : '' ?>>Pospuesta</option>
                    <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
                
                <select name="project_id" class="filter-select">
                    <option value="">Todos los proyectos</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id'] ?>" <?= $project_filter == $project['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($project['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="priority" class="filter-select">
                    <option value="">Todas las prioridades</option>
                    <option value="low" <?= $priority_filter == 'low' ? 'selected' : '' ?>>Baja</option>
                    <option value="medium" <?= $priority_filter == 'medium' ? 'selected' : '' ?>>Media</option>
                    <option value="high" <?= $priority_filter == 'high' ? 'selected' : '' ?>>Alta</option>
                    <option value="critical" <?= $priority_filter == 'critical' ? 'selected' : '' ?>>Crítica</option>
                </select>
                
                <button type="submit" class="filter-button">Filtrar</button>
                <a href="tasks.php" class="filter-button" style="text-align: center ;text-decoration: none; display: inline-block; background-color: #6c757d;">Limpiar</a>
                
                <?php if ($userRole === 'admin' || $userRole === 'manager'): ?>
                <a href="create_task.php" class="filter-button" style="text-align: center; text-decoration: none; display: inline-block; background-color: #28a745;">Nueva Tarea</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Lista de Tareas -->
        <?php if (count($tasks) > 0): ?>
            <div class="task-list">
                <?php foreach ($tasks as $task): ?>
                <div class="task-card">
                    <div class="task-header">
                        <div>
                            <h3 class="task-title"><?= htmlspecialchars($task['title']) ?></h3>
                            <div class="task-project">Proyecto: <?= htmlspecialchars($task['project_name']) ?></div>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $task['status'] ?>"><?= getStatusName($task['status']) ?></span>
                            <span class="priority-badge priority-<?= $task['priority'] ?>"><?= getPriorityName($task['priority']) ?></span>
                        </div>
                    </div>
                    
                    <div class="task-meta">
                        <div><strong>Asignado a:</strong> <?= $task['assigned_to'] ? htmlspecialchars($task['assigned_name'] . ' ' . $task['assigned_lastname']) : 'Sin asignar' ?></div>
                        <div><strong>Creado por:</strong> <?= htmlspecialchars($task['created_name'] . ' ' . $task['created_lastname']) ?></div>
                        <div><strong>Fecha límite:</strong> <?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : 'Sin fecha' ?></div>
                    </div>
                    
                    <?php if ($task['description']): ?>
                        <div class="task-description">
                            <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Barra de progreso -->
                    <div>
                        <div><strong>Progreso:</strong> <?= $task['progress'] ?>%</div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?= $task['progress'] ?>%;"></div>
                        </div>
                    </div>
                    
                    <!-- Actualizar estado y progreso -->
                    <?php 
                    $canUpdateTask = $userRole == 'admin' || 
                                    $task['assigned_to'] == $userId || 
                                    $task['created_by'] == $userId;
                    if ($canUpdateTask): 
                    ?>
                        <form action="" method="POST" class="update-status-form">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="update_task" value="1">
                            
                            <label for="status_<?= $task['id'] ?>">Estado:</label>
                            <select name="status" id="status_<?= $task['id'] ?>">
                                <option value="not_started" <?= $task['status'] == 'not_started' ? 'selected' : '' ?>>No iniciada</option>
                                <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                                <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completada</option>
                                <option value="deferred" <?= $task['status'] == 'deferred' ? 'selected' : '' ?>>Pospuesta</option>
                                <option value="cancelled" <?= $task['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                            
                            <label for="progress_<?= $task['id'] ?>">Progreso:</label>
                            <input type="number" name="progress" id="progress_<?= $task['id'] ?>" min="0" max="100" value="<?= $task['progress'] ?>" style="width: 60px;">%
                            
                            <button type="submit">Actualizar</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($userRole === 'admin' || $task['created_by'] == $userId): ?>
                        <div class="task-actions" style="display: flex; gap: 10px; align-items: center;">
                            <a href="edit_task.php?id=<?= $task['id'] ?>" class="edit-btn" style="text-decoration: none; display: inline-block;">Editar</a>
                            - <?php if ($userRole === 'admin' || $userRole === 'manager'): ?>
                                <a href="eliminar_task.php?id=<?= $task['id'] ?>" 
                                class="delete-btn" 
                                style="text-decoration: none; display: inline-block;"
                                onclick="return confirm('¿Estás seguro de que quieres eliminar esta tarea?');">
                                Eliminar
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-tasks">
                <p>No hay tareas disponibles con los filtros seleccionados.</p>
            </div>
        <?php endif; ?>
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

<?php
// Funciones auxiliares
function getStatusName($status) {
    $statusNames = [
        'not_started' => 'No iniciada',
        'in_progress' => 'En progreso',
        'completed' => 'Completada',
        'deferred' => 'Pospuesta',
        'cancelled' => 'Cancelada'
    ];
    
    return isset($statusNames[$status]) ? $statusNames[$status] : $status;
}

function getPriorityName($priority) {
    $priorityNames = [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'critical' => 'Crítica'
    ];
    
    return isset($priorityNames[$priority]) ? $priorityNames[$priority] : $priority;
}
?>
