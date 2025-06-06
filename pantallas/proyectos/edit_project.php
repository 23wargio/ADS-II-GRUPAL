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

// Verificar si se proporcionó un ID de proyecto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID de proyecto inválido.'];
    header("Location: projects.php");
    exit();
}

$projectId = $_GET['id'];

// Obtener los datos del proyecto
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Proyecto no encontrado.'];
    header("Location: projects.php");
    exit();
}

// Verificar permisos para editar el proyecto
$canEdit = false;
if ($userRole == 'admin') {
    $canEdit = true;
} elseif ($project['manager_id'] == $userId) {
    $canEdit = true;
}

if (!$canEdit) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para editar este proyecto.'];
    header("Location: projects.php");
    exit();
}

// Procesar el formulario de edición de proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $client_id = $_POST['client_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $budget = $_POST['budget'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];

    // Validaciones
    $errors = [];

    if (empty($name)) {
        $errors[] = 'El nombre del proyecto es obligatorio.';
    }

    if (empty($status)) {
        $errors[] = 'El estado del proyecto es obligatorio.';
    }

    if (empty($priority)) {
        $errors[] = 'La prioridad del proyecto es obligatoria.';
    }

    // Validación de fechas
    if (strtotime($end_date) && strtotime($start_date) && strtotime($end_date) < strtotime($start_date)) {
        $errors[] = 'La fecha de finalización no puede ser anterior a la fecha de inicio.';
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE projects SET 
                                        name = ?, 
                                        description = ?, 
                                        client_id = ?, 
                                        start_date = ?, 
                                        end_date = ?, 
                                        budget = ?, 
                                        status = ?, 
                                        priority = ?, 
                                        updated_at = NOW() 
                                        WHERE id = ?");

            if ($updateStmt->execute([$name, $description, $client_id, $start_date, $end_date, $budget, $status, $priority, $projectId])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Proyecto actualizado con éxito.'];
                header("Location: projects.php");
                exit();
            } else {
                $errors[] = 'Error al actualizar el proyecto.';
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
    <title>Editar Proyecto - Zidkenu</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <?php include '../../estructura/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <div class="page-header">
                <h1>Editar Proyecto</h1>
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
                    <label for="name">Nombre del proyecto <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($project['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($project['description']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="client_id">Cliente</label>
                    <select id="client_id" name="client_id">
                        <option value="">-- Seleccionar cliente --</option>
                        <?php
                        // Aquí obtienes los clientes
                        $stmtClients = $pdo->query("SELECT id, name FROM clients");
                        $clients = $stmtClients->fetchAll();
                        foreach ($clients as $client):
                        ?>
                            <option value="<?= $client['id'] ?>" <?= $client['id'] == $project['client_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Fecha de inicio</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $project['start_date'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">Fecha de finalización</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $project['end_date'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="budget">Presupuesto</label>
                    <input type="number" id="budget" name="budget" value="<?= $project['budget'] ?>" step="0.01">
                </div>

                <div class="form-group">
                    <label for="status">Estado *</label>
                    <select name="status" required>
                        <option value="">Seleccione el Estado</option>
                        <option value="planning">Planificación</option>
                        <option value="in_progress">En progreso</option>
                        <option value="on_hold">En espera</option>
                        <option value="completed">Completado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>

                
                <div class="form-group">
                    <label for="priority">Prioridad</label>
                    <select id="priority" name="priority" required>
                        <option value="low" <?= $project['priority'] == 'low' ? 'selected' : '' ?>>Baja</option>
                        <option value="medium" <?= $project['priority'] == 'medium' ? 'selected' : '' ?>>Media</option>
                        <option value="high" <?= $project['priority'] == 'high' ? 'selected' : '' ?>>Alta</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Actualizar Proyecto</button>
                <a href="projects.php" class="btn" style="background: #6c757d;">Cancelar</a>
            </form>
        </div>
    </div>

    <?php include '../../estructura/footer.php'; ?>
</body>
</html>
