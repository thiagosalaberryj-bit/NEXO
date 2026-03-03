/**
 * Perfil - Funcionalidad de navegación y gestión
 */

document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const navItems = document.querySelectorAll('.pf-nav-item');
    const sections = document.querySelectorAll('.pf-section');
    const themeToggle = document.getElementById('theme-toggle-setting');
    const editAvatarBtn = document.getElementById('edit-avatar-btn');

    // estado local
    let allStories = [];

    // Navegación entre secciones
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const sectionId = item.dataset.section;

            // Remover clase active de todos los items y secciones
            navItems.forEach(nav => nav.classList.remove('active'));
            sections.forEach(section => section.classList.remove('active'));

            // Agregar clase active al item y sección seleccionados
            item.classList.add('active');
            document.getElementById(`${sectionId}-section`).classList.add('active');
        });
    });

    // Toggle de tema
    if (themeToggle) {
        // Sincronizar con el estado actual del tema
        const currentTheme = localStorage.getItem('theme');
        themeToggle.checked = currentTheme === 'dark';

        themeToggle.addEventListener('change', () => {
            const newTheme = themeToggle.checked ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);

            if (newTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            } else {
                document.documentElement.classList.remove('dark-theme');
            }

            // Mostrar notificación
            if (typeof showNotification === 'function') {
                showNotification('success', `Tema ${newTheme === 'dark' ? 'oscuro' : 'claro'} activado`);
            }
        });
    }

    // Editar avatar (placeholder)
    if (editAvatarBtn) {
        editAvatarBtn.addEventListener('click', () => {
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de cambio de avatar próximamente');
            }
        });
    }

    // Simular datos de usuario (esto vendría del backend)
    // Elementos del DOM adicionales
    const createFormBtn = document.getElementById('create-form-btn');

    // Crear formulario
    if (createFormBtn) {
        createFormBtn.addEventListener('click', () => {
            ProfileUtils.createStoryForm();
        });
    }

    // Cargar datos reales del perfil e historias
    loadProfileInfo();
    loadUserStories();

    // Funciones de utilidad usadas por la UI actual
    window.ProfileUtils = {
        // Editar perfil
        editProfile: function() {
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de edición de perfil próximamente');
            }
        },

        // Cambiar contraseña
        changePassword: function() {
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de cambio de contraseña próximamente');
            }
        },

        // Cerrar sesión
        logout: function() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                window.location.href = '../backend/session/logout.php';
            }
        },

        // Crear formulario para historia
        createStoryForm: function() {
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de creación de formularios próximamente');
            }
        },

        // Cargar historias del usuario
        loadUserStories: function() {
            loadUserStories();
        }
    };

    function loadProfileInfo() {
        apiFetch('/backend/perfil/profile_info.php', {
            method: 'GET'
        }).then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        }).then(data => {
            if (!data.success || !data.profile) {
                throw new Error(data.message || 'No se pudo cargar el perfil');
            }

            renderProfileInfo(data.profile);
        }).catch(error => {
            console.error('Error cargando perfil:', error);
            if (typeof showNotification === 'function') {
                showNotification('error', 'No se pudo cargar la información del perfil');
            }
        });
    }

    function renderProfileInfo(profile) {
        const userNameEl = document.getElementById('user-name');
        const userEmailEl = document.getElementById('user-email');
        const userStoriesEl = document.getElementById('user-stories');
        const userLikesEl = document.getElementById('user-likes');
        const userCommentsEl = document.getElementById('user-comments');

        const fullName = `${profile.nombre} ${profile.apellido}`.trim();

        if (userNameEl) userNameEl.textContent = fullName || profile.username || 'Usuario';
        if (userEmailEl) userEmailEl.textContent = profile.email || '';
        if (userStoriesEl) userStoriesEl.textContent = profile.total_historias || 0;
        if (userLikesEl) userLikesEl.textContent = profile.total_likes || 0;
        if (userCommentsEl) userCommentsEl.textContent = profile.total_comentarios || 0;
    }

    function loadUserStories() {
        apiFetch('/backend/perfil/historias/list.php', {
            method: 'GET'
        }).then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        }).then(data => {
            if (!data.success || !Array.isArray(data.historias)) {
                throw new Error(data.message || 'No se pudieron cargar las historias');
            }

            allStories = data.historias;
            renderStories(allStories);
            setupStoryCardOptions();
        }).catch(error => {
            console.error('Error cargando historias:', error);
            if (typeof showNotification === 'function') {
                showNotification('error', 'No se pudieron cargar tus historias');
            }
        });
    }

    function renderStories(stories) {
        const storiesGrid = document.getElementById('stories-grid');
        if (!storiesGrid) {
            return;
        }

        if (!stories.length) {
            storiesGrid.innerHTML = `
                <div class="pf-empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>Aún no has creado ninguna historia</h3>
                    <p>¡Comparte tus historias con la comunidad!</p>
                    <a href="subir-historia.php" class="pf-btn-primary">
                        Crear Primera Historia
                    </a>
                </div>
            `;
            return;
        }

        storiesGrid.innerHTML = stories.map(story => {
            const fecha = formatDate(story.fecha_creacion);
            const descripcion = escapeHtml((story.descripcion || '').trim());
            const descripcionCorta = descripcion.length > 120 ? descripcion.slice(0, 120) + '...' : descripcion;
            const portadaUrl = story.portada ? getBaseUrl() + story.portada : 'https://via.placeholder.com/400x240?text=Sin+Portada';

            return `
                <article class="pf-story-card" data-story-id="${story.id_historia}">
                    <div class="pf-story-cover">
                        <img src="${escapeHtml(portadaUrl)}" alt="Portada de ${escapeHtml(story.titulo || 'historia')}" loading="lazy">
                        <div class="pf-story-overlay"></div>
                        <button type="button" class="pf-story-toggle" data-card-toggle aria-label="Opciones">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <div class="pf-story-content">
                        <h3 class="pf-story-title">${escapeHtml(story.titulo || 'Sin título')}</h3>
                        <p class="pf-story-description">${descripcionCorta || 'Sin descripción'}</p>
                        <div class="pf-story-meta">
                            <span class="pf-story-genre"><i class="fas fa-tag"></i> ${escapeHtml(story.genero || 'Sin género')}</span>
                            <div class="pf-story-stats">
                                <span><i class="fas fa-eye"></i> ${story.total_vistas || 0}</span>
                                <span><i class="fas fa-heart"></i> ${story.total_likes || 0}</span>
                                <span><i class="fas fa-comments"></i> ${story.total_comentarios || 0}</span>
                            </div>
                        </div>
                    </div>
                    <div class="pf-story-options-menu" data-card-menu>
                        <button type="button" class="pf-story-option-btn" data-card-action="read" data-story-id="${story.id_historia}">Leer historia</button>
                        <button type="button" class="pf-story-option-btn" data-card-action="edit" data-story-id="${story.id_historia}">Modificar</button>
                        <button type="button" class="pf-story-option-btn" data-card-action="status" data-story-id="${story.id_historia}">${story.estado === 'publicada' ? 'Pasar a borrador' : 'Publicar historia'}</button>
                        <button type="button" class="pf-story-option-btn" data-card-action="versions" data-story-id="${story.id_historia}">Versiones</button>
                    </div>
                </article>
            `;
        }).join('');
    }

    function formatDate(value) {
        if (!value) {
            return 'Fecha no disponible';
        }

        const date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function setupStoryCardOptions() {
        const storiesGrid = document.getElementById('stories-grid');
        if (!storiesGrid) return;

        storiesGrid.addEventListener('click', (event) => {
            const toggleBtn = event.target.closest('[data-card-toggle]');
            const actionBtn = event.target.closest('[data-card-action]');

            if (toggleBtn) {
                const card = toggleBtn.closest('.pf-story-card');
                const menu = card ? card.querySelector('[data-card-menu]') : null;

                storiesGrid.querySelectorAll('[data-card-menu].show').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });

                if (menu) menu.classList.toggle('show');
                return;
            }

            if (actionBtn) {
                const action = actionBtn.getAttribute('data-card-action');
                const storyId = actionBtn.getAttribute('data-story-id');

                if (action === 'read') {
                    // abrir sin contar vistas: usamos la ruta del archivo twine si está disponible
                    const story = allStories.find(s => String(s.id_historia) === storyId);
                    if (story && story.archivo_twine) {
                        window.open(getBaseUrl() + story.archivo_twine, '_blank');
                    } else {
                        if (typeof showNotification === 'function') {
                            showNotification('error', 'No hay archivo disponible para esta historia');
                        }
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        if (action === 'edit') showNotification('info', 'Edición de historia próximamente');
                        else if (action === 'status') showNotification('info', 'Cambio de estado próximamente');
                        else if (action === 'versions') showNotification('info', 'Gestión de versiones próximamente');
                    }
                }

                const m = actionBtn.closest('[data-card-menu]');
                if (m) m.classList.remove('show');
            }
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.pf-story-card')) {
                storiesGrid.querySelectorAll('[data-card-menu].show').forEach(m => m.classList.remove('show'));
            }
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});