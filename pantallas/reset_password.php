<?php
require '../conexion/config.php';
session_start();

// Verificar que venimos de la validación correcta
if (!isset($_SESSION['reset_allowed']) || !$_SESSION['reset_allowed']) {
    header("Location: olvido_contrasena.php");
    exit;
}

$user_id = $_SESSION['reset_user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password != $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } else {
        // Hashear la nueva contraseña
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Actualizar la contraseña
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        
        // Limpiar la sesión
        unset($_SESSION['reset_allowed']);
        unset($_SESSION['reset_user_id']);
        
        $message = "Contraseña actualizada correctamente. Puede iniciar sesión ahora.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Restablecer Contraseña</h1>
        
        <?php if (isset($message)): ?>
            <div class="success-message"><?= htmlspecialchars($message) ?></div>
            <a href="../login.php" class="btn">Ir al login</a>
        <?php else: ?>
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                
                <button type="submit" class="btn">Cambiar Contraseña</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>