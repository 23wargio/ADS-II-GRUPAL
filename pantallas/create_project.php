<?php
require '../conexion/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Verificar si el rol del usuario es admin o manager
if ($user['role'] !== 'admin' && $user['role'] !== 'manager') {
    // Si el rol no es admin ni manager, redirigir
    header("Location: home_screen.php");
    exit();
}

// Obtener clientes y managers para el formulario
$clientsStmt = $pdo->query("SELECT id, name FROM clients");
$clients = $clientsStmt->fetchAll();

$managersStmt = $pdo->query("SELECT id, nombres, apellidos FROM users WHERE role = 'manager'");
$managers = $managersStmt->fetchAll();

$error = '';
$success = '';

// Procesar formulario de creación de proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_NUMBER_INT);
    $manager_id = filter_input(INPUT_POST, 'manager_id', FILTER_SANITIZE_NUMBER_INT);
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
    $budget = filter_input(INPUT_POST, 'budget', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_STRING);

    // Validaciones básicas
    if (empty($name) || empty($manager_id) || empty($start_date) || empty($status) || empty($priority)) {
        $error = "Nombre, Manager, Fecha de inicio, Estado y Prioridad son obligatorios.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insertar nuevo proyecto
            $stmt = $pdo->prepare(
                "INSERT INTO projects (name, description, client_id, manager_id, start_date, end_date, budget, status, priority) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $description, $client_id, $manager_id, $start_date, $end_date, $budget, $status, $priority]);

            // Commit de la transacción
            $pdo->commit();
            
            // Mensaje de éxito
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Proyecto creado correctamente.'
            ];
            
            // Redirigir a la lista de proyectos
            header("Location: projects.php");
            exit(); // Aseguramos que el script se detenga después de la redirección

        } catch (Exception $e) {
            // En caso de error, hacer rollback y mostrar el error
            $pdo->rollBack();
            $error = "Error al crear el proyecto: " . $e->getMessage();
            echo $error; // Esto imprimirá el error en pantalla, puedes eliminarlo una vez lo depures
            exit(); // Evitar que el script continúe
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Proyecto</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .success {
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
    
<body>
    <?php include '../estructura/header.php'; ?>

    <div class="form-container">
        <h1>Crear Nuevo Proyecto</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="create_project.php" method="post">
            <div class="form-group">
                <label for="name">Nombre del proyecto *</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="client_id">Cliente</label>
                <select name="client_id">
                    <option value="">-- Seleccionar cliente --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="manager_id">Manager del proyecto *</label>
                <select name="manager_id" required>
                    <?php foreach ($managers as $manager): ?>
                        <option value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['nombres'] . ' ' . $manager['apellidos']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="start_date">Fecha de inicio *</label>
                <input type="date" name="start_date" required>
            </div>

            <div class="form-group">
                <label for="end_date">Fecha de finalización prevista</label>
                <input type="date" name="end_date">
            </div>

            <div class="form-group">
                <label for="budget">Presupuesto (S/.)</label>
                <input type="number" name="budget" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="status">Estado *</label>
                <select name="status" required>
                    <option value="planning">Planificación</option>
                    <option value="in_progress">En progreso</option>
                    <option value="on_hold">En espera</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>

            <div class="form-group">
                <label for="priority">Prioridad *</label>
                <select name="priority" required>
                    <option value="low">Baja</option>
                    <option value="medium">Media</option>
                    <option value="high">Alta</option>
                    <option value="critical">Crítica</option>
                </select>
            </div>

            <button type="submit" class="btn">Crear Proyecto</button>
            <a href="projects.php" class="btn" style="background: #6c757d;">Cancelar</a>
        </form>
    </div>

    <?php include '../estructura/footer.php'; ?>
</body>
</html>
