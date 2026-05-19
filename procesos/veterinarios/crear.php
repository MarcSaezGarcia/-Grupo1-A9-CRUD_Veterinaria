<?php

// Cargamos el conector relacional para disponer del puente activo a la base de datos ($conn)
include "../../includes/conexion.php";

// Cargamos la cabecera común del ecosistema, asegurando el control de accesos protegidos por sesión
include "../../includes/header.php";
?>

<div class="container mt-5">
    
    <div class="card p-4 shadow-sm border-0">
        <h2 class="mb-4">Alta de Personal Facultativo</h2>
        
        <form action="../../scripts/guardar_veterinario.php" method="POST" id="formVeterinario" novalidate>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre Completo *</label>
                <input type="text" name="nombre" id="nom_vet" class="form-control" 
                       placeholder="Ej. Dra. María Antonieta Gómez" onblur="validar(this, 'texto')" required>
                <span id="err-nom_vet" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="row">
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Especialidad Clínica *</label>
                    <input type="text" name="especialidad" id="esp" class="form-control" 
                           placeholder="Ej. Cirugía, Oncología, Animales Exóticos..." onblur="validar(this, 'texto')" required>
                    <span id="err-esp" class="error-msg text-danger small mt-1 d-block"></span>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Asignación Salarial Mensual (€) *</label>
                    <input type="number" step="0.01" name="salario" id="salario" class="form-control" 
                           placeholder="0.00" min="0" required>
                </div>
                
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Teléfono de Contacto *</label>
                <input type="tel" name="telefono" id="tel_vet" class="form-control" maxlength="15"
                       placeholder="Ej. 600123456" onblur="validar(this, 'texto')" required>
                <span id="err-tel_vet" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Institucional *</label>
                <input type="email" name="email" id="mail_vet" class="form-control" 
                       placeholder="Ej. m.gomez@clinicaperriatra.com" onblur="validar(this, 'email')" required>
                <span id="err-mail_vet" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Registrar Veterinario</button>
                <a href="index.php" class="btn btn-secondary">Cancelar y Volver</a>
            </div>
            
        </form>
    </div>
</div>

<?php 
// Importamos el archivo de cierre de estructura HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>