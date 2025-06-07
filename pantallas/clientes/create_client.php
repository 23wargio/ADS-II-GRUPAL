<?php
require '../../conexion/config.php';
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Solo admin o manager
if ($user['role'] !== 'admin' && $user['role'] !== 'manager') {
    header("Location: home_screen.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $tax_id = trim($_POST['tax_id']);
    $notes = trim($_POST['notes']);

    if (empty($name) || !$email) {
        $error = "El nombre y un correo electrónico válido son obligatorios.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clients 
                (name, contact_person, email, phone, address, tax_id, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $contact_person ?: null,
                $email,
                $phone ?: null,
                $address ?: null,
                $tax_id ?: null,
                $notes ?: null,
                $_SESSION['user_id']
            ]);

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Cliente creado exitosamente.'
            ];
            header("Location: clients.php");
            exit();
        } catch (Exception $e) {
            $error = "Error al insertar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cliente</title>
    <link rel="stylesheet" href="../../css/style.css">

</head>
<body>

<?php include '../../estructura/header.php'; ?>

<div class="form-container">
    <h1>Crear Cliente</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Persona de contacto</label>
            <input type="text" name="contact_person">
        </div>
        <div class="form-group">
            <label>Correo electrónico *</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="phone">
        </div>
        <div class="form-group">
            <label>Dirección</label>
            <textarea name="address"></textarea>
        </div>
        <div class="form-group">
            <label>RUC / Tax ID</label>
            <input type="text" name="tax_id">
        </div>
        <div class="form-group">
            <label>Notas</label>
            <textarea name="notes"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Crear Cliente</button>
        <a href="clients.php" class="btn" style="background: #6c757d;">Cancelar</a>
    </form>
</div>

<?php include '../../estructura/footer.php'; ?>
</body>
</html>
