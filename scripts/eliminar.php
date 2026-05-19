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

// CAPA DE VALIDACIÓN Y CONTROL DE ENTRADAS (MÉTODO GET):
// Evaluamos que tanto el identificador como el nombre de la entidad destino estén presentes.
if (isset($_GET['tabla']) && isset($_GET['id'])) {

    // Sanitizamos el parámetro forzando su conversión a entero puro (intval)
    $id = intval($_GET['id']);
    $tabla = $_GET['tabla'];

    // MEDIDA DE SEGURIDAD CRÍTICA (SQL INJECTION PREVENTION): Lista Blanca de Tablas (Whitelist).
    // Las sentencias preparadas nativas de SQL no permiten parametrizar marcadores de posición (?) 
    // en identificadores estructurales como nombres de tablas o columnas. Para mitigar la inyección SQL 
    // por concatenación dinámica, mapeamos de forma estricta las entidades permitidas.
    $tablas_permitidas = [
        'mascotas'     => ['col' => 'id_mascota',     'back' => 'mascotas'],
        'propietarios' => ['col' => 'id_propietario', 'back' => 'propietarios'],
        'veterinarios' => ['col' => 'id_veterinario', 'back' => 'veterinarios'],
        'razas'        => ['col' => 'id_raza',        'back' => 'razas'],
    ];

    // Control de flujo: Si la tabla enviada por la URL no coincide exactamente con las llaves de nuestro array, abortamos.
    if (!array_key_exists($tabla, $tablas_permitidas)) {
        echo "<script>alert('Intento de acceso no autorizado: La entidad solicitada no existe o está protegida.'); window.history.back();</script>";
        exit();
    }

    // Extracción segura de la estructura relacional tras pasar el control de la lista blanca
    $col  = $tablas_permitidas[$tabla]['col'];
    $back = $tablas_permitidas[$tabla]['back'];

    // Al haber validado previamente los nombres de la tabla y columna mediante la lista blanca, 
    // la concatenación directa aquí es 100% segura. El valor dinámico ($id) se pasa mediante marcador (?)
    $sql       = "DELETE FROM $tabla WHERE $col = ?";
    $resultado = mysqli_prepare($conn, $sql);
    
    // Vinculamos el parámetro dinámico. El indicador "i" define que la variable '$id' es de tipo entero.
    mysqli_stmt_bind_param($resultado, "i", $id);

    // Ejecutamos de forma aislada la sentencia de borrado en el motor de la Base de Datos
    if (mysqli_stmt_execute($resultado)) {
        // Confirmación visual y redirección síncrona hacia el índice del módulo correspondiente
        echo "<script>alert('El registro ha sido eliminado correctamente del sistema.'); window.location='../procesos/$back/index.php';</script>";
    } else {
        
        // MANEJO DE EXCEPCIONES DE INTEGRIDAD REFERENCIAL (LLAVES FORÁNEAS - FK):
        // Si el motor de la base de datos devuelve el código de error 1451, significa que no podemos 
        // eliminar este registro debido a que existen filas secundarias que dependen de él 
        // (Por ejemplo: Intentar borrar un propietario que aún tiene mascotas vinculadas).
        if (mysqli_errno($conn) == 1451) {
            echo "<script>alert('Operación denegada: No se puede eliminar el registro debido a que tiene un historial médico o dependencias asociadas en el sistema.'); window.history.back();</script>";
        } else {
            // Captura genérica para fallos de red, caídas de sesión del motor o bloqueo de tablas
            echo "<script>alert('Error crítico de persistencia: No se pudo completar la purga de datos.'); window.history.back();</script>";
        }
    }

    // Liberación estricta de punteros de memoria y clausura del canal de datos relacional
    mysqli_stmt_close($resultado);
    mysqli_close($conn);

} else {
    // Redirección preventiva si se intenta invocar el script sin los parámetros obligatorios
    header("Location: ../index.php");
    exit();
}
?>