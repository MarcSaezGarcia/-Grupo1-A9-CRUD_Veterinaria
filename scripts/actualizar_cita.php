<?php

// Inicializamos el entorno de sesiones de PHP para interceptar tokens de identidad activos
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
    // Sanitizamos los IDs numéricos mediante la función 'intval()' para mitigar inyecciones accidentales.
    $id             = intval($_POST['id']             ?? 0);
    $id_mascota     = intval($_POST['id_mascota']     ?? 0);
    $id_veterinario = intval($_POST['id_veterinario'] ?? 0);
    
    // Extracción limpia de cadenas de texto y asignación de valores por defecto (Null-coalescing)
    $fecha          = $_POST['fecha']          ?? '';
    $hora           = $_POST['hora']           ?? '';
    $tipo           = $_POST['tipo']           ?? '';
    $motivo         = trim($_POST['motivo']    ?? '');
    $observaciones  = trim($_POST['observaciones'] ?? '');
    $estado         = $_POST['estado']         ?? 'Pendiente';

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)
    
    // Regla 1: Restricción de Integridad / Campos Obligatorios Vacíos o IDs Corruptos
    if ($id <= 0 || $id_mascota <= 0 || $id_veterinario <= 0 || empty($fecha) || empty($hora) || empty($tipo) || empty($motivo)) {
        echo "<script>alert('Todos los campos marcados como obligatorios deben estar debidamente cumplimentados.'); window.history.back();</script>";
        exit;
    }
    
    // Regla 2: Longitud del motivo clínico (Consistencia semántica e informativa)
    if (strlen($motivo) < 3) {
        echo "<script>alert('El motivo de la cita médica debe poseer una extensión mínima de 3 caracteres.'); window.history.back();</script>";
        exit;
    }

    // Regla 3: Validación por Whitelist (Listas Blancas) para inputs de Tipo ENUM en la BD
    $tipos_validos   = ['Consulta', 'Operación', 'Vacuna', 'Revisión'];
    $estados_validos = ['Pendiente', 'Realizada', 'Cancelada'];
    
    if (!in_array($tipo, $tipos_validos)) { 
        echo "<script>alert('El tipo de intervención médica seleccionado no es válido en el sistema.'); window.history.back();</script>"; 
        exit; 
    }
    if (!in_array($estado, $estados_validos)) { 
        echo "<script>alert('El estado clínico asignado a la cita no corresponde con los flujos permitidos.'); window.history.back();</script>"; 
        exit; 
    }

    // Regla 4: Normalización de datos opcionales. Si el bloque de observaciones está vacío, 
    // mapeamos la variable a NULL nativo para evitar inyectar cadenas vacías en columnas que aceptan nulos.
    $obs = ($observaciones !== '') ? $observaciones : null;
    
    // Estructura parametrizada de la consulta SQL para evitar ataques por inyección de código (SQLi)
    $sql = "UPDATE citas SET 
                id_mascota = ?, 
                id_veterinario = ?, 
                fecha = ?, 
                hora = ?, 
                tipo = ?, 
                motivo = ?, 
                observaciones = ?, 
                estado = ? 
            WHERE id_cita = ?";
            
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los tipos de datos: 
    // "ii" -> Dos enteros (id_mascota, id_veterinario)
    // "ssssss" -> Seis strings (fecha, hora, tipo, motivo, observaciones, estado)
    // "i" -> Un entero final (id_cita para la cláusula condicional WHERE)
    mysqli_stmt_bind_param($result, "iissssssi", $id_mascota, $id_veterinario, $fecha, $hora, $tipo, $motivo, $obs, $estado, $id);

    // Ejecutamos de forma aislada la sentencia preparada en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Cita médica actualizada y consolidada correctamente.'); window.location='../procesos/citas/index.php';</script>";
    } else {
        // Captura de excepción en caso de fallos de concurrencia o restricciones de llave foránea
        echo "<script>alert('Error crítico de persistencia: No se pudo actualizar el registro médico.'); window.history.back();</script>";
    }

    // Liberación estricta de punteros de memoria y clausura del canal de datos relacional
    mysqli_stmt_close($result);
    mysqli_close($conn);
} else {
    // Si se intenta invocar el script mediante accesos directos por GET u otros verbos HTTP no autorizados
    header("Location: ../procesos/citas/index.php");
    exit();
}
?>