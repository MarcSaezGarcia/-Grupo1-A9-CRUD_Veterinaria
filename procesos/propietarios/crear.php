<?php
// ========================================================================================
// CAPA DE CONFIGURACIÓN Y PERSISTENCIA DE SESIÓN (BACKEND) - NUEVO PROPIETARIO
// ========================================================================================

// Cargamos el módulo de conexión para inicializar el puente de datos ($conn) con la BD
include "../../includes/conexion.php";

// Cargamos la cabecera del ecosistema web, controlando accesos protegidos mediante variables de sesión
include "../../includes/header.php";
?>

<div class="container mt-4">
    
    <div class="card p-4 shadow-sm border-0">
        <h2 class="mb-4">Registrar Nuevo Propietario</h2>
        
        <form action="../../scripts/guardar_propietario.php" method="POST" id="formPropietario" novalidate>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre Completo *</label>
                <input type="text" name="nombre" id="nom_prop" class="form-control" 
                       placeholder="Ej. Juan Pérez García" onblur="validar(this, 'texto')" required>
                <span id="err-nom_prop" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Documento de Identidad (DNI) *</label>
                <input type="text" name="DNI" id="dni" class="form-control" maxlength="9"
                       placeholder="Ej. 12345678X" onblur="validar(this, 'texto')" required>
                <span id="err-dni" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Teléfono de Contacto *</label>
                <input type="tel" name="telefono" id="tel" class="form-control" maxlength="15"
                       placeholder="Ej. 600123456" onblur="validar(this, 'texto')" required>
                <span id="err-tel" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Correo Electrónico (Email) *</label>
                <input type="email" name="email" id="email_p" class="form-control" 
                       placeholder="Ej. juan.perez@example.com" onblur="validar(this, 'email')" required>
                <span id="err-email_p" class="error-msg text-danger small mt-1 d-block"></span>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">Registrar Propietario</button>
                <a href="index.php" class="btn btn-secondary">Cancelar y Volver</a>
            </div>
            
        </form>
    </div>
</div>

<?php 
// Cargamos la estructura final del pie de página común, inyectando los ficheros JS y cerrando el búfer del documento
include "../../includes/footer.php"; 
?>