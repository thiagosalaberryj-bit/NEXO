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

    loadMockUserData();

    function loadMockUserData() {
        // Datos simulados - reemplazar con llamadas reales al backend
        const mockData = {
            name: 'María González',
            email: 'maria.gonzalez@nexo.edu.ar',
            stories: 5,
            likes: 127,
            comments: 23,
            storiesStats: [
                {
                    id: 1,
                    title: 'La Aventura del Bosque Encantado',
                    likes: 45,
                    formsCompleted: 12,
                    totalViews: 234,
                    publishDate: '2024-01-15'
                },
                {
                    id: 2,
                    title: 'El Misterio de la Biblioteca Antigua',
                    likes: 32,
                    formsCompleted: 8,
                    totalViews: 189,
                    publishDate: '2024-01-20'
                },
                {
                    id: 3,
                    title: 'Viaje al Centro de la Tierra',
                    likes: 28,
                    formsCompleted: 15,
                    totalViews: 156,
                    publishDate: '2024-01-25'
                },
                {
                    id: 4,
                    title: 'La Leyenda del Dragón Dorado',
                    likes: 22,
                    formsCompleted: 6,
                    totalViews: 98,
                    publishDate: '2024-02-01'
                }
            ],
            forms: [
                {
                    id: 1,
                    title: 'Feedback - La Aventura del Bosque Encantado',
                    storyTitle: 'La Aventura del Bosque Encantado',
                    responses: 12,
                    questions: 5,
                    createdDate: '2024-01-16'
                },
                {
                    id: 2,
                    title: 'Opinión sobre personajes',
                    storyTitle: 'El Misterio de la Biblioteca Antigua',
                    responses: 8,
                    questions: 3,
                    createdDate: '2024-01-21'
                }
            ]
        };

        // Actualizar elementos del DOM
        document.getElementById('user-name').textContent = mockData.name;
        document.getElementById('user-email').textContent = mockData.email;
        document.getElementById('user-stories').textContent = mockData.stories;
        document.getElementById('user-likes').textContent = mockData.likes;
        document.getElementById('user-comments').textContent = mockData.comments;

        // Cargar estadísticas de historias
        loadStoriesStats(mockData.storiesStats);

        // Cargar formularios
        loadFormsList(mockData.forms);
    }

    function loadStoriesStats(storiesStats) {
        const container = document.getElementById('stories-stats');

        if (storiesStats.length === 0) {
            container.innerHTML = `
                <div class="pf-empty-state">
                    <i class="fas fa-chart-line"></i>
                    <h3>No hay estadísticas disponibles</h3>
                    <p>Las estadísticas aparecerán cuando tus historias reciban interacciones</p>
                </div>
            `;
            return;
        }

        let html = '';
        storiesStats.forEach(story => {
            html += `
                <div class="pf-story-stat-card">
                    <div class="pf-story-stat-header">
                        <h3>${story.title}</h3>
                        <span class="pf-story-date">${new Date(story.publishDate).toLocaleDateString('es-ES')}</span>
                    </div>
                    <div class="pf-story-stat-metrics">
                        <div class="pf-stat-metric">
                            <i class="fas fa-heart"></i>
                            <span class="pf-metric-value">${story.likes}</span>
                            <span class="pf-metric-label">Likes</span>
                        </div>
                        <div class="pf-stat-metric">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="pf-metric-value">${story.formsCompleted}</span>
                            <span class="pf-metric-label">Formularios</span>
                        </div>
                        <div class="pf-stat-metric">
                            <i class="fas fa-eye"></i>
                            <span class="pf-metric-value">${story.totalViews}</span>
                            <span class="pf-metric-label">Vistas</span>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function loadFormsList(forms) {
        const container = document.getElementById('forms-list');

        if (forms.length === 0) {
            container.innerHTML = `
                <div class="pf-empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Aún no has creado formularios</h3>
                    <p>Crea formularios para recopilar feedback de tus lectores</p>
                </div>
            `;
            return;
        }

        let html = '';
        forms.forEach(form => {
            html += `
                <div class="pf-form-item">
                    <div class="pf-form-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="pf-form-info">
                        <h3>${form.title}</h3>
                        <p>Historia: ${form.storyTitle}</p>
                        <div class="pf-form-meta">
                            <span><i class="fas fa-question-circle"></i> ${form.questions} preguntas</span>
                            <span><i class="fas fa-users"></i> ${form.responses} respuestas</span>
                            <span><i class="fas fa-calendar"></i> ${new Date(form.createdDate).toLocaleDateString('es-ES')}</span>
                        </div>
                    </div>
                    <div class="pf-form-actions">
                        <button class="pf-btn-outline" onclick="ProfileUtils.viewFormResponses(${form.id})">
                            <i class="fas fa-eye"></i> Ver Respuestas
                        </button>
                        <button class="pf-btn-outline" onclick="ProfileUtils.editForm(${form.id})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Funciones de utilidad para futuras implementaciones
    window.ProfileUtils = {
        // Cambiar avatar
        changeAvatar: function(file) {
            // Implementar lógica para subir imagen
            console.log('Cambiando avatar:', file);
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de cambio de avatar próximamente');
            }
        },

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
                window.location.href = '../backend/auth/logout.php';
            }
        },

        // Crear formulario para historia
        createStoryForm: function() {
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de creación de formularios próximamente');
            }
        },

        // Ver respuestas de formulario
        viewFormResponses: function(formId) {
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de ver respuestas próximamente');
            }
        },

        // Editar formulario
        editForm: function(formId) {
            if (typeof showNotification === 'function') {
                showNotification('info', 'Función de editar formulario próximamente');
            }
        },

        // Actualizar perfil
        updateProfile: function(data) {
            // Implementar lógica para actualizar datos del usuario
            console.log('Actualizando perfil:', data);
        },

        // Cargar historias del usuario
        loadUserStories: function() {
            // Implementar lógica para cargar historias
            console.log('Cargando historias del usuario');
        }
    };
});