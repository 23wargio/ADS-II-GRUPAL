<?php
require '../conexion/config.php';

// Iniciar sesión si no se ha iniciado
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

// Función para redireccionar
if (!function_exists('redirect')) {
    function redirect($location) {
        header("Location: $location");
        exit();
    }
}

// Funciones para manejar mensajes flash
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

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Definir el rol si no está establecido
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'member';
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_team':
                $team_name = trim($_POST['team_name']);
                $team_description = trim($_POST['team_description']);
                
                if (!empty($team_name)) {
                    try {
                        // Crear el equipo
                        $stmt = $pdo->prepare("INSERT INTO teams (name, description, created_by, created_at) VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$team_name, $team_description, $_SESSION['user_id']]);
                        
                        $team_id = $pdo->lastInsertId();
                        
                        // Agregar al creador como líder del equipo
                        $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role, joined_at) VALUES (?, ?, 'leader', NOW())");
                        $stmt->execute([$team_id, $_SESSION['user_id']]);
                        
                        set_flash_message('success', 'Equipo creado exitosamente');
                    } catch (PDOException $e) {
                        set_flash_message('error', 'Error al crear el equipo: ' . $e->getMessage());
                    }
                } else {
                    set_flash_message('error', 'El nombre del equipo es obligatorio');
                }
                redirect('team.php');
                break;
                
            case 'join_team':
                $team_id = (int)$_POST['team_id'];
                
                try {
                    // Verificar si ya es miembro
                    $stmt = $pdo->prepare("SELECT id FROM team_members WHERE team_id = ? AND user_id = ?");
                    $stmt->execute([$team_id, $_SESSION['user_id']]);
                    
                    if ($stmt->fetch()) {
                        set_flash_message('warning', 'Ya eres miembro de este equipo');
                    } else {
                        // Unirse al equipo
                        $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())");
                        $stmt->execute([$team_id, $_SESSION['user_id']]);
                        
                        set_flash_message('success', 'Te has unido al equipo exitosamente');
                    }
                } catch (PDOException $e) {
                    set_flash_message('error', 'Error al unirse al equipo: ' . $e->getMessage());
                }
                redirect('team.php');
                break;
                
            case 'leave_team':
                $team_id = (int)$_POST['team_id'];
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
                    $stmt->execute([$team_id, $_SESSION['user_id']]);
                    
                    set_flash_message('success', 'Has salido del equipo');
                } catch (PDOException $e) {
                    set_flash_message('error', 'Error al salir del equipo: ' . $e->getMessage());
                }
                redirect('team.php');
                break;
                
            case 'delete_team':
                $team_id = (int)$_POST['team_id'];
                
                try {
                    // Verificar si es el creador del equipo
                    $stmt = $pdo->prepare("SELECT created_by FROM teams WHERE id = ?");
                    $stmt->execute([$team_id]);
                    $team = $stmt->fetch();
                    
                    if ($team && ($team['created_by'] == $_SESSION['user_id'] || $_SESSION['user_role'] == 'admin')) {
                        // Eliminar miembros del equipo
                        $stmt = $pdo->prepare("DELETE FROM team_members WHERE team_id = ?");
                        $stmt->execute([$team_id]);
                        
                        // Eliminar el equipo
                        $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
                        $stmt->execute([$team_id]);
                        
                        set_flash_message('success', 'Equipo eliminado exitosamente');
                    } else {
                        set_flash_message('error', 'No tienes permisos para eliminar este equipo');
                    }
                } catch (PDOException $e) {
                    set_flash_message('error', 'Error al eliminar el equipo: ' . $e->getMessage());
                }
                redirect('team.php');
                break;
        }
    }
}

// Obtener todos los equipos disponibles
try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.nombres as creator_name, u.apellidos as creator_lastname,
               COUNT(tm.id) as member_count,
               MAX(CASE WHEN tm.user_id = ? THEN tm.role END) as user_role_in_team
        FROM teams t 
        LEFT JOIN users u ON t.created_by = u.id
        LEFT JOIN team_members tm ON t.id = tm.team_id
        GROUP BY t.id, t.name, t.description, t.created_by, t.created_at, u.nombres, u.apellidos
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $teams = $stmt->fetchAll();
} catch (PDOException $e) {
    $teams = [];
    set_flash_message('error', 'Error al cargar los equipos: ' . $e->getMessage());
}

// Obtener equipos del usuario
try {
    $stmt = $pdo->prepare("
        SELECT t.*, tm.role as user_role, tm.joined_at,
               COUNT(tm2.id) as member_count
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        LEFT JOIN team_members tm2 ON t.id = tm2.team_id
        WHERE tm.user_id = ?
        GROUP BY t.id, t.name, t.description, t.created_by, t.created_at, tm.role, tm.joined_at
        ORDER BY tm.joined_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_teams = $stmt->fetchAll();
} catch (PDOException $e) {
    $user_teams = [];
}

$flash_message = get_flash_message();

// Función para asignar clases de colores a los roles
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
    <title>Equipos - Zidkenu</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .team-container {
            width: 95%;
            margin: 0 auto;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            align-items: start;
        }
        
        .team-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .nav-tabs .nav-link {
            color: #6c757d;
        }
        
        .nav-tabs .nav-link.active {
            color: #007bff;
            border-color: #007bff;
        }
        
        .modal-backdrop {
            z-index: 1040;
        }
        
        .modal {
            z-index: 1050;
        }
    </style>
</head>
<body>
    <?php include '../estructura/header.php'; ?>

    <?php if ($flash_message): ?>
        <div id="flash-message-container">
            <div class="flash-message flash-<?= $flash_message['type'] ?>">
                <?= $flash_message['message'] ?>
                <span class="close-flash">&times;</span>
            </div>
        </div>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="team-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users"></i> Equipos</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                    <i class="fas fa-plus"></i> Crear Equipo
                </button>
            </div>

            <!-- Pestañas de navegación -->
            <ul class="nav nav-tabs mb-4" id="teamTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="my-teams-tab" data-bs-toggle="tab" href="#my-teams" role="tab">
                        <i class="fas fa-user-friends"></i> Mis Equipos (<?= count($user_teams) ?>)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="all-teams-tab" data-bs-toggle="tab" href="#all-teams" role="tab">
                        <i class="fas fa-globe"></i> Todos los Equipos (<?= count($teams) ?>)
                    </a>
                </li>
            </ul>

            <!-- Contenido de las pestañas -->
            <div class="tab-content" id="teamTabsContent">
                <!-- Mis Equipos -->
                <div class="tab-pane fade show active" id="my-teams" role="tabpanel">
                    <?php if (!empty($user_teams)): ?>
                        <div class="team-grid">
                            <?php foreach ($user_teams as $team): ?>
                                <div class="card team-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title mb-0"><?= htmlspecialchars($team['name']) ?></h5>
                                            <span class="badge bg-<?= get_role_badge($team['user_role']) ?>">
                                                <?= ucfirst($team['user_role']) ?>
                                            </span>
                                        </div>
                                        
                                        <p class="card-text text-muted small mb-3">
                                            <?= htmlspecialchars($team['description'] ?: 'Sin descripción') ?>
                                        </p>
                                        
                                        <div class="small text-muted mb-3">
                                            <div><i class="fas fa-users"></i> <?= $team['member_count'] ?> miembros</div>
                                            <div><i class="fas fa-calendar"></i> Unido: <?= date('d/m/Y', strtotime($team['joined_at'])) ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent d-flex gap-2">
                                        <a href="team_members.php?id=<?= $team['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                                            Ver Miembros
                                        </a>
                                        
                                        <?php if ($team['user_role'] == 'leader' || $_SESSION['user_role'] == 'admin'): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $team['id'] ?>, '<?= htmlspecialchars($team['name']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-warning" onclick="leaveTeam(<?= $team['id'] ?>, '<?= htmlspecialchars($team['name']) ?>')">
                                                Salir
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            No perteneces a ningún equipo aún. ¡Crea uno o únete a uno existente!
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Todos los Equipos -->
                <div class="tab-pane fade" id="all-teams" role="tabpanel">
                    <?php if (!empty($teams)): ?>
                        <div class="team-grid">
                            <?php foreach ($teams as $team): ?>
                                <div class="card team-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($team['name']) ?></h5>
                                        
                                        <p class="card-text text-muted small mb-3">
                                            <?= htmlspecialchars($team['description'] ?: 'Sin descripción') ?>
                                        </p>
                                        
                                        <div class="small text-muted mb-3">
                                            <div><i class="fas fa-user"></i> Creado por: <?= htmlspecialchars($team['creator_name'] . ' ' . $team['creator_lastname']) ?></div>
                                            <div><i class="fas fa-users"></i> <?= $team['member_count'] ?> miembros</div>
                                            <div><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($team['created_at'])) ?></div>
                                        </div>
                                        
                                        <?php if ($team['user_role_in_team']): ?>
                                            <span class="badge bg-success">Ya eres miembro</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent d-flex gap-2">
                                        <?php if ($team['user_role_in_team']): ?>
                                            <a href="team_members.php?id=<?= $team['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                                                Ver Miembros
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-primary flex-fill" onclick="joinTeam(<?= $team['id'] ?>, '<?= htmlspecialchars($team['name']) ?>')">
                                                <i class="fas fa-user-plus"></i> Unirse
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay equipos disponibles.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear equipo -->
    <div class="modal fade" id="createTeamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nuevo Equipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_team">
                        
                        <div class="mb-3">
                            <label for="team_name" class="form-label">Nombre del Equipo *</label>
                            <input type="text" class="form-control" id="team_name" name="team_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="team_description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="team_description" name="team_description" rows="3" placeholder="Describe el propósito del equipo..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Equipo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../estructura/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Manejar mensajes flash
        document.querySelectorAll('.close-flash').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });

        setTimeout(() => {
            document.querySelectorAll('.flash-message').forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            });
        }, 5000);

        // Función para unirse a un equipo
        function joinTeam(teamId, teamName) {
            if (confirm(`¿Estás seguro de que quieres unirte al equipo "${teamName}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="join_team">
                    <input type="hidden" name="team_id" value="${teamId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Función para salir de un equipo
        function leaveTeam(teamId, teamName) {
            if (confirm(`¿Estás seguro de que quieres salir del equipo "${teamName}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="leave_team">
                    <input type="hidden" name="team_id" value="${teamId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Función para confirmar eliminación de equipo
        function confirmDelete(teamId, teamName) {
            if (confirm(`¿Estás seguro de que quieres eliminar el equipo "${teamName}"? Esta acción no se puede deshacer.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_team">
                    <input type="hidden" name="team_id" value="${teamId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>