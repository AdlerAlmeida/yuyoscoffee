<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "yuyo"; // tu base de datos

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error al conectar con MySQL: " . $conexion->connect_error);
}
?>