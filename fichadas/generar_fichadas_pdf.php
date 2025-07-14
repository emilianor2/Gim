<?php
require_once __DIR__ . '/dompdf/vendor/autoload.php'; // ✅ NUEVA RUTA para tu estructura

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=c1412397_reloj", "c1412397_reloj", "Saurina0746");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT nombre, id_persona, fecha, hora, status FROM hik ORDER BY fecha_hora DESC LIMIT 200");
    $fichadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar: " . $e->getMessage());
}

$html = '<h2 style="text-align:center;">Listado de Fichadas</h2>
<table border="1" cellspacing="0" cellpadding="5" width="100%">
<thead>
<tr style="background-color:#f2f2f2;">
    <th>Nombre</th>
    <th>ID</th>
    <th>Fecha</th>
    <th>Hora</th>
    <th>Estado</th>
</tr>
</thead>
<tbody>';

foreach ($fichadas as $f) {
    $html .= "<tr>
        <td>{$f['nombre']}</td>
        <td>{$f['id_persona']}</td>
        <td>" . date('d/m/Y', strtotime($f['fecha'])) . "</td>
        <td>" . date('H:i', strtotime($f['hora'])) . "</td>
        <td>{$f['status']}</td>
    </tr>";
}

$html .= '</tbody></table>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("fichadas.pdf", ["Attachment" => true]);
?>
