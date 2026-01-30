/**
 * Navbar Functionality - Funcionalidad del navbar
 * Proyecto NEXO - Escuela Secundaria Técnica N°1
 *
 * Este archivo maneja exclusivamente la funcionalidad del navbar:
 * - Menú móvil (hamburger menu)
 * - Menú de usuario (dropdown)
 * - Navegación responsive
 */

// Función principal para inicializar el navbar
function initNavbar() {
    initMobileMenu();
    initUserMenu();
    initSmoothScroll();
}

// Función para el menú móvil (hamburger)
function initMobileMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const navCenter = document.querySelector('.nav-center');

    if (!menuToggle || !navCenter) {
        console.warn('Mobile menu elements not found');
        return;
    }

    // Event listener para el botón hamburger
    menuToggle.addEventListener('click', () => {
        navCenter.classList.toggle('open');
    });

    // Cerrar menú al hacer click en un enlace
    navCenter.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navCenter.classList.remove('open');
        });
    });

    // Cerrar menú al hacer click fuera (en móviles)
    document.addEventListener('click', (e) => {
        if (!menuToggle.contains(e.target) && !navCenter.contains(e.target)) {
            navCenter.classList.remove('open');
        }
    });
}

// Función para el menú de usuario (dropdown)
function initUserMenu() {
    const userToggle = document.getElementById('user-toggle');
    const userDropdown = document.querySelector('.user-dropdown');

    if (!userToggle || !userDropdown) {
        // No mostrar warning ya que no todas las páginas tienen menú de usuario
        return;
    }

    // Event listener para el botón de usuario
    userToggle.addEventListener('click', (e) => {
        e.stopPropagation(); // Evitar que se propague al document
        userDropdown.classList.toggle('show');
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', (e) => {
        if (!userToggle.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });

    // Cerrar dropdown al presionar Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            userDropdown.classList.remove('show');
        }
    });
}

// Función para smooth scroll en enlaces internos
function initSmoothScroll() {
    // Smooth scroll para enlaces que empiezan con #
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            // Solo para enlaces internos (no URLs completas)
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);

                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

// Función para marcar el enlace activo basado en la URL actual
function setActiveNavLink() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        link.classList.remove('active');

        // Obtener la URL del enlace
        const linkHref = link.getAttribute('href');

        if (linkHref) {
            // Comparar rutas (ignorando parámetros de query)
            const linkPath = linkHref.split('?')[0];
            const currentPathName = currentPath.split('/').pop() || 'explorar.php';

            if (linkPath === currentPathName || linkPath === currentPath) {
                link.classList.add('active');
            }
        }
    });
}

// Función para ocultar/mostrar navbar al hacer scroll (opcional)
function initScrollHide() {
    /*
    let lastScrollTop = 0;
    const navbar = document.getElementById('header');
    const navbarHeight = navbar ? navbar.offsetHeight : 72;

    window.addEventListener('scroll', () => {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > navbarHeight) {
            if (scrollTop > lastScrollTop) {
                // Scroll down - hide navbar
                navbar.classList.add('hidden');
            } else {
                // Scroll up - show navbar
                navbar.classList.remove('hidden');
            }
        } else {
            navbar.classList.remove('hidden');
        }

        lastScrollTop = scrollTop;
    });
    */
}

// Función para verificar si estamos en móvil
function isMobile() {
    return window.innerWidth <= 900;
}

// Función para ajustar el navbar según el tamaño de pantalla
function handleResize() {
    // Lógica adicional si es necesaria para cambios de tamaño
    const navCenter = document.querySelector('.nav-center');
    if (navCenter && navCenter.classList.contains('open') && !isMobile()) {
        navCenter.classList.remove('open');
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    setActiveNavLink();
});

// Event listener para cambios de tamaño de ventana
window.addEventListener('resize', handleResize);

// Exportar funciones para uso externo si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initNavbar,
        initMobileMenu,
        initUserMenu,
        setActiveNavLink,
        isMobile
    };
}