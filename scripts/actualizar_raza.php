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
    // Sanitizamos el ID numérico mediante la función 'intval()' para asegurar una cláusula WHERE íntegra.
    $id       = intval($_POST['id'] ?? 0);
    
    // Extracción limpia eliminando espacios en blanco innecesarios en los extremos (trim)
    $nombre   = trim($_POST['nombre'] ?? '');
    $fisico   = trim($_POST['fisico'] ?? '');
    $comporta = trim($_POST['comportamiento'] ?? '');

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)
    
    // Regla 1: Restricción de Integridad / Comprobación de existencia de ID válido
    if ($id <= 0) {
        echo "<script>alert('Identificador de registro no válido.'); window.history.back();</script>";
        exit;
    }

    // Regla 2: Control de longitud mínima y campos vacíos de forma unificada
    // Garantiza que las descripciones etológicas y morfológicas aporten valor clínico real (Mín. 3 caracteres)
    if (strlen($nombre) < 3 || strlen($fisico) < 3 || strlen($comporta) < 3) {
        echo "<script>alert('Todos los campos son obligatorios y deben contener una extensión mínima de 3 caracteres.'); window.history.back();</script>"; 
        exit;
    }
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
    // Coincide con las columnas de la entidad: 'nombre', 'caracteristicas_fisicas' y 'comportamiento'
    $sql    = "UPDATE razas SET nombre = ?, caracteristicas_fisicas = ?, comportamiento = ? WHERE id_raza = ?";
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los parámetros dinámicos al controlador de la sentencia:
    // "sss" -> Tres cadenas de texto consecutivas (nombre, caracteristicas_fisicas, comportamiento)
    // "i"   -> Un entero final para mapear la llave primaria condicional (id_raza)
    mysqli_stmt_bind_param($result, "sssi", $nombre, $fisico, $comporta, $id);

    // Ejecutamos de forma aislada la sentencia en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Ficha taxonómica de la raza actualizada y consolidada correctamente.'); window.location='../procesos/razas/index.php';</script>";
    } else {
        // Captura de excepción en caso de errores en restricciones del motor o nombres duplicados si existiera un índice UNIQUE
        echo "<script>alert('Error crítico de persistencia: No se pudieron actualizar los datos de la raza.'); window.history.back();</script>";
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