<footer class="footer mt-auto py-4 bg-white border-top">
    <div class="container text-center">
        <p class="text-muted mb-0">© 2026 Clínica Veterinaria <strong>Perriatra</strong> - Panel de Gestión ASIX1</p>
        <small>Desarrollado con PHP, MySQLi.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
// LÓGICA DE ENRUTAMIENTO: Detecta la ubicación del archivo actual para enlazar correctamente el JS
$en_raiz = strpos($_SERVER['PHP_SELF'], '/procesos/') === false;

// Si está en la raíz accede directo; si está dentro de '/procesos/', retrocede los niveles necesarios
$base_js = $en_raiz ? '' : '../../';

// Inyecta la etiqueta script apuntando siempre al archivo JS correcto de validaciones
echo '<script src="' . $base_js . 'js/script.js"></script>';
?>
</body>
</html>