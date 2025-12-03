<?php
// Línea 2: Incluye el archivo de conexión a la base de datos
include "db.php";

// Línea 4: Decodifica los datos JSON recibidos (probablemente de una solicitud AJAX)
$data = json_decode(file_get_contents("php://input"), true);
// ESTO CORRIGE el primer Warning que viste al evitar acceder a una clave en un array 'null'.
$ingredientes = $data['ingredientes'] ?? []; // Usa el array vacío [] si $data['ingredientes'] es null
// Los marcadores son para los valores que van a cambiar ($uso y $clave)
$stmt = $conexion->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE clave = ?");
// ESTO EVITA la inyección SQL, ya que MySQL sabe exactamente qué es dato y qué es código.
$stmt->bind_param("is", $uso, $clave);
// 3. EJECUTAR EN BUCLE
// Ahora, el bucle foreach es más limpio y seguro:
foreach ($ingredientes as $clave => $uso) {
    // 4. Se ejecutan los valores vinculados para cada iteración
    $stmt->execute();
}
// 5. CERRAR LA SENTENCIA
$stmt->close();

// Línea 14: Mensaje de éxito
echo "OK";
?>