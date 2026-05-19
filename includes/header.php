<?php
// Comprobamos el estado de la sesión; si no existe ninguna activa, la inicializamos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no existe la variable de sesión, denegamos el acceso
if (!isset($_SESSION['usuario'])) {
    // Calculamos de manera matemática la profundidad para redirigir correctamente al login
    $profundidad = substr_count($_SERVER['PHP_SELF'], '/');
    $base = str_repeat('../', $profundidad - 2);
    header("Location: " . $base . "procesos/login.php");
    exit(); // Detiene la ejecución del código por completo por seguridad
}

// LÓGICA DE ENRUTAMIENTO: Determina la ubicación para no romper los enlaces relativos
$en_raiz    = strpos($_SERVER['PHP_SELF'], '/procesos/') === false;
$base_url   = $en_raiz ? '' : '../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perriatra - Gestión Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>estilos/style.css">
    <link rel="icon" href="img/favicon/favicon.jpg">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">Perriatra</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>procesos/mascotas/index.php">Mascotas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>procesos/propietarios/index.php">Propietarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>procesos/veterinarios/index.php">Veterinarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>procesos/razas/index.php">Razas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>procesos/citas/index.php">Citas</a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
            </span>
            <a href="<?php echo $base_url; ?>scripts/logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>