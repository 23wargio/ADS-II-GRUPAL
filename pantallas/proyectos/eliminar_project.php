<?php
require '../../conexion/config.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener los datos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$userId = $_SESSION['user_id'];
$userRole = $user['role'];

// Verificar si se proporcionó un ID de proyecto y si es válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $projectId = $_GET['id'];

    // Obtener datos del proyecto
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();

    if (!$project) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'El proyecto no existe.'];
        header("Location: projects.php");
        exit();
    }

    // Verificar si el usuario tiene permisos para eliminar el proyecto
    $canDelete = false;
    if ($userRole == 'admin') {
        $canDelete = true;
    } elseif ($userRole == 'manager' && $project['manager_id'] == $userId) {
        $canDelete = true;
    }

    if ($canDelete) {
        try {
            // Iniciar transacción para eliminar el proyecto y sus tareas relacionadas
            $pdo->beginTransaction();

            // Eliminar tareas asociadas al proyecto
            $deleteTasksStmt = $pdo->prepare("DELETE FROM tasks WHERE project_id = ?");
            $deleteTasksStmt->execute([$projectId]);

            // Eliminar el proyecto
            $deleteProjectStmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            if ($deleteProjectStmt->execute([$projectId])) {
                $pdo->commit();
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Proyecto eliminado con éxito.'];
            } else {
                $pdo->rollBack();
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Hubo un error al eliminar el proyecto.'];
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al eliminar el proyecto: ' . $e->getMessage()];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permiso para eliminar este proyecto.'];
    }
} else {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID de proyecto inválido.'];
}

header("Location: projects.php");
exit();
?>
