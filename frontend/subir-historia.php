<?php
require_once __DIR__ . '/../backend/session/session_manager.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Historia - NEXO | E.S.T. N°1 Vicente López</title>
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
        <div class="nav-container">
            <div class="nav-left">
                <a href="explorar.php" class="brand" aria-label="Ir a explorar">
                    <img src="../components/Logo-2.png" alt="NEXO" class="logo" />
                </a>
            </div>

            <button id="menu-toggle" class="menu-toggle" aria-label="Abrir menú de navegación">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="nav-center">
                <a href="explorar.php" class="nav-link">Explorar</a>
                <a href="perfil.php" class="nav-link">Mi Perfil</a>
                <a href="subir-historia.php" class="nav-link active">
                    <i class="fas fa-plus"></i>
                    Subir Historia
                </a>
                <!-- Botón de iniciar sesión para menú móvil -->
                <?php if (!isLoggedIn()) : ?>
                    <button class="login-btn login-btn-mobile" data-open-login>Iniciar sesión</button>
                <?php else : ?>
                    <!-- Botón de cerrar sesión para menú móvil -->
                    <a href="../backend/session/logout.php" class="logout-btn logout-btn-mobile">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                <?php endif; ?>
            </nav>

            <div class="nav-right">
                <?php if (isLoggedIn()) : ?>
                    <div class="user-menu user-menu-desktop">
                        <button id="user-toggle" class="user-toggle" aria-label="Menú de usuario">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars(getCurrentUserName(), ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                        <div class="user-dropdown">
                            <a href="estadisticas.php"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                            <a href="../backend/session/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else : ?>
                    <button class="login-btn login-btn-desktop" data-open-login>Iniciar sesión</button>
                <?php endif; ?>
                <button id="theme-toggle" class="theme-toggle" aria-label="Cambiar tema claro/oscuro">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Contenido vacío por ahora -->
    <main style="padding-top: 100px; min-height: 100vh;">
        <!-- Contenido para subir historia irá aquí -->
    </main>

    <!-- Scripts separados -->
    <script src="../js/theme.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/modals.js"></script>
    <script>
        // Mostrar notificación de logout si viene de cerrar sesión
        if (window.location.search.includes('logout=success')) {
            showNotification('success', 'Sesión cerrada correctamente');
            // Limpiar la URL para evitar mostrar la notificación al recargar
            history.replaceState(null, '', window.location.pathname + window.location.search.replace(/[?&]logout=success/, ''));
        }
    </script>

</body>
</html>
