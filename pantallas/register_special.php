<?php
require '../conexion/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $dni = $_POST['dni'];
    $celular = $_POST['celular'];
    $role = $_POST['role']; // Nuevo campo para el rol

    // Directorio donde se guardarán las fotos
    $fotoDir = '../assets/foto_perfil/';
    $fotoNombre = '../assets/foto_perfil/default.jpg'; // Valor por defecto

    // Si el usuario sube una imagen
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['foto_perfil']['tmp_name'];
        $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('perfil_') . '.' . strtolower($ext);
        $destino = $fotoDir . $newFileName;

        // Mover la imagen
        if (move_uploaded_file($tmpName, $destino)) {
            $fotoNombre = $destino;
        }
    }

    // Insertar usuario con foto y rol
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, nombres, apellidos, fecha_nacimiento, dni, celular, foto_perfil, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $email, $nombres, $apellidos, $fecha_nacimiento, $dni, $celular, $fotoNombre, $role]);

    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Gestión de Proyectos Zidkenu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Registrate</h1>
        <form method="POST" enctype="multipart/form-data">
            <label for="username">Usuario *</label>
            <input type="text" name="username" id="username" placeholder="Username" required>

            <label for="password">Contraseña *</label>
            <input type="password" name="password" id="password" placeholder="Password" required>

            <label for="email">Correo electrónico</label>
            <input type="email" name="email" id="email" placeholder="Correo electrónico">
            <div id="email-error" class="error-msg"></div>

            <label for="nombres">Nombres *</label>
            <input type="text" name="nombres" id="nombres" placeholder="Nombres" required>

            <label for="apellidos">Apellidos *</label>
            <input type="text" name="apellidos" id="apellidos" placeholder="Apellidos" required>

            <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" placeholder="Fecha Nacimiento" required>

            <label for="dni">DNI *</label>
            <input type="number" name="dni" id="dni" placeholder="DNI" required>
            <div id="dni-error" class="error-msg"></div>

            <label for="celular">Celular *</label>
            <input type="number" name="celular" id="celular" placeholder="Celular" required>
            <div id="celular-error" class="error-msg"></div>
            
            <!-- Nuevo campo para seleccionar el rol -->
            <label for="role">Rol *</label>
            <select name="role" id="role" required>
                <option value="">Seleccione un rol</option>
                <option value="member">Miembro</option>
                <option value="manager">Gerente</option>
                <option value="admin">Administrador</option>
            </select>
            
            <label for="foto_perfil">Foto de perfil</label>
            <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">

            <button type="submit">Registrarse</button>
        </form>
    </div>
    <script src="../javascript/main.js"></script>
</body>
</html>