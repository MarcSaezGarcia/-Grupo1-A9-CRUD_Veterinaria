<?php

// Inicializamos el entorno de sesiones de PHP para rastrear tokens de autenticación activos
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
    // Sanitizamos el ID numérico mediante la función 'intval()' para mitigar corrupciones en el WHERE.
    $id       = intval($_POST['id'] ?? 0);
    
    // Extracción limpia eliminando espacios en blanco innecesarios en los extremos (trim)
    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    
    // Nota de consistencia: Se añade el campo DNI en la recolección para mantener la simetría 
    // con la estructura relacional definida en database.sql y sus formularios de gestión.
    $dni      = trim($_POST['DNI'] ?? '');

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)
    
    // Regla 1: Restricción de Integridad / Todos los campos esenciales deben contener datos
    if ($id <= 0 || empty($nombre) || empty($dni) || empty($telefono) || empty($email)) {
        echo "<script>alert('Todos los campos marcados como obligatorios (incluyendo el DNI) deben estar cumplimentados.'); window.history.back();</script>";
        exit;
    }
    
    // Regla 2: Filtro de validación nativo para estructuras de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('La dirección de correo electrónico introducida no posee un formato sintáctico válido.'); window.history.back();</script>"; 
        exit;
    }
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
    $sql    = "UPDATE propietarios SET nombre = ?, DNI = ?, telefono = ?, email = ? WHERE id_propietario = ?";
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los parámetros dinámicos al controlador de la sentencia:
    // "ssss" -> Cuatro strings consecutivas (nombre, DNI, telefono, email)
    // "i"    -> Un entero final para la cláusula condicional (id_propietario)
    mysqli_stmt_bind_param($result, "ssssi", $nombre, $dni, $telefono, $email, $id);

    // Ejecutamos de forma aislada la sentencia en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Expediente del propietario actualizado y consolidado correctamente.'); window.location='../procesos/propietarios/index.php';</script>";
    } else {
        // Captura de excepción en caso de colisión de índices únicos (DNI o Email duplicados) o fallos del motor
        echo "<script>alert('Error crítico de persistencia: No se pudieron actualizar los datos (Verifique si el DNI o Email ya existen).'); window.history.back();</script>";
    }

    // Liberación estricta de punteros de memoria y clausura del canal de datos relacional
    mysqli_stmt_close($result);
    mysqli_close($conn);
} else {
    // Si se intenta invocar el script mediante accesos directos por URL u otros verbos HTTP no autorizados
    header("Location: ../procesos/propietarios/index.php");
    exit();
}
?>