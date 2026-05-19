<?php

// Cargamos el conector relacional para disponer del puente activo a la base de datos ($conn)
include "../../includes/conexion.php";

// Cargamos la cabecera común del ecosistema, asegurando el control de accesos protegidos por sesión
include "../../includes/header.php";
?>

<div class="container mt-5">
    
    <div class="card p-4 shadow-sm border-0">
        <h2 class="mb-4">Registrar Nueva Raza Canina / Felina</h2>
        
        <form action="../../scripts/guardar_raza.php" method="POST" id="formRaza" novalidate>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre de la Raza *</label>
                <input type="text" name="nombre" id="nom_raza" class="form-control" 
                       placeholder="Ej. Pastor Alemán, Siamés..." onblur="validar(this, 'texto')" required>
                <span id="err-nom_raza" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Características Físicas y Morfología *</label>
                <textarea name="fisico" id="fisico" class="form-control" rows="3"
                          placeholder="Describa el tamaño, pelaje, colores predominantes y contextura..." 
                          onblur="validar(this, 'texto')" required></textarea>
                <span id="err-fisico" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Temperamento y Comportamiento Habitual *</label>
                <textarea name="comportamiento" id="compo" class="form-control" rows="3"
                          placeholder="Describa el nivel de energía, sociabilidad, predisposición al entrenamiento..." 
                          onblur="validar(this, 'texto')" required></textarea>
                <span id="err-compo" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Guardar Raza</button>
                <a href="index.php" class="btn btn-secondary">Cancelar y Volver</a>
            </div>
            
        </form>
    </div>
</div>

<?php 
// Importamos el archivo de cierre de estructura HTML junto con la carga implícita de ficheros JavaScript externos
include "../../includes/footer.php"; 
?>