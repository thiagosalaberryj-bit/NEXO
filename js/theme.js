/**
 * Theme Toggle - Cambio de tema claro/oscuro
 * Proyecto NEXO - Escuela Secundaria Técnica N°1
 *
 * Este archivo maneja exclusivamente el cambio entre tema claro y oscuro,
 * guardando la preferencia del usuario en localStorage.
 */

// Función principal para inicializar el toggle de tema
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;

    if (!themeToggle) {
        console.warn('Theme toggle button not found');
        return;
    }

    // Si este botón ya fue inicializado, solo sincronizar estados
    if (themeToggle.dataset.initialized === 'true') {
        syncThemeWithStorage();
        updateThemeButtonState();
        return;
    }

    const themeIcon = themeToggle.querySelector('i');

    // Cargar tema guardado del usuario
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        html.classList.add('dark-theme');
        if (themeIcon) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
    } else {
        html.classList.remove('dark-theme');
        if (themeIcon) {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }

    // Event listener para el botón de cambio de tema
    themeToggle.addEventListener('click', () => {
        // Agregar clase de transición para animación suave
        html.classList.add('theme-transitioning');

        // Pequeño delay para que la transición se active
        setTimeout(() => {
            // Toggle de la clase del tema
            html.classList.toggle('dark-theme');

            // Actualizar icono y guardar preferencia
            if (html.classList.contains('dark-theme')) {
                if (themeIcon) {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                }
                localStorage.setItem('theme', 'dark');
            } else {
                if (themeIcon) {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
                localStorage.setItem('theme', 'light');
            }

            // Quitar clase de transición después de la animación
            setTimeout(() => {
                html.classList.remove('theme-transitioning');
            }, 300); // Tiempo de la transición CSS
        }, 50); // Pequeño delay inicial
    });

    // Marcar este botón como inicializado para evitar listeners duplicados
    themeToggle.dataset.initialized = 'true';
}

// Función para actualizar el estado visual del botón de tema
function updateThemeButtonState() {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;

    const themeIcon = themeToggle.querySelector('i');
    if (!themeIcon) return;

    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
    } else {
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
    }
}

// Mantener la clase del <html> sincronizada con la preferencia persistida
function syncThemeWithStorage() {
    const html = document.documentElement;
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        html.classList.add('dark-theme');
    } else {
        html.classList.remove('dark-theme');
    }
}

// Función para obtener el tema actual
function getCurrentTheme() {
    return localStorage.getItem('theme') || 'light';
}

// Función para forzar un tema específico
function setTheme(theme) {
    const html = document.documentElement;
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle ? themeToggle.querySelector('i') : null;

    // Agregar clase de transición
    html.classList.add('theme-transitioning');

    setTimeout(() => {
        if (theme === 'dark') {
            html.classList.add('dark-theme');
            if (themeIcon) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
            localStorage.setItem('theme', 'dark');
        } else {
            html.classList.remove('dark-theme');
            if (themeIcon) {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            localStorage.setItem('theme', 'light');
        }

        // Quitar clase de transición después de la animación
        setTimeout(() => {
            html.classList.remove('theme-transitioning');
        }, 300);
    }, 50);
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initThemeToggle);

// Exportar funciones para uso externo si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initThemeToggle,
        getCurrentTheme,
        setTheme,
        updateThemeButtonState,
        syncThemeWithStorage
    };
}