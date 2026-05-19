<?php

// Inicializamos el entorno de sesiones de PHP para comprobar tokens de autenticación activos
session_start();

// Importamos el puente de conexión relacional a la base de datos ($conn)
include "../includes/conexion.php";

// CAPA GUARDRAIL (AUTENTICACIÓN): Verificamos de forma estricta que exista una sesión activa.
// Si no la hay, se aborta el flujo de ejecución y se fuerza el desvío hacia la pantalla de Login.
if (!isset($_SESSION['usuario'])) {
    header("Location: ../procesos/login.php"); 
    exit();
}

// CAPA DE ENRUTAMIENTO: Restringimos la ejecución de este script de manera exclusiva para 
// peticiones estructuradas bajo el protocolo de transferencia HTTP POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // RECOPILACIÓN Y DESINFECCIÓN TÁCTICA DE ENTRADAS (PAYLOAD DEL POST):
    // Extracción limpia eliminando espacios en blanco innecesarios en los extremos (trim)
    $nombre   = trim($_POST['nombre'] ?? '');
    $fisico   = trim($_POST['fisico'] ?? '');
    $comporta = trim($_POST['comportamiento'] ?? '');

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)
    
    // Control de longitud mínima y campos vacíos de forma unificada.
    // Garantiza que las descripciones etológicas y morfológicas aporten valor clínico real (Mín. 3 caracteres)
    if (strlen($nombre) < 3 || strlen($fisico) < 3 || strlen($comporta) < 3) {
        echo "<script>alert('Todos los campos son estrictamente obligatorios y deben contener una extensión mínima de 3 caracteres.'); window.history.back();</script>"; 
        exit();
    }
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi).
    // Coincide simétricamente con el esquema relacional de la entidad: 'nombre', 'caracteristicas_fisicas' y 'comportamiento'.
    $sql    = "INSERT INTO razas (nombre, caracteristicas_fisicas, comportamiento) VALUES (?, ?, ?)";
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los parámetros dinámicos al controlador de la sentencia:
    // "sss" -> Tres cadenas de texto consecutivas (nombre, caracteristicas_fisicas, comportamiento)
    mysqli_stmt_bind_param($result, "sss", $nombre, $fisico, $comporta);

    // Ejecutamos de forma aislada la sentencia de inserción en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Nueva ficha taxonómica de raza dada de alta correctamente.'); window.location='../procesos/razas/index.php';</script>";
    } else {
        
        // MANEJO DE EXCEPCIONES Y ERRORES DE LLAVE ÚNICA (ÍNDICE/NOMBRE DUPLICADO):
        // Si el motor devuelve el código de error 1062, significa que se violó una restricción UNIQUE.
        // Evita que se dupliquen registros taxonómicos idénticos en el sistema.
        if (mysqli_errno($conn) == 1062) {
            echo "<script>alert('Operación denegada: El nombre de la raza introducido ya se encuentra registrado en el sistema.'); window.history.back();</script>";
        } else {
            // Captura genérica para prevenir fallos por desbordamiento de búfer o caídas del motor
            echo "<script>alert('Error crítico de persistencia: No se pudo registrar la nueva raza.'); window.history.back();</script>";
        }
    }

    // Liberación estricta de punteros de memoria y clausura del canal de datos relacional
    mysqli_stmt_close($result);
    mysqli_close($conn);
} else {
    // Si se intenta invocar el script mediante accesos directos por URL u otros verbos HTTP no autorizados
    header("Location: ../procesos/razas/index.php");
    exit();
}
?>