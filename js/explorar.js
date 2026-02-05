/**
 * Explorar Historias - NEXO Platform
 * JavaScript específico para la página de explorar
 */

// Control de pantalla de carga
document.addEventListener('DOMContentLoaded', function() {
    const loadingScreen = document.getElementById('loading-screen');

    // Verificar si viene desde la landing page
    const urlParams = new URLSearchParams(window.location.search);
    const fromLanding = urlParams.get('from') === 'landing';

    if (fromLanding) {
        // Mostrar pantalla de carga por 2 segundos
        setTimeout(function() {
            loadingScreen.classList.add('hidden');

            // Limpiar el parámetro de la URL después de la carga
            const url = new URL(window.location);
            url.searchParams.delete('from');
            window.history.replaceState({}, '', url);
            
            // Inicializar funcionalidades después de la carga
            initializeExplorar();
        }, 2000);
    } else {
        // Si no viene desde landing, ocultar inmediatamente
        loadingScreen.style.display = 'none';
        // Inicializar funcionalidades
        initializeExplorar();
    }
});

// Inicializar funcionalidades de la página
function initializeExplorar() {
    animateCounters();
    setupScrollIndicator();
    setupSearchAndFilters();
}

// Configurar indicador de scroll
function setupScrollIndicator() {
    const scrollIndicator = document.getElementById('scroll-indicator');
    const contentSections = document.getElementById('content-sections');
    
    if (scrollIndicator && contentSections) {
        scrollIndicator.addEventListener('click', function() {
            showContentSections();
        });
        
        // También mostrar al hacer scroll
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                if (window.scrollY > 100) {
                    showContentSections();
                }
            }, 100);
        });
    }
}

// Función para mostrar las secciones de contenido
function showContentSections() {
    const contentSections = document.getElementById('content-sections');
    const scrollIndicator = document.getElementById('scroll-indicator');
    
    if (contentSections && !contentSections.classList.contains('visible')) {
        contentSections.classList.remove('hidden');
        contentSections.classList.add('visible');
        
        // Ocultar la flecha después de mostrar el contenido
        if (scrollIndicator) {
            scrollIndicator.style.opacity = '0';
            setTimeout(() => {
                scrollIndicator.style.display = 'none';
            }, 500);
        }
        
        // Hacer scroll suave hacia las secciones
        setTimeout(() => {
            contentSections.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }, 300);
    }
}

// Animar contadores de estadísticas
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // 2 segundos
                const increment = target / (duration / 16); // 60fps
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
                observer.unobserve(counter);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => observer.observe(counter));
}

// Configurar búsqueda y filtros
function setupSearchAndFilters() {
    const searchInput = document.getElementById('search-input');
    const filterGenre = document.getElementById('filter-genre');
    const filterSort = document.getElementById('filter-sort');
    const filterStatus = document.getElementById('filter-status');
    const resetButton = document.getElementById('reset-filters');
    
    // Búsqueda en tiempo real
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterStories, 300));
    }
    
    // Filtros
    if (filterGenre) {
        filterGenre.addEventListener('change', filterStories);
    }
    if (filterSort) {
        filterSort.addEventListener('change', filterStories);
    }
    if (filterStatus) {
        filterStatus.addEventListener('change', filterStories);
    }
    
    // Botón de reset
    if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
    }
}

// Función debounce para evitar llamadas excesivas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Filtrar historias
function filterStories() {
    const searchTerm = document.getElementById('search-input')?.value.toLowerCase() || '';
    const genreFilter = document.getElementById('filter-genre')?.value || '';
    const statusFilter = document.getElementById('filter-status')?.value || '';
    
    const storyCards = document.querySelectorAll('.story-card');
    let visibleCount = 0;
    
    storyCards.forEach(card => {
        const title = card.querySelector('.story-title')?.textContent.toLowerCase() || '';
        const author = card.querySelector('.story-author')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.story-description')?.textContent.toLowerCase() || '';
        const genre = card.querySelector('.story-genre')?.textContent.toLowerCase() || '';
        
        // Verificar búsqueda
        const matchesSearch = !searchTerm || 
            title.includes(searchTerm) || 
            author.includes(searchTerm) || 
            description.includes(searchTerm) ||
            genre.includes(searchTerm);
        
        // Verificar género
        const matchesGenre = !genreFilter || genre.includes(genreFilter.toLowerCase());
        
        // Mostrar/ocultar
        if (matchesSearch && matchesGenre) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Mostrar mensaje de sin resultados
    const noResults = document.getElementById('no-results');
    const storiesGrid = document.getElementById('stories-grid');
    
    if (visibleCount === 0) {
        if (noResults) noResults.style.display = 'block';
        if (storiesGrid) storiesGrid.style.display = 'none';
    } else {
        if (noResults) noResults.style.display = 'none';
        if (storiesGrid) storiesGrid.style.display = 'grid';
    }
}

// Reset filtros
function resetFilters() {
    // Limpiar inputs
    const searchInput = document.getElementById('search-input');
    if (searchInput) searchInput.value = '';
    
    const filterGenre = document.getElementById('filter-genre');
    if (filterGenre) filterGenre.value = '';
    
    const filterSort = document.getElementById('filter-sort');
    if (filterSort) filterSort.value = 'recent';
    
    const filterStatus = document.getElementById('filter-status');
    if (filterStatus) filterStatus.value = '';
    
    // Mostrar todas las historias
    const storyCards = document.querySelectorAll('.story-card');
    storyCards.forEach(card => {
        card.style.display = 'flex';
    });
    
    // Ocultar mensaje de sin resultados
    const noResults = document.getElementById('no-results');
    if (noResults) noResults.style.display = 'none';
    
    const storiesGrid = document.getElementById('stories-grid');
    if (storiesGrid) storiesGrid.style.display = 'grid';
}