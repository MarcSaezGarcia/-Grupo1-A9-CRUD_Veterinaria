<?php

// Importamos el archivo de conexión que inicializa la variable de control relacional $conn
include "../../includes/conexion.php";

// Importamos la cabecera común global, encargada de verificar la sesión activa y pintar el menú
include "../../includes/header.php";

// CONSULTA DE EXTRACCIÓN ALFABÉTICA:
// Seleccionamos los registros de la entidad 'razas'. Añadimos una cláusula 'ORDER BY' por 
// nombre de forma ascendente para mantener un orden lógico y limpio en la interfaz del usuario.
$sql = "SELECT * FROM razas ORDER BY nombre ASC";
$resultado = mysqli_query($conn, $sql);

// Métrica de control preventivo: Evaluamos el volumen de registros devueltos por el motor SQL
$total = mysqli_num_rows($resultado);
?>

<main class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Catálogo de Razas Clasificadas</h2>
            <p class="text-muted small mb-0">Especies, morfologías y patrones etológicos registrados en el sistema.</p>
        </div>
        <a href="crear.php" class="btn btn-success d-flex align-items-center gap-1">
            Añadir Nueva Raza
        </a>
    </div>
    
    <div class="row">
        <?php if ($total === 0): ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-4 border-0 shadow-sm" role="alert">
                    🐾 No se han encontrado razas registradas en el sistema actualmente.
                </div>
            </div>
        <?php else: ?>
            <?php 
            // Bucle iterativo de extracción asociativa sobre la consulta de la tabla 'razas'
            while($row = mysqli_fetch_assoc($resultado)): 
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm card-raza">
                    <div class="card-body">
                        <h5 class="card-title text-success fw-bold mb-3">
                            🐶 <?php echo htmlspecialchars($row['nombre']); ?>
                        </h5>
                        
                        <p class="card-text mb-2">
                            <span class="badge bg-light text-dark border-0 fw-semibold">Morfología:</span><br>
                            <span class="text-secondary small"><?php echo htmlspecialchars($row['caracteristicas_fisicas']); ?></span>
                        </p>
                        
                        <p class="card-text">
                            <span class="badge bg-light text-dark border-0 fw-semibold">Temperamento:</span><br>
                            <span class="text-secondary small"><?php echo htmlspecialchars($row['comportamiento']); ?></span>
                        </p>
                    </div>
                    
                    <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 d-flex gap-2">
                        <a href="editar.php?id=<?php echo $row['id_raza']; ?>" class="btn btn-sm btn-outline-warning flex-fill">
                            Editar
                        </a>
                        
                        <a href="../../scripts/eliminar.php?tabla=razas&id=<?php echo $row['id_raza']; ?>" 
                           class="btn btn-sm btn-outline-danger flex-fill" 
                           onclick="return confirm('¿Está seguro de eliminar permanentemente la raza << <?php echo htmlspecialchars($row['nombre']); ?> >>? Esta acción puede desvincular expedientes clínicos de mascotas asociadas.')">
                            Borrar
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>

<?php 
// Importamos el archivo de cierre de estructura HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>