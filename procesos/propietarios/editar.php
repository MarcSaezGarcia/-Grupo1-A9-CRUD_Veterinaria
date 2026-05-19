<?php

// Importamos el conector a la base de datos ($conn) mediante rutas relativas de dos niveles
include "../../includes/conexion.php";

// Importamos la cabecera del sitio encargada de controlar la sesión y autenticación del usuario
include "../../includes/header.php";

// Captura segura del identificador único del propietario transmitido por parámetro URL mediante el método GET.
// Se fuerza la conversión a entero con 'intval()' para prevenir manipulaciones e inyecciones de código (SQLi).
$id = intval($_GET['id'] ?? 0);

// REQUISITO DE SEGURIDAD EXIGIDO: Sentencias Preparadas (Prepared Statements).
// Evitan vulnerabilidades de inyección SQL al separar la estructura de la consulta de los datos del usuario.
$sql = "SELECT * FROM propietarios WHERE id_propietario = ?";
$res = mysqli_prepare($conn, $sql);

// Vinculamos el parámetro dinámico. El indicador "i" define que la variable '$id' es de tipo entero.
mysqli_stmt_bind_param($res, "i", $id);

// Ejecutamos la consulta de manera aislada en el motor de la base de datos
mysqli_stmt_execute($res);

// Capturamos el conjunto de resultados y extraemos la fila correspondiente en forma de array asociativo
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($res));

// Liberamos la memoria del controlador de la sentencia preparada
mysqli_stmt_close($res);

// Bloque de control de flujo: Si el identificador no coincide con ningún registro, abortamos el proceso
if (!$p) {
    echo "<script>alert('Propietario no encontrado.'); window.location='index.php';</script>";
    exit();
}
?>

<div class="container mt-4">
    
    <div class="card p-4 shadow-sm border-0">
        <h2 class="mb-4">✏️ Editar Propietario: <?php echo htmlspecialchars($p['nombre']); ?></h2>
        
        <form action="../../scripts/actualizar_propietario.php" method="POST" id="formPropietario" novalidate>
            
            <input type="hidden" name="id" value="<?php echo $p['id_propietario']; ?>">
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre Completo *</label>
                <input type="text" name="nombre" id="nom_prop"
                       value="<?php echo htmlspecialchars($p['nombre']); ?>" 
                       class="form-control" onblur="validar(this, 'texto')" required>
                <span id="err-nom_prop" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Documento de Identidad (DNI) *</label>
                <input type="text" name="DNI" id="dni" maxlength="9"
                       value="<?php echo htmlspecialchars($p['DNI'] ?? ''); ?>" 
                       class="form-control" onblur="validar(this, 'texto')" required>
                <span id="err-dni" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Teléfono de Contacto *</label>
                <input type="tel" name="telefono" id="tel" maxlength="15"
                       value="<?php echo htmlspecialchars($p['telefono']); ?>" 
                       class="form-control" onblur="validar(this, 'texto')" required>
                <span id="err-tel" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Correo Electrónico (Email) *</label>
                <input type="email" name="email" id="email_p"
                       value="<?php echo htmlspecialchars($p['email']); ?>" 
                       class="form-control" onblur="validar(this, 'email')" required>
                <span id="err-email_p" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary text-white">Actualizar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Cancelar y Volver</a>
            </div>
            
        </form>
    </div>
</div>

<?php 
// Importamos el archivo de cierre estructural HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>