/**
 * Perfil - Funcionalidad de navegación y gestión
 */

document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const navItems = document.querySelectorAll('.pf-nav-item');
    const sections = document.querySelectorAll('.pf-section');
    const themeToggle = document.getElementById('theme-toggle-setting');
    const editAvatarBtn = document.getElementById('edit-avatar-btn');

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

            renderStories(data.historias);
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
            const estadoClass = story.estado === 'publicada' ? 'Publicado' : 'Borrador';
            const fecha = formatDate(story.fecha_creacion);
            const descripcion = escapeHtml((story.descripcion || '').trim());
            const descripcionCorta = descripcion.length > 120 ? descripcion.slice(0, 120) + '...' : descripcion;

            return `
                <article class="pf-form-item">
                    <div class="pf-form-icon"><i class="fas fa-book"></i></div>
                    <div class="pf-form-info">
                        <h3>${escapeHtml(story.titulo || 'Sin título')}</h3>
                        <p>${descripcionCorta || 'Sin descripción'}</p>
                        <div class="pf-form-meta">
                            <span><i class="fas fa-tag"></i> ${escapeHtml(story.genero || 'Sin género')}</span>
                            <span><i class="fas fa-circle"></i> ${estadoClass}</span>
                            <span><i class="fas fa-calendar"></i> ${fecha}</span>
                        </div>
                        <div class="pf-form-meta">
                            <span><i class="fas fa-eye"></i> ${story.total_vistas || 0}</span>
                            <span><i class="fas fa-heart"></i> ${story.total_likes || 0}</span>
                            <span><i class="fas fa-comments"></i> ${story.total_comentarios || 0}</span>
                        </div>
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

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});