<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar Historias - NEXO | E.S.T. N°1 Vicente López</title>
    <link rel="stylesheet" href="../css/navbar.css">    
    <link rel="stylesheet" href="../css/explorar.css">    
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

    <!-- Pantalla de carga -->
    <div id="loading-screen" class="loading-screen">
        <div class="loading-spinner"></div>
        <div class="loading-text">Cargando NEXO</div>
        <div class="loading-subtitle">Preparando tu experiencia...</div>
    </div>

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
                <a href="explorar.php" class="nav-link active">Explorar</a>
                <a href="perfil.php" class="nav-link">Mi Perfil</a>
                <a href="subir-historia.php" class="nav-link">
                    <i class="fas fa-plus"></i>
                    Subir Historia
                </a>
            </nav>

            <div class="nav-right">
                <div class="user-menu">
                    <button id="user-toggle" class="user-toggle" aria-label="Menú de usuario">
                        <i class="fas fa-user"></i>
                        <span>Usuario</span>
                    </button>
                    <div class="user-dropdown">
                        <a href="estadisticas.php"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </div>
                </div>
                <button id="theme-toggle" class="theme-toggle" aria-label="Cambiar tema claro/oscuro">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Contenido vacío por ahora -->
    <main style="padding-top: 100px; min-height: 100vh;">
        <!-- Contenido de explorar historias irá aquí -->
    </main>

    <!-- Scripts separados -->
    <script src="../js/theme.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/explorar.js"></script>

</body>
</html>
