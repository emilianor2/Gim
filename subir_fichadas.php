<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=c1412397_reloj", "c1412397_reloj", "Saurina0746");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_persona  = $_POST['id_persona']  ?? null;
    $fecha_hora  = $_POST['fecha_hora']  ?? null;
    $fecha       = $_POST['fecha']       ?? null;
    $hora        = $_POST['hora']        ?? null;
    $nombre      = $_POST['nombre']      ?? null;
    $status      = $_POST['status']      ?? null;

    if (!$id_persona)  exit("⚠️ Falta id_persona");
    if (!$fecha_hora)  exit("⚠️ Falta fecha_hora");
    if (!$fecha)       exit("⚠️ Falta fecha");
    if (!$hora)        exit("⚠️ Falta hora");
    if (!$nombre)      exit("⚠️ Falta nombre");
    if (!$status)      exit("⚠️ Falta status");

    $stmt = $pdo->prepare("INSERT INTO hik (id_persona, fecha_hora, fecha, hora, nombre, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_persona, $fecha_hora, $fecha, $hora, $nombre, $status]);

    echo "✅ Fichada guardada correctamente";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
