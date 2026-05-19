<?php
// Importamos el archivo de conexión y la cabecera con validación de sesión
include "../../includes/conexion.php";
include "../../includes/header.php";

// CONSULTA RELACIONAL (JOIN): Recuperamos las mascotas cruzando con sus propietarios
$mascotas = mysqli_fetch_all(mysqli_query($conn,
    "SELECT m.id_mascota, m.nombre, m.especie, p.nombre AS propietario
     FROM mascotas m JOIN propietarios p ON m.id_propietario = p.id_propietario
     ORDER BY m.nombre ASC"), MYSQLI_ASSOC);

// CONSULTA SIMPLE: Recuperamos todos los veterinarios disponibles
$vets = mysqli_fetch_all(mysqli_query($conn,
    "SELECT id_veterinario, nombre, especialidad FROM veterinarios ORDER BY nombre ASC"), MYSQLI_ASSOC);
?>
<!-- Formulario -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Nueva Cita</h2>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="../../scripts/guardar_cita.php" method="POST" id="formCita" novalidate>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mascota *</label>
                        <select name="id_mascota" id="id_mascota" class="form-select" onblur="validar(this, 'select')" required>
                            <option value="">— Selecciona mascota —</option>
                            <?php foreach ($mascotas as $m): ?>
                                <option value="<?php echo $m['id_mascota']; ?>">
                                    <?php echo htmlspecialchars($m['nombre']); ?> 
                                    (<?php echo htmlspecialchars($m['especie']); ?> — <?php echo htmlspecialchars($m['propietario']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span id="err-id_mascota" class="error-msg"></span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Veterinario *</label>
                        <select name="id_veterinario" id="id_veterinario" class="form-select" onblur="validar(this, 'select')" required>
                            <option value="">— Selecciona veterinario —</option>
                            <?php foreach ($vets as $v): ?>
                                <option value="<?php echo $v['id_veterinario']; ?>">
                                    <?php echo htmlspecialchars($v['nombre']); ?> (<?php echo htmlspecialchars($v['especialidad']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span id="err-id_veterinario" class="error-msg"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha *</label>
                        <input type="date" name="fecha" id="fecha" class="form-control"
                               min="<?php echo date('Y-m-d'); ?>"
                               onblur="validarFechaCita(this)" required>
                        <span id="err-fecha" class="error-msg"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hora *</label>
                        <input type="time" name="hora" id="hora" class="form-control" required>
                        <span id="err-hora" class="error-msg"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo de visita *</label>
                        <select name="tipo" id="tipo" class="form-select" onblur="validar(this, 'select')" required>
                            <option value="">— Selecciona tipo —</option>
                            <option value="Consulta">Consulta</option>
                            <option value="Operación">Operación</option>
                            <option value="Vacuna">Vacuna</option>
                            <option value="Revisión">Revisión</option>
                        </select>
                        <span id="err-tipo" class="error-msg"></span>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Motivo *</label>
                        <input type="text" name="motivo" id="motivo" class="form-control"
                               placeholder="Describe brevemente el motivo de la visita"
                               onblur="validar(this, 'texto')" required>
                        <span id="err-motivo" class="error-msg"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="Pendiente" selected>Pendiente</option>
                            <option value="Realizada">✅ Realizada</option>
                            <option value="Cancelada">❌ Cancelada</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"
                                  placeholder="Instrucciones previas, alergias, notas adicionales..."></textarea>
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success">Guardar Cita</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>