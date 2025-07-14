<?php
require_once __DIR__ . '/dompdf/vendor/autoload.php'; // Ajustado a tu estructura

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=c1412397_reloj", "c1412397_reloj", "Saurina0746");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT DISTINCT id_persona, nombre FROM hik ORDER BY nombre ASC");
    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar: " . $e->getMessage());
}

$html = '<h2 style="text-align:center;">Listado de Socios</h2>
<table border="1" cellspacing="0" cellpadding="5" width="100%">
<thead>
<tr style="background-color:#f2f2f2;">
    <th>ID</th>
    <th>Nombre</th>
</tr>
</thead>
<tbody>';

foreach ($socios as $s) {
    $html .= "<tr>
        <td>{$s['id_persona']}</td>
        <td>{$s['nombre']}</td>
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
$dompdf->stream("lista_socios.pdf", ["Attachment" => true]);
?>
