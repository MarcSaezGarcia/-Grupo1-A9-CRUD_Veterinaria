<?php

// Importamos el conector a la base de datos ($conn) mediante rutas relativas de dos niveles
include "../../includes/conexion.php";

// Importamos la cabecera del sitio encargada de controlar la sesión y autenticación del usuario
include "../../includes/header.php";

// Captura segura del identificador único de la mascota transmitido por parámetro URL mediante el método GET.
// Se fuerza la conversión a entero con 'intval()' para prevenir manipulaciones e inyecciones de código (SQLi).
$id = intval($_GET['id'] ?? 0);

// REQUISITO DE SEGURIDAD EXIGIDO: Sentencias Preparadas (Prepared Statements).
// Evitan vulnerabilidades de inyección SQL al separar la estructura de la consulta de los datos del usuario.
$sql = "SELECT * FROM mascotas WHERE id_mascota = ?";
$res = mysqli_prepare($conn, $sql);

// Vinculamos el parámetro dinámico. El indicador "i" define que la variable '$id' es de tipo entero.
mysqli_stmt_bind_param($res, "i", $id);

// Ejecutamos la consulta de manera aislada en el motor de la base de datos
mysqli_stmt_execute($res);

// Capturamos el conjunto de resultados y extraemos la fila correspondiente en forma de array asociativo
$m = mysqli_fetch_assoc(mysqli_stmt_get_result($res));

// Liberamos la memoria del controlador de la sentencia preparada
mysqli_stmt_close($res);

// Bloque de control de flujo: Si el identificador no coincide con ningún registro, abortamos el proceso
if (!$m) {
    echo "<script>alert('Mascota no encontrada.'); window.location='index.php';</script>";
    exit();
}

// CONSULTAS PARA LLENAR LOS SELECTORES (Relaciones estructurales con claves foráneas)
// Se ordenan alfabéticamente para facilitar la UX (Experiencia de Usuario).
$res_razas = mysqli_query($conn, "SELECT id_raza, nombre FROM razas ORDER BY nombre ASC");
$res_props = mysqli_query($conn, "SELECT id_propietario, nombre FROM propietarios ORDER BY nombre ASC");
$res_vets  = mysqli_query($conn, "SELECT id_veterinario, nombre FROM veterinarios ORDER BY nombre ASC");
?>

<div class="container mt-5">
    
    <div class="card p-4 shadow-sm border-0">
        <h2 class="mb-4">Editar Mascota: <?php echo htmlspecialchars($m['nombre']); ?></h2>
        
        <form action="../../scripts/guardar_mascota.php" method="POST" id="formMascota" novalidate>
            
            <input type="hidden" name="id_mascota" value="<?php echo $m['id_mascota']; ?>">

            <div class="row">
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Código de Chip *</label>
                    <input type="text" name="chip" id="chip" class="form-control"
                           value="<?php echo htmlspecialchars($m['chip']); ?>"
                           onblur="validar(this, 'texto')" required>
                    <span id="err-chip" class="error-msg"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nombre de la Mascota *</label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                           value="<?php echo htmlspecialchars($m['nombre']); ?>"
                           onblur="validar(this, 'texto')" required>
                    <span id="err-nombre" class="error-msg"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Sexo / Género *</label>
                    <select name="sexo" class="form-select" required>
                        <option value="Macho"  <?php if ($m['sexo'] == 'Macho')  echo 'selected'; ?>>♂ Macho</option>
                        <option value="Hembra" <?php if ($m['sexo'] == 'Hembra') echo 'selected'; ?>>♀ Hembra</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Especie *</label>
                    <select name="especie" class="form-select" required>
                        <?php foreach (['Perro','Gato','Ave','Reptil','Otro'] as $esp): ?>
                            <option value="<?php echo $esp; ?>" <?php if ($m['especie'] == $esp) echo 'selected'; ?>>
                                <?php echo $esp; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Fecha de Nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" id="fecha" class="form-control"
                           value="<?php echo $m['fecha_nacimiento']; ?>"
                           max="<?php echo date('Y-m-d'); ?>"
                           onblur="validar(this, 'fecha')" required>
                    <span id="err-fecha" class="error-msg"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Raza Clasificada *</label>
                    <select name="id_raza" class="form-select" required>
                        <?php while ($r = mysqli_fetch_assoc($res_razas)): ?>
                            <option value="<?php echo $r['id_raza']; ?>"
                                <?php if ($r['id_raza'] == $m['id_raza']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($r['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Propietario / Cliente *</label>
                    <select name="id_propietario" class="form-select" required>
                        <?php while ($p = mysqli_fetch_assoc($res_props)): ?>
                            <option value="<?php echo $p['id_propietario']; ?>"
                                <?php if ($p['id_propietario'] == $m['id_propietario']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($p['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Veterinario de Cabecera *</label>
                    <select name="id_veterinario" class="form-select" required>
                        <?php while ($v = mysqli_fetch_assoc($res_vets)): ?>
                            <option value="<?php echo $v['id_veterinario']; ?>"
                                <?php if ($v['id_veterinario'] == $m['id_veterinario']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($v['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" name="update" class="btn btn-warning">Actualizar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>

<?php 
// Importamos el archivo de cierre estructural HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>