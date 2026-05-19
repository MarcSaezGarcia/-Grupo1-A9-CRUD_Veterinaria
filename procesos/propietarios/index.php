<?php

// Importamos el archivo de conexión que inicializa la variable de control relacional $conn
include "../../includes/conexion.php";

// Importamos la cabecera común global, encargada de verificar la sesión activa y pintar el menú
include "../../includes/header.php";

// CONSULTA DE EXTRACCIÓN ALFABÉTICA:
// Recuperamos todos los registros de la entidad 'propietarios'. Añadimos una cláusula de ordenación 
// 'ORDER BY' por nombre de manera ascendente para mejorar la legibilidad y la experiencia de usuario.
$sql = "SELECT * FROM propietarios ORDER BY nombre ASC";
$resultado = mysqli_query($conn, $sql);

// Métrica de control preventivo: Evaluamos cuántos registros se han devuelto
$total = mysqli_num_rows($resultado);
?>

<main class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Gestión de Propietarios</h2>
            <p class="text-muted small mb-0">Listado de clientes registrados en la clínica veterinaria.</p>
        </div>
        <a href="crear.php" class="btn btn-success d-flex align-items-center gap-1">
            Añadir Propietario
        </a>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Documento (DNI)</th>
                            <th>Teléfono</th>
                            <th>Correo Electrónico</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($total === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay propietarios registrados en el sistema actualmente.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Bucle iterativo de extracción asociativa sobre la consulta de la tabla 'propietarios'
                        while($row = mysqli_fetch_assoc($resultado)): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                            <td><code class="text-dark fw-bold"><?php echo htmlspecialchars($row['DNI']); ?></code></td>
                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none text-secondary">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="editar.php?id=<?php echo $row['id_propietario']; ?>" class="btn btn-sm btn-warning text-dark">
                                        Editar
                                    </a>
                                    
                                    <a href="../../scripts/eliminar.php?tabla=propietarios&id=<?php echo $row['id_propietario']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('¿Estás absolutamente seguro de que deseas eliminar al propietario <?php echo htmlspecialchars($row['nombre']); ?>? Esta acción podría afectar a sus mascotas asociadas.')">
                                        Borrar
                                    </a>
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
// Importamos el archivo de cierre de estructura HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>