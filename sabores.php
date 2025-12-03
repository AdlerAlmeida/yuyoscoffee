<?php
// Línea 2: Incluye el archivo de conexión a la base de datos
include "db.php";


// Verifica si 'producto_id' existe en $_GET. Si no existe, usa 0 o un valor seguro.
$id = $_GET["producto_id"] ?? 0;


// Inicializa la variable de salida
$salida = [];

// 1. Prepara la consulta con un marcador de posición (?)
$stmt = $conexion->prepare("SELECT * FROM sabores WHERE producto_id = ?");

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
    // Manejo de error si la ejecución de la consulta falla
    // Opcional, pero útil para depuración.
    // die("Error de consulta: " . $stmt->error);
}

// 6. Cierra la sentencia preparada
$stmt->close();

// Línea 12: Devuelve el resultado en formato JSON
echo json_encode($salida);
?>