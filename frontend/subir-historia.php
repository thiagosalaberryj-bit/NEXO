<?php
require_once __DIR__ . '/../backend/session/session_manager.php';
if (!isLoggedIn()) {
    header('Location: explorar.php?auth=required');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Historia - NEXO | E.S.T. N°1 Vicente López</title>
    <link rel="shortcut icon" href="../components/Logo.png?v=1" type="image/x-icon">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/explorar.css">
    <link rel="stylesheet" href="../css/subir-historia.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark-theme');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body>
    <div id="top"></div>

    <header id="header" class="navbar">
        <?php include __DIR__ . '/../components/navbar.php'; ?>
    </header>

    <main class="sh-main">
        <div class="sh-container">
            <h1 class="sh-page-title"><i class="fas fa-pen-fancy"></i> Crear Nueva Historia</h1>

            <!-- Barra de pasos -->
            <div class="sh-stepper">
                <div class="sh-step active" data-step="1">
                    <div class="sh-step-circle">1</div>
                    <span class="sh-step-label">Datos</span>
                </div>
                <div class="sh-step-line"></div>
                <div class="sh-step" data-step="2">
                    <div class="sh-step-circle">2</div>
                    <span class="sh-step-label">Archivos</span>
                </div>
                <div class="sh-step-line"></div>
                <div class="sh-step" data-step="3">
                    <div class="sh-step-circle">3</div>
                    <span class="sh-step-label">Colaboradores</span>
                </div>
                <div class="sh-step-line"></div>
                <div class="sh-step" data-step="4">
                    <div class="sh-step-circle">4</div>
                    <span class="sh-step-label">Vista previa</span>
                </div>
                <div class="sh-step-line"></div>
                <div class="sh-step" data-step="5">
                    <div class="sh-step-circle">5</div>
                    <span class="sh-step-label">Publicar</span>
                </div>
            </div>

            <!-- Contenedor del formulario -->
            <form id="upload-form" class="sh-form-card" enctype="multipart/form-data">

                <!-- Paso 1: Datos básicos -->
                <div class="sh-panel active" id="panel-1">
                    <h2 class="sh-panel-title"><i class="fas fa-info-circle"></i> Información Básica</h2>

                    <div class="sh-field">
                        <label for="story-title">Título <span class="sh-req">*</span></label>
                        <input type="text" id="story-title" name="title" placeholder="Ej: El reino de los códigos perdidos" required maxlength="100">
                        <small class="sh-counter"><span id="title-count">0</span>/100</small>
                    </div>

                    <div class="sh-field">
                        <label for="story-desc">Descripción <span class="sh-req">*</span></label>
                        <textarea id="story-desc" name="description" placeholder="Cuenta de qué trata tu historia..." required maxlength="500" rows="4"></textarea>
                        <small class="sh-counter"><span id="desc-count">0</span>/500</small>
                    </div>

                    <div class="sh-field">
                        <label for="story-genre">Género <span class="sh-req">*</span></label>
                        <select id="story-genre" name="genre" required>
                            <option value="">Selecciona un género</option>
                            <option value="ciencia-ficcion">Ciencia Ficción</option>
                            <option value="fantasia">Fantasía</option>
                            <option value="terror">Terror</option>
                            <option value="romance">Romance</option>
                            <option value="aventura">Aventura</option>
                            <option value="misterio">Misterio</option>
                            <option value="drama">Drama</option>
                            <option value="humor">Humor</option>
                            <option value="historico">Histórico</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="sh-field sh-hidden" id="custom-genre-field">
                        <label for="custom-genre">Género personalizado <span class="sh-req">*</span></label>
                        <input type="text" id="custom-genre" name="custom_genre" placeholder="Escribe tu género...">
                    </div>
                </div>

                <!-- Paso 2: Archivos -->
                <div class="sh-panel" id="panel-2">
                    <h2 class="sh-panel-title"><i class="fas fa-file-upload"></i> Archivos de la Historia</h2>

                    <div class="sh-field">
                        <label>Archivo HTML de la historia <span class="sh-req">*</span></label>
                        <div class="sh-dropzone" id="drop-html">
                            <i class="fas fa-file-code"></i>
                            <p>Arrastra tu archivo <strong>.html</strong> aquí o haz clic para seleccionarlo</p>
                            <input type="file" id="story-html" name="story_html" accept=".html,.htm" hidden>
                            <button type="button" class="sh-btn-outline" onclick="document.getElementById('story-html').click()">Seleccionar archivo</button>
                        </div>
                        <div class="sh-file-info sh-hidden" id="html-info"></div>
                    </div>

                    <div class="sh-field">
                        <label>Imagen de portada <span class="sh-req">*</span></label>
                        <div class="sh-dropzone" id="drop-cover">
                            <i class="fas fa-image"></i>
                            <p>Arrastra tu imagen de portada o haz clic para seleccionarla</p>
                            <small>JPG, PNG o GIF — Recomendado 800×1200 px</small>
                            <input type="file" id="story-cover" name="cover" accept="image/*" hidden>
                            <button type="button" class="sh-btn-outline" onclick="document.getElementById('story-cover').click()">Seleccionar imagen</button>
                        </div>
                        <ul class="sh-file-list" id="cover-list"></ul>
                    </div>

                    <div class="sh-field">
                        <label>Recursos adicionales <span class="sh-optional">(opcional)</span></label>
                        <div class="sh-inline-field">
                            <input type="text" id="folder-name" name="folder_name" placeholder="Nombre de la carpeta de recursos (opcional)">
                        </div>
                        <div class="sh-dropzone sh-dropzone-small" id="drop-resources">
                            <i class="fas fa-folder-plus"></i>
                            <p>Arrastra videos, GIFs o imágenes (máx. 5 MB cada uno)</p>
                            <input type="file" id="story-resources" name="resources[]" accept="image/*,video/*,.gif" multiple hidden>
                            <button type="button" class="sh-btn-outline" onclick="document.getElementById('story-resources').click()">Seleccionar archivos</button>
                        </div>
                        <ul class="sh-file-list" id="resources-list"></ul>
                    </div>

                    <div class="sh-alert sh-alert-info">
                        <div class="sh-alert-header">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota sobre carpetas</strong>
                        </div>
                        <p>Luego al editar la historia en tu perfil podrás crear múltiples carpetas para organizar tus historias. Si subes contenido aquí pero no le pones nombre a tu carpeta, se pondrá automáticamente como "contenido" (es recomendable ponerle un nombre para evitar problemas de enrutamiento).</p>
                    </div>
                </div>

                <!-- Paso 3: Colaboradores -->
                <div class="sh-panel" id="panel-3">
                    <h2 class="sh-panel-title"><i class="fas fa-users"></i> Agregar Colaboradores</h2>
                    <p class="sh-panel-desc">Busca usuarios por nombre completo o username para invitarlos a colaborar en tu historia.</p>

                    <div class="sh-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="collab-search" placeholder="Buscar por nombre o @username...">
                    </div>

                    <!-- Resultados de búsqueda simulados -->
                    <div class="sh-search-results sh-hidden" id="search-results"></div>

                    <div class="sh-collab-list-wrap">
                        <h3 class="sh-collab-list-title">Invitaciones pendientes <span id="collab-count">(0)</span></h3>
                        <div class="sh-collab-empty" id="collab-empty">
                            <i class="fas fa-user-friends"></i>
                            <p>Aún no agregaste colaboradores</p>
                        </div>
                        <ul class="sh-collab-list" id="collab-list"></ul>
                    </div>
                </div>

                <!-- Paso 4: Vista previa -->
                <div class="sh-panel" id="panel-4">
                    <h2 class="sh-panel-title"><i class="fas fa-eye"></i> Vista Previa</h2>
                    <p class="sh-panel-desc">Así se verá la tarjeta de tu historia en la página de explorar.</p>

                    <div class="sh-preview-center">
                        <div class="story-card sh-preview-card">
                            <div class="story-cover">
                                <img id="preview-cover" src="https://via.placeholder.com/400x240?text=Portada" alt="Portada">
                                <div class="story-overlay"></div>
                            </div>
                            <div class="story-content">
                                <h3 class="story-title" id="preview-title">Título de tu historia</h3>
                                <p class="story-author" id="preview-author">por <?php echo htmlspecialchars(getCurrentUserName(), ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="story-description" id="preview-desc">La descripción de tu historia aparecerá aquí...</p>
                                <div class="story-meta">
                                    <span class="story-genre"><i class="fas fa-tag"></i> <span id="preview-genre">Género</span></span>
                                    <div class="story-stats">
                                        <span><i class="fas fa-eye"></i> 0</span>
                                        <span><i class="fas fa-heart"></i> 0</span>
                                    </div>
                                </div>
                                <div class="story-actions">
                                    <button type="button" class="action-btn" disabled><i class="far fa-heart"></i><span>Like</span></button>
                                    <button type="button" class="action-btn" disabled><i class="far fa-comment"></i><span>Comentar</span></button>
                                    <button type="button" class="action-btn" disabled><i class="fas fa-book-open"></i><span>Leer</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paso 5: Publicar -->
                <div class="sh-panel" id="panel-5">
                    <h2 class="sh-panel-title"><i class="fas fa-rocket"></i> ¡Todo listo!</h2>
                    <p class="sh-panel-desc">Elige cómo quieres guardar tu historia.</p>

                    <div class="sh-publish-grid">
                        <div class="sh-publish-option" id="opt-publish">
                            <div class="sh-publish-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <h3>Publicar Ahora</h3>
                            <p>Tu historia será visible para toda la comunidad de NEXO. Aparecerá en la página de explorar y cualquier usuario podrá leerla, darle like y comentar.</p>
                            <button type="button" class="sh-btn-primary" id="btn-publish">
                                <i class="fas fa-paper-plane"></i> Publicar Historia
                            </button>
                        </div>

                        <div class="sh-publish-option" id="opt-draft">
                            <div class="sh-publish-icon draft">
                                <i class="fas fa-save"></i>
                            </div>
                            <h3>Guardar como Borrador</h3>
                            <p>Tu historia se guardará de forma privada. Solo vos y los colaboradores podrán verla y editarla. Cuando esté lista, podrás publicarla desde tu perfil.</p>
                            <button type="button" class="sh-btn-secondary" id="btn-draft">
                                <i class="fas fa-save"></i> Guardar Borrador
                            </button>
                        </div>
                    </div>

                    <div id="collab-publish-message" class="sh-alert sh-alert-info sh-hidden">
                        <div class="sh-alert-header">
                            <i class="fas fa-info-circle"></i>
                            <strong>Colaboradores invitados</strong>
                        </div>
                        <p>No puedes publicar la historia hasta que todos los colaboradores acepten la invitación. Guarda como borrador y verifica el estado desde tu perfil.</p>
                    </div>
                </div>

            </form>

            <!-- Navegación -->
            <div class="sh-nav-buttons">
                <button type="button" class="sh-btn-secondary" id="btn-prev" disabled>
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
                <button type="button" class="sh-btn-primary" id="btn-next">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <p>© 2026 NEXO - Escuela Secundaria Técnica N°1 de Vicente López</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/theme.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/api.js"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/modals.js"></script>
    <script src="../js/subir-historia.js"></script>

    <!-- Modal para vista de imágenes -->
    <div id="image-modal" class="sh-modal">
        <div class="sh-modal-overlay" id="image-modal-overlay"></div>
        <div class="sh-modal-content">
            <div class="sh-modal-header">
                <h3 id="image-modal-title">Vista previa</h3>
                <button type="button" class="sh-modal-close" id="image-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="sh-modal-body">
                <img id="image-modal-img" src="" alt="Imagen completa" class="sh-modal-image">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initThemeToggle === 'function') initThemeToggle();
            if (typeof initUserMenu === 'function') initUserMenu();
            if (typeof setActiveNavLink === 'function') setActiveNavLink();
            if (typeof initNavbar === 'function') initNavbar();
            if (typeof initAuthModals === 'function') initAuthModals();
        });
    </script>
</body>
</html>
