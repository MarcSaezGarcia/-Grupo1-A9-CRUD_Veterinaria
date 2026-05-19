<?php

// Importamos el puente de conexión relacional a la base de datos ($conn)
include "../includes/conexion.php";

// CAPA DE ENRUTAMIENTO: Restringimos la ejecución de este script de manera exclusiva para 
// peticiones estructuradas bajo el protocolo de transferencia HTTP POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // RECOPILACIÓN Y DESINFECCIÓN TÁCTICA DE ENTRADAS (PAYLOAD DEL POST):
    // Extracción limpia eliminando espacios en blanco innecesarios en los extremos (trim)
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    
    // Conservamos los caracteres exactos de la clave secreta para no corromper la contraseña elegida
    $pass_raw = $_POST['password'] ?? '';

    // Colector estructurado de errores de validación
    $errores = [];

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)ç
    
    // Regla 1: Control de longitud mínima para el nombre de perfil
    if (strlen($nombre) < 3) {
        $errores[] = "El nombre completo debe contener una extensión mínima de 3 caracteres.";
    }
    
    // Regla 2: Filtro de validación nativo para estructuras de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "La dirección de correo electrónico introducida no posee un formato sintáctico válido.";
    }
    
    // Regla 3: Auditoría de robustez de contraseña mediante Expresiones Regulares (Regex)
    // Exige: Al menos 8 caracteres de longitud, una letra mayúscula y un dígito numérico
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $pass_raw)) {
        $errores[] = "La contraseña de seguridad no cumple los requisitos mínimos (8 caracteres, una mayúscula y un número).";
    }

    // Evaluación de la primera fase de validaciones
    if (count($errores) === 0) {


        // CAPA DE COMPROBACIÓN PREVENTIVA DE DUPLICADOS 

        
        // Ejecutamos una consulta parametrizada para verificar si la dirección de correo ya está asignada
        $check = mysqli_prepare($conn, "SELECT id_usuario FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        
        // Forzamos el almacenamiento interno del set de datos para poder realizar el conteo de filas
        mysqli_stmt_store_result($check);

        // Si la consulta arroja un número de filas superior a cero, el correo ya está ocupado
        if (mysqli_stmt_num_rows($check) > 0) {
            echo "<script>alert('Operación denegada: La dirección de correo electrónico ya se encuentra registrada en el sistema.'); window.history.back();</script>";
            mysqli_stmt_close($check);
            mysqli_close($conn);
            exit();
        }
        
        // Liberamos el cursor de comprobación inicial
        mysqli_stmt_close($check);
        
        // MEDIDA DE SEGURIDAD CRÍTICA: Generación del Hash de la contraseña.
        // Aplica el algoritmo estándar de la industria (Bcrypt/Argon2) de manera nativa y asíncrona,
        // garantizando que las contraseñas nunca se almacenen en texto plano dentro de la BD.
        $pass_hash = password_hash($pass_raw, PASSWORD_DEFAULT);

        // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
        $sql    = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
        $result = mysqli_prepare($conn, $sql);
        
        // Vinculamos los parámetros dinámicos asignados a los marcadores de posición (?):
        // "sss" -> Tres cadenas de texto consecutivas (nombre, email, password hash)
        mysqli_stmt_bind_param($result, "sss", $nombre, $email, $pass_hash);

        // Ejecutamos de forma aislada la sentencia de inserción en el motor de la Base de Datos
        if (mysqli_stmt_execute($result)) {
            echo "<script>alert('¡Registro de credenciales completado con éxito! Ya puede acceder a la plataforma.'); window.location='../procesos/login.php';</script>";
        } else {
            // Captura genérica para prevenir fallos por saturación del búfer o caídas del motor relacional
            echo "<script>alert('Error crítico de persistencia: No se pudo consolidar el alta de usuario.'); window.history.back();</script>";
        }

        // Liberación de recursos de la sentencia de inserción
        mysqli_stmt_close($result);

    } else {
        // PROCESAMIENTO DE EXCEPCIONES FORMULARIAS:
        // Si el array contiene elementos, unificamos los mensajes de error utilizando saltos de línea 
        // interpretables por el cuadro de diálogo de JavaScript (\n) y retornamos al usuario.
        $lista = implode('\n', $errores);
        echo "<script>alert('$lista'); window.history.back();</script>";
    }

    // Clausura definitiva del canal de datos relacional
    mysqli_close($conn);
    
} else {
    // Si se intenta invocar el script mediante accesos directos por URL u otros verbos HTTP no autorizados
    header("Location: ../procesos/login.php");
    exit();
}
?>