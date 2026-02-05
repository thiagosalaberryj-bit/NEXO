<?php
require_once __DIR__ . '/../backend/session/session_manager.php';
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
        <!-- Navbar se carga dinámicamente con AJAX -->
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
                        <div class="stat-number" data-target="247">0</div>
                        <div class="stat-label">Historias</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number" data-target="1856">0</div>
                        <div class="stat-label">Usuarios</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-pen-fancy"></i>
                        </div>
                        <div class="stat-number" data-target="89">0</div>
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

        <!-- Secciones ocultas inicialmente -->
        <div id="content-sections" class="content-sections hidden">
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
                        <label for="filter-status">Estado</label>
                        <select id="filter-status">
                            <option value="">Todos</option>
                            <option value="completa">Completas</option>
                            <option value="en-proceso">En proceso</option>
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
                    <!-- Las historias se cargarán dinámicamente con JavaScript -->
                    <!-- Ejemplo de tarjeta de historia -->
                    <div class="story-card">
                        <div class="story-cover">
                            <img src="https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400" alt="Historia 1">
                            <div class="story-overlay"></div>
                        </div>
                        <div class="story-content">
                            <h3 class="story-title">El Reino Perdido</h3>
                            <p class="story-author">por Juan Pérez</p>
                            <p class="story-description">Una aventura épica en un mundo de fantasía donde un joven héroe debe encontrar el reino perdido.</p>
                            <div class="story-meta">
                                <span class="story-genre"><i class="fas fa-tag"></i> Fantasía</span>
                                <div class="story-stats">
                                    <span><i class="fas fa-eye"></i> 1.2k</span>
                                    <span><i class="fas fa-heart"></i> 234</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="story-card">
                        <div class="story-cover">
                            <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=400" alt="Historia 2">
                            <div class="story-overlay"></div>
                        </div>
                        <div class="story-content">
                            <h3 class="story-title">Estrellas del Mañana</h3>
                            <p class="story-author">por María García</p>
                            <p class="story-description">En un futuro lejano, la humanidad coloniza nuevos planetas en busca de un hogar.</p>
                            <div class="story-meta">
                                <span class="story-genre"><i class="fas fa-tag"></i> Ciencia Ficción</span>
                                <div class="story-stats">
                                    <span><i class="fas fa-eye"></i> 856</span>
                                    <span><i class="fas fa-heart"></i> 189</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="story-card">
                        <div class="story-cover">
                            <img src="https://images.unsplash.com/photo-1519682337058-a94d519337bc?w=400" alt="Historia 3">
                            <div class="story-overlay"></div>
                        </div>
                        <div class="story-content">
                            <h3 class="story-title">Secretos del Pasado</h3>
                            <p class="story-author">por Carlos López</p>
                            <p class="story-description">Un misterio ancestral que conecta el presente con acontecimientos del pasado.</p>
                            <div class="story-meta">
                                <span class="story-genre"><i class="fas fa-tag"></i> Misterio</span>
                                <div class="story-stats">
                                    <span><i class="fas fa-eye"></i> 2.1k</span>
                                    <span><i class="fas fa-heart"></i> 456</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="story-card">
                        <div class="story-cover">
                            <img src="https://images.unsplash.com/photo-1474552226712-ac0f0961a954?w=400" alt="Historia 4">
                            <div class="story-overlay"></div>
                        </div>
                        <div class="story-content">
                            <h3 class="story-title">Corazones Entrelazados</h3>
                            <p class="story-author">por Ana Martínez</p>
                            <p class="story-description">Una historia de amor que desafía las convenciones sociales y el tiempo.</p>
                            <div class="story-meta">
                                <span class="story-genre"><i class="fas fa-tag"></i> Romance</span>
                                <div class="story-stats">
                                    <span><i class="fas fa-eye"></i> 3.4k</span>
                                    <span><i class="fas fa-heart"></i> 678</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="story-card">
                        <div class="story-cover">
                            <img src="https://images.unsplash.com/photo-1475694867812-f82b8696d610?w=400" alt="Historia 5">
                            <div class="story-overlay"></div>
                        </div>
                        <div class="story-content">
                            <h3 class="story-title">La Casa del Silencio</h3>
                            <p class="story-author">por Pedro Sánchez</p>
                            <p class="story-description">Una mansión abandonada guarda secretos que nadie debería descubrir.</p>
                            <div class="story-meta">
                                <span class="story-genre"><i class="fas fa-tag"></i> Terror</span>
                                <div class="story-stats">
                                    <span><i class="fas fa-eye"></i> 1.8k</span>
                                    <span><i class="fas fa-heart"></i> 345</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="story-card">
                        <div class="story-cover">
                            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400" alt="Historia 6">
                            <div class="story-overlay"></div>
                        </div>
                        <div class="story-content">
                            <h3 class="story-title">Viaje al Amanecer</h3>
                            <p class="story-author">por Laura Fernández</p>
                            <p class="story-description">Una expedición hacia lo desconocido que cambiará la vida de sus protagonistas.</p>
                            <div class="story-meta">
                                <span class="story-genre"><i class="fas fa-tag"></i> Aventura</span>
                                <div class="story-stats">
                                    <span><i class="fas fa-eye"></i> 992</span>
                                    <span><i class="fas fa-heart"></i> 167</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div id="no-results" class="no-results" style="display: none;">
                    <i class="fas fa-search"></i>
                    <h3>No se encontraron historias</h3>
                    <p>Intenta ajustar los filtros o buscar con otros términos</p>
                </div>
            </div>
        </section>
        </div> <!-- Cierre de content-sections -->
    </main>

    <!-- Scripts separados -->
    <script src="../js/theme.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/explorar.js"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/modals.js"></script>
    <script>
        // Cargar navbar dinámicamente
        document.addEventListener('DOMContentLoaded', function() {
            fetch('../backend/session/navbar_state.php?page=explorar')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.navbar').innerHTML = data.navbarHTML;
                        // Inicializar funcionalidades después de cargar el navbar
                        initThemeToggle(); // Inicializar cambio de tema
                        if (typeof initMobileMenu === 'function') {
                            initMobileMenu();
                        }
                        if (typeof initUserMenu === 'function') {
                            initUserMenu();
                        }
                        if (typeof setActiveNavLink === 'function') {
                            setActiveNavLink();
                        }
                        initAuthModals();
                    }
                })
                .catch(error => {
                    console.error('Error cargando navbar:', error);
                });
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
