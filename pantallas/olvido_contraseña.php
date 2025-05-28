<?php
require '../conexion/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dni = $_POST['dni'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    
    // Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE dni = ? AND fecha_nacimiento = ?");
    $stmt->execute([$dni, $fecha_nacimiento]); // Ejecutamos una sola vez con ambos parámetros
    $user = $stmt->fetch();
    
    if ($user) {
        // Redirigir directamente al formulario de cambio de contraseña
        // Guardamos el ID en sesión para validar después
        session_start();
        $_SESSION['reset_user_id'] = $user['id'];
        $_SESSION['reset_allowed'] = true;
        
        header("Location: reset_password.php?user_id=".$user['id']);
        exit;
    } else {
        $error = "No se encontró una cuenta con esos datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - ZIDKENU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="logo-space">
            <img class="logo" src="../assets/img/zidkenu_logov2.jfif" alt="Logo ZIDKENU">
        </div>
        
        <h1>Recuperar Contraseña</h1>
        
        <?php if ($message): ?>
            <div class="success-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="dni">DNI</label>
                <input type="text" name="dni" id="dni" required>
            </div>
            
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
            </div>
            
            <button type="submit" class="btn">Continuar</button>
        </form>
        
        <div class="login-footer">
            <a href="../login.php">Volver al inicio de sesión</a>
        </div>
    </div>
</body>
</html>