<?php
// Línea 2: Incluye el archivo de conexión a la base de datos
include "db.php";

// Usa el operador de fusión de nulos (??). Si 'producto_id' no existe en $_GET, usa 0.
$id = $_GET["producto_id"] ?? 0;

// Inicializa el array de salida
$salida = [];


// Esto corrige el Error Fatal de sintaxis y previene la Inyección SQL.


// 1. Prepara la consulta con un marcador de posición (?)
$stmt = $conexion->prepare("SELECT * FROM tamanos WHERE producto_id = ?");

// 2. Vincula el parámetro. 'i' significa que $id es un entero (integer).
$stmt->bind_param("i", $id);

// 3. Ejecuta la consulta
if ($stmt->execute()) {
    // 4. Obtiene el objeto de resultado
    $r = $stmt->get_result();
    
    // 5. Itera sobre los resultados y los añade al array $salida
    while ($f = $r->fetch_assoc()) {
        $salida[] = $f;
    }

    // Libera la memoria del resultado
    $r->free();
} else {
    // Opcional: manejar el error de ejecución de la consulta
    // die("Error de consulta: " . $stmt->error);
}

// 6. Cierra la sentencia preparada
$stmt->close();

// Línea 12: Devuelve el resultado en formato JSON
echo json_encode($salida);
?>