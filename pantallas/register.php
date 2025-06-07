<?php
require '../conexion/config.php';
session_start();

// Configuración (en producción usa variables de entorno)
define('MASTER_KEY', 'admin1234');
$allowed_roles = ['admin', 'manager', 'member'];

// Manejo de mensajes
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger datos del formulario
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $dni = trim($_POST['dni'] ?? '');
    $celular = trim($_POST['celular'] ?? '');

    // Procesamiento de rol (member por defecto)
    $role = 'member';
    $special_register = false;

    // Verificar solo si se activó registro especial y se proporcionó clave
    if (!empty($_POST['special_access']) && !empty($_POST['master_key'])) {
        if ($_POST['master_key'] === MASTER_KEY) {
            $role = in_array($_POST['role'], $allowed_roles) ? $_POST['role'] : 'member';
            $special_register = true;
        } else {
            $error_message = "Clave maestra incorrecta. Solo personal autorizado puede crear cuentas especiales.";
        }
    }

    // Si no hay errores, proceder con registro
    if (empty($error_message)) {
        try {
            // Hash de contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Procesar foto de perfil
            $fotoDir = '../assets/foto_perfil/';
            $fotoNombre = '../assets/foto_perfil/default.jpg';

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['foto_perfil']['tmp_name'];
                $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($ext, $allowed_ext)) {
                    $newFileName = uniqid('perfil_') . '.' . $ext;
                    $destino = $fotoDir . $newFileName;
                    
                    if (move_uploaded_file($tmpName, $destino)) {
                        $fotoNombre = $destino;
                    }
                }
            }

            // Insertar usuario
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, nombres, apellidos, fecha_nacimiento, dni, celular, foto_perfil, role) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password_hash, $email, $nombres, $apellidos, $fecha_nacimiento, $dni, $celular, $fotoNombre, $role]);

            // Mensaje de éxito
            $success_message = $special_register 
                ? "¡Registro exitoso como " . ucfirst($role) . "!" 
                : "¡Registro exitoso! Bienvenido a Zidkenu";

            // Redirigir después de 3 segundos
            header("Refresh: 3; url=../login.php");

        } catch (PDOException $e) {
            $error_message = (strpos($e->getMessage(), 'Duplicate entry')) !== false
                ? "El nombre de usuario ya está registrado"
                : "Error en el registro: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Zidkenu</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --error-color: #c62828;
            --success-color: #2e7d32;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .register-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo {
            max-height: 80px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        input:focus, select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }
        
        .special-access-section {
            background-color: #f8f9fa;
            padding: 1.2rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            border-left: 4px solid var(--primary-color);
        }
        
        .special-access-toggle {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        
        .special-access-toggle input {
            margin-right: 10px;
        }
        
        .special-fields {
            display: none;
            margin-top: 1rem;
            animation: fadeIn 0.3s;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }
        
        button[type="submit"]:hover {
            background-color: var(--secondary-color);
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            display: block;
            color: #666;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .close-alert {
            background: none;
            border: none;
            color: inherit;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 600px) {
            .register-container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo-container">
            <img src="../assets/img/zidkenu_logov2.jfif" alt="Zidkenu" class="logo">
        </div>
        
        <h1>Crear cuenta en Zidkenu</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error" id="errorAlert">
                <span><?= htmlspecialchars($error_message) ?></span>
                <button class="close-alert" onclick="document.getElementById('errorAlert').style.display='none'">×</button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" id="successAlert">
                <span><?= htmlspecialchars($success_message) ?></span>
                <button class="close-alert" onclick="document.getElementById('successAlert').style.display='none'">×</button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($success_message)): ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Usuario *</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña *</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email">
            </div>
            
            <div class="form-group">
                <label for="nombres">Nombres *</label>
                <input type="text" name="nombres" id="nombres" required>
            </div>
            
            <div class="form-group">
                <label for="apellidos">Apellidos *</label>
                <input type="text" name="apellidos" id="apellidos" required>
            </div>
            
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de nacimiento *</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
            </div>
            
            <div class="form-group">
                <label for="dni">DNI *</label>
                <input type="number" name="dni" id="dni" required>
            </div>
            
            <div class="form-group">
                <label for="celular">Celular *</label>
                <input type="number" name="celular" id="celular" required>
            </div>
            
            <div class="form-group">
                <label for="foto_perfil">Foto de perfil</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">
            </div>
            
            <div class="special-access-section">
                <label class="special-access-toggle">
                    <input type="checkbox" name="special_access" id="specialAccess">
                    <span>Registro con rol especial (requiere clave autorizada)</span>
                </label>
                
                <div class="special-fields" id="specialFields">
                    <div class="form-group">
                        <label for="master_key">Clave maestra</label>
                        <input type="password" name="master_key" id="master_key">
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Seleccionar rol</label>
                        <select name="role" id="role">
                            <option value="manager">Gerente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit">Registrarse</button>
        </form>
        
        <p class="login-link">¿Ya tienes una cuenta? <a href="../login.php">Inicia sesión</a></p>
        <?php endif; ?>
    </div>

    <script>
        // Mostrar/ocultar campos especiales
        document.getElementById('specialAccess').addEventListener('change', function() {
            const specialFields = document.getElementById('specialFields');
            if (this.checked) {
                specialFields.style.display = 'block';
            } else {
                specialFields.style.display = 'none';
            }
        });
        
        // Cerrar alertas automáticamente después de 5 segundos
        setTimeout(() => {
            const errorAlert = document.getElementById('errorAlert');
            if (errorAlert) errorAlert.style.display = 'none';
            
            const successAlert = document.getElementById('successAlert');
            if (successAlert) successAlert.style.display = 'none';
        }, 5000);
    </script>
</body>
</html>