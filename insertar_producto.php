<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

include "db.php";
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if(json_last_error() !== JSON_ERROR_NONE){
    echo json_encode(['status'=>'error','code'=>'BAD_JSON','msg'=>json_last_error_msg()]);
    exit;
}

$clave      = $data["clave"] ?? null;
$nombre     = $data["nombre"] ?? null;
$cantidad   = isset($data["cantidad"]) ? (int)$data["cantidad"] : null;
$minimo     = isset($data["min"]) ? (int)$data["min"] : null;
$categoria  = $data["cat"] ?? null;

if(!$clave || !$nombre || $cantidad === null || $minimo === null || !$categoria){
    echo json_encode(['status'=>'error','code'=>'MISSING_FIELDS','msg'=>'Faltan campos requeridos']);
    exit;
}

$sql = "
INSERT INTO inventario (clave, nombre, cantidad, minimo, categoria)
VALUES (?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    cantidad = VALUES(cantidad),
    minimo = VALUES(minimo),
    categoria = VALUES(categoria)
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssiss", $clave, $nombre, $cantidad, $minimo, $categoria);

if($stmt->execute()){
    echo json_encode(['status'=>'ok','msg'=>'Producto insertado/actualizado']);
} else {
    echo json_encode(['status'=>'error','code'=>'EXEC_FAILED','msg'=>$stmt->error]);
}

$stmt->close();
?>
