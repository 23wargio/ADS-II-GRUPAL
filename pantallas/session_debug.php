<?php
// Este archivo te ayudará a diagnosticar problemas con las variables de sesión
session_start();

echo "<h1>Depuración de Sesión</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Variables específicas importantes:</h2>";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'No definido') . "<br>";
echo "user_role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'No definido') . "<br>";
echo "username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'No definido') . "<br>";

// Agregar un formulario para corregir el rol si es necesario
?>

<h2>Establecer o corregir el rol de usuario</h2>
<form method="post" action="">
    <label for="user_role">Rol de usuario:</label>
    <select name="user_role" id="user_role">
        <option value="admin">Admin</option>
        <option value="manager">Manager</option>
        <option value="member">Member</option>
    </select>
    <input type="submit" name="set_role" value="Establecer Rol">
</form>

<?php
// Procesar el formulario
if (isset($_POST['set_role'])) {
    $_SESSION['user_role'] = $_POST['user_role'];
    echo "<p style='color:green'>Rol establecido a: " . $_SESSION['user_role'] . "</p>";
    echo "<p>Recarga la página para ver los cambios en la sesión.</p>";
}
?>