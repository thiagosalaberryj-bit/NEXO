<?php
require_once __DIR__ . '/../backend/session/session_manager.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - NEXO | E.S.T. N°1 Vicente López</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="shortcut icon" href="../components/Logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        // Aplicar tema inmediatamente para evitar flash blanco
        (function() {
            try {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark-theme');
                }
            } catch (e) {
                // Fallback si localStorage no está disponible
            }
        })();
    </script>
</head>
<body>
    <div id="top"></div>

    <header id="header" class="navbar">
        <?php include __DIR__ . '/../components/navbar.php'; ?>
    </header>

    <!-- Contenido vacío por ahora -->
    <main style="padding-top: 100px; min-height: 100vh;">
        <!-- Contenido del perfil irá aquí -->
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <p>© 2026 NEXO - Escuela Secundaria Técnica N°1 de Vicente López</p>
        </div>
    </footer>

    <!-- Scripts separados -->
    <script src="../js/theme.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/modals.js"></script>
    <script>
        // Inicializar utilidades ahora que el navbar se incluye en el servidor
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initThemeToggle === 'function') initThemeToggle();
            if (typeof initUserMenu === 'function') initUserMenu();
            if (typeof setActiveNavLink === 'function') setActiveNavLink();
            if (typeof initNavbar === 'function') initNavbar();
            if (typeof initAuthModals === 'function') initAuthModals();
        });

        // Mostrar notificación de logout si viene de cerrar sesión
        if (window.location.search.includes('logout=success')) {
            showNotification('success', 'Sesión cerrada correctamente');
            // Limpiar la URL para evitar mostrar la notificación al recargar
            history.replaceState(null, '', window.location.pathname + window.location.search.replace(/[?&]logout=success/, ''));
        }
    </script>
</body>
</html>
