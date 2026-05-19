<?php

// Importamos el conector a la base de datos ($conn) mediante rutas relativas de dos niveles
include "../../includes/conexion.php";

// Importamos la cabecera del sitio encargada de controlar la sesión y autenticación del usuario
include "../../includes/header.php";

// Captura segura del identificador único de la cita transmitido por parámetro URL mediante el método GET.
// Se fuerza la conversión a entero con 'intval()' para prevenir manipulaciones e inyecciones de código.
$id = intval($_GET['id'] ?? 0);

// REQUISITO DE SEGURIDAD EXIGIDO POR EL PROFESOR: Sentencias Preparadas (Prepared Statements).
// Evitan vulnerabilidades de inyección SQL (SQLi) al separar la estructura de la consulta de los datos del usuario.
$sql = "SELECT * FROM citas WHERE id_cita = ?";
$res = mysqli_prepare($conn, $sql);

// Vinculamos el parámetro dinámico. El indicador "i" define que la variable '$id' es de tipo entero.
mysqli_stmt_bind_param($res, "i", $id);

// Ejecutamos la consulta en el motor de la base de datos de manera aislada
mysqli_stmt_execute($res);

// Capturamos el conjunto de resultados y extraemos la fila correspondiente en forma de array asociativo
$c = mysqli_fetch_assoc(mysqli_stmt_get_result($res));

// Liberamos la memoria del controlador de la sentencia preparada
mysqli_stmt_close($res);

// Bloque de control: Si el identificador no coincide con ninguna fila de la base de datos, abortamos el proceso
if (!$c) {
    echo "<script>alert('Cita no encontrada.'); window.location='index.php';</script>";
    exit();
}

// CONSULTA RELACIONAL (JOIN): Recuperamos las mascotas y propietarios para alimentar el desplegable de opciones.
// Mantiene seleccionada la mascota original gracias a la posterior validación interna del bucle.
$mascotas = mysqli_fetch_all(mysqli_query($conn,
    "SELECT m.id_mascota, m.nombre, m.especie, p.nombre AS propietario
     FROM mascotas m JOIN propietarios p ON m.id_propietario = p.id_propietario
     ORDER BY m.nombre ASC"), MYSQLI_ASSOC);

// CONSULTA SIMPLE: Recuperamos todos los veterinarios disponibles para la reasignación de personal en la cita.
$vets = mysqli_fetch_all(mysqli_query($conn,
    "SELECT id_veterinario, nombre, especialidad FROM veterinarios ORDER BY nombre ASC"), MYSQLI_ASSOC);
?>

<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Editar Cita</h2>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            
            <form action="../../scripts/actualizar_cita.php" method="POST" id="formCita" novalidate>
                
                <input type="hidden" name="id" value="<?php echo $c['id_cita']; ?>">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mascota *</label>
                        <select name="id_mascota" id="id_mascota" class="form-select" required>
                            <option value="">— Selecciona mascota —</option>
                            <?php foreach ($mascotas as $m): ?>
                                <option value="<?php echo $m['id_mascota']; ?>"
                                    <?php if ($m['id_mascota'] == $c['id_mascota']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($m['nombre']); ?>
                                    (<?php echo htmlspecialchars($m['especie']); ?> — <?php echo htmlspecialchars($m['propietario']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Veterinario *</label>
                        <select name="id_veterinario" id="id_veterinario" class="form-select" required>
                            <option value="">— Selecciona veterinario —</option>
                            <?php foreach ($vets as $v): ?>
                                <option value="<?php echo $v['id_veterinario']; ?>"
                                    <?php if ($v['id_veterinario'] == $c['id_veterinario']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($v['nombre']); ?>
                                    (<?php echo htmlspecialchars($v['especialidad']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha *</label>
                        <input type="date" name="fecha" id="fecha" class="form-control"
                               value="<?php echo $c['fecha']; ?>"
                               onblur="validarFechaCita(this)" required>
                        <span id="err-fecha" class="error-msg"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hora *</label>
                        <input type="time" name="hora" id="hora" class="form-control"
                               value="<?php echo substr($c['hora'], 0, 5); ?>" required>
                        <span id="err-hora" class="error-msg"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo de visita *</label>
                        <select name="tipo" class="form-select" required>
                            <?php foreach (['Consulta','Operación','Vacuna','Revisión'] as $t): ?>
                                <option value="<?php echo $t; ?>" <?php if ($c['tipo'] === $t) echo 'selected'; ?>>
                                    <?php echo $t; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Motivo *</label>
                        <input type="text" name="motivo" id="motivo" class="form-control"
                               value="<?php echo htmlspecialchars($c['motivo']); ?>"
                               onblur="validar(this, 'texto')" required>
                        <span id="err-motivo" class="error-msg"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" class="form-select">
                            <?php foreach (['Pendiente','Realizada','Cancelada'] as $e): ?>
                                <option value="<?php echo $e; ?>" <?php if ($c['estado'] === $e) echo 'selected'; ?>>
                                    <?php echo $e; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"
                                  placeholder="Instrucciones previas, alergias, notas adicionales..."><?php echo htmlspecialchars($c['observaciones'] ?? ''); ?></textarea>
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php 
// Importamos el cierre del pie de página para finalizar el marcado estructural del documento e incorporar scripts JavaScript
include "../../includes/footer.php"; 
?>