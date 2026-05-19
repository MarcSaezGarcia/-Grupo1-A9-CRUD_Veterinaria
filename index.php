<?php
include "includes/conexion.php";
include "includes/header.php";
?>

<div class="container mt-5">
    <div class="jumbotron text-center p-5 bg-light rounded shadow">
        <h1 class="display-4">🐾 Panel de Control Perriatra</h1>
        <p class="lead">Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>. Estás en el sistema de gestión veterinaria.</p>
        <hr class="my-4">
        <p>Utiliza el menú superior o los accesos directos para gestionar la clínica.</p>
        
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-4">

            <a href="procesos/mascotas/index.php" class="text-decoration-none text-white text-center p-4 rounded-3" style="background-color: #0d6efd; width: 160px; min-height: 120px;">
                <span style="font-size: 1.5rem;">🐶</span>
                <div class="fw-bold mt-2">Mascotas</div>
            </a>

            <a href="procesos/propietarios/index.php" class="text-decoration-none text-white text-center p-4 rounded-3" style="background-color: #0dcaf0; width: 160px; min-height: 120px;">
                <span style="font-size: 1.5rem;">👤</span>
                <div class="fw-bold mt-2">Propietarios</div>
            </a>

            <a href="procesos/veterinarios/index.php" class="text-decoration-none text-white text-center p-4 rounded-3" style="background-color: #198754; width: 160px; min-height: 120px;">
                <span style="font-size: 1.5rem;">🩺</span>
                <div class="fw-bold mt-2">Veterinarios</div>
            </a>

            <a href="procesos/razas/index.php" class="text-decoration-none text-white text-center p-4 rounded-3" style="background-color: #ffc107; width: 160px; min-height: 120px;">
                <span style="font-size: 1.5rem;">📜</span>
                <div class="fw-bold mt-2">Razas</div>
            </a>

            <a href="procesos/citas/index.php" class="text-decoration-none text-white text-center p-4 rounded-3" style="background-color: #dc3545; width: 160px; min-height: 120px;">
                <span style="font-size: 1.5rem;">📅</span>
                <div class="fw-bold mt-2">Citas</div>
            </a>

        </div>
</div>

<?php include "includes/footer.php"; ?>
