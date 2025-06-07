<?php
require '../conexion/config.php';
session_start();

// Función para traducir estados
function translateStatus($status) {
    $translations = [
        'planning' => 'Planificando',
        'in_progress' => 'En Progreso',
        'completed' => 'Completado',
        'on_hold' => 'En Pausa',
        'cancelled' => 'Cancelado'
    ];
    return $translations[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

// Función para traducir prioridades
function translatePriority($priority) {
    $translations = [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'critical' => 'Crítica'
    ];
    return $translations[$priority] ?? ucfirst($priority);
}

// Mostrar mensaje flash si existe
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['user_id'])) {
   header("Location: login.php");
   exit();
}

// Obtener datos del usuario actual
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();

// Verificar foto de perfil
$fotoPerfil = file_exists($user['foto_perfil']) ? $user['foto_perfil'] : '../assets/foto_perfil/default.jpg';

// Obtener proyectos del usuario
$projects_stmt = $pdo->prepare("SELECT p.* FROM projects p 
                              JOIN project_team pt ON p.id = pt.project_id 
                              WHERE pt.user_id = ? ORDER BY p.end_date ASC");
$projects_stmt->execute([$_SESSION['user_id']]);
$projects = $projects_stmt->fetchAll();

// Obtener tareas asignadas
$tasks_stmt = $pdo->prepare("SELECT t.*, p.name as project_name FROM tasks t
                            JOIN projects p ON t.project_id = p.id
                            WHERE t.assigned_to = ? AND t.status != 'completed'
                            ORDER BY t.due_date ASC LIMIT 5");
$tasks_stmt->execute([$_SESSION['user_id']]);
$tasks = $tasks_stmt->fetchAll();

// Obtener estadísticas
$stats_stmt = $pdo->prepare("SELECT 
                            (SELECT COUNT(*) FROM projects p JOIN project_team pt ON p.id = pt.project_id WHERE pt.user_id = ?) as total_projects,
                            (SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'completed') as completed_tasks,
                            (SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status != 'completed') as pending_tasks");
$stats_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$stats = $stats_stmt->fetch();

// Obtener equipos del usuario
$teams_stmt = $pdo->prepare("SELECT t.* FROM teams t
                            JOIN team_members tm ON t.id = tm.team_id
                            WHERE tm.user_id = ?");
$teams_stmt->execute([$_SESSION['user_id']]);
$teams = $teams_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Zidkenu</title>
    <link rel="stylesheet" href="../css/main_screen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../estructura/header.php'; ?>
    
    <div id="flash-message-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Panel de Control</h1>
        </div>
        
        <div class="welcome-section">
            <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" class="profile-img">
            <div class="user-info">
                <h1>Bienvenido, <?= htmlspecialchars($user['nombres'] . ' ' . htmlspecialchars($user['apellidos'])) ?></h1>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <p><i class="fas fa-user-tag"></i> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-project-diagram" style="color: var(--primary-color);"></i>
                <h3><?= htmlspecialchars($stats['total_projects']) ?></h3>
                <p>Proyectos</p>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-tasks" style="color: var(--success-color);"></i>
                <h3><?= htmlspecialchars($stats['completed_tasks']) ?></h3>
                <p>Tareas Completadas</p>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-clipboard-list" style="color: var(--warning-color);"></i>
                <h3><?= htmlspecialchars($stats['pending_tasks']) ?></h3>
                <p>Tareas Pendientes</p>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-users" style="color: var(--secondary-color);"></i>
                <h3><?= count($teams) ?></h3>
                <p>Equipos</p>
            </div>
        </div>
        
        <div class="projects-section">
            <div class="section-header">
                <h2><i class="fas fa-project-diagram"></i> Mis Proyectos</h2>
            </div>
            
            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <i class="fas fa-project-diagram"></i>
                    <p>No tienes proyectos asignados.</p>
                </div>
            <?php else: ?>
                <div class="projects-grid">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card">
                            <h3><?= htmlspecialchars($project['name']) ?></h3>
                            <p><?= htmlspecialchars($project['description']) ?></p>
                            
                            <div class="project-meta">
                                <span>
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?= date('d/m/Y', strtotime($project['start_date'])) ?> - 
                                    <?= $project['end_date'] ? date('d/m/Y', strtotime($project['end_date'])) : 'Sin fecha fin' ?>
                                </span>
                                
                                <span>
                                    <i class="fas fa-dollar-sign"></i> 
                                    <?= $project['budget'] ? number_format($project['budget'], 2) : 'Sin presupuesto' ?>
                                </span>
                                
                                <span>
                                    <i class="fas fa-battery-three-quarters"></i> 
                                    <span class="status-badge status-<?= str_replace('_', '-', $project['status']) ?>">
                                        <?= translateStatus($project['status']) ?>
                                    </span>
                                </span>
                                
                                <span>
                                    <i class="fas fa-exclamation-circle"></i> 
                                    <span class="priority-badge priority-<?= $project['priority'] ?>">
                                        <?= translatePriority($project['priority']) ?>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= 
                                    $project['status'] == 'completed' ? '100%' : 
                                    ($project['status'] == 'in_progress' ? '60%' : 
                                    ($project['status'] == 'on_hold' ? '30%' : '10%')) ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tasks-section">
            <div class="section-header">
                <h2><i class="fas fa-tasks"></i> Tareas Pendientes</h2>
            </div>
            
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <p>No tienes tareas pendientes.</p>
                </div>
            <?php else: ?>
                <div class="tasks-grid">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card">
                            <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
                            
                            <div class="task-meta">
                                <span>
                                    <i class="fas fa-project-diagram"></i> 
                                    <?= htmlspecialchars($task['project_name']) ?>
                                </span>
                                
                                <span>
                                    <i class="fas fa-calendar-day"></i> 
                                    <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                                </span>
                                
                                <span>
                                    <i class="fas fa-flag"></i> 
                                    <span class="priority-badge priority-<?= $task['priority'] ?>">
                                        <?= translatePriority($task['priority']) ?>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $task['progress'] ?>%"></div>
                            </div>
                            
                            <div class="task-actions">
                                <a href="./tareas/edit_task.php?id=<?= $task['id'] ?>" class="btn" style="padding: 8px 12px; font-size: 0.9rem;">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($teams)): ?>
        <div class="teams-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Mis Equipos</h2>
            </div>
            
            <div class="teams-grid">
                <?php foreach ($teams as $team): ?>
                    <div class="team-card">
                        <div class="team-icon"><?= strtoupper(substr($team['name'], 0, 1)) ?></div>
                        <div class="team-info">
                            <div class="team-name"><?= htmlspecialchars($team['name']) ?></div>
                            <div class="team-meta"><?= htmlspecialchars($team['description']) ?></div>
                        </div>
                        <a href="team_members.php?id=<?= $team['id'] ?>" class="btn" style="padding: 8px 12px;">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
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