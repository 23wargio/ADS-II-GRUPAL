<?php
require '../conexion/config.php';
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

// Solo admin y manager pueden eliminar
if (!in_array($userRole, ['admin', 'manager'])) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para eliminar tareas.'];
    header("Location: tasks.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];

    // Si es manager, verificar que la tarea pertenece a un proyecto que administra
    if ($userRole === 'manager') {
        $checkStmt = $pdo->prepare("SELECT p.manager_id FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ?");
        $checkStmt->execute([$task_id]);
        $project = $checkStmt->fetch();

        if (!$project || $project['manager_id'] != $userId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para eliminar esta tarea.'];
            header("Location: tasks.php");
            exit();
        }
    }

    // Eliminar la tarea
    $deleteStmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    if ($deleteStmt->execute([$task_id])) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Tarea eliminada con éxito.'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al eliminar la tarea.'];
    }
} else {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Solicitud inválida.'];
}

header("Location: tasks.php");
exit();
?>
