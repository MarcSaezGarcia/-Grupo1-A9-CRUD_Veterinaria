<?php

// Importamos el archivo de conexión que inicializa la variable de control relacional $conn
include "../../includes/conexion.php";

// Importamos la cabecera común global, que además verifica el estado de autenticación por sesión
include "../../includes/header.php";

// CONSULTAS SIMPLES DE RELACIONES DE TABLAS:
// Extraemos los identificadores primarios y descriptores para popular dinámicamente los listados "select".
// El puntero resultante se procesará mediante estructuras de control iterativas dentro del marcado HTML5.
$res_razas = mysqli_query($conn, "SELECT id_raza, nombre FROM razas ORDER BY nombre ASC");
$res_props = mysqli_query($conn, "SELECT id_propietario, nombre FROM propietarios ORDER BY nombre ASC");
$res_vets  = mysqli_query($conn, "SELECT id_veterinario, nombre FROM veterinarios ORDER BY nombre ASC");
?>

<div class="container mt-5">
    
    <div class="card p-4 shadow-sm border-0">
        <h2 class="mb-4">Registrar Nueva Mascota</h2>
        
        <form action="../../scripts/guardar_mascota.php" method="POST" id="formMascota" novalidate>
            
            <div class="row">
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Código de Chip *</label>
                    <input type="text" name="chip" id="chip" class="form-control" 
                           placeholder="Ej. 7240980000XXXXX" onblur="validar(this, 'texto')" required>
                    <span id="err-chip" class="error-msg"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nombre de la Mascota *</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" 
                           placeholder="Ej. Rocky" onblur="validar(this, 'texto')" required>
                    <span id="err-nombre" class="error-msg"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Sexo / Género *</label>
                    <select name="sexo" class="form-select" required>
                        <option value="Macho">♂ Macho</option>
                        <option value="Hembra">♀ Hembra</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Fecha de Nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" id="fecha" class="form-control" 
                           max="<?php echo date('Y-m-d'); ?>" onblur="validar(this, 'fecha')" required>
                    <span id="err-fecha" class="error-msg"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Raza Clasificada *</label>
                    <select name="id_raza" class="form-select" required>
                        <option value="">— Selecciona una raza —</option>
                        <?php 
                        // Bucle iterativo de extracción asociativa sobre la consulta de la tabla 'razas'
                        while($r = mysqli_fetch_assoc($res_razas)): 
                            echo "<option value='{$r['id_raza']}'>" . htmlspecialchars($r['nombre']) . "</option>"; 
                        endwhile; 
                        ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Propietario / Cliente *</label>
                    <select name="id_propietario" class="form-select" required>
                        <option value="">— Selecciona el propietario —</option>
                        <?php 
                        // Extraemos secuencialmente los datos del búfer de MySQL asignados al cliente legal
                        while($p = mysqli_fetch_assoc($res_props)): 
                            echo "<option value='{$p['id_propietario']}'>" . htmlspecialchars($p['nombre']) . "</option>"; 
                        endwhile; 
                        ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Veterinario de Cabecera *</label>
                    <select name="id_veterinario" class="form-select" required>
                        <option value="">— Asigna un facultativo —</option>
                        <?php 
                        // Generamos las opciones del control select renderizando el identificador numérico interno
                        while($v = mysqli_fetch_assoc($res_vets)): 
                            echo "<option value='{$v['id_veterinario']}'>" . htmlspecialchars($v['nombre']) . "</option>"; 
                        endwhile; 
                        ?>
                    </select>
                </div>
                
            </div>
            
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-success">Guardar Mascota</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
            
        </form>
    </div>
</div>

<?php 
// Importamos el archivo de cierre de estructura HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>