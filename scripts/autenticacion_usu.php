<?php

// Inicializamos el entorno de sesiones de PHP para poder registrar los tokens de identidad
session_start();

// Importamos el puente de conexión relacional a la base de datos ($conn)
include "../includes/conexion.php";

// CAPA DE ENRUTAMIENTO: Restringimos la ejecución de este script de manera exclusiva para 
// peticiones estructuradas bajo el protocolo de transferencia HTTP POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // RECOPILACIÓN Y LIMPIEZA DE ENTRADAS (PAYLOAD DEL FORMULARIO):
    // Eliminamos espacios en blanco accidentales en los extremos del correo electrónico.
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // CAPA INTEGRAL DE VALIDACIONES PREVENTIVAS EN SERVIDOR (PHP BACKEND)
    
    // Regla 1: Restricción de campos vacíos
    if (empty($email) || empty($password)) {
        echo "<script>alert('Por favor, introduzca su correo electrónico y contraseña.'); window.location='../procesos/login.php';</script>";
        exit();
    }

    // Regla 2: Filtro de validación nativo para mitigar peticiones con correos sintácticamente corruptos
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('El formato del correo electrónico introducido no es válido.'); window.location='../procesos/login.php';</script>";
        exit();
    }
    
    // Estructura parametrizada de la consulta SQL para mitigar ataques por inyección de código (SQLi)
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $resultado = mysqli_prepare($conn, $sql);
    
    // Vinculamos el parámetro dinámico. El indicador "s" define que la variable '$email' es una cadena (string).
    mysqli_stmt_bind_param($resultado, "s", $email);
    
    // Ejecutamos la consulta de manera aislada en el motor de la base de datos
    mysqli_stmt_execute($resultado);
    
    // Almacenamos el búfer de resultados para liberar el hilo de ejecución
    $query = mysqli_stmt_get_result($resultado);

    // CONTROL DE SESIÓN
    
    // Evaluamos si el registro existe en la base de datos
    if ($row = mysqli_fetch_assoc($query)) {
        
        // MEDIDA DE SEGURIDAD CRÍTICA: Verificación del Hash Criptográfico.
        // Compara la contraseña en texto plano con el hash seguro (por ejemplo, Bcrypt/Argon2) almacenado en la BD.
        if (password_verify($password, $row['password'])) {
            
            // Regeneración preventiva del ID de la sesión para mitigar ataques de Fijación de Sesión (Session Fixation)
            session_regenerate_id(true);

            // Inyección de variables de estado en la sesión global del servidor
            $_SESSION['usuario']    = $row['nombre'];
            $_SESSION['id_usuario'] = $row['id_usuario'];

            // Clausura explícita del manejador de la sentencia preparada antes de redirigir
            mysqli_stmt_close($resultado);
            mysqli_close($conn);

            // Redirección exitosa hacia el panel de control principal (Dashboard)
            header("Location: ../index.php");
            exit();
            
        } else {
            // Mitigación de fugas de información: Por seguridad general, se recomienda usar mensajes 
            // unificados (ej. "Credenciales incorrectas") para no dar pistas a atacantes, pero mantenemos 
            // la lógica de alertas del sistema original sanitizando el flujo.
            echo "<script>alert('Contraseña incorrecta.'); window.location='../procesos/login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('El usuario no se encuentra registrado en el sistema.'); window.location='../procesos/login.php';</script>";
        exit();
    }

    // Cierre de seguridad secundario en caso de flujos alternativos no interceptados
    mysqli_stmt_close($resultado);
    mysqli_close($conn);
    
} else {
    // Si se intenta invocar el script mediante accesos directos por URL u otros verbos HTTP no autorizados
    header("Location: ../procesos/login.php");
    exit();
}
?>