<?php

// Inicializamos el entorno de sesiones de PHP para interceptar cookies de rastreo de usuario
session_start();

// CAPA GUARDRAIL: Si el usuario ya cuenta con un token de identidad activo en el servidor, 
// se aborta el acceso al login y se le redirige de forma automática hacia el panel de control.
if (isset($_SESSION['usuario'])) { 
    header("Location: ../index.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Perriatra</title>
    
    <link rel="stylesheet" href="../estilos/style.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container vh-100 d-flex justify-content-center align-items-center">
        
        <div class="card p-4 shadow-sm border-0" style="width: 100%; max-width: 400px; border-radius: 16px;">
            
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark mb-1">🐾 Acceso Personal</h2>
                <p class="text-muted small">Clínica Veterinaria Perriatra</p>
            </div>
            
            <form action="../scripts/autenticacion_usu.php" method="POST" id="formLogin" novalidate>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control p-2.5" 
                           placeholder="ejemplo@clinicaperriatra.com" onblur="validar(this, 'email')" required>
                    <span id="err-email" class="error-msg text-danger small mt-1 d-block"></span>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold text-secondary mb-0">Contraseña</label>
                    </div>
                    <input type="password" name="password" id="pass" class="form-control p-2.5" 
                           placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                    Entrar al Sistema
                </button>
                
                <div class="mt-4 text-center border-top pt-3">
                    <p class="small text-muted mb-0">
                        ¿No tienes una cuenta de empleado? <br>
                        <a href="registro.html" class="text-success fw-semibold text-decoration-none">Regístrate aquí</a>
                    </p>
                </div>
                
            </form>
        </div>
    </div>

    <script src="../js/script.js"></script>
</body>
</html>