<?php
include "db.php";
$r = $conexion->query("SELECT * FROM productos");

$salida = [];
while ($f = $r->fetch_assoc()) {
    $salida[] = $f;
}

echo json_encode($salida);
?>
