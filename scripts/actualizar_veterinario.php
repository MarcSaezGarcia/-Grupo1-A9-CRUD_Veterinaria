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
    $id           = intval($_POST['id'] ?? 0);
    
    // Extracción limpia eliminando espacios en blanco innecesarios en los extremos (trim)
    $nombre       = trim($_POST['nombre'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    
    // Conversión forzada a tipo flotante (floatval) para la validación de montos monetarios/numéricos
    $salario      = floatval($_POST['salario'] ?? 0);
    
    // Nota de consistencia: Se añaden los campos 'telefono' y 'email' en la recolección para mantener 
    // la simetría absoluta con el formulario de edición y la estructura relacional de la tabla.
    $telefono     = trim($_POST['telefono'] ?? '');
    $email        = trim($_POST['email'] ?? '');

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)
    
    // Regla 1: Restricción de Integridad / Comprobación de campos esenciales vacíos o ID corrupto
    if ($id <= 0 || empty($nombre) || empty($especialidad) || empty($telefono) || empty($email)) {
        echo "<script>alert('Todos los campos marcados como obligatorios deben estar cumplimentados.'); window.history.back();</script>";
        exit;
    }

    // Regla 2: Control de longitud mínima para el nombre corporativo/profesional
    if (strlen($nombre) < 3) {
        echo "<script>alert('El nombre completo debe tener al menos 3 caracteres.'); window.history.back();</script>"; 
        exit;
    }
    
    // Regla 3: Validación de consistencia para el bloque salarial (Debe ser numérico y estrictamente positivo)
    if ($salario <= 0) {
        echo "<script>alert('La asignación salarial debe ser un monto numérico positivo.'); window.history.back();</script>"; 
        exit;
    }

    // Regla 4: Filtro de validación nativo para la sintaxis del correo electrónico institucional
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('La dirección de correo electrónico introducida no posee un formato válido.'); window.history.back();</script>"; 
        exit;
    }
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi).
    // Se expande la sentencia UPDATE para incluir y consolidar de manera simétrica el teléfono y el email.
    $sql    = "UPDATE veterinarios SET nombre = ?, especialidad = ?, salario = ?, telefono = ?, email = ? WHERE id_veterinario = ?";
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los parámetros dinámicos al controlador de la sentencia:
    // "ssdds" -> Dos strings (nombre, esp), un double/float (salario) y dos strings (tel, email)
    // "i"     -> Un entero final para mapear la llave primaria condicional (id_veterinario)
    mysqli_stmt_bind_param($result, "ssdssi", $nombre, $especialidad, $salario, $telefono, $email, $id);

    // Ejecutamos de forma aislada la sentencia en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Ficha profesional del veterinario actualizada y consolidada correctamente.'); window.location='../procesos/veterinarios/index.php?msg=success';</script>";
    } else {
        // Captura de excepción en caso de colisión de restricciones de llave única (Email duplicado)
        echo "<script>alert('Error crítico de persistencia: No se pudieron actualizar los datos (Verifique si el Email ya se encuentra asignado).'); window.history.back();</script>";
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