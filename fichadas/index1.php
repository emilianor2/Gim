<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=labelsolut_reloj", "labelsolut_reloj", "emilianor2");
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fichadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background: #f0f2f5;
            padding-top: 70px;
        }
        header {
            position: fixed;
            top: 0;
            width: 100%;
            height: 60px;
            background-color: #003366;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
            border-bottom: 2px solid black;
        }
        header img {
            height: 40px;
            margin-right: 10px;
        }
        .header-left {
            display: flex;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
        }
        .header-right a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
        }
        .header-right a:hover {
            text-decoration: underline;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
        }
        table {
            border-collapse: collapse;
            border: 3px solid #000;
            width: 100%;
        }
        table th, table td {
            border: 2px solid #ccc;
            padding: 8px;
        }
        table th {
            background-color: #003366;
            color: white;
        }
        table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .contenedor-fichadas {
            background-color: #e0e0e0;
            border: 2px solid #000;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        input, select, button {
            border: 0.1px solid #333 !important;
            box-shadow: none !important;
        }
        a.btn-secondary {
            background-color: #ccc;
            color: #000;
            border-color: #000;
        }
        /* Ocultar buscador global de DataTables */
        div.dataTables_filter {
            display: none;
        }
    </style>
</head>
<body>
<header>
    <div class="header-left">
        <img src="img/logo.png" alt="Logo">
        Control Horario S.A.
    </div>
    <div class="header-right">
        <a href="index.php">Inicio</a>
        <a href="reportes.php">Reportes</a>
    </div>
</header>
<div class="container contenedor-fichadas">
    <h2 class="mb-4">Registro de Fichadas</h2>

    <form method="get" class="row g-3 align-items-center mb-4">
        <div class="col-md-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Desde</label>
            <input type="date" class="form-control" name="desde" value="<?= htmlspecialchars($fecha_desde) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Hasta</label>
            <input type="date" class="form-control" name="hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="">Todos</option>
                <option value="E" <?= $status === 'E' ? 'selected' : '' ?>>E</option>
                <option value="S" <?= $status === 'S' ? 'selected' : '' ?>>S</option>
            </select>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="index.php" class="btn btn-secondary">Limpiar filtros</a>
        </div>
    </form>

    <table id="tablaFichadas" class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Persona</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fichadas as $f): ?>
                <tr>
                    <td><?= $f['id'] ?></td>
                    <td><?= htmlspecialchars($f['id_persona']) ?></td>
                    <td><?= htmlspecialchars($f['nombre']) ?></td>
                    <td><?= htmlspecialchars($f['fecha']) ?></td>
                    <td><?= htmlspecialchars($f['hora']) ?></td>
                    <td><?= htmlspecialchars($f['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#tablaFichadas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[ 3, 'desc' ]]
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
