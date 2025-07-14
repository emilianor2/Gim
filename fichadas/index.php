<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=c1412397_reloj", "c1412397_reloj", "Saurina0746");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nombre = $_GET['nombre'] ?? '';
    $fecha_desde = $_GET['desde'] ?? '';
    $fecha_hasta = $_GET['hasta'] ?? '';
    $status = $_GET['status'] ?? '';

    $query = "SELECT * FROM hik WHERE 1=1";
    $params = [];

    if ($nombre !== '') {
        $query .= " AND nombre LIKE ?";
        $params[] = "%$nombre%";
    }
    if ($fecha_desde !== '') {
        $query .= " AND fecha >= ?";
        $params[] = $fecha_desde;
    }
    if ($fecha_hasta !== '') {
        $query .= " AND fecha <= ?";
        $params[] = $fecha_hasta;
    }
    if ($status !== '') {
        $query .= " AND status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY fecha_hora DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $fichadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Función para obtener el día de la semana en español
function obtenerDiaSemana($fecha) {
    $dias = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    return $dias[date('l', strtotime($fecha))];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Horario - Registro de Fichadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        /* Header moderno y más grueso */
        .navbar {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-bottom: 3px solid #1d4ed8;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            padding: 1.5rem 0;
            min-height: 80px;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
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

        /* Formulario de filtros */
        .filter-section {
            margin-bottom: 2rem;
        }

        .filter-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Botones mejorados */
        .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Tabla moderna sin scroll horizontal */
        .table-container {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border-color);
        }

        .table {
            margin-bottom: 0;
            font-size: 1rem;
            width: 100%;
        }

        .table thead th {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            border: none;
            font-weight: 700;
            padding: 1rem 0.75rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-weight: 500;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .employee-name {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1rem;
        }

        .employee-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .date-time {
            font-family: 'Monaco', 'Menlo', monospace;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .day-name {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status badges mejorados */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            min-width: 70px;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .status-entrada {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: 2px solid #10b981;
        }

        .status-salida {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: 2px solid #ef4444;
        }

        .status-entrada:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        }

        .status-salida:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        /* DataTables customization */
        .dataTables_wrapper .dataTables_info {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.5rem !important;
            margin: 0 0.25rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }

        .dataTables_wrapper .dataTables_length {
            display: none; /* Ocultar selector de registros */
        }

        /* Ocultar buscador global de DataTables */
        div.dataTables_filter {
            display: none;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }
            
            .navbar-brand i {
                font-size: 1.8rem;
            }
            
            .navbar-toggler {
                margin-left: auto;
            }
            
            .navbar-collapse {
                margin-top: 0.5rem;
            }

            .main-container {
                padding: 0 0.5rem;
                margin: 1rem auto;
            }

            .glass-card {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .table thead th {
                padding: 0.75rem 0.5rem;
                font-size: 0.7rem;
            }

            .table tbody td {
                padding: 0.75rem 0.5rem;
            }

            .employee-name {
                font-size: 0.9rem;
            }

            .date-time {
                font-size: 0.8rem;
            }
        }

        /* Responsive para status badges */
        @media (max-width: 576px) {
            .full-text { 
                display: none; 
            }
            
            .only-mobile { 
                display: inline !important; 
                font-size: 0.7rem; 
            }
            
            .status-badge {
                min-width: 40px;
                padding: 0.3rem 0.6rem;
                font-size: 0.7rem;
            }
        }
        
        @media (min-width: 577px) {
            .only-mobile { 
                display: none !important; 
            }
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

        .glass-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Loader */
        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }

        .loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            <h1><i class="fas fa-user-clock"></i> Registro de Fichadas</h1>
            <p>Gestiona y consulta los registros de entrada y salida del personal</p>
        </div>

        <!-- Formulario de filtros -->
        <div class="glass-card">
            <div class="filter-section">
                <h3><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                <form method="get" class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Nombre del Socio
                        </label>
                        <input type="text" class="form-control" name="nombre" 
                               value="<?= htmlspecialchars($nombre) ?>" 
                               placeholder="Buscar por nombre...">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt"></i> Fecha Desde
                        </label>
                        <input type="date" class="form-control" name="desde" 
                               value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt"></i> Fecha Hasta
                        </label>
                        <input type="date" class="form-control" name="hasta" 
                               value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">
                            <i class="fas fa-toggle-on"></i> Estado
                        </label>
                        <select class="form-select" name="status">
                            <option value="">Todos los estados</option>
                            <option value="E" <?= $status === 'E' ? 'selected' : '' ?>>Entrada</option>
                            <option value="S" <?= $status === 'S' ? 'selected' : '' ?>>Salida</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar Resultados
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de resultados -->
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="fas fa-table"></i> Registros Encontrados</h3>
                <span class="badge bg-primary fs-6"><?= count($fichadas) ?> registros</span>
            </div>
            
            <div class="table-container">
                <table id="tablaFichadas" class="table">
                    <thead>
                        <tr>
                            <th>Socio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fichadas as $f): ?>
                            <tr>
                                <td>
                                    <div class="employee-name"><?= htmlspecialchars($f['nombre']) ?></div>
                                    <div class="employee-id d-none d-md-block">N° Socio: <?= htmlspecialchars($f['id_persona']) ?></div>
                                </td>
                                <td data-order="<?= date('Y-m-d', strtotime($f['fecha'])) ?>">
                                    <div class="date-time">
                                        <div class="day-name d-none d-md-block">
                                            <?= obtenerDiaSemana($f['fecha']) ?>
                                        </div>
                                        <div>
                                            <?= date('d/m/Y', strtotime($f['fecha'])) ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-time">
                                        <?= date('H:i', strtotime($f['hora'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?= $f['status'] === 'E' ? 'status-entrada' : 'status-salida' ?>">
                                        <span class="full-text">
                                            <?= $f['status'] === 'E' ? 'Entrada' : 'Salida' ?>
                                        </span>
                                        <span class="only-mobile"><?= $f['status'] ?></span>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#tablaFichadas').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[ 1, 'desc' ]],
                pageLength: 50, // Fijo en 50 registros
                lengthChange: false, // Deshabilitar cambio de cantidad
                searching: false, // Deshabilitar búsqueda global
                responsive: false, // Deshabilitar responsive para evitar scroll
                scrollX: false, // Sin scroll horizontal
                autoWidth: false, // Sin ancho automático
                columnDefs: [
                    { width: "35%", targets: 0 }, // Socio
                    { width: "25%", targets: 1 }, // Fecha
                    { width: "20%", targets: 2 }, // Hora
                    { width: "20%", targets: 3 }  // Estado
                ],
                dom: '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                drawCallback: function() {
                    // Aplicar estilos a los badges después de cada redibujado
                    $('.status-badge').each(function() {
                        $(this).addClass('animate__animated animate__fadeIn');
                    });
                }
            });

            // Animación de carga
            $('.glass-card').each(function(index) {
                $(this).css('animation-delay', (index * 0.2) + 's');
            });

            // Mejorar la experiencia del usuario con loading
            $('form').on('submit', function() {
                $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
            });
        });
    </script>
</body>
</html>