<?php

// Importamos el conector a la base de datos ($conn) mediante rutas relativas de dos niveles
include "../../includes/conexion.php";

// Importamos la cabecera del sitio encargada de controlar la sesión y autenticación del usuario
include "../../includes/header.php";

// Captura segura del identificador único de la raza transmitido por parámetro URL mediante el método GET.
// Se fuerza la conversión a entero con 'intval()' para prevenir manipulaciones e inyecciones de código (SQLi).
$id = intval($_GET['id'] ?? 0);

// REQUISITO DE SEGURIDAD EXIGIDO: Sentencias Preparadas (Prepared Statements).
// Evitan vulnerabilidades de inyección SQL al separar la estructura de la consulta de los datos del usuario.
$sql = "SELECT * FROM razas WHERE id_raza = ?";
$res = mysqli_prepare($conn, $sql);

// Vinculamos el parámetro dinámico. El indicador "i" define que la variable '$id' es de tipo entero.
mysqli_stmt_bind_param($res, "i", $id);

// Ejecutamos la consulta de manera aislada en el motor de la base de datos
mysqli_stmt_execute($res);

// Capturamos el conjunto de resultados y extraemos la fila correspondiente en forma de array asociativo
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($res));

// Liberamos la memoria del controlador de la sentencia preparada
mysqli_stmt_close($res);

// Bloque de control de flujo: Si el identificador no coincide con ningún registro, abortamos el proceso
if (!$r) {
    echo "<script>alert('Raza no encontrada.'); window.location='index.php';</script>";
    exit();
}
?>

<div class="container mt-5">
    
    <div class="card p-4 shadow-sm border-0 border-start border-warning border-4">
        <h2 class="mb-4">Editar Raza: <?php echo htmlspecialchars($r['nombre']); ?></h2>
        
        <form action="../../scripts/actualizar_raza.php" method="POST" id="formRaza" novalidate>
            
            <input type="hidden" name="id" value="<?php echo $r['id_raza']; ?>">
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre de la Raza *</label>
                <input type="text" name="nombre" id="nom_raza"
                       value="<?php echo htmlspecialchars($r['nombre']); ?>" 
                       class="form-control" onblur="validar(this, 'texto')" required>
                <span id="err-nom_raza" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Características Físicas y Morfología *</label>
                <textarea name="fisico" id="fisico" class="form-control" rows="3" 
                          onblur="validar(this, 'texto')" required><?php echo htmlspecialchars($r['caracteristicas_fisicas'] ?? ''); ?></textarea>
                <span id="err-fisico" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Temperamento y Comportamiento Habitual *</label>
                <textarea name="comportamiento" id="compo" class="form-control" rows="3" 
                          onblur="validar(this, 'texto')" required><?php echo htmlspecialchars($r['comportamiento'] ?? ''); ?></textarea>
                <span id="err-compo" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Volver al Listado</a>
            </div>
            
        </form>
    </div>
</div>

<?php 
// Importamos el archivo de cierre estructural HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>