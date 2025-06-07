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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID de cliente inválido.'];
    header("Location: clients.php");
    exit();
}

$clientId = $_GET['id'];

// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$clientId]);
$client = $stmt->fetch();

if (!$client) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Cliente no encontrado.'];
    header("Location: clients.php");
    exit();
}

// Obtener proyectos asociados al cliente
$stmt = $pdo->prepare("SELECT * FROM projects WHERE client_id = ? ORDER BY created_at DESC");
$stmt->execute([$clientId]);
$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Cliente - Zidkenu</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        .client-card {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .client-card h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .client-details p {
            margin: 8px 0;
            line-height: 1.5;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .table th, .table td {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
        }
        .table tr:hover {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 8px 14px;
            margin-top: 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.9em;
        }
        .btn-warning {
            background: #f39c12;
        }
        .btn-warning:hover {
            background: #d68910;
        }
    </style>
</head>
<body>
<?php include '../../estructura/header.php'; ?>

<div class="container">
    <div class="client-card">
        <h2>Información del Cliente</h2>
        <div class="client-details">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($client['name']) ?></p>
            <p><strong>Persona de contacto:</strong> <?= htmlspecialchars($client['contact_person']) ?></p>
            <p><strong>Correo electrónico:</strong> <?= htmlspecialchars($client['email']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($client['phone']) ?></p>
            <p><strong>Dirección:</strong> <?= htmlspecialchars($client['address']) ?></p>
            <p><strong>RUC/DNI:</strong> <?= htmlspecialchars($client['tax_id']) ?></p>
            <p><strong>Notas:</strong> <?= nl2br(htmlspecialchars($client['notes'])) ?></p>
        </div>
    </div>

    <h2>Proyectos Asociados</h2>

    <?php if (count($projects) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Fecha de creación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= htmlspecialchars($project['name']) ?></td>
                        <td><?= htmlspecialchars($project['status']) ?></td>
                        <td><?= htmlspecialchars($project['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay proyectos asociados a este cliente.</p>
    <?php endif; ?>

    <a href="clients.php" class="btn">← Volver a la lista de clientes</a>
</div>

<?php include '../../estructura/footer.php'; ?>
</body>
</html>