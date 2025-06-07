<?php
require '../../conexion/config.php';
session_start();

// Verificar si el usuario está logueado
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

// Verificar si se proporcionó un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $clientId = $_GET['id'];

    // Verificar si el cliente existe
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$clientId]);
    $client = $stmt->fetch();

    if (!$client) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'El cliente no existe.'];
        header("Location: clients.php");
        exit();
    }

    // Solo admin o manager pueden eliminar clientes
    $canDelete = false;
    if ($userRole === 'admin' || $userRole === 'manager') {
        $canDelete = true;
    }

    if ($canDelete) {
        try {
            // Iniciar transacción (por si hay relaciones futuras)
            $pdo->beginTransaction();

            // Si hay datos relacionados con el cliente, primero deberían eliminarse aquí

            // Eliminar cliente
            $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
            if ($stmt->execute([$clientId])) {
                $pdo->commit();
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cliente eliminado con éxito.'];
            } else {
                $pdo->rollBack();
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al eliminar el cliente.'];
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al eliminar el cliente: ' . $e->getMessage()];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'No tienes permisos para eliminar este cliente.'];
    }
} else {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID de cliente inválido.'];
}

// Redirigir de regreso
header("Location: clients.php");
exit();
?>