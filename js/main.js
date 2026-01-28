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

// Hide Navbar on Scroll
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

// Active section underline via IntersectionObserver
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
        let best = null;
        let bestRatio = 0;
        entries.forEach(entry => {
            if (entry.isIntersecting && entry.intersectionRatio > bestRatio) {
                best = entry;
                bestRatio = entry.intersectionRatio;
            }
        });
        if (best) {
            const link = linkMap.get(best.target);
            setActive(link);
        }
    }, { threshold: [0.25, 0.5, 0.75], rootMargin: '-30% 0px -50% 0px' });

    linkMap.forEach((_, section) => observer.observe(section));

    // initial state
    const firstLink = linkMap.values().next().value;
    setActive(firstLink);
})();