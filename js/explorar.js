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
        }, 2000);
    } else {
        // Si no viene desde landing, ocultar inmediatamente
        loadingScreen.style.display = 'none';
    }
});