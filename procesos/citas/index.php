<?php

// Cargamos el conector a la base de datos ($conn) usando la ruta relativa de dos niveles
include "../../includes/conexion.php";

// Cargamos la cabecera común para asegurar la persistencia de las sesiones del usuario activo
include "../../includes/header.php";

// RECEPCIÓN DE FILTROS (MÉTODO GET): Capturamos las variables del formulario de búsqueda.
// Usamos el operador de fusión de nulos '??' para evitar advertencias de variables indefinidas.
$filtro_estado = $_GET['estado']    ?? '';
$filtro_tipo   = $_GET['tipo']      ?? '';
$filtro_vet    = intval($_GET['id_veterinario'] ?? 0); // Convertimos a entero por seguridad aritmética

// ESTRUCTURA DE LA CONSULTA BASE: Seleccionamos todos los campos de citas (c.*) 
// y cruzamos mediante INNER JOIN para obtener nombres descriptivos de mascotas y veterinarios.
$sql = "SELECT c.*, m.nombre AS nombre_mascota, m.especie,
               v.nombre AS nombre_vet
        FROM citas c
        JOIN mascotas m    ON c.id_mascota    = m.id_mascota
        JOIN veterinarios v ON c.id_veterinario = v.id_veterinario
        WHERE 1=1"; // El 'WHERE 1=1' facilita la concatenación

// Inicializamos las variables para el enlazado de parámetros dinámicos (Sentencias Preparadas)
$tipos  = "";
$params = [];

// REQUISITO PDF - FILTROS SUMATIVOS: Evaluamos cada campo y concatenamos las restricciones de forma acumulativa
if ($filtro_estado !== '') {
    $sql     .= " AND c.estado = ?";
    $tipos   .= "s"; // 's' indica que el tipo de dato es una cadena de texto (string)
    $params[] = $filtro_estado;
}
if ($filtro_tipo !== '') {
    $sql     .= " AND c.tipo = ?";
    $tipos   .= "s";
    $params[] = $filtro_tipo;
}
if ($filtro_vet > 0) {
    $sql     .= " AND c.id_veterinario = ?";
    $tipos   .= "i"; // 'i' indica que el tipo de dato es un entero (integer)
    $params[] = $filtro_vet;
}

// Criterio de Ordenación: Organizamos de manera cronológica ascendente por fecha y luego por hora
$sql .= " ORDER BY c.fecha ASC, c.hora ASC";

// EJECUCIÓN CON SENTENCIAS PREPARADAS: Evita inyecciones SQL al procesar variables externas
$stmt = mysqli_prepare($conn, $sql);

// Si el usuario aplicó al menos un filtro, vinculamos dinámicamente los tipos y el array indexado mediante el operador '...'
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $tipos, ...$params);
}

// Ejecutamos la consulta final combinada en el servidor
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Volcamos el set de registros completo en un array asociativo multidimensional
$citas     = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

// REQUISITO PDF: Contador dinámico del volumen total de resultados obtenidos en la consulta
$total     = count($citas);

// Cerramos el puntero de la sentencia preparada para liberar recursos del servidor
mysqli_stmt_close($stmt);

// REPOSITORIO DE DATOS AUXILIAR: Obtenemos el listado completo de veterinarios para popular el select del filtro
$vets = mysqli_fetch_all(mysqli_query($conn, "SELECT id_veterinario, nombre FROM veterinarios ORDER BY nombre ASC"), MYSQLI_ASSOC);
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Agenda de Citas</h2>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="crear.php" class="btn btn-success">Nueva Cita</a>
        <?php endif; ?>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">— Todos —</option>
                        <?php foreach (['Pendiente','Realizada','Cancelada'] as $e): ?>
                            <option value="<?php echo $e; ?>" <?php if ($filtro_estado === $e) echo 'selected'; ?>>
                                <?php echo $e; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">— Todos —</option>
                        <?php foreach (['Consulta','Operación','Vacuna','Revisión'] as $t): ?>
                            <option value="<?php echo $t; ?>" <?php if ($filtro_tipo === $t) echo 'selected'; ?>>
                                <?php echo $t; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Veterinario</label>
                    <select name="id_veterinario" class="form-select">
                        <option value="">— Todos —</option>
                        <?php foreach ($vets as $v): ?>
                            <option value="<?php echo $v['id_veterinario']; ?>"
                                <?php if ($filtro_vet === intval($v['id_veterinario'])) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($v['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                    <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>

            <div class="mt-3">
                <span class="badge bg-light text-dark border">
                    📊 <?php echo $total; ?> cita<?php echo $total !== 1 ? 's' : ''; ?> encontrada<?php echo $total !== 1 ? 's' : ''; ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mascota</th>
                        <th>Veterinario</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($citas)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay citas registradas con los filtros seleccionados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($citas as $c):
                        // LÓGICA DE MAQUETACIÓN DINÁMICA: Asignación de clases contextuales de Bootstrap según el estado
                        $badge = match($c['estado']) {
                            'Pendiente'  => 'warning text-dark',
                            'Realizada'  => 'success',
                            'Cancelada'  => 'danger',
                            default      => 'secondary'
                        };
                        
                        // Lógica de Alerta Temprana: Detectamos si el registro coincide con el día actual del servidor
                        $es_hoy = $c['fecha'] === date('Y-m-d');
                    ?>
                    <tr <?php if ($es_hoy) echo 'class="table-warning"'; ?>>
                        <td>
                            <strong><?php echo htmlspecialchars($c['nombre_mascota']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($c['especie']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($c['nombre_vet']); ?></td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($c['fecha'])); ?>
                            <?php if ($es_hoy): ?>
                                <span class="badge bg-danger ms-1">HOY</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo substr($c['hora'], 0, 5); ?> hs</td>
                        <td><?php echo htmlspecialchars($c['motivo']); ?></td>
                        <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $c['estado']; ?></span></td>
                        
                        <td class="text-center">
                            <a href="editar.php?id=<?php echo $c['id_cita']; ?>" class="btn btn-sm btn-warning">Editar</a>
                            
                            <a href="../../scripts/eliminar_cita.php?id=<?php echo $c['id_cita']; ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Estás absolutamente seguro de que deseas eliminar permanentemente esta cita?')">Borrar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php 
// Cargamos el footer estructural para cerrar el documento HTML e inicializar bibliotecas adicionales
include "../../includes/footer.php"; 
?>