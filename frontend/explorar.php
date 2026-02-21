<?php
require_once __DIR__ . '/../backend/session/session_manager.php';
$isLogged = isLoggedIn();
?>
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
            <?php include __DIR__ . '/../components/navbar.php'; ?>
        </header>

    <!-- Contenido Principal -->
    <main class="explorar-main">
        <!-- Sección de Bienvenida -->
        <section class="welcome-section">
            <div class="welcome-container">
                <h1 class="welcome-title">¡Bienvenido a NEXO!</h1>
                <p class="welcome-text">Explora un mundo infinito de historias creadas por nuestra comunidad. Encuentra tu próxima aventura literaria.</p>
                
                <!-- Estadísticas Animadas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-number" data-target="0" id="hero-total-historias">0</div>
                        <div class="stat-label">Historias</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number" data-target="0" id="hero-total-usuarios">0</div>
                        <div class="stat-label">Usuarios</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-pen-fancy"></i>
                        </div>
                        <div class="stat-number" data-target="0" id="hero-total-escritores">0</div>
                        <div class="stat-label">Escritores</div>
                    </div>
                </div>
                
                <!-- Flecha para bajar -->
                <div class="scroll-indicator" id="scroll-indicator">
                    <div class="scroll-text">Descubre más historias</div>
                    <div class="scroll-arrow">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Secciones siempre visibles -->
        <div id="content-sections" class="content-sections visible">
        <section class="search-section">
            <div class="search-container">
                <div class="search-header">
                    <h2 class="search-title">Descubre Historias</h2>
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search-input" placeholder="Buscar historias, autores, géneros..." />
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="filter-genre">Género</label>
                        <select id="filter-genre">
                            <option value="">Todos los géneros</option>
                            <option value="fantasia">Fantasía</option>
                            <option value="ciencia-ficcion">Ciencia Ficción</option>
                            <option value="romance">Romance</option>
                            <option value="misterio">Misterio</option>
                            <option value="terror">Terror</option>
                            <option value="aventura">Aventura</option>
                            <option value="drama">Drama</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-sort">Ordenar por</label>
                        <select id="filter-sort">
                            <option value="recent">Más recientes</option>
                            <option value="popular">Más populares</option>
                            <option value="liked">Más gustados</option>
                            <option value="title">Título (A-Z)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-interaction">Interacción</label>
                        <select id="filter-interaction">
                            <option value="all">Todas las historias</option>
                            <option value="favorites">Solo favoritas</option>
                            <option value="commented">En las que comenté</option>
                        </select>
                    </div>

                    <button class="btn-reset-filters" id="reset-filters">
                        <i class="fas fa-redo"></i>
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </section>

        <!-- Grid de Historias -->
        <section class="stories-section">
            <div class="stories-container">
                <!-- Estado de carga -->
                <div id="loading-state" class="loading-state" style="display: none;">
                    <div class="loading-spinner"></div>
                    <p>Cargando historias...</p>
                </div>

                <!-- Grid de historias -->
                <div id="stories-grid" class="stories-grid">
                    <!-- Las historias se cargan dinámicamente con JavaScript -->
                </div>

                <!-- Sin resultados -->
                <div id="no-results" class="no-results" style="display: none;">
                    <i class="fas fa-search"></i>
                    <h3>No se encontraron historias</h3>
                    <p>Intenta ajustar los filtros o buscar con otros términos</p>
                </div>

                <div id="pagination" class="pagination"></div>
            </div>
        </section>
        </div> <!-- Cierre de content-sections -->
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <p>© 2026 NEXO - Escuela Secundaria Técnica N°1 de Vicente López</p>
        </div>
    </footer>

    <div id="comments-modal" class="comments-modal hidden" aria-hidden="true">
        <div class="comments-modal-overlay" id="comments-modal-close"></div>
        <div class="comments-modal-container" role="dialog" aria-modal="true" aria-labelledby="comments-modal-title">
            <div class="comments-modal-header">
                <h3 id="comments-modal-title">Comentarios</h3>
                <button type="button" class="comments-modal-x" id="comments-modal-x" aria-label="Cerrar comentarios">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="comments-modal-loading" class="comments-loading">Cargando comentarios...</div>

            <div id="comments-modal-list" class="comments-list"></div>

            <form id="comments-modal-form" class="comments-form">
                <label for="comments-modal-input" class="comments-form-label">Escribe tu comentario</label>
                <textarea id="comments-modal-input" rows="3" maxlength="500" placeholder="Comparte tu opinión sobre esta historia..."></textarea>
                <div class="comments-form-footer">
                    <small id="comments-modal-hint">Máximo 500 caracteres</small>
                    <button type="submit" id="comments-modal-submit" class="comments-submit-btn">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts separados -->
    <script src="../js/theme.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/api.js"></script>
    <script>
        window.NEXO_IS_LOGGED = <?php echo $isLogged ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/explorar.js"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/modals.js"></script>
    <script>
        // Inicializar utilidades ahora que el navbar se incluye en el servidor
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initThemeToggle === 'function') initThemeToggle();
            if (typeof initMobileMenu === 'function') initMobileMenu();
            if (typeof initUserMenu === 'function') initUserMenu();
            if (typeof setActiveNavLink === 'function') setActiveNavLink();
            if (typeof initAuthModals === 'function') initAuthModals();
        });

        // Mostrar notificación de logout si viene de cerrar sesión
        if (window.location.search.includes('logout=success')) {
            showNotification('success', 'Sesión cerrada correctamente');
            // Limpiar la URL para evitar mostrar la notificación al recargar
            history.replaceState(null, '', window.location.pathname + window.location.search.replace(/[?&]logout=success/, ''));
        }

        // Mostrar notificación si se redirige por falta de sesión
        if (window.location.search.includes('auth=required')) {
            showNotification('info', 'Debes iniciar sesión para acceder a esa sección');
            history.replaceState(null, '', window.location.pathname + window.location.search.replace(/[?&]auth=required/, ''));
        }
    </script>
</body>
</html>
