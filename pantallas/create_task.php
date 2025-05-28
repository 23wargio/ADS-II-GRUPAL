<?php
require '../conexion/config.php';
session_start();

// Función para redireccionar
function redirect($location) {
    header("Location: $location");
    exit();
}

// Función para establecer mensaje flash
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Obtener datos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Verificar permisos (solo admin y manager pueden crear tareas)
if ($user['role'] !== 'admin' && $user['role'] !== 'manager') {
    set_flash_message('error', 'No tienes permisos para crear tareas.');
    redirect('tasks.php');
}

$userId = $_SESSION['user_id'];
$userRole = $user['role'];

// Obtener proyectos según el rol del usuario
if ($userRole == 'admin') {
    $projectStmt = $pdo->prepare("SELECT id, name FROM projects ORDER BY name");
    $projectStmt->execute();
} else {
    // Manager solo ve sus proyectos
    $projectStmt = $pdo->prepare("SELECT id, name FROM projects WHERE manager_id = ? ORDER BY name");
    $projectStmt->execute([$userId]);
}
$projects = $projectStmt->fetchAll();

// Obtener usuarios para asignar tareas
if ($userRole == 'admin') {
    $userStmt = $pdo->prepare("SELECT id, nombres, apellidos FROM users ORDER BY nombres, apellidos");
    $userStmt->execute();
} else {
    // Manager solo puede asignar a miembros de sus proyectos
    $userStmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.nombres, u.apellidos 
        FROM users u
        LEFT JOIN project_team pt ON u.id = pt.user_id
        LEFT JOIN projects p ON pt.project_id = p.id
        WHERE u.id = ? OR p.manager_id = ?
        ORDER BY u.nombres, u.apellidos
    ");
    $userStmt->execute([$userId, $userId]);
}
$users = $userStmt->fetchAll();

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $project_id = $_POST['project_id'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    
    $errors = [];
    
    // Validaciones
    if (empty($title)) {
        $errors[] = 'El título es obligatorio.';
    }
    
    if (empty($project_id)) {
        $errors[] = 'Debe seleccionar un proyecto.';
    } else {
        // Verificar que el usuario tenga acceso al proyecto
        if ($userRole == 'admin') {
            $checkProject = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
            $checkProject->execute([$project_id]);
        } else {
            $checkProject = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND manager_id = ?");
            $checkProject->execute([$project_id, $userId]);
        }
        
        if (!$checkProject->fetch()) {
            $errors[] = 'No tienes permisos para crear tareas en este proyecto.';
        }
    }
    
    if ($assigned_to) {
        // Verificar que el usuario asignado existe y el manager tenga permisos para asignarlo
        if ($userRole == 'admin') {
            $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $checkUser->execute([$assigned_to]);
        } else {
            $checkUser = $pdo->prepare("
                SELECT DISTINCT u.id 
                FROM users u
                LEFT JOIN project_team pt ON u.id = pt.user_id
                LEFT JOIN projects p ON pt.project_id = p.id
                WHERE u.id = ? AND (u.id = ? OR p.manager_id = ?)
            ");
            $checkUser->execute([$assigned_to, $userId, $userId]);
        }
        
        if (!$checkUser->fetch()) {
            $errors[] = 'El usuario seleccionado no es válido.';
        }
    }
    
    if ($due_date && strtotime($due_date) < strtotime('today')) {
        $errors[] = 'La fecha límite no puede ser anterior a hoy.';
    }
    
    // Si no hay errores, crear la tarea
    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO tasks (title, description, project_id, assigned_to, priority, due_date, created_by, status, progress, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'not_started', 0, NOW(), NOW())
            ");
            
            $success = $insertStmt->execute([
                $title,
                $description,
                $project_id,
                $assigned_to ?: null,
                $priority,
                $due_date ?: null,
                $userId
            ]);
            
            if ($success) {
                set_flash_message('success', 'Tarea creada exitosamente.');
                redirect('tasks.php');
            } else {
                $errors[] = 'Error al crear la tarea. Inténtalo de nuevo.';
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
    <title>Crear Tarea - Zidkenu</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../estructura/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <div class="d-flex align-items-center mb-4">
                <a href="tasks.php" class="btn btn-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <h1><i class="fas fa-plus-circle"></i> Crear Nueva Tarea</h1>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Errores encontrados:</h5>
                    <ul style="margin-bottom: 0;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i> Título de la Tarea *
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                           required
                           placeholder="Ingresa el título de la tarea">
                </div>
                
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Descripción
                    </label>
                    <textarea id="description" 
                              name="description" 
                              placeholder="Describe los detalles de la tarea..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="project_id">
                            <i class="fas fa-project-diagram"></i> Proyecto *
                        </label>
                        <select id="project_id" name="project_id" required>
                            <option value="">Selecciona un proyecto</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>" 
                                        <?= (($_POST['project_id'] ?? '') == $project['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="assigned_to">
                            <i class="fas fa-user-tag"></i> Asignar a
                        </label>
                        <select id="assigned_to" name="assigned_to">
                            <option value="">Sin asignar</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" 
                                        <?= (($_POST['assigned_to'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="priority">
                            <i class="fas fa-exclamation"></i> Prioridad
                        </label>
                        <select id="priority" name="priority">
                            <option value="low" <?= (($_POST['priority'] ?? 'medium') == 'low') ? 'selected' : '' ?>>
                                <span class="priority-indicator priority-low"></span> Baja
                            </option>
                            <option value="medium" <?= (($_POST['priority'] ?? 'medium') == 'medium') ? 'selected' : '' ?>>
                                <span class="priority-indicator priority-medium"></span> Media
                            </option>
                            <option value="high" <?= (($_POST['priority'] ?? 'medium') == 'high') ? 'selected' : '' ?>>
                                <span class="priority-indicator priority-high"></span> Alta
                            </option>
                            <option value="critical" <?= (($_POST['priority'] ?? 'medium') == 'critical') ? 'selected' : '' ?>>
                                <span class="priority-indicator priority-critical"></span> Crítica
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">
                            <i class="fas fa-calendar-alt"></i> Fecha Límite
                        </label>
                        <input type="date" 
                               id="due_date" 
                               name="due_date" 
                               value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="tasks.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Tarea
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../estructura/footer.php'; ?>
    
    <script>
        // Validación del formulario en el cliente
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const project = document.getElementById('project_id').value;
            
            if (!title) {
                alert('El título es obligatorio.');
                e.preventDefault();
                return;
            }
            
            if (!project) {
                alert('Debe seleccionar un proyecto.');
                e.preventDefault();
                return;
            }
        });
        
        // Mejorar la visualización de prioridades en el select
        document.getElementById('priority').addEventListener('change', function() {
            updatePrioritySelect(this);
        });
        
        function updatePrioritySelect(select) {
            const selectedOption = select.options[select.selectedIndex];
            const priority = selectedOption.value;
            
            // Cambiar el color del borde según la prioridad
            const colors = {
                'low': '#28a745',
                'medium': '#ffc107', 
                'high': '#fd7e14',
                'critical': '#dc3545'
            };
            
            select.style.borderLeftWidth = '4px';
            select.style.borderLeftColor = colors[priority] || '#ddd';
        }
        
        // Aplicar estilo inicial
        updatePrioritySelect(document.getElementById('priority'));
    </script>
</body>
</html>