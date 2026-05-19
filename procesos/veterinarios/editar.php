<?php

// Importamos el conector a la base de datos ($conn) mediante rutas relativas de dos niveles
include "../../includes/conexion.php";

// Importamos la cabecera del sitio encargada de controlar la sesión y autenticación del usuario
include "../../includes/header.php";

// Captura segura del identificador único del veterinario transmitido por parámetro URL mediante el método GET.
// Se fuerza la conversión a entero con 'intval()' para prevenir manipulaciones e inyecciones de código (SQLi).
$id = intval($_GET['id'] ?? 0);

// REQUISITO DE SEGURIDAD EXIGIDO: Sentencias Preparadas (Prepared Statements).
// Evitan vulnerabilidades de inyección SQL al separar la estructura de la consulta de los datos del usuario.
$sql = "SELECT * FROM veterinarios WHERE id_veterinario = ?";
$res = mysqli_prepare($conn, $sql);

// Vinculamos el parámetro dinámico. El indicador "i" define que la variable '$id' es de tipo entero.
mysqli_stmt_bind_param($res, "i", $id);

// Ejecutamos la consulta de manera aislada en el motor de la base de datos
mysqli_stmt_execute($res);

// Capturamos el conjunto de resultados y extraemos la fila correspondiente en forma de array asociativo
$v = mysqli_fetch_assoc(mysqli_stmt_get_result($res));

// Liberamos la memoria del controlador de la sentencia preparada
mysqli_stmt_close($res);

// Bloque de control de flujo: Si el identificador no coincide con ningún registro, abortamos el proceso
if (!$v) {
    echo "<script>alert('Veterinario no encontrado.'); window.location='index.php';</script>";
    exit();
}
?>

<div class="container mt-4">
    
    <div class="card p-4 shadow-sm border-0">
        <h2 class="mb-4">Editar Veterinario: <?php echo htmlspecialchars($v['nombre']); ?></h2>
        
        <form action="../../scripts/actualizar_veterinario.php" method="POST" id="formVeterinario" novalidate>
            
            <input type="hidden" name="id" value="<?php echo $v['id_veterinario']; ?>">
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre Completo *</label>
                <input type="text" name="nombre" id="nom_vet"
                       value="<?php echo htmlspecialchars($v['nombre']); ?>" 
                       class="form-control" onblur="validar(this, 'texto')" required>
                <span id="err-nom_vet" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="row">
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Especialidad Clínica *</label>
                    <input type="text" name="especialidad" id="esp"
                           value="<?php echo htmlspecialchars($v['especialidad']); ?>" 
                           class="form-control" onblur="validar(this, 'texto')" required>
                    <span id="err-esp" class="error-msg text-danger small mt-1 d-block"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Asignación Salarial Mensual (€) *</label>
                    <input type="number" step="0.01" name="salario" 
                           value="<?php echo htmlspecialchars($v['salario']); ?>" 
                           class="form-control" min="0" required>
                </div>
                
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Teléfono de Contacto *</label>
                <input type="tel" name="telefono" id="tel_vet" maxlength="15"
                       value="<?php echo htmlspecialchars($v['telefono'] ?? ''); ?>" 
                       class="form-control" onblur="validar(this, 'texto')" required>
                <span id="err-tel_vet" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Institucional *</label>
                <input type="email" name="email" id="mail_vet"
                       value="<?php echo htmlspecialchars($v['email'] ?? ''); ?>" 
                       class="form-control" onblur="validar(this, 'email')" required>
                <span id="err-mail_vet" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-warning">Actualizar Datos</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
            
        </form>
    </div>
</div>

<?php 
// Importamos el archivo de cierre estructural HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>