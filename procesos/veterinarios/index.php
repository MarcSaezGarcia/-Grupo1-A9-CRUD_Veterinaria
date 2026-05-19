<?php

// Importamos el archivo de conexión que inicializa la variable de control relacional $conn
include "../../includes/conexion.php";

// Importamos la cabecera común global, encargada de verificar la sesión activa y pintar el menú
include "../../includes/header.php";

// CONSULTA DE EXTRACCIÓN ALFABÉTICA:
// Seleccionamos todos los registros de la entidad 'veterinarios'. Añadimos una cláusula de ordenación 
// 'ORDER BY' por nombre de manera ascendente para mantener la consistencia con el resto del ecosistema.
$sql = "SELECT * FROM veterinarios ORDER BY nombre ASC";
$resultado = mysqli_query($conn, $sql);

// Métrica de control preventivo: Evaluamos el volumen total de facultativos en el sistema
$total = mysqli_num_rows($resultado);
?>

<main class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Gestión de Personal Veterinario</h2>
            <p class="text-muted small mb-0">Control de la plantilla médica, especialidades y asignaciones de la clínica.</p>
        </div>
        <a href="crear.php" class="btn btn-success d-flex align-items-center gap-1">
            Añadir Veterinario
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <strong>¡Éxito!</strong> La operación solicitada se ha procesado y consolidado correctamente en la base de datos.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="px-4 pt-3 pb-2 border-bottom bg-light">
                <span class="text-muted small">Total de profesionales en plantilla activa: </span>
                <span class="badge bg-dark text-white fw-bold ms-1"><?php echo $total; ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 80px;">Identificador</th>
                            <th>Nombre Completo</th>
                            <th>Especialidad Clínica</th>
                            <th>Correo Electrónico</th>
                            <th>Honorarios Mensuales</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($total > 0): ?>
                        <?php 
                        // Bucle iterativo de extracción asociativa sobre el set de resultados
                        while($row = mysqli_fetch_assoc($resultado)): 
                        ?>
                        <tr>
                            <td><code class="text-muted"><?php echo $row['id_veterinario']; ?></code></td>
                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                            <td>
                                <span class="badge bg-info text-dark p-2 border-0">
                                    <?php echo htmlspecialchars($row['especialidad']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none text-secondary">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </a>
                            </td>
                            <td class="fw-semibold text-success">
                                <?php echo number_format($row['salario'], 2, ',', '.'); ?> €
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="editar.php?id=<?php echo $row['id_veterinario']; ?>" class="btn btn-warning btn-sm">
                                        Editar
                                    </a>
                                    
                                    <a href="../../scripts/eliminar.php?tabla=veterinarios&id=<?php echo $row['id_veterinario']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('¿Está absolutamente seguro de que desea rescindir y eliminar permanentemente el perfil de la/del <?php echo htmlspecialchars($row['nombre']); ?>? Esta acción desvinculará sus mascotas asignadas.')">
                                        Borrar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No se han encontrado facultativos veterinarios registrados en el sistema actualmente.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php 
// Importamos el archivo de cierre de estructura HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>