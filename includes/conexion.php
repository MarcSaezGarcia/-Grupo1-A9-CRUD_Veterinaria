<?php

include "config.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Verificación de la conexión
if (!$conn) {
    echo "<script> alert('Error de conexion')</script>";
    die("Error de conexión: " . mysqli_connect_error());
}

?>
