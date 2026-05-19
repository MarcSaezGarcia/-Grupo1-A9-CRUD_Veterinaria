<?php

// Importamos las credenciales desde el archivo de configuración externo
include "config.php";

// Intentamos establecer la conexión con el servidor MySQL mediante la extensión mysqli
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Verificación de seguridad: si la conexión falla, detenemos la carga de la página
if (!$conn) {
    echo "<script> alert('Error de conexion') </script>";
    die("Error de conexión: " . mysqli_connect_error());
}

// Configuración del set de caracteres para evitar problemas con tildes, eñes y caracteres especiales
mysqli_set_charset($conn, "utf8mb4");

?>