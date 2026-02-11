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
    <link rel="stylesheet" href="../css/perfil.css">
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

    <main class="pf-main">
        <div class="pf-container">
            <!-- Sidebar izquierdo -->
            <aside class="pf-sidebar">
                <div class="pf-user-info">
                    <div class="pf-avatar">
                        <img src="" alt="">
                        <button class="pf-avatar-edit" id="edit-avatar-btn">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h2 class="pf-user-name" id="user-name"><?php echo htmlspecialchars(getCurrentUserName(), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="pf-user-email" id="user-email">usuario@ejemplo.com</p>
                    <div class="pf-user-stats">
                        <div class="pf-stat">
                            <span class="pf-stat-number" id="user-stories">0</span>
                            <span class="pf-stat-label">Historias</span>
                        </div>
                        <div class="pf-stat">
                            <span class="pf-stat-number" id="user-likes">0</span>
                            <span class="pf-stat-label">Likes</span>
                        </div>
                        <div class="pf-stat">
                            <span class="pf-stat-number" id="user-comments">0</span>
                            <span class="pf-stat-label">Comentarios</span>
                        </div>
                    </div>
                </div>

                <nav class="pf-nav">
                    <button class="pf-nav-item active" data-section="stories">
                        <i class="fas fa-book"></i>
                        <span>Mis Historias</span>
                    </button>
                    <button class="pf-nav-item" data-section="stats">
                        <i class="fas fa-chart-bar"></i>
                        <span>Estadísticas</span>
                    </button>
                    <button class="pf-nav-item" data-section="forms">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Formularios</span>
                    </button>
                    <button class="pf-nav-item" data-section="comments">
                        <i class="fas fa-comments"></i>
                        <span>Comentarios</span>
                    </button>
                    <button class="pf-nav-item" data-section="settings">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </button>
                </nav>
            </aside>

            <!-- Contenido principal -->
            <div class="pf-content">
                <!-- Mis Historias -->
                <section class="pf-section active" id="stories-section">
                    <div class="pf-section-header">
                        <h1><i class="fas fa-book"></i> Mis Historias</h1>
                        <button class="pf-btn-primary">
                            <i class="fas fa-plus"></i> Crear Nueva Historia
                        </button>
                    </div>
                    <div class="pf-stories-grid" id="stories-grid">
                        <div class="pf-empty-state">
                            <i class="fas fa-book-open"></i>
                            <h3>Aún no has creado ninguna historia</h3>
                            <p>¡Comparte tus historias con la comunidad!</p>
                            <button class="pf-btn-primary">
                                Crear Primera Historia
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Estadísticas -->
                <section class="pf-section" id="stats-section">
                    <div class="pf-section-header">
                        <h1><i class="fas fa-chart-bar"></i> Estadísticas de Historias</h1>
                        <p>Ve los likes y formularios contestados de tus historias</p>
                    </div>
                    <div class="pf-stories-stats" id="stories-stats">
                        <!-- Las historias se cargarán dinámicamente aquí -->
                        <div class="pf-empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h3>No hay estadísticas disponibles</h3>
                            <p>Las estadísticas aparecerán cuando tus historias reciban interacciones</p>
                        </div>
                    </div>
                </section>

                <!-- Formularios -->
                <section class="pf-section" id="forms-section">
                    <div class="pf-section-header">
                        <h1><i class="fas fa-clipboard-list"></i> Formularios de Historias</h1>
                        <p>Crea formularios para que los lectores interactúen con tus historias</p>
                    </div>
                    <div class="pf-forms-creator">
                        <div class="pf-form-creator-header">
                            <button class="pf-btn-primary" id="create-form-btn">
                                <i class="fas fa-plus"></i> Crear Nuevo Formulario
                            </button>
                        </div>
                        <div class="pf-forms-list" id="forms-list">
                            <!-- Los formularios se cargarán dinámicamente aquí -->
                            <div class="pf-empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h3>Aún no has creado formularios</h3>
                                <p>Crea formularios para recopilar feedback de tus lectores</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Comentarios -->
                <section class="pf-section" id="comments-section">
                    <div class="pf-section-header">
                        <h1><i class="fas fa-comments"></i> Comentarios</h1>
                    </div>
                    <div class="pf-comments-list" id="comments-list">
                        <div class="pf-empty-state">
                            <i class="fas fa-comments"></i>
                            <h3>Aún no tienes comentarios</h3>
                            <p>Los comentarios en tus historias aparecerán aquí</p>
                        </div>
                    </div>
                </section>

                <!-- Configuración -->
                <section class="pf-section" id="settings-section">
                    <div class="pf-section-header">
                        <h1><i class="fas fa-cog"></i> Configuración</h1>
                    </div>
                    <div class="pf-settings-grid">
                        <div class="pf-setting-group">
                            <h3>Apariencia</h3>
                            <div class="pf-setting-item">
                                <label class="pf-toggle">
                                    <input type="checkbox" id="theme-toggle-setting">
                                    <span class="pf-toggle-slider"></span>
                                </label>
                                <div class="pf-setting-info">
                                    <span class="pf-setting-title">Modo Oscuro</span>
                                    <span class="pf-setting-desc">Cambia entre tema claro y oscuro</span>
                                </div>
                            </div>
                        </div>

                        <div class="pf-setting-group">
                            <h3>Cuenta</h3>
                            <div class="pf-setting-item">
                                <button class="pf-btn-outline" onclick="ProfileUtils.editProfile()">
                                    <i class="fas fa-user-edit"></i> Editar Perfil
                                </button>
                            </div>
                            <div class="pf-setting-item">
                                <button class="pf-btn-outline" onclick="ProfileUtils.changePassword()">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            </div>
                            <div class="pf-setting-item">
                                <button class="pf-btn-danger" onclick="ProfileUtils.logout()">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </button>
                            </div>
                        </div>

                        <div class="pf-setting-group">
                            <h3>Privacidad</h3>
                            <div class="pf-setting-item">
                                <label class="pf-toggle">
                                    <input type="checkbox" id="profile-visibility">
                                    <span class="pf-toggle-slider"></span>
                                </label>
                                <div class="pf-setting-info">
                                    <span class="pf-setting-title">Perfil Público</span>
                                    <span class="pf-setting-desc">Permitir que otros usuarios vean tu perfil</span>
                                </div>
                            </div>
                            <div class="pf-setting-item">
                                <label class="pf-toggle">
                                    <input type="checkbox" id="email-notifications">
                                    <span class="pf-toggle-slider"></span>
                                </label>
                                <div class="pf-setting-info">
                                    <span class="pf-setting-title">Notificaciones por Email</span>
                                    <span class="pf-setting-desc">Recibir notificaciones de actividad</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
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
    <script src="../js/perfil.js"></script>
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
