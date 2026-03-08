/**
 * Navbar Functionality - Funcionalidad del navbar
 * Proyecto NEXO - Escuela Secundaria Técnica N°1
 *
 * Este archivo maneja exclusivamente la funcionalidad del navbar:
 * - Menú móvil (hamburger menu)
 * - Menú de usuario (dropdown)
 * - Navegación responsive
 */

// Flags para evitar listeners duplicados
let userMenuDocumentListenersBound = false;
let mobileMenuDocumentListenersBound = false;
let notificationsHandlersBound = false;
let notificationsRefreshTimer = null;
let notificationsModalOpen = false;

// Función principal para inicializar el navbar
function initNavbar() {
    initMobileMenu();
    initUserMenu();
    initNotifications();
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

    // Evitar agregar listeners múltiples si se re-inicializa
    if (menuToggle.dataset.bound === 'true') {
        return;
    }

    // Event listener para el botón hamburger
    menuToggle.addEventListener('click', () => {
        navCenter.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', navCenter.classList.contains('open'));
    });

    // Cerrar menú al hacer click en un enlace
    navCenter.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navCenter.classList.remove('open');
            menuToggle.setAttribute('aria-expanded', 'false');
        });
    });

    // Cerrar menú al hacer click fuera (en móviles)
    if (!mobileMenuDocumentListenersBound) {
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !navCenter.contains(e.target)) {
                navCenter.classList.remove('open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
        mobileMenuDocumentListenersBound = true;
    }

    menuToggle.dataset.bound = 'true';
}

// Función para el menú de usuario (dropdown)
function initUserMenu() {
    const userToggle = document.getElementById('user-toggle');
    const userDropdown = document.querySelector('.user-dropdown');

    if (!userToggle || !userDropdown) {
        // No mostrar warning ya que no todas las páginas tienen menú de usuario
        return;
    }

    // Evitar agregar múltiples listeners al mismo botón cuando el navbar se re-renderiza
    if (!userToggle.dataset.bound) {
        userToggle.addEventListener('click', handleUserToggleClick);
        userToggle.dataset.bound = 'true';
    }

    if (!userMenuDocumentListenersBound) {
        document.addEventListener('click', handleUserMenuOutsideClick);
        document.addEventListener('keydown', handleUserMenuEscape);
        userMenuDocumentListenersBound = true;
    }
}

function handleUserToggleClick(e) {
    e.preventDefault();
    e.stopPropagation();
    const dropdown = document.querySelector('.user-dropdown');
    if (!dropdown) return;
    dropdown.classList.toggle('show');
}

function handleUserMenuOutsideClick(e) {
    const toggle = document.getElementById('user-toggle');
    const dropdown = document.querySelector('.user-dropdown');
    if (!toggle || !dropdown) return;
    if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
    }
}

function handleUserMenuEscape(e) {
    if (e.key !== 'Escape') return;
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
    }

    const notificationsModal = document.getElementById('notifications-modal');
    if (notificationsModal && !notificationsModal.classList.contains('hidden')) {
        closeNotificationsModal();
    }
}

function initNotifications() {
    const openBtn = document.getElementById('open-notifications');
    const modal = document.getElementById('notifications-modal');
    const closeOverlay = document.getElementById('notifications-modal-close');
    const closeButton = document.getElementById('notifications-modal-x');
    const searchInput = document.getElementById('notifications-search');
    const statusFilter = document.getElementById('notifications-filter-status');
    const dateFilter = document.getElementById('notifications-filter-date');
    const listContainer = document.getElementById('notifications-modal-list');

    if (!openBtn || !modal || !closeOverlay || !closeButton || !searchInput || !statusFilter || !dateFilter || !listContainer) {
        return;
    }

    if (!notificationsHandlersBound) {
        openBtn.addEventListener('click', function(event) {
            event.preventDefault();
            openNotificationsModal();
        });

        closeOverlay.addEventListener('click', closeNotificationsModal);
        closeButton.addEventListener('click', closeNotificationsModal);

        searchInput.addEventListener('input', debounce(function() {
            loadInvitationsForModal();
        }, 250));

        statusFilter.addEventListener('change', loadInvitationsForModal);
        dateFilter.addEventListener('change', loadInvitationsForModal);

        listContainer.addEventListener('click', function(event) {
            const button = event.target.closest('[data-inv-action]');
            if (!button) return;

            const action = button.getAttribute('data-inv-action');
            const invitationId = Number(button.getAttribute('data-inv-id'));
            if (!action || !invitationId) return;

            respondInvitation(invitationId, action);
        });

        notificationsHandlersBound = true;
    }

    loadInvitationsForModal();

    if (notificationsRefreshTimer) {
        clearInterval(notificationsRefreshTimer);
    }
    notificationsRefreshTimer = setInterval(function() {
        loadInvitationsForModal();
    }, 30000);
}

function openNotificationsModal() {
    const modal = document.getElementById('notifications-modal');
    if (!modal) return;
    notificationsModalOpen = true;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');

    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        userDropdown.classList.remove('show');
    }

    const searchInput = document.getElementById('notifications-search');
    if (searchInput) {
        searchInput.focus();
    }

    loadInvitationsForModal();
}

function closeNotificationsModal() {
    const modal = document.getElementById('notifications-modal');
    if (!modal) return;
    notificationsModalOpen = false;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');

    // Solo el invitador marca como visto al cerrar el modal.
    markInviterInvitationsSeen();
}

function loadInvitationsForModal() {
    const searchInput = document.getElementById('notifications-search');
    const statusFilter = document.getElementById('notifications-filter-status');
    const dateFilter = document.getElementById('notifications-filter-date');

    const query = searchInput ? searchInput.value.trim() : '';
    const estado = statusFilter ? statusFilter.value : 'all';
    const fecha = dateFilter ? dateFilter.value : 'recent';

    const params = new URLSearchParams();
    if (query) params.set('q', query);
    if (estado) params.set('estado', estado);
    if (fecha) params.set('fecha', fecha);

    const endpoint = '/backend/invitaciones/list.php' + (params.toString() ? '?' + params.toString() : '');

    apiFetch(endpoint, { method: 'GET' })
        .then(parseJsonSafe)
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'No se pudieron cargar las invitaciones');
            }

            renderInvitations(data.invitaciones || []);
            updateInvitationIndicators(Number(data.unseen_count) || 0);
        })
        .catch(error => {
            console.error('Error cargando invitaciones:', error);
        });
}

function renderInvitations(invitaciones) {
    const container = document.getElementById('notifications-modal-list');
    if (!container) return;

    if (!invitaciones.length) {
        container.innerHTML = '<div class="notifications-empty">No hay notificaciones de invitaciones para los filtros seleccionados</div>';
        return;
    }

    container.innerHTML = invitaciones.map(function(item) {
        const esInvitador = !!item.es_invitador;
        const usuarioRelacionado = escapeHtml(esInvitador ? (item.invitado || 'Usuario') : (item.invitador || 'Usuario'));
        const titulo = escapeHtml(item.titulo_historia || 'Sin título');
        const estado = String(item.estado || 'pendiente');
        const statusClass = 'status-' + estado;
        const unseenClass = item.visto_actual ? '' : 'is-unseen';

        const rolEtiqueta = esInvitador ? 'Enviada por ti' : 'Recibida';
        const estadoLabel = estado.charAt(0).toUpperCase() + estado.slice(1);

        let mensaje = '';
        if (esInvitador) {
            if (estado === 'pendiente') {
                mensaje = `Se ha enviado correctamente la invitación a ${usuarioRelacionado}. Mantente atento para saber el estado de "${titulo}".`;
            } else if (estado === 'aceptada') {
                mensaje = `${usuarioRelacionado} aceptó participar en la historia "${titulo}".`;
            } else {
                mensaje = `${usuarioRelacionado} rechazó participar en la historia "${titulo}".`;
            }
        } else {
            if (estado === 'pendiente') {
                mensaje = `${usuarioRelacionado} te está invitando a participar en la historia "${titulo}".`;
            } else if (estado === 'aceptada') {
                mensaje = `Aceptaste la invitación para la historia "${titulo}".`;
            } else {
                mensaje = `Rechazaste la invitación para la historia "${titulo}".`;
            }
        }

        const canRespond = !esInvitador && estado === 'pendiente';

        const avatarLetter = esInvitador ? 'E' : 'R';
        return `
            <article class="notification-item ${statusClass} ${unseenClass}">
                <div class="notification-avatar">${avatarLetter}</div>
                <div class="notification-content">
                    <div class="notification-meta">
                        <span class="notification-role">${rolEtiqueta}</span>
                        <span class="notification-status ${statusClass}">${estadoLabel}</span>
                    </div>
                    <h4>${titulo}</h4>
                    <p>${mensaje}</p>
                    ${canRespond ? `
                        <div class="notification-actions">
                            <button type="button" class="notification-btn accept" data-inv-action="aceptar" data-inv-id="${item.id_invitacion}">Aceptar</button>
                            <button type="button" class="notification-btn reject" data-inv-action="rechazar" data-inv-id="${item.id_invitacion}">Rechazar</button>
                        </div>
                    ` : ''}
                    <div class="notification-date">${formatDate(item.fecha_invitacion)}</div>
                </div>
            </article>
        `;
    }).join('');
}

function respondInvitation(invitationId, action) {
    const formData = new FormData();
    formData.append('id_invitacion', String(invitationId));
    formData.append('accion', action);

    apiFetch('/backend/invitaciones/respond.php', {
        method: 'POST',
        body: formData
    }).then(parseJsonSafe).then(data => {
        if (!data.success) {
            throw new Error(data.message || 'No se pudo responder la invitación');
        }

        loadInvitationsForModal();
    }).catch(error => {
        console.error('Error respondiendo invitación:', error);
        if (typeof showNotification === 'function') {
            showNotification('error', error.message || 'Error al responder invitación');
        }
    });
}

function updateInvitationIndicators(count) {
    const countBadge = document.getElementById('invitation-count');
    const dot = document.getElementById('invitation-dot');

    if (countBadge) {
        countBadge.textContent = String(count);
    }

    if (dot) {
        dot.classList.toggle('hidden', count <= 0);
    }
}

function markInviterInvitationsSeen() {
    apiFetch('/backend/invitaciones/mark_seen.php', {
        method: 'POST'
    }).then(parseJsonSafe).then(data => {
        if (!data.success) {
            return;
        }
        if (!notificationsModalOpen) {
            loadInvitationsForModal();
        }
    }).catch(error => {
        console.error('Error marcando invitaciones vistas:', error);
    });
}

function formatDate(value) {
    if (!value) return 'Sin fecha';
    const date = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleString('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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

function debounce(callback, delay) {
    let timeout = null;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), delay);
    };
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
    const menuToggle = document.getElementById('menu-toggle');
    if (navCenter && navCenter.classList.contains('open') && !isMobile()) {
        navCenter.classList.remove('open');
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
        }
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

function parseJsonSafe(response) {
    return response.text().then(function(text) {
        try {
            return JSON.parse(text);
        } catch (error) {
            const snippet = text ? text.slice(0, 180) : 'Respuesta vacia';
            throw new Error('El backend no devolvio JSON valido. Detalle: ' + snippet);
        }
    });
}