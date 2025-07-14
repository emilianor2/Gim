<?php
$pdo = new PDO("mysql:host=localhost;dbname=reloj", "root", "emilianor2");

$lastIdFile = 'ultimo_id.txt';
$lastId = file_exists($lastIdFile) ? (int)file_get_contents($lastIdFile) : 0;

$stmt = $pdo->prepare("SELECT * FROM hik WHERE id > ? ORDER BY id ASC");
$stmt->execute([$lastId]);
$fichadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nuevoUltimoId = $lastId;

foreach ($fichadas as $f) {
    if (
        empty($f['fecha']) ||
        empty($f['hora']) ||
        empty($f['status']) ||
        empty($f['id_persona']) ||
        empty($f['nombre']) ||
        empty($f['fecha_hora'])
    ) {
        echo "⛔ Fichada ID {$f['id']} incompleta. Saltada.\n";
        continue;
    }

    $post = array(
        'id_persona' => $f['id_persona'],
        'fecha_hora' => $f['fecha_hora'],
        'fecha'      => $f['fecha'],
        'hora'       => $f['hora'],
        'nombre'     => $f['nombre'],
        'status'     => $f['status']
    );

    $ch = curl_init("http://emilianor.com.ar/subir_fichadas.php");
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
    ));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "📤 Enviado ID {$f['id']} ({$f['fecha']} {$f['hora']}) → Respuesta: $response (HTTP $http_code)\n";

    if ($http_code === 200 && strpos($response, '✅') !== false) {
        $nuevoUltimoId = $f['id'];
    }
}

file_put_contents($lastIdFile, $nuevoUltimoId);
?>
