/**
 * Explorar Historias - NEXO Platform
 * JavaScript específico para la página de explorar
 */

const PAGE_SIZE = 6;

function isUserLoggedIn() {
    return window.NEXO_IS_LOGGED === true;
}

const state = {
    q: '',
    genre: '',
    sort: 'recent',
    interaction: 'all',
    page: 1,
    totalPages: 0,
    stories: [],
    activeCommentsStory: null
};

document.addEventListener('DOMContentLoaded', function() {
    const loadingScreen = document.getElementById('loading-screen');
    const urlParams = new URLSearchParams(window.location.search);
    const fromLanding = urlParams.get('from') === 'landing';

    if (fromLanding) {
        setTimeout(function() {
            loadingScreen.classList.add('hidden');
            const url = new URL(window.location);
            url.searchParams.delete('from');
            window.history.replaceState({}, '', url);
            initializeExplorar();
        }, 2000);
    } else {
        loadingScreen.style.display = 'none';
        initializeExplorar();
    }
});

function initializeExplorar() {
    setupScrollIndicator();
    setupSearchAndFilters();
    setupStoryActions();
    setupCommentsModal();
    fetchHeroStats();
    loadStories();
}

// Configurar indicador de scroll (ahora solo para navegación)
function setupScrollIndicator() {
    const scrollIndicator = document.getElementById('scroll-indicator');
    const contentSections = document.getElementById('content-sections');

    if (scrollIndicator && contentSections) {
        scrollIndicator.addEventListener('click', function() {
            // Solo hacer scroll suave hacia las secciones
            contentSections.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    }
}

function fetchHeroStats() {
    apiFetch('/backend/explorar/hero.php', {
        method: 'GET'
    }).then(response => response.json()).then(data => {
        if (!data.success || !data.stats) {
            return;
        }

        animateCounter(document.getElementById('hero-total-historias'), data.stats.total_historias);
        animateCounter(document.getElementById('hero-total-usuarios'), data.stats.total_usuarios);
        animateCounter(document.getElementById('hero-total-escritores'), data.stats.total_escritores);
    }).catch(function(error) {
        console.error('Error cargando hero:', error);
    });
}

function animateCounter(element, target) {
    if (!element) {
        return;
    }

    const endValue = Number(target) || 0;
    element.setAttribute('data-target', String(endValue));

    const duration = 1000;
    const increment = Math.max(1, endValue / (duration / 16));
    let current = 0;

    const update = function() {
        current += increment;
        if (current < endValue) {
            element.textContent = Math.floor(current);
            requestAnimationFrame(update);
        } else {
            element.textContent = endValue;
        }
    };

    update();
}

function setupSearchAndFilters() {
    const searchInput = document.getElementById('search-input');
    const filterGenre = document.getElementById('filter-genre');
    const filterSort = document.getElementById('filter-sort');
    const filterInteraction = document.getElementById('filter-interaction');
    const resetButton = document.getElementById('reset-filters');

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            state.q = searchInput.value.trim();
            state.page = 1;
            loadStories();
        }, 300));
    }

    if (filterGenre) {
        filterGenre.addEventListener('change', function() {
            state.genre = filterGenre.value;
            state.page = 1;
            loadStories();
        });
    }

    if (filterSort) {
        filterSort.addEventListener('change', function() {
            state.sort = filterSort.value;
            state.page = 1;
            loadStories();
        });
    }

    if (filterInteraction) {
        filterInteraction.addEventListener('change', function() {
            state.interaction = filterInteraction.value;
            state.page = 1;
            loadStories();
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
    }
}

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

function loadStories() {
    const loadingState = document.getElementById('loading-state');
    const storiesGrid = document.getElementById('stories-grid');

    if (loadingState) loadingState.style.display = 'block';
    if (storiesGrid) storiesGrid.style.display = 'none';

    const params = new URLSearchParams();
    params.set('page', String(state.page));
    params.set('sort', state.sort);
    params.set('interaction', state.interaction);
    if (state.q) params.set('q', state.q);
    if (state.genre) params.set('genre', state.genre);

    apiFetch('/backend/explorar/historias.php?' + params.toString(), {
        method: 'GET'
    }).then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    }).then(data => {
        if (!data.success) {
            throw new Error(data.message || 'No se pudieron cargar las historias');
        }

        state.stories = Array.isArray(data.stories) ? data.stories : [];
        state.totalPages = data.pagination ? Number(data.pagination.total_pages) || 0 : 0;

        renderStories();
        renderPagination();
    }).catch(error => {
        console.error('Error cargando historias:', error);
        if (typeof showNotification === 'function') {
            showNotification('error', 'Error al cargar historias');
        }
        state.stories = [];
        state.totalPages = 0;
        renderStories();
        renderPagination();
    }).finally(() => {
        if (loadingState) loadingState.style.display = 'none';
        if (storiesGrid) storiesGrid.style.display = 'grid';
    });
}

function resetFilters() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) searchInput.value = '';

    const filterGenre = document.getElementById('filter-genre');
    if (filterGenre) filterGenre.value = '';

    const filterSort = document.getElementById('filter-sort');
    if (filterSort) filterSort.value = 'recent';

    const filterInteraction = document.getElementById('filter-interaction');
    if (filterInteraction) filterInteraction.value = 'all';

    state.q = '';
    state.genre = '';
    state.sort = 'recent';
    state.interaction = 'all';
    state.page = 1;
    loadStories();
}

function renderStories() {
    const storiesGrid = document.getElementById('stories-grid');
    const noResults = document.getElementById('no-results');

    if (!storiesGrid) return;

    if (!state.stories.length) {
        storiesGrid.innerHTML = '';
        if (noResults) noResults.style.display = 'block';
        return;
    }

    if (noResults) noResults.style.display = 'none';

    storiesGrid.innerHTML = state.stories.map(story => {
        const portada = story.portada ? getBaseUrl() + story.portada : 'https://via.placeholder.com/400x240?text=Sin+Portada';
        const liked = story.liked_by_user ? 'true' : 'false';
        const likeIcon = story.liked_by_user ? 'fas' : 'far';

        return `
            <article class="story-card" data-story-id="${story.id_historia}">
                <div class="story-cover">
                    <img src="${escapeHtml(portada)}" alt="${escapeHtml(story.titulo || 'Historia')}">
                    <div class="story-overlay"></div>
                    ${story.has_formulario ? '<div class="story-badge"><i class="fas fa-file-alt"></i><span>Formulario</span></div>' : ''}
                </div>
                <div class="story-content">
                    <h3 class="story-title">${escapeHtml(story.titulo || 'Sin título')}</h3>
                    <p class="story-author">por ${escapeHtml(story.autor || 'Autor desconocido')}</p>
                    <p class="story-description">${escapeHtml(story.descripcion || 'Sin descripción')}</p>
                    <div class="story-meta">
                        <span class="story-genre"><i class="fas fa-tag"></i> ${escapeHtml(story.genero || 'Sin género')}</span>
                        <div class="story-stats">
                            <span><i class="fas fa-eye"></i> <span class="stat-views">${story.total_vistas || 0}</span></span>
                            <span class="stat-likes" data-likes="${story.total_likes || 0}"><i class="fas fa-heart"></i> ${story.total_likes || 0}</span>
                        </div>
                    </div>
                    <div class="story-actions">
                        <button class="action-btn like-btn" data-action="like" data-story-id="${story.id_historia}" data-liked="${liked}">
                            <i class="${likeIcon} fa-heart"></i>
                            <span>Like</span>
                        </button>
                        <button class="action-btn comment-btn" data-action="comment" data-story-id="${story.id_historia}">
                            <i class="far fa-comment"></i>
                            <span>Comentar</span>
                        </button>
                        <button class="action-btn read-btn" data-action="read" data-story-id="${story.id_historia}">
                            <i class="fas fa-book-open"></i>
                            <span>Leer</span>
                        </button>
                        ${isUserLoggedIn() && story.has_formulario && story.viewed_by_user ? `
                            <button class="action-btn form-btn" data-action="form" data-story-id="${story.id_historia}">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Contestar formulario</span>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </article>
        `;
    }).join('');
}

function renderPagination() {
    const paginationEl = document.getElementById('pagination');
    if (!paginationEl) return;

    if (state.totalPages <= 1) {
        paginationEl.innerHTML = '';
        return;
    }

    let pageButtons = '';
    for (let page = 1; page <= state.totalPages; page++) {
        pageButtons += `
            <button class="page-btn ${page === state.page ? 'active' : ''}" data-page="${page}">${page}</button>
        `;
    }

    paginationEl.innerHTML = `
        <button class="page-btn" data-page="${state.page - 1}" ${state.page <= 1 ? 'disabled' : ''}>Anterior</button>
        ${pageButtons}
        <button class="page-btn" data-page="${state.page + 1}" ${state.page >= state.totalPages ? 'disabled' : ''}>Siguiente</button>
    `;

    paginationEl.querySelectorAll('[data-page]').forEach(btn => {
        btn.addEventListener('click', function() {
            const nextPage = Number(this.getAttribute('data-page'));
            if (!nextPage || nextPage < 1 || nextPage > state.totalPages || nextPage === state.page) {
                return;
            }
            state.page = nextPage;
            loadStories();
        });
    });
}

function setupStoryActions() {
    const storiesGrid = document.getElementById('stories-grid');
    if (!storiesGrid) return;

    storiesGrid.addEventListener('click', function(event) {
        const button = event.target.closest('button[data-action]');
        if (!button) return;

        const action = button.getAttribute('data-action');
        const storyId = Number(button.getAttribute('data-story-id'));
        const story = state.stories.find(item => Number(item.id_historia) === storyId);

        if (!story) return;

        if (action === 'like') {
            handleLike(story, button);
            return;
        }

        if (action === 'comment') {
            handleComment(story);
            return;
        }

        if (action === 'read') {
            handleRead(story);
            return;
        }

        if (action === 'form') {
            handleForm(story);
        }
    });
}

function handleLike(story, button) {
    if (!isUserLoggedIn()) {
        showNotification('info', 'Debes iniciar sesión para dar like');
        return;
    }

    const formData = new FormData();
    formData.append('id_historia', story.id_historia);

    apiFetch('/backend/explorar/like.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json()).then(data => {
        if (!data.success) {
            throw new Error(data.message || 'No se pudo actualizar el like');
        }

        story.liked_by_user = !!data.liked;
        story.total_likes = Number(data.total_likes) || 0;
        renderStories();
    }).catch(error => {
        console.error('Error like:', error);
        showNotification('error', error.message || 'Error al actualizar like');
    });
}

function handleComment(story) {
    openCommentsModal(story);
}

function handleRead(story) {
    const openStory = function() {
        if (!story.archivo_twine) {
            showNotification('error', 'Esta historia no tiene archivo para abrir');
            return;
        }

        window.open(getBaseUrl() + story.archivo_twine, '_blank');
    };

    if (!isUserLoggedIn()) {
        openStory();
        return;
    }

    const formData = new FormData();
    formData.append('id_historia', story.id_historia);

    apiFetch('/backend/explorar/view.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json()).then(data => {
        if (data.success) {
            story.viewed_by_user = true;
            story.total_vistas = Number(data.total_vistas) || story.total_vistas;
            renderStories();
        }
    }).catch(error => {
        console.error('Error view:', error);
    }).finally(openStory);
}

function handleForm(story) {
    if (!isUserLoggedIn() || !story.has_formulario || !story.viewed_by_user) {
        return;
    }

    showNotification('info', 'Sección de formulario próximamente disponible');
}

function setupCommentsModal() {
    const modal = document.getElementById('comments-modal');
    const closeOverlay = document.getElementById('comments-modal-close');
    const closeButton = document.getElementById('comments-modal-x');
    const form = document.getElementById('comments-modal-form');

    if (!modal || !closeOverlay || !closeButton || !form) {
        return;
    }

    closeOverlay.addEventListener('click', closeCommentsModal);
    closeButton.addEventListener('click', closeCommentsModal);

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeCommentsModal();
        }
    });

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        submitComment();
    });
}

function openCommentsModal(story) {
    const modal = document.getElementById('comments-modal');
    const title = document.getElementById('comments-modal-title');
    const loading = document.getElementById('comments-modal-loading');
    const list = document.getElementById('comments-modal-list');
    const input = document.getElementById('comments-modal-input');
    const submit = document.getElementById('comments-modal-submit');
    const hint = document.getElementById('comments-modal-hint');

    if (!modal || !title || !loading || !list || !input || !submit || !hint) {
        return;
    }

    state.activeCommentsStory = story;
    title.textContent = `Comentarios · ${story.titulo || 'Historia'}`;
    loading.style.display = 'block';
    list.innerHTML = '';
    input.value = '';

    const logged = isUserLoggedIn();
    input.disabled = !logged;
    submit.disabled = !logged;
    hint.textContent = logged ? 'Máximo 500 caracteres' : 'Inicia sesión para publicar comentarios';

    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');

    loadStoryComments(story.id_historia);
}

function closeCommentsModal() {
    const modal = document.getElementById('comments-modal');
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    state.activeCommentsStory = null;
}

function loadStoryComments(storyId) {
    const loading = document.getElementById('comments-modal-loading');
    const list = document.getElementById('comments-modal-list');

    if (!loading || !list) {
        return;
    }

    loading.style.display = 'block';

    apiFetch('/backend/explorar/comments.php?id_historia=' + encodeURIComponent(String(storyId)), {
        method: 'GET'
    }).then(response => response.json()).then(data => {
        if (!data.success) {
            throw new Error(data.message || 'No se pudieron cargar los comentarios');
        }

        const comments = Array.isArray(data.comments) ? data.comments : [];
        renderCommentsList(comments);
    }).catch(error => {
        console.error('Error loading comments:', error);
        list.innerHTML = '<div class="comments-empty">No se pudieron cargar los comentarios.</div>';
    }).finally(() => {
        loading.style.display = 'none';
    });
}

function renderCommentsList(comments) {
    const list = document.getElementById('comments-modal-list');
    if (!list) {
        return;
    }

    if (!comments.length) {
        list.innerHTML = '<div class="comments-empty">Todavía no hay comentarios. ¡Sé el primero en comentar!</div>';
        return;
    }

    list.innerHTML = comments.map(renderCommentItem).join('');
}

function renderCommentItem(comment) {
    const role = String(comment.tipo_usuario || 'estudiante').toLowerCase();
    const roleLabel = role === 'profesor' ? 'Profesor' : (role === 'admin' ? 'Admin' : 'Estudiante');
    const roleClass = role === 'profesor' ? 'role-profesor' : (role === 'admin' ? 'role-admin' : 'role-estudiante');

    return `
        <article class="comment-item">
            <div class="comment-head">
                <div class="comment-author-wrap">
                    <strong class="comment-author">${escapeHtml(comment.autor || 'Usuario')}</strong>
                    <span class="comment-role ${roleClass}">${roleLabel}</span>
                </div>
                <span class="comment-date">${formatCommentDate(comment.fecha_comentario)}</span>
            </div>
            <p class="comment-body">${escapeHtml(comment.contenido || '')}</p>
        </article>
    `;
}

function submitComment() {
    if (!isUserLoggedIn()) {
        showNotification('info', 'Debes iniciar sesión para comentar');
        return;
    }

    const story = state.activeCommentsStory;
    const input = document.getElementById('comments-modal-input');
    const submit = document.getElementById('comments-modal-submit');

    if (!story || !input || !submit) {
        return;
    }

    const contenido = input.value.trim();
    if (!contenido) {
        showNotification('error', 'El comentario no puede estar vacío');
        return;
    }

    submit.disabled = true;

    const formData = new FormData();
    formData.append('id_historia', story.id_historia);
    formData.append('contenido', contenido);

    apiFetch('/backend/explorar/comment.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json()).then(data => {
        if (!data.success) {
            throw new Error(data.message || 'No se pudo publicar el comentario');
        }

        story.commented_by_user = true;
        story.total_comentarios = Number(data.total_comentarios) || story.total_comentarios;

        input.value = '';
        showNotification('success', 'Comentario publicado');

        if (data.comment) {
            prependCommentToList(data.comment);
        } else {
            loadStoryComments(story.id_historia);
        }

        renderStories();
    }).catch(error => {
        console.error('Error comment:', error);
        showNotification('error', error.message || 'Error al comentar');
    }).finally(() => {
        submit.disabled = false;
    });
}

function prependCommentToList(comment) {
    const list = document.getElementById('comments-modal-list');
    if (!list) {
        return;
    }

    const emptyEl = list.querySelector('.comments-empty');
    if (emptyEl) {
        emptyEl.remove();
    }

    list.insertAdjacentHTML('afterbegin', renderCommentItem(comment));
}

function formatCommentDate(dateValue) {
    if (!dateValue) {
        return 'Hace un momento';
    }

    const date = new Date(dateValue.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) {
        return 'Hace un momento';
    }

    return new Intl.DateTimeFormat('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}