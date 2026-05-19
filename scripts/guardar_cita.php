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
    // Sanitizamos los IDs numéricos mediante la función 'intval()' para garantizar claves foráneas válidas.
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
    if ($id_mascota <= 0 || $id_veterinario <= 0 || empty($fecha) || empty($hora) || empty($tipo) || empty($motivo)) {
        echo "<script>alert('Todos los campos marcados como obligatorios deben estar debidamente cumplimentados.'); window.history.back();</script>";
        exit;
    }
    
    // Regla 2: Longitud del motivo clínico (Consistencia semántica e informativa)
    if (strlen($motivo) < 3) {
        echo "<script>alert('El motivo de la cita médica debe poseer una extensión mínima de 3 caracteres.'); window.history.back();</script>";
        exit;
    }
    
    // Regla 3: Restricción cronológica / Impedir reservas en fechas pasadas
    // Compara la marca de tiempo de la cita frente al inicio del día de la fecha del servidor actual
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
        echo "<script>alert('Restricción temporal: La fecha de la cita no puede ser anterior al día de hoy.'); window.history.back();</script>";
        exit;
    }

    // Regla 4: Validaciones por Whitelist (Listas Blancas) para inputs de Tipo ENUM en la BD
    $tipos_validos   = ['Consulta', 'Operación', 'Vacuna', 'Revisión'];
    $estados_validos = ['Pendiente', 'Realizada', 'Cancelada'];
    
    if (!in_array($tipo, $tipos_validos)) { 
        echo "<script>alert('El tipo de intervención médica seleccionado no es admitido por el sistema.'); window.history.back();</script>"; 
        exit; 
    }
    if (!in_array($estado, $estados_validos)) { 
        echo "<script>alert('El estado inicial asignado a la cita no corresponde con los flujos permitidos.'); window.history.back();</script>"; 
        exit; 
    }

    // Regla 5: Normalización de campos opcionales. Si el bloque de observaciones está vacío, 
    // mapeamos la variable a NULL nativo para mantener limpia la persistencia en columnas NULL de la BD.
    $obs = ($observaciones !== '') ? $observaciones : null;
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
    $sql = "INSERT INTO citas (id_mascota, id_veterinario, fecha, hora, tipo, motivo, observaciones, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $result = mysqli_prepare($conn, $sql);
    
    // Vinculamos los tipos de datos asignados a los marcadores de posición (?):
    // "ii" -> Dos enteros correspondientes a las llaves foráneas (id_mascota, id_veterinario)
    // "ssssss" -> Seis strings consecutivas (fecha, hora, tipo, motivo, observaciones/obs, estado)
    mysqli_stmt_bind_param($result, "iissssss", $id_mascota, $id_veterinario, $fecha, $hora, $tipo, $motivo, $obs, $estado);

    // Ejecutamos de forma aislada la sentencia de inserción en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        echo "<script>alert('Nueva cita médica agendada y registrada correctamente.'); window.location='../procesos/citas/index.php';</script>";
    } else {
        // Captura de excepción en caso de violación de restricciones de llave foránea u otros fallos críticos del motor
        echo "<script>alert('Error crítico de persistencia: No se pudo salvar la cita médica en el sistema.'); window.history.back();</script>";
    }

    // Liberación de recursos de memoria y cierre definitivo de las transmisiones de datos del puente relacional
    mysqli_stmt_close($result);
    mysqli_close($conn);
} else {
    // Si se intenta invocar el script mediante accesos directos por URL u otros verbos HTTP no autorizados
    header("Location: ../procesos/citas/index.php");
    exit();
}
?>