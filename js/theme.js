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
    const body = document.body;

    if (!themeToggle) {
        console.warn('Theme toggle button not found');
        return;
    }

    const themeIcon = themeToggle.querySelector('i');

    // Cargar tema guardado del usuario
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        body.classList.add('dark-theme');
        if (themeIcon) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
    }

    // Event listener para el botón de cambio de tema
    themeToggle.addEventListener('click', () => {
        // Agregar clase de transición para animación suave
        body.classList.add('theme-transitioning');

        // Pequeño delay para que la transición se active
        setTimeout(() => {
            // Toggle de la clase del tema
            body.classList.toggle('dark-theme');

            // Actualizar icono y guardar preferencia
            if (body.classList.contains('dark-theme')) {
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
                body.classList.remove('theme-transitioning');
            }, 300); // Tiempo de la transición CSS
        }, 50); // Pequeño delay inicial
    });
}

// Función para obtener el tema actual
function getCurrentTheme() {
    return localStorage.getItem('theme') || 'light';
}

// Función para forzar un tema específico
function setTheme(theme) {
    const body = document.body;
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle ? themeToggle.querySelector('i') : null;

    // Agregar clase de transición
    body.classList.add('theme-transitioning');

    setTimeout(() => {
        if (theme === 'dark') {
            body.classList.add('dark-theme');
            if (themeIcon) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
            localStorage.setItem('theme', 'dark');
        } else {
            body.classList.remove('dark-theme');
            if (themeIcon) {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            localStorage.setItem('theme', 'light');
        }

        // Quitar clase de transición después de la animación
        setTimeout(() => {
            body.classList.remove('theme-transitioning');
        }, 300);
    }, 50);
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initThemeToggle);

// Exportar funciones para uso externo si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initThemeToggle, getCurrentTheme, setTheme };
}