/**
 * Sistema de notificaciones mejorado con animaciones y barra de progreso
 */

let notificationId = 0;

function showNotification(type, message, duration = 4000) {
    const id = ++notificationId;
    
    // Crear o obtener contenedor
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            pointer-events: none;
            max-width: 380px;
        `;
        document.body.appendChild(container);
    }

    // Crear notificación
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.dataset.id = id;
    toast.style.cssText = getToastStyles(type);

    const content = `
        <div class="toast-content">
            <div class="toast-icon">${getToastIcon(type)}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="closeNotification(${id})">&times;</button>
        </div>
        <div class="toast-progress">
            <div class="toast-progress-bar" style="animation: toast-countdown ${duration}ms linear;"></div>
        </div>
    `;
    
    toast.innerHTML = content;
    toast.style.pointerEvents = 'auto';
    
    // Agregar evento de click para cerrar
    toast.addEventListener('click', function(e) {
        if (!e.target.classList.contains('toast-close')) {
            closeNotification(id);
        }
    });

    // Insertar al principio del contenedor
    container.insertBefore(toast, container.firstChild);

    // Animación de entrada
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    // Auto-cerrar después del tiempo especificado
    setTimeout(() => {
        closeNotification(id);
    }, duration);

    return id;
}

function closeNotification(id) {
    const toast = document.querySelector(`[data-id="${id}"]`);
    if (!toast) return;

    // Animación de salida
    toast.style.transform = 'translateX(100%)';
    toast.style.opacity = '0';

    // Remover del DOM después de la animación
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
        
        // Limpiar contenedor vacío
        const container = document.querySelector('.toast-container');
        if (container && container.children.length === 0) {
            container.parentNode.removeChild(container);
        }
    }, 300);
}

function getToastStyles(type) {
    const baseStyles = `
        margin-bottom: 12px;
        min-height: 60px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
    `;

    const typeStyles = {
        success: 'background: var(--success-color, #10b981); color: white;',
        error: 'background: var(--error-color, #ef4444); color: white;',
        warning: 'background: var(--warning-color, #f59e0b); color: white;',
        info: 'background: var(--primary-color, #2563eb); color: white;'
    };

    return baseStyles + (typeStyles[type] || typeStyles.info);
}

function getToastIcon(type) {
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    return icons[type] || icons.info;
}

// Añadir estilos CSS para las notificaciones
if (!document.querySelector('#toast-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = `
        .toast-content {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            gap: 12px;
            position: relative;
        }

        .toast-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .toast-message {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        .toast-close {
            background: none;
            border: none;
            color: currentColor;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            opacity: 0.8;
            transition: opacity 0.2s ease, background 0.2s ease;
            flex-shrink: 0;
        }

        .toast-close:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.1);
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .toast-progress-bar {
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            width: 100%;
            transform-origin: left;
        }

        @keyframes toast-countdown {
            from {
                transform: scaleX(1);
            }
            to {
                transform: scaleX(0);
            }
        }

        /* Responsivo */
        @media (max-width: 480px) {
            .toast-container {
                left: 16px;
                right: 16px;
                max-width: none;
            }

            .toast-message {
                font-size: 13px;
            }

            .toast-content {
                padding: 14px 16px;
            }
        }
    `;
    document.head.appendChild(style);
}
