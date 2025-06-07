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

// Verificar si se proporcionó un ID de cliente válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID de cliente inválido.'];
    header("Location: clients.php");
    exit();
}

$clientId = $_GET['id'];

// Obtener los datos del cliente
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$clientId]);
$client = $stmt->fetch();

if (!$client) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Cliente no encontrado.'];
    header("Location: clients.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $tax_id = trim($_POST['tax_id']);
    $notes = trim($_POST['notes']);

    // Validación básica
    if (empty($name)) {
        $errors[] = 'El nombre del cliente es obligatorio.';
    }

    if (empty($email)) {
        $errors[] = 'El correo electrónico es obligatorio.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE clients SET 
                name = ?, 
                contact_person = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                tax_id = ?, 
                notes = ?, 
                updated_at = NOW()
                WHERE id = ?");

            if ($stmt->execute([$name, $contact_person, $email, $phone, $address, $tax_id, $notes, $clientId])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cliente actualizado con éxito.'];
                header("Location: clients.php");
                exit();
            } else {
                $errors[] = 'Error al actualizar el cliente.';
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
    <title>Editar Cliente - Zidkenu</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <?php include '../../estructura/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <div class="page-header">
                <h1>Editar Cliente</h1>
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
                    <label for="name">Nombre <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($client['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="contact_person">Persona de contacto</label>
                    <input type="text" id="contact_person" name="contact_person" value="<?= htmlspecialchars($client['contact_person']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($client['phone']) ?>">
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <textarea id="address" name="address"><?= htmlspecialchars($client['address']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tax_id">RUC / DNI</label>
                    <input type="text" id="tax_id" name="tax_id" value="<?= htmlspecialchars($client['tax_id']) ?>">
                </div>

                <div class="form-group">
                    <label for="notes">Notas</label>
                    <textarea id="notes" name="notes"><?= htmlspecialchars($client['notes']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Editar Cliente</button>
                <a href="clients.php" class="btn" style="background: #6c757d;">Cancelar</a>
            </form>
        </div>
    </div>

    <?php include '../../estructura/footer.php'; ?>
</body>
</html>
