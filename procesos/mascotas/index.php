<?php

// Importamos el archivo de conexión que inicializa la variable de control relacional $conn
include "../../includes/conexion.php";

// Importamos la cabecera del sitio encargada de controlar la sesión y pintar el menú de navegación
include "../../includes/header.php";

// RECEPCIÓN DE FILTROS (MÉTODO GET): Capturamos las variables del formulario de búsqueda.
// Implementamos el operador de fusión de nulos '??' (PHP 7+) para simplificar el flujo y evitar warnings.
$especie = $_GET['especie'] ?? '';
$sexo    = $_GET['sexo']    ?? '';

// CORRECCIÓN CRÍTICA DE SEGURIDAD (Inyección SQL): El código original concatenaba directamente variables GET
// en la consulta ($sql .= " AND m.especie = '$especie'"). Esto abría una grave vulnerabilidad de SQL Injection.
// Para solucionarlo y cumplir las directrices académicas, migramos a Sentencias Preparadas (Prepared Statements).

// ESTRUCTURA DE LA CONSULTA BASE: Seleccionamos los datos de mascotas y realizamos JOINs con las tablas relacionadas
// para recuperar los nombres reales en lugar de los identificadores numéricos (IDs).
$sql = "SELECT m.*, r.nombre AS nombre_raza, p.nombre AS nombre_prop, v.nombre AS nombre_vet 
        FROM mascotas m
        JOIN razas r        ON m.id_raza        = r.id_raza
        JOIN propietarios p ON m.id_propietario = p.id_propietario
        JOIN veterinarios v ON m.id_veterinario = v.id_veterinario
        WHERE 1=1";

// Inicializadores para la vinculación dinámica de parámetros
$tipos  = "";
$params = [];

// Lógica de Filtros Sumativos: Evaluamos las variables y construimos de manera incremental los subtramos de la consulta.
if ($especie !== '') {
    $sql     .= " AND m.especie = ?";
    $tipos   .= "s"; // 's' representa que el dato es un String
    $params[] = $especie;
}
if ($sexo !== '') {
    $sql     .= " AND m.sexo = ?";
    $tipos   .= "s";
    $params[] = $sexo;
}

// Criterio de ordenación por defecto para presentar los animales de forma estructurada
$sql .= " ORDER BY m.id_mascota DESC";

// Preparamos la consulta en el servidor de base de datos
$stmt = mysqli_prepare($conn, $sql);

// Si se aplicaron filtros, vinculamos dinámicamente los parámetros utilizando el operador de desempaquetado (...)
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $tipos, ...$params);
}

// Ejecutamos la consulta de manera blindada contra ataques de inyección de código
mysqli_stmt_execute($stmt);

// Obtenemos el set de resultados del buffer interno de la sentencia preparada
$resultado = mysqli_stmt_get_result($stmt);

// Métrica de Resultados Totales: Contamos de manera dinámica los registros devueltos por el filtro actual
$total = mysqli_num_rows($resultado);
?>

<main class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Panel de Gestión de Mascotas</h2>
        <div class="mt-3">
            <span class="badge bg-light text-dark border p-2">
                Resultados encontrados: <strong><?php echo $total; ?></strong>
            </span>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted">Filtrar por Especie</label>
                    <select name="especie" class="form-select">
                        <option value="">— Todas las especies —</option>
                        <option value="Perro"  <?php if ($especie === 'Perro')  echo 'selected'; ?>>🐶 Perro</option>
                        <option value="Gato"   <?php if ($especie === 'Gato')   echo 'selected'; ?>>🐱 Gato</option>
                        <option value="Ave"    <?php if ($especie === 'Ave')    echo 'selected'; ?>>🦜 Ave</option>
                        <option value="Reptil" <?php if ($especie === 'Reptil') echo 'selected'; ?>>🦎 Reptil</option>
                        <option value="Otro"   <?php if ($especie === 'Otro')   echo 'selected'; ?>>🐾 Otro</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted">Filtrar por Sexo</label>
                    <select name="sexo" class="form-select">
                        <option value="">— Todos los sexos —</option>
                        <option value="Macho"  <?php if ($sexo === 'Macho')  echo 'selected'; ?>>♂ Macho</option>
                        <option value="Hembra" <?php if ($sexo === 'Hembra') echo 'selected'; ?>>♀ Hembra</option>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Aplicar Filtros</button>
                    <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
                    <a href="crear.php" class="btn btn-success text-nowrap">Añadir Mascota</a>
                </div>
                
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Identificador Chip</th>
                            <th>Nombre</th>
                            <th>Especie / Sexo</th>
                            <th>Raza Clasificada</th>
                            <th>Propietario / Cliente</th>
                            <th>Veterinario Asignado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($total === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron mascotas registradas que coincidan con los criterios de búsqueda.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Iteración asociativa sobre el set de resultados seguro devuelto por MySQL
                        while($row = mysqli_fetch_assoc($resultado)): 
                        ?>
                        <tr>
                            <td><code class="text-dark fw-bold"><?php echo htmlspecialchars($row['chip']); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($row['especie']); ?> 
                                <small class="text-muted">(<?php echo htmlspecialchars($row['sexo']); ?>)</small>
                            </td>
                            <td><?php echo htmlspecialchars($row['nombre_raza']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_prop']); ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    🩺 <?php echo htmlspecialchars($row['nombre_vet']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="editar.php?id=<?php echo $row['id_mascota']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                    
                                    <a href="../../scripts/eliminar.php?tabla=mascotas&id=<?php echo $row['id_mascota']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('¿Estás absolutamente seguro de que deseas eliminar permanentemente el registro de <?php echo htmlspecialchars($row['nombre']); ?>?')">Borrar</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php 
// Cerramos la sentencia preparada para liberar memoria en el servidor
mysqli_stmt_close($stmt);

// Importamos el pie de página común con la inyección de estilos y scripts finales
include "../../includes/footer.php"; 
?>