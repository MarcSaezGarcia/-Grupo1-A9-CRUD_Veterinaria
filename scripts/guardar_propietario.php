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
    $telefono = trim($_POST['telefono'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    
    // Normalización estricta: Forzamos el documento DNI/NIE a letras mayúsculas uniformes
    $dni      = strtoupper(trim($_POST['dni'] ?? ''));

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)

    // Regla 1: Restricción de Integridad / Todos los campos esenciales deben contener datos
    if (empty($nombre) || empty($dni) || empty($telefono) || empty($email)) {
        echo "<script>alert('Todos los campos son estrictamente obligatorios.'); window.history.back();</script>"; 
        exit;
    }
    
    // Regla 2: Control de longitud mínima para el nombre corporativo o del cliente
    if (strlen($nombre) < 3) {
        echo "<script>alert('El nombre del propietario debe contener al menos 3 caracteres.'); window.history.back();</script>"; 
        exit;
    }
    
    // Regla 3: Filtro de validación nativo para estructuras de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('La dirección de correo electrónico introducida no posee un formato sintáctico válido.'); window.history.back();</script>"; 
        exit;
    }
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
    $sql    = "INSERT INTO propietarios (nombre, DNI, telefono, email) VALUES (?, ?, ?, ?)";
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los parámetros dinámicos al controlador de la sentencia:
    // "ssss" -> Cuatro strings consecutivas (nombre, DNI, telefono, email)
    mysqli_stmt_bind_param($result, "ssss", $nombre, $dni, $telefono, $email);

    // Ejecutamos de forma aislada la sentencia de inserción en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Ficha del propietario creada y consolidada correctamente.'); window.location='../procesos/propietarios/index.php';</script>";
    } else {
        
        // MANEJO DE EXCEIPCIONES Y ERRORES DE LLAVE ÚNICA (DOCK/ÍNDICE DUPLICADO):
        // Si el motor devuelve el código de error 1062, significa que se violó una restricción UNIQUE.
        // En este contexto, el documento DNI o la dirección de Email ya constan en la base de datos.
        if (mysqli_errno($conn) == 1062) {
            echo "<script>alert('Operación denegada: El DNI o el correo electrónico introducido ya se encuentran vinculados a un expediente existente.'); window.history.back();</script>";
        } else {
            // Captura genérica para prevenir fallos por desbordamiento o caídas del canal relacional
            echo "<script>alert('Error crítico de persistencia: No se pudo registrar el propietario en el sistema.'); window.history.back();</script>";
        }
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