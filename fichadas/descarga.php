
<?php
// Página para exportar diferentes datos a PDF
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Exportar Reportes - Control Horario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 700px;
            margin-top: 100px;
            padding: 2rem;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 2rem;
            color: #1e3a8a;
            text-align: center;
        }
        .btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .btn i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-file-pdf"></i> Exportar Reportes a PDF</h1>
        <a href="generar_fichadas_pdf.php" class="btn btn-danger">
            <i class="fas fa-download"></i> Descargar Fichadas (PDF)
        </a>
        <a href="generar_socios_pdf.php" class="btn btn-secondary">
            <i class="fas fa-download"></i> Descargar Lista de Socios (PDF)
        </a>
    </div>
</body>
</html>
