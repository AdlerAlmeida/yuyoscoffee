<?php
include "db.php";

header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "msg" => "Datos no recibidos"]);
    exit;
}

$clave = $conexion->real_escape_string($data["clave"]);
$nombre = $conexion->real_escape_string($data["nombre"]);
$cantidad = intval($data["cantidad"]);
$min = intval($data["min"]);
$cat = $conexion->real_escape_string($data["cat"]);

// Comprobar si existe el producto
$sqlCheck = "SELECT clave FROM inventario WHERE clave='$clave'";
$resultCheck = $conexion->query($sqlCheck);

if ($resultCheck->num_rows > 0) {
    // ⚡ Si existe → actualizar
    $sqlUpdate = "UPDATE inventario SET 
        nombre='$nombre',
        cantidad=$cantidad,
        minimo=$min,
        categoria='$cat'
        WHERE clave='$clave'";

    if ($conexion->query($sqlUpdate)) {
        echo json_encode(["status" => "ok", "msg" => "Producto actualizado"]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al actualizar"]);
    }

} else {
    // 🟢 Si NO existe → insertar
    $sqlInsert = "INSERT INTO inventario (clave, nombre, cantidad, minimo, categoria) 
                  VALUES ('$clave', '$nombre', $cantidad, $min, '$cat')";

    if ($conexion->query($sqlInsert)) {
        echo json_encode(["status" => "ok", "msg" => "Producto agregado"]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al insertar"]);
    }
}

?>
