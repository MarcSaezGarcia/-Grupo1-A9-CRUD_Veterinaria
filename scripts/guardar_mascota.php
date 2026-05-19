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
    // Sanitizamos los identificadores relacionales y forzamos su conversión a tipo entero puro (intval).
    $id_raza  = intval($_POST['id_raza']        ?? 0);
    $id_prop  = intval($_POST['id_propietario'] ?? 0);
    $id_vet   = intval($_POST['id_veterinario'] ?? 0);
    
    // El parámetro flag '$editar' determina si la operación transaccional es un UPDATE (> 0) o un INSERT (== 0)
    $editar   = isset($_POST['id_mascota']) ? intval($_POST['id_mascota']) : 0;

    // Extracción limpia eliminando espacios en blanco innecesarios en los extremos (trim)
    $chip     = trim($_POST['chip']    ?? '');
    $nombre   = trim($_POST['nombre']  ?? '');
    $sexo     = $_POST['sexo']         ?? '';
    $especie  = $_POST['especie']      ?? '';
    $fecha    = $_POST['fecha_nacimiento'] ?? '';

    // CAPA INTEGRAL DE VALIDACIONES DE NEGOCIO EN SERVIDOR (PHP BACKEND)
    
    // Regla 1: Restricción de Integridad / Campos Obligatorios Vacíos o IDs Corruptos
    if (empty($chip) || empty($nombre) || empty($fecha) || $id_raza <= 0 || $id_prop <= 0 || $id_vet <= 0) {
        echo "<script>alert('Todos los campos marcados como obligatorios deben estar debidamente cumplimentados.'); window.history.back();</script>";
        exit;
    }
    
    // Regla 2: Control de longitud mínima para el nombre de la mascota
    if (strlen($nombre) < 3) {
        echo "<script>alert('El nombre de la mascota debe contener al menos 3 caracteres.'); window.history.back();</script>";
        exit;
    }
    
    // Regla 3: Restricción cronológica / Impedir registros con fechas de nacimiento futuras
    if (strtotime($fecha) > time()) {
        echo "<script>alert('Restricción temporal: La fecha de nacimiento no puede ser posterior al día de hoy.'); window.history.back();</script>";
        exit;
    }

    // Regla 4: Validaciones por Whitelist (Listas Blancas) para inputs estructurados (ENUM / Opciones fijas)
    $sexos_validos    = ['Macho', 'Hembra'];
    $especies_validas = ['Canina', 'Felina', 'Equina', 'Exótico']; // Adaptar según las especies soportadas en tu BD
    
    if (!in_array($sexo, $sexos_validos)) {
        echo "<script>alert('El sexo seleccionado no coincide con las opciones permitidas.'); window.history.back();</script>";
        exit;
    }
    if (!in_array($especie, $especies_validas)) {
        echo "<script>alert('La especie seleccionada no está catalogada en el sistema.'); window.history.back();</script>";
        exit;
    }

    if ($editar > 0) {
        // OPERACIÓN DE ACTUALIZACIÓN (UPDATE): Modifica un expediente clínico preexistente
        $sql = "UPDATE mascotas SET 
                    chip = ?, 
                    nombre = ?, 
                    sexo = ?, 
                    especie = ?, 
                    fecha_nacimiento = ?, 
                    id_raza = ?, 
                    id_propietario = ?, 
                    id_veterinario = ? 
                WHERE id_mascota = ?";
                
        $result = mysqli_prepare($conn, $sql);
        
        // Corregido el tipado estricto del bind_param conforme al conteo de variables:
        // "sssssiii" -> 5 strings (chip, nom, sexo, esp, fecha) y 3 enteros (raza, prop, vet)
        // El último entero "i" mapea el ID de la cláusula condicional WHERE ($editar)
        // Cadena de formato resultante: "sssssiiii"
        mysqli_stmt_bind_param($result, "sssssiiii", $chip, $nombre, $sexo, $especie, $fecha, $id_raza, $id_prop, $id_vet, $editar);
        
    } else {
        // OPERACIÓN DE INSERCIÓN (INSERT): Registra un nuevo paciente en la base de datos
        $sql = "INSERT INTO mascotas (chip, nombre, sexo, especie, fecha_nacimiento, id_raza, id_propietario, id_veterinario) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
        $result = mysqli_prepare($conn, $sql);
        
        // Corregido el tipado estricto del bind_param para el flujo de inserción:
        // Estaban pasándose 8 variables pero la cadena de definición original tenía "ssssssii" (6 strings y 2 enteros).
        // Se corrige a: 5 strings (chip, nom, sexo, esp, fecha) y 3 enteros (raza, prop, vet).
        // Cadena de formato resultante: "sssssiii"
        mysqli_stmt_bind_param($result, "sssssiii", $chip, $nombre, $sexo, $especie, $fecha, $id_raza, $id_prop, $id_vet);
    }

    // Ejecutamos de forma aislada la sentencia preparada en el motor de la Base de Datos
    if (mysqli_stmt_execute($result)) {
        $msg = $editar > 0 ? 'Expediente de la mascota actualizado correctamente.' : 'Nueva mascota registrada y asignada correctamente.';
        echo "<script>alert('$msg'); window.location='../procesos/mascotas/index.php';</script>";
    } else {
        
        // MANEJO DE EXCEPCIONES Y ERRORES DE LLAVE ÚNICA (DOCK/CHIP DUPLICADO):
        // Si el motor devuelve el código de error 1062, significa que se violó una restricción UNIQUE.
        // En este contexto, el código de microchip ya pertenece a otro animal dentro de la base de datos.
        if (mysqli_errno($conn) == 1062) {
            echo "<script>alert('Operación denegada: El número de microchip introducido ya se encuentra registrado a nombre de otro paciente.'); window.history.back();</script>";
        } else {
            // Captura genérica para prevenir fallos en restricciones de llaves foráneas o saturación del búfer
            echo "<script>alert('Error crítico de persistencia: No se pudo consolidar la transacción en la base de datos.'); window.history.back();</script>";
        }
    }

    // Liberación estricta de punteros de memoria y clausura del canal de datos relacional
    mysqli_stmt_close($result);
    mysqli_close($conn);
} else {
    // Si se intenta invocar el script mediante accesos directos por URL u otros verbos HTTP no autorizados
    header("Location: ../procesos/mascotas/index.php");
    exit();
}
?>