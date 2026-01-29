// Toggle Theme
const themeToggle = document.getElementById('theme-toggle');
const body = document.body;
const themeIcon = themeToggle.querySelector('i');
const menuToggle = document.getElementById('menu-toggle');
const navCenter = document.querySelector('.nav-center');

// Cargar tema guardado
const currentTheme = localStorage.getItem('theme') || 'light';
if (currentTheme === 'dark') {
    body.classList.add('dark-theme');
    themeIcon.classList.remove('fa-moon');
    themeIcon.classList.add('fa-sun');
}

themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-theme');
    
    if (body.classList.contains('dark-theme')) {
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        localStorage.setItem('theme', 'dark');
    } else {
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        localStorage.setItem('theme', 'light');
    }
});

// Toggle mobile menu
if (menuToggle && navCenter) {
    menuToggle.addEventListener('click', () => {
        navCenter.classList.toggle('open');
    });

    // Close menu on link click
    navCenter.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navCenter.classList.remove('open');
        });
    });
}

// Hide Navbar on Scroll - DESHABILITADO
/*
let lastScrollTop = 0;
const navbar = document.getElementById('header');
const navbarHeight = navbar.offsetHeight;

window.addEventListener('scroll', () => {
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > navbarHeight) {
        if (scrollTop > lastScrollTop) {
            // Scroll down
            navbar.classList.add('hidden');
        } else {
            // Scroll up
            navbar.classList.remove('hidden');
        }
    } else {
        navbar.classList.remove('hidden');
    }
    
    lastScrollTop = scrollTop;
});
*/

// Smooth scroll para enlaces
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Active section underline via IntersectionObserver (más preciso)
(function setupActiveSectionObserver() {
    const ids = ['#que-es', '#acerca', '#caracteristicas'];
    const linkMap = new Map();
    ids.forEach(id => {
        const link = document.querySelector(`.nav-center a[href="${id}"]`);
        const section = document.querySelector(id);
        if (link && section) {
            linkMap.set(section, link);
        }
    });

    if (linkMap.size === 0) return;

    const setActive = (activeLink) => {
        document.querySelectorAll('.nav-center .nav-link').forEach(a => a.classList.remove('active'));
        if (activeLink) activeLink.classList.add('active');
    };

    const observer = new IntersectionObserver((entries) => {
        let mostVisible = null;
        let maxVisible = 0;

        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            const rect = entry.boundingClientRect;
            const viewportHeight = window.innerHeight;
            const visibleTop = Math.max(0, -rect.top);
            const visibleBottom = Math.min(rect.height, viewportHeight - rect.top);
            const visibleHeight = Math.max(0, visibleBottom - visibleTop);
            const ratio = visibleHeight / viewportHeight;

            if (ratio > maxVisible) {
                maxVisible = ratio;
                mostVisible = entry.target;
            }
        });

        if (mostVisible) {
            const link = linkMap.get(mostVisible);
            setActive(link);
        }
    }, {
        threshold: Array.from({ length: 11 }, (_, i) => i / 10),
        rootMargin: '-15% 0px -15% 0px'
    });

    linkMap.forEach((_, section) => observer.observe(section));
})();

// Parallax effect para la imagen flotante
window.addEventListener('scroll', () => {
    const heroImage = document.querySelector('.hero-image');
    const heroSection = document.querySelector('.hero-section');
    
    if (heroImage && heroSection) {
        const rect = heroSection.getBoundingClientRect();
        const scrollTop = window.pageYOffset;
        
        // Solo aplicar parallax cuando la sección está visible
        if (rect.bottom > 0 && rect.top < window.innerHeight) {
            // Calcular offset basado en el scroll (movimiento más suave)
            const parallaxOffset = scrollTop * 0.15; // Factor de 0.15 para movimiento sutil
            
            // Limitar el movimiento para que no se salga del contenedor
            const maxOffset = 30; // máximo 30px de desplazamiento
            const clampedOffset = Math.max(-maxOffset, Math.min(maxOffset, parallaxOffset));
            
            heroImage.style.setProperty('--scroll-offset', `${clampedOffset}px`);
        }
    }
});

// Mejorar la animación de float con interacción del usuario
document.addEventListener('DOMContentLoaded', () => {
    const floatingImage = document.querySelector('.floating-image');
    
    if (floatingImage) {
        // Pausar animación al hover para mejor UX
        floatingImage.addEventListener('mouseenter', () => {
            floatingImage.style.animationPlayState = 'paused';
        });
        
        floatingImage.addEventListener('mouseleave', () => {
            floatingImage.style.animationPlayState = 'running';
        });
    }
});