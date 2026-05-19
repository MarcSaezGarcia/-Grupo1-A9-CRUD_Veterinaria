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
    $nombre       = trim($_POST['nombre'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    
    // Conversión forzada a tipo flotante (floatval) para garantizar un valor numérico limpio en el salario
    $salario      = floatval($_POST['salario'] ?? 0);

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)
    
    // Regla 1: Restricción de Integridad / Todos los campos esenciales deben contener datos
    if (empty($nombre) || empty($especialidad) || empty($email) || empty($telefono)) {
        echo "<script>alert('Todos los campos marcados como obligatorios deben estar cumplimentados.'); window.history.back();</script>";
        exit;
    }

    // Regla 2: Control de longitud mínima para el nombre profesional
    if (strlen($nombre) < 3) {
        echo "<script>alert('El nombre completo del facultativo debe tener al menos 3 caracteres.'); window.history.back();</script>"; 
        exit;
    }
    
    // Regla 3: Filtro de validación nativo para la sintaxis del correo electrónico institucional
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('La dirección de correo electrónico introducida no posee un formato sintáctico válido.'); window.history.back();</script>"; 
        exit;
    }
    
    // Regla 4: Validación de consistencia para el bloque salarial (Debe ser estrictamente positivo)
    if ($salario <= 0) {
        echo "<script>alert('La asignación salarial debe ser un monto numérico positivo.'); window.history.back();</script>"; 
        exit;
    }
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
    $sql    = "INSERT INTO veterinarios (nombre, especialidad, salario, email, telefono) VALUES (?, ?, ?, ?, ?)";
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los parámetros dinámicos al controlador de la sentencia:
    // "ssdds" -> "ss" (nombre, especialidad como strings), "d" (salario como double/float), "ss" (email, telefono como strings)
    mysqli_stmt_bind_param($result, "ssdss", $nombre, $especialidad, $salario, $email, $telefono);

    // Ejecutamos de forma aislada la sentencia de inserción en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Ficha del profesional veterinario dada de alta correctamente.'); window.location='../procesos/veterinarios/index.php';</script>";
    } else {
        
        // MANEJO DE EXCEPCIONES Y ERRORES DE LLAVE ÚNICA (ÍNDICES COINCIDENTES EN LA BD):
        // Si el motor devuelve el código de error 1062, significa que se violó una restricción UNIQUE.
        // En este contexto, la dirección de Email o el Teléfono ya están asignados a otro veterinario en el sistema.
        if (mysqli_errno($conn) == 1062) {
            echo "<script>alert('Operación denegada: El correo electrónico o el teléfono introducido ya se encuentran vinculados a un expediente existente.'); window.history.back();</script>";
        } else {
            // Captura genérica para prevenir fallos por desbordamiento de búfer o caídas del canal relacional
            echo "<script>alert('Error crítico de persistencia: No se pudo registrar el veterinario en el sistema.'); window.history.back();</script>";
        }
    }

    // Liberación estricta de punteros de memoria y clausura del canal de datos relacional
    mysqli_stmt_close($result);
    mysqli_close($conn);
} else {
    // Si se intenta invocar el script mediante accesos directos por URL u otros verbos HTTP no autorizados
    header("Location: ../procesos/veterinarios/index.php");
    exit();
}
?>