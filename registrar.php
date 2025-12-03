<?php
// Línea 2: Incluye el archivo de conexión a la base de datos
include "db.php";

// Línea 4: Decodifica los datos JSON recibidos
$data = json_decode(file_get_contents("php://input"), true);

// =======================================================================
// SOLUCIÓN 1: Manejo de Warnings (Líneas 6 y 7)
// Usar el operador de fusión de nulos (??) para evitar que $data sea null.
// Si $data no se pudo decodificar, usamos un array vacío.
$data = $data ?? []; 
// =======================================================================

// Aseguramos que las claves existan para evitar errores.
$total = $data["total"] ?? 0; // Usar 0 si 'total' no existe
$items = $data["items"] ?? []; // Usar un array vacío si 'items' no existe

// 1. Crear venta - CORRECCIÓN DEL ERROR FATAL
// Usamos Sentencias Preparadas (PREPARE, BIND, EXECUTE) para la seguridad

// a. Prepara la consulta para INSERT INTO ventas (total) VALUES (?)
$stmt_venta = $conexion->prepare("INSERT INTO ventas (total) VALUES (?)");
$stmt_venta->bind_param("d", $total); // 'd' para double/decimal

if ($stmt_venta->execute()) {
    // b. Obtener el ID insertado
    $idVenta = $conexion->insert_id;
} else {
    // Manejo de error si la inserción falla
    die("Error al insertar venta: " . $stmt_venta->error);
}
$stmt_venta->close();

// 2. Insertar cada línea de venta - CORRECCIÓN Y SEGURIDAD
// Preparamos la sentencia para detalle_venta fuera del bucle
$stmt_detalle = $conexion->prepare("
    INSERT INTO detalle_venta (venta_id, producto, sabor, tamano, unidades, precio_unit, precio_linea) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
// 'i': integer (venta_id), 's': string (producto, sabor, tamano), 'i': integer (unidades), 'd': double (precios)
$stmt_detalle->bind_param("isssidd", $idVenta, $p, $s, $t, $u, $pu, $pl);

foreach ($items as $item) {
    // Asignar variables desde el ítem (usamos $item en lugar de $l)
    $p = $item["producto"] ?? '';
    $s = $item["sabor"] ?? '';
    $t = $item["tamano"] ?? '';
    $u = $item["unidades"] ?? 0;
    $pu = $item["unitPrice"] ?? 0.0;
    $pl = $item["linePrice"] ?? 0.0;

    // Ejecutar la sentencia preparada
    $stmt_detalle->execute();
}
$stmt_detalle->close();

echo "VENTA OK";
?>