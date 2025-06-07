<?php
require '../../conexion/config.php';
session_start();

// Obtener datos del usuario para el header
if (isset($_SESSION['user_id'])) {
    $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
} else {
    $user = null;
}

// Definir funciones de utilidad
if (!function_exists('redirect')) {
    function redirect($location, $delay = 0) {
        if ($delay > 0) {
            header("Refresh: $delay; url=$location");
        } else {
            header("Location: $location");
        }
        exit();
    }
}

if (!function_exists('set_flash_message')) {
    function set_flash_message($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ];
    }
}

if (!function_exists('get_flash_message')) {
    function get_flash_message() {
        if (empty($_SESSION['flash_message'])) return null;
        
        // Eliminar mensajes antiguos (más de 5 minutos)
        if (time() - $_SESSION['flash_message']['timestamp'] > 300) {
            unset($_SESSION['flash_message']);
            return null;
        }
        
        $flash_message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash_message;
    }
}

// Verificación de autenticación y permisos
if (!isset($_SESSION['user_id'])) {
    set_flash_message('warning', 'Debes iniciar sesión para acceder a esta página');
    redirect('login.php');
}

$allowed_roles = ['admin', 'manager','member'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    set_flash_message('error', 'No tienes permisos para acceder a esta sección');
    redirect('home_screen.php', 3);
}

// Obtener estadísticas con manejo de errores
try {
    $stats = [];
    
    // Proyectos por estado
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM projects GROUP BY status");
    $stats['projects_by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tareas por estado
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
    $stats['tasks_by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Proyectos por prioridad
    $stmt = $pdo->query("SELECT priority, COUNT(*) as count FROM projects GROUP BY priority");
    $stats['projects_by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tareas por prioridad
    $stmt = $pdo->query("SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority");
    $stats['tasks_by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Proyectos por cliente (solo admin)
    if ($_SESSION['user_role'] == 'admin') {
        $stmt = $pdo->query("SELECT c.name, COUNT(p.id) as count FROM projects p LEFT JOIN clients c ON p.client_id = c.id GROUP BY p.client_id");
        $stats['projects_by_client'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Tareas por usuario
    $stmt = $pdo->query("SELECT CONCAT(u.nombres, ' ', u.apellidos) as full_name, COUNT(t.id) as count FROM tasks t JOIN users u ON t.assigned_to = u.id GROUP BY t.assigned_to");
    $stats['tasks_by_user'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Totales para tarjetas
    $stats['total_projects'] = array_sum(array_column($stats['projects_by_status'], 'count'));
    $stats['total_tasks'] = array_sum(array_column($stats['tasks_by_status'], 'count'));
    $stats['completed_tasks'] = array_reduce($stats['tasks_by_status'], function($carry, $item) {
        return $carry + ($item['status'] == 'completed' ? $item['count'] : 0);
    }, 0);
    
} catch (PDOException $e) {
    set_flash_message('error', 'Error al generar reportes: ' . $e->getMessage());
    error_log('Reportes Error: ' . $e->getMessage());
}

$flash_message = get_flash_message();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Zidkenu</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
</head>
<body>
    <?php include '../../estructura/header.php'; ?>
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-chart-pie"></i> Panel de Reportes
            </h1>
            <div>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir Reporte
                </button>
            </div>
        </div>

        <!-- Mensajes flash -->
        <?php if ($flash_message): ?>
            <div class="flash-message flash-<?= $flash_message['type'] ?>">
                <i class="fas fa-<?= 
                    $flash_message['type'] == 'success' ? 'check-circle' : 
                    ($flash_message['type'] == 'error' ? 'exclamation-circle' : 
                    ($flash_message['type'] == 'warning' ? 'exclamation-triangle' : 'info-circle')) 
                ?>"></i>
                <span><?= $flash_message['message'] ?></span>
                <span class="close-flash">&times;</span>
            </div>
        <?php endif; ?>

        <!-- Tarjetas de resumen -->
        <div class="stat-grid">
            <div class="stat-card projects">
                <div class="card-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3 class="card-title">Proyectos Totales</h3>
                <p class="card-value"><?= number_format($stats['total_projects']) ?></p>
                <div class="card-percentage">
                    <?php 
                        $completed_projects = array_reduce($stats['projects_by_status'], function($carry, $item) {
                            return $carry + ($item['status'] == 'completed' ? $item['count'] : 0);
                        }, 0);
                        $completion_rate = $stats['total_projects'] > 0 ? round(($completed_projects / $stats['total_projects']) * 100) : 0;
                    ?>
                    <span class="text-success"><i class="fas fa-arrow-up"></i> <?= $completion_rate ?>% completados</span>
                </div>
            </div>
            
            <div class="stat-card tasks">
                <div class="card-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="card-title">Tareas Totales</h3>
                <p class="card-value"><?= number_format($stats['total_tasks']) ?></p>
                <div class="card-percentage">
                    <span class="text-success"><i class="fas fa-arrow-up"></i> <?= $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0 ?>% completadas</span>
                </div>
            </div>
            
            <div class="stat-card completed">
                <div class="card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="card-title">Tareas Completadas</h3>
                <p class="card-value"><?= number_format($stats['completed_tasks']) ?></p>
                <div class="card-percentage">
                    <span>Últimos 30 días: +12%</span>
                </div>
            </div>
            
            <?php if ($_SESSION['user_role'] == 'admin'): ?>
            <div class="stat-card users">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="card-title">Clientes Activos</h3>
                <p class="card-value"><?= isset($stats['projects_by_client']) ? count($stats['projects_by_client']) : '0' ?></p>
                <div class="card-percentage">
                    <span>Con proyectos activos</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Gráficos principales -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Proyectos por Estado</h3>
                        <div class="chart-actions">
                            <button class="chart-btn" onclick="changeChartType('projectsByStatusChart', 'doughnut')">
                                <i class="fas fa-circle"></i>
                            </button>
                            <button class="chart-btn" onclick="changeChartType('projectsByStatusChart', 'bar')">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </div>
                    </div>
                    <canvas id="projectsByStatusChart" height="300"></canvas>
                </div>
            </div>
            
            
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Tareas por Estado</h3>
                        <div class="chart-actions">
                            <button class="chart-btn" onclick="changeChartType('tasksByStatusChart', 'pie')">
                                <i class="fas fa-chart-pie"></i>
                            </button>
                            <button class="chart-btn" onclick="changeChartType('tasksByStatusChart', 'bar')">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </div>
                    </div>
                    <canvas id="tasksByStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráficos secundarios -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Proyectos por Prioridad</h3>
                    </div>
                    <canvas id="projectsByPriorityChart" height="250"></canvas>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Tareas por Usuario</h3>
                    </div>
                    <canvas id="tasksByUserChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <?php if ($_SESSION['user_role'] == 'admin'): ?>
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Proyectos por Cliente</h3>
                    </div>
                    <canvas id="projectsByClientChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Configuración común para los gráficos
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            family: 'Poppins',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        family: 'Poppins',
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Poppins',
                        size: 12
                    },
                    padding: 12,
                    cornerRadius: 6
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    formatter: (value, ctx) => {
                        const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = (value * 100 / sum).toFixed(1) + '%';
                        return percentage;
                    }
                }
            }
        };

        // Colores personalizados
        const statusColors = {
            planning: '#6c757d', 
            in_progress: '#4361ee',
            on_hold: '#f8961e', 
            completed: '#4cc9f0', 
            cancelled: '#f72585'
        };
        
        const priorityColors = {
            high: '#f72585',
            medium: '#f8961e',
            low: '#4cc9f0'
        };

        // Gráfico de proyectos por estado
        const projectsByStatusChart = new Chart(
            document.getElementById('projectsByStatusChart'),
            {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_map(function($status) {
                        return ucwords(str_replace('_', ' ', $status['status']));
                    }, $stats['projects_by_status'])) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($stats['projects_by_status'], 'count')) ?>,
                        backgroundColor: Object.values(statusColors),
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            }
        );

        // Gráfico de tareas por estado
        const tasksByStatusChart = new Chart(
            document.getElementById('tasksByStatusChart'),
            {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_map(function($status) {
                        return ucwords(str_replace('_', ' ', $status['status']));
                    }, $stats['tasks_by_status'])) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($stats['tasks_by_status'], 'count')) ?>,
                        backgroundColor: Object.values(statusColors),
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            }
        );

        // Gráfico de proyectos por prioridad
        const projectsByPriorityChart = new Chart(
            document.getElementById('projectsByPriorityChart'),
            {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_map(function($priority) {
                        return ucfirst($priority['priority']);
                    }, $stats['projects_by_priority'])) ?>,
                    datasets: [{
                        label: 'Proyectos',
                        data: <?= json_encode(array_column($stats['projects_by_priority'], 'count')) ?>,
                        backgroundColor: Object.values(priorityColors),
                        borderWidth: 1
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            }
        );

        // Gráfico de tareas por usuario
        const tasksByUserChart = new Chart(
            document.getElementById('tasksByUserChart'),
            {
                type: 'bar', // Cambiado de 'horizontalBar' a 'bar'
                data: {
                    labels: <?= json_encode(array_column($stats['tasks_by_user'], 'full_name')) ?>,
                    datasets: [{
                        label: 'Tareas Asignadas',
                        data: <?= json_encode(array_column($stats['tasks_by_user'], 'count')) ?>,
                        backgroundColor: '#4361ee',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...chartOptions,
                    indexAxis: 'y', // Esto hace que sea horizontal
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            }
        );


        <?php if ($_SESSION['user_role'] == 'admin'): ?>
        // Gráfico de proyectos por cliente
        const projectsByClientChart = new Chart(
            document.getElementById('projectsByClientChart'),
            {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($stats['projects_by_client'], 'name')) ?>,
                    datasets: [{
                        label: 'Proyectos',
                        data: <?= json_encode(array_column($stats['projects_by_client'], 'count')) ?>,
                        backgroundColor: '#4cc9f0',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            }
        );
        <?php endif; ?>

        // Función para cambiar el tipo de gráfico
        function changeChartType(chartId, type) {
            const chart = Chart.getChart(chartId);
            chart.config.type = type;
            chart.update();
        }

        // Cerrar mensajes flash
        document.querySelectorAll('.close-flash').forEach(button => {
            button.addEventListener('click', (e) => {
                e.target.closest('.flash-message').style.animation = 'slideIn 0.3s reverse forwards';
                setTimeout(() => {
                    e.target.closest('.flash-message').remove();
                }, 300);
            });
        });

        // Auto cerrar mensajes flash después de 5 segundos
        setTimeout(() => {
            document.querySelectorAll('.flash-message').forEach(message => {
                message.style.animation = 'slideIn 0.3s reverse forwards';
                setTimeout(() => message.remove(), 300);
            });
        }, 5000);
    </script>

    <?php include '../../estructura/footer.php'; ?>
</body>
</html>