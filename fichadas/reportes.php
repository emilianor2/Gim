<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=c1412397_reloj", "c1412397_reloj", "Saurina0746");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Reporte 1: Fichadas por día (últimos 30 días)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(fecha, '%d/%m') AS fecha_formato, fecha, COUNT(*) AS cantidad 
        FROM hik 
        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY fecha 
        ORDER BY fecha DESC
        LIMIT 30
    ");
    $fichadasDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reporte 2: Socios más activos esta semana
    $stmt = $pdo->query("
        SELECT nombre, COUNT(*) AS cantidad 
        FROM hik 
        WHERE YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1) 
        GROUP BY nombre
        ORDER BY cantidad DESC
        LIMIT 10
    ");
    $topSemana = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reporte 3: Distribución por tipo de fichada
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN status = 'E' THEN 'Entradas'
                WHEN status = 'S' THEN 'Salidas'
                ELSE 'Otros'
            END AS tipo,
            COUNT(*) AS cantidad 
        FROM hik 
        GROUP BY status
    ");
    $porTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reporte 4: Fichadas por hora del día
    $stmt = $pdo->query("
        SELECT 
            HOUR(hora) AS hora_del_dia,
            COUNT(*) AS cantidad
        FROM hik 
        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY HOUR(hora)
        ORDER BY hora_del_dia
    ");
    $fichadasHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reporte 5: Resumen mensual (últimos 12 meses)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(fecha, '%m/%Y') AS mes_formato,
            DATE_FORMAT(fecha, '%Y-%m') AS mes,
            COUNT(*) AS cantidad 
        FROM hik 
        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(fecha, '%Y-%m')
        ORDER BY mes DESC
        LIMIT 12
    ");
    $fichadasMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reporte 6: Socios por total de fichadas
    $stmt = $pdo->query("
        SELECT nombre, COUNT(*) AS cantidad 
        FROM hik 
        GROUP BY nombre 
        ORDER BY cantidad DESC
        LIMIT 15
    ");
    $porPersona = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estadísticas generales
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM hik");
    $totalFichadas = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(DISTINCT nombre) AS total FROM hik");
    $totalSocios = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM hik WHERE fecha = CURDATE()");
    $fichadasHoy = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM hik WHERE YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)");
    $fichadasSemana = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Control Horario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            color: var(--text-primary);
        }

        /* Header moderno */
        .navbar {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-bottom: 3px solid #1d4ed8;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            padding: 1.5rem 0;
            min-height: 80px;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 2rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand i {
            font-size: 2.5rem;
            color: #fbbf24;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .navbar-nav .nav-link {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link i {
            margin-right: 0.5rem;
        }

        /* Container principal */
        .main-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Tarjetas con glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -1px;
        }

        .page-header p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Tarjetas de estadísticas */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 1rem;
            padding: 2rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.5s ease;
        }

        .stat-card:hover::before {
            animation: shine 0.5s ease-in-out;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .stat-card.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .stat-card.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        /* Contenedores de gráficos */
        .chart-container {
            position: relative;
            height: 400px;
            margin: 1rem 0;
        }

        .chart-container.large {
            height: 500px;
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .glass-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .main-container {
                padding: 0 0.5rem;
                margin: 1rem auto;
            }

            .glass-card {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .chart-container {
                height: 300px;
            }

            .stat-number {
                font-size: 2rem;
            }
        }

        /* Estilos adicionales para los gráficos */
        .chart-section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-clock"></i>
                Control Horario S.A.
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="descarga.php">
                            <i class="fas fa-download"></i> Descargas
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="main-container" style="margin-top: 120px;">
        <!-- Header de la página -->
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Reportes y Estadísticas</h1>
            <p>Análisis detallado del sistema de control horario</p>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <span class="stat-number"><?= number_format($totalFichadas) ?></span>
                    <div class="stat-label">Total Fichadas</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number"><?= number_format($totalSocios) ?></span>
                    <div class="stat-label">Socios Activos</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <span class="stat-number"><?= number_format($fichadasHoy) ?></span>
                    <div class="stat-label">Fichadas Hoy</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card danger">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <span class="stat-number"><?= number_format($fichadasSemana) ?></span>
                    <div class="stat-label">Esta Semana</div>
                </div>
            </div>
        </div>

        <!-- Gráficos principales -->
        <div class="row">
            <!-- Fichadas por día -->
            <div class="col-lg-8 mb-4">
                <div class="glass-card">
                    <div class="chart-title">
                        <i class="fas fa-chart-line text-primary"></i>
                        Fichadas por Día (Últimos 30 días)
                    </div>
                    <div class="chart-container">
                        <canvas id="fichadasDia"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribución por tipo -->
            <div class="col-lg-4 mb-4">
                <div class="glass-card">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie text-success"></i>
                        Tipo de Fichadas
                    </div>
                    <div class="chart-container">
                        <canvas id="fichadasTipo"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Fichadas por hora -->
            <div class="col-lg-7 mb-4">
                <div class="glass-card">
                    <div class="chart-title">
                        <i class="fas fa-clock text-warning"></i>
                        Distribución por Hora del Día
                    </div>
                    <div class="chart-container">
                        <canvas id="fichadasHora"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top socios semana -->
            <div class="col-lg-5 mb-4">
                <div class="glass-card">
                    <div class="chart-title">
                        <i class="fas fa-trophy text-danger"></i>
                        Top Socios (Esta Semana)
                    </div>
                    <div class="chart-container">
                        <canvas id="topSemana"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos adicionales -->
        <div class="row">
            <!-- Evolución mensual -->
            <div class="col-12 mb-4">
                <div class="glass-card">
                    <div class="chart-title">
                        <i class="fas fa-chart-area text-info"></i>
                        Evolución Mensual (Últimos 12 meses)
                    </div>
                    <div class="chart-container large">
                        <canvas id="fichadasMes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Ranking de socios -->
            <div class="col-12 mb-4">
                <div class="glass-card">
                    <div class="chart-title">
                        <i class="fas fa-ranking-star text-primary"></i>
                        Ranking de Socios (Total de Fichadas)
                    </div>
                    <div class="chart-container">
                        <canvas id="fichadasPersona"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuración global de Chart.js
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#64748b';

        // Paleta de colores moderna
        const colors = {
            primary: '#3b82f6',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#06b6d4',
            gradient: {
                primary: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                success: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                warning: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                danger: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'
            }
        };

        // Gráfico de fichadas por día
        new Chart(document.getElementById('fichadasDia'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_reverse(array_column($fichadasDia, 'fecha_formato'))) ?>,
                datasets: [{
                    label: 'Fichadas',
                    data: <?= json_encode(array_reverse(array_column($fichadasDia, 'cantidad'))) ?>,
                    borderColor: colors.primary,
                    backgroundColor: colors.primary + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                }
            }
        });

        // Gráfico de distribución por tipo
        new Chart(document.getElementById('fichadasTipo'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($porTipo, 'tipo')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($porTipo, 'cantidad')) ?>,
                    backgroundColor: [colors.success, colors.danger],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de fichadas por hora
        new Chart(document.getElementById('fichadasHora'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($h) { return $h['hora_del_dia'] . ':00'; }, $fichadasHora)) ?>,
                datasets: [{
                    label: 'Fichadas',
                    data: <?= json_encode(array_column($fichadasHora, 'cantidad')) ?>,
                    backgroundColor: colors.warning,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico top socios de la semana
        new Chart(document.getElementById('topSemana'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($topSemana, 'nombre')) ?>,
                datasets: [{
                    label: 'Fichadas',
                    data: <?= json_encode(array_column($topSemana, 'cantidad')) ?>,
                    backgroundColor: colors.danger,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico evolución mensual
        new Chart(document.getElementById('fichadasMes'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_reverse(array_column($fichadasMes, 'mes_formato'))) ?>,
                datasets: [{
                    label: 'Fichadas Mensuales',
                    data: <?= json_encode(array_reverse(array_column($fichadasMes, 'cantidad'))) ?>,
                    borderColor: colors.info,
                    backgroundColor: colors.info + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: colors.info,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                }
            }
        });

        // Gráfico ranking de socios
        new Chart(document.getElementById('fichadasPersona'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($porPersona, 'nombre')) ?>,
                datasets: [{
                    label: 'Total Fichadas',
                    data: <?= json_encode(array_column($porPersona, 'cantidad')) ?>,
                    backgroundColor: colors.primary,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Animación de números en las tarjetas de estadísticas
        function animateNumbers() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(element => {
                const target = parseInt(element.textContent.replace(/,/g, ''));
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    element.textContent = Math.floor(current).toLocaleString();
                }, 40);
            });
        }

        // Inicializar animaciones cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(animateNumbers, 500);
        });
    </script>
</body>
</html>