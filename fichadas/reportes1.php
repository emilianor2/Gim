<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=labelsolut_reloj", "labelsolut_reloj", "emilianor2");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT fecha, COUNT(*) AS cantidad FROM hik GROUP BY fecha ORDER BY fecha");
    $porDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("
        SELECT nombre, COUNT(*) AS cantidad 
        FROM hik 
        WHERE YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1) 
        GROUP BY nombre
        ORDER BY cantidad DESC
    ");
    $topSemana = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("
        SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COUNT(*) AS cantidad 
        FROM hik 
        GROUP BY mes 
        ORDER BY mes
    ");
    $porMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT status, COUNT(*) AS cantidad FROM hik GROUP BY status");
    $porTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT nombre, COUNT(*) AS cantidad FROM hik GROUP BY nombre ORDER BY cantidad DESC");
    $porPersona = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de Fichadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 70px;
            background-color: #f0f2f5;
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
        .header-left {
            display: flex;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
        }
        .header-left img {
            height: 40px;
            margin-right: 10px;
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
        .chart-container {
            border: 2px solid black;
            padding: 10px;
            border-radius: 8px;
            background: white;
            margin-bottom: 40px;
        }
        .row > div[class^="col-"] {
            padding-left: 15px;
            padding-right: 15px;
        }
        .container {
            padding-left: 30px;
            padding-right: 30px;
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
<div class="container mt-4">
    <h2 class="mb-4">Reportes de Fichadas</h2>

    <div class="row">
        <div class="col-md-6 chart-container">
            <canvas id="fichadasDia"></canvas>
        </div>
        <div class="col-md-6 chart-container">
            <canvas id="topSemana"></canvas>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 chart-container">
            <canvas id="fichadasTipo"></canvas>
        </div>
        <div class="col-md-6 chart-container">
            <canvas id="fichadasPersona"></canvas>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 chart-container">
            <canvas id="fichadasMes"></canvas>
        </div>
    </div>
</div>

<script>
    new Chart(document.getElementById('fichadasDia'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($porDia, 'fecha')) ?>,
            datasets: [{
                label: 'Fichadas por día',
                data: <?= json_encode(array_column($porDia, 'cantidad')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)'
            }]
        },
        options: { responsive: true }
    });

    new Chart(document.getElementById('topSemana'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($topSemana, 'nombre')) ?>,
            datasets: [{
                label: 'Fichadas esta semana',
                data: <?= json_encode(array_column($topSemana, 'cantidad')) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)'
            }]
        },
        options: { responsive: true, indexAxis: 'y' }
    });

    new Chart(document.getElementById('fichadasTipo'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($porTipo, 'status')) ?>,
            datasets: [{
                label: 'Tipos de fichadas',
                data: <?= json_encode(array_column($porTipo, 'cantidad')) ?>,
                backgroundColor: ['rgba(255, 205, 86, 0.7)', 'rgba(153, 102, 255, 0.7)']
            }]
        },
        options: { responsive: true }
    });

    new Chart(document.getElementById('fichadasPersona'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($porPersona, 'nombre')) ?>,
            datasets: [{
                label: 'Fichadas totales por persona',
                data: <?= json_encode(array_column($porPersona, 'cantidad')) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.7)'
            }]
        },
        options: { responsive: true, indexAxis: 'y' }
    });

    new Chart(document.getElementById('fichadasMes'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($porMes, 'mes')) ?>,
            datasets: [{
                label: 'Fichadas por mes',
                data: <?= json_encode(array_column($porMes, 'cantidad')) ?>,
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.3
            }]
        },
        options: { responsive: true }
    });
</script>
</body>
</html>
