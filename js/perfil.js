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

    // Por ahora no cargar datos - próximamente conexión con backend
    // loadUserData();

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