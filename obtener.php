<?php
include "db.php";

$sql = "SELECT * FROM inventario";
$result = $conexion->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[$row["clave"]] = [
        "nombre" => $row["nombre"],
        "cantidad" => $row["cantidad"],
        "min" => $row["minimo"],
        "cat" => $row["categoria"]
    ];
}

echo json_encode($data);
?>
