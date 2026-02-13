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
                    <input type="text" name="identifier" placeholder="Email o Username" required>
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
                    <input type="text" name="firstName" placeholder="Nombre" required>
                    <input type="text" name="lastName" placeholder="Apellido" required>
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Contraseña (mínimo 6 caracteres)" required>
                    <input type="password" name="confirmPassword" placeholder="Confirmar contraseña" required>
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

    apiFetch('/backend/session/auth.php', {
        method: 'POST',
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            closeLoginModal();
            // Mostrar notificación rápida y recargar para que el servidor renderice el navbar correcto
            showNotification('success', '¡Bienvenido! Sesión iniciada correctamente');
            setTimeout(function() {
                // Recargar la página actual para reflejar el estado de sesión en el navbar
                window.location.reload();
            }, 800);
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
    var password = form.querySelector('input[name="password"]').value;
    var confirmPassword = form.querySelector('input[name="confirmPassword"]').value;

    // Validar que las contraseñas coincidan
    if (password !== confirmPassword) {
        showNotification('error', 'Las contraseñas no coinciden');
        return;
    }

    var formData = new FormData(form);
    formData.append('action', 'register');

    apiFetch('/backend/session/auth.php', {
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

// Función para manejar logout con AJAX
function handleAjaxLogout(event) {
    event.preventDefault();

    apiFetch('/backend/session/logout.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            // Redirigir a explorar con bandera de logout para que el servidor renderice el navbar de invitado
            window.location.href = getBaseUrl() + '/frontend/explorar.php?logout=success';
        } else {
            showNotification('error', data.message || 'Error al cerrar sesión');
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
