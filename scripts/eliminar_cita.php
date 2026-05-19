<?php

// Inicializamos el entorno de sesiones de PHP para rastrear tokens de autenticación activos
session_start();

// Importamos el puente de conexión relacional a la base de datos ($conn)
include "../includes/conexion.php";

// Verificamos de forma estricta que exista una sesión activa.
// Si no la hay, se aborta el flujo de ejecución y se fuerza el desvío hacia la pantalla de Login.
if (!isset($_SESSION['usuario'])) {
    header("Location: ../procesos/login.php"); 
    exit();
}

// CAPA DE VALIDACIÓN Y CONTROL DE ENTRADAS (MÉTODO GET):
// Evaluamos que el parámetro identificador esté presente en la URL y corresponda a una estructura numérica.
if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    // Sanitizamos el parámetro forzando su conversión a entero puro (intval)
    $id = intval($_GET['id']);

    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
    $sql       = "DELETE FROM citas WHERE id_cita = ?";
    $resultado = mysqli_prepare($conn, $sql);
    
    // Vinculamos el parámetro dinámico. El indicador "i" define que la variable '$id' es de tipo entero.
    mysqli_stmt_bind_param($resultado, "i", $id);

    // Ejecutamos de forma aislada la sentencia de borrado en el motor de la Base de Datos
    if (mysqli_stmt_execute($resultado)) {
        // Confirmación visual y redirección síncrona hacia el índice de control de citas
        echo "<script>alert('Cita médica eliminada correctamente del registro activo.'); window.location='../procesos/citas/index.php';</script>";
    } else {
        // Captura de excepción en caso de que la cita esté vinculada a restricciones de integridad (Llaves foráneas)
        echo "<script>alert('Error crítico de persistencia: No se pudo eliminar la cita (Verifique dependencias históricas).'); window.history.back();</script>";
    }

    // Liberación estricta de punteros de memoria y clausura del canal de datos relacional
    mysqli_stmt_close($resultado);
    mysqli_close($conn);

} else {
    // Redirección preventiva si se intenta invocar el script sin un identificador válido o corrupto
    header("Location: ../procesos/citas/index.php");
    exit();
}
?>