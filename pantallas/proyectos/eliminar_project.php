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

// Solo admin y manager pueden eliminar
if (!in_array($userRole, ['admin', 'manager'])) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para eliminar proyectos.'];
    header("Location: projects.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];

    // Si es manager, verificar que el proyecto pertenece a su gestión
    if ($userRole === 'manager') {
        $checkStmt = $pdo->prepare("SELECT manager_id FROM projects WHERE id = ?");
        $checkStmt->execute([$project_id]);
        $project = $checkStmt->fetch();

        if (!$project || $project['manager_id'] != $userId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para eliminar este proyecto.'];
            header("Location: projects.php");
            exit();
        }
    }

    // Eliminar el proyecto
    try {
        $pdo->beginTransaction();
        
        // Eliminar tareas asociadas al proyecto
        $deleteTasksStmt = $pdo->prepare("DELETE FROM tasks WHERE project_id = ?");
        $deleteTasksStmt->execute([$project_id]);

        // Eliminar el proyecto
        $deleteProjectStmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        if ($deleteProjectStmt->execute([$project_id])) {
            $pdo->commit();
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Proyecto eliminado con éxito.'];
        } else {
            $pdo->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al eliminar el proyecto.'];
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al eliminar el proyecto: ' . $e->getMessage()];
    }
} else {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Solicitud inválida.'];
}

header("Location: projects.php");
exit();
?>
