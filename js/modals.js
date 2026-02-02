/**
 * Modals para login/registro - se comunica con backend/session/auth.php
 */

function initAuthModals() {
    createAuthModals();
    setupAuthModalEvents();
}

function createAuthModals() {
    const html = `
        <div id="login-modal" class="modal-overlay" style="display:none;">
            <div class="modal">
                <button class="modal-close" data-close="login">&times;</button>
                <h2>Iniciar sesión</h2>
                <form id="login-form">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit">Iniciar sesión</button>
                </form>
                <p>
                    ¿No tenés cuenta?
                    <button type="button" data-open-register-inline>Registrate</button>
                </p>
            </div>
        </div>
        <div id="register-modal" class="modal-overlay" style="display:none;">
            <div class="modal">
                <button class="modal-close" data-close="register">&times;</button>
                <h2>Crear cuenta</h2>
                <form id="register-form">
                    <input type="text" name="name" placeholder="Nombre completo" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Contraseña (mínimo 6 caracteres)" required>
                    <button type="submit">Registrarse</button>
                </form>
                <p>
                    ¿Ya tenés cuenta?
                    <button type="button" data-open-login-inline>Iniciar sesión</button>
                </p>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);
}

function setupAuthModalEvents() {
    var loginButtons = document.querySelectorAll('[data-open-login]');
    loginButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openLoginModal();
        });
    });

    var registerButtons = document.querySelectorAll('[data-open-register]');
    registerButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openRegisterModal();
        });
    });

    var inlineToRegister = document.querySelector('[data-open-register-inline]');
    if (inlineToRegister) {
        inlineToRegister.addEventListener('click', function() {
            closeLoginModal();
            openRegisterModal();
        });
    }

    var inlineToLogin = document.querySelector('[data-open-login-inline]');
    if (inlineToLogin) {
        inlineToLogin.addEventListener('click', function() {
            closeRegisterModal();
            openLoginModal();
        });
    }

    var loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }

    var registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }

    var closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var type = btn.getAttribute('data-close');
            if (type === 'login') {
                closeLoginModal();
            } else if (type === 'register') {
                closeRegisterModal();
            }
        });
    });
}

function openLoginModal() {
    var modal = document.getElementById('login-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function openRegisterModal() {
    var modal = document.getElementById('register-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeLoginModal() {
    var modal = document.getElementById('login-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function closeRegisterModal() {
    var modal = document.getElementById('register-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function handleLoginSubmit(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);
    formData.append('action', 'login');

    fetch('/PROYECTO_NEXO/backend/session/auth.php', {
        method: 'POST',
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            showNotification('success', '¡Bienvenido! Sesión iniciada correctamente');
            // Esperar 2.5 segundos para que se vea la notificación antes de recargar
            setTimeout(function() {
                window.location.reload();
            }, 2500);
        } else {
            showNotification('error', data.message || 'Error al iniciar sesión');
        }
    }).catch(function() {
        showNotification('error', 'Error de conexión');
    });
}

function handleRegisterSubmit(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);
    formData.append('action', 'register');

    fetch('/PROYECTO_NEXO/backend/session/auth.php', {
        method: 'POST',
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            showNotification('success', data.message || 'Registro exitoso');
            closeRegisterModal();
            openLoginModal();
        } else {
            showNotification('error', data.message || 'Error al registrarse');
        }
    }).catch(function() {
        showNotification('error', 'Error de conexión');
    });
}

// Inicializar al cargar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthModals);
} else {
    initAuthModals();
}
