/**
 * API Helper - Ayuda con rutas de fetch para diferentes entornos
 * Proyecto NEXO - Escuela Secundaria Técnica N°1
 *
 * Este archivo proporciona funciones para manejar rutas de API de manera
 * dinámica, adaptándose a diferentes profundidades de carpetas en el servidor.
 */

// Función para obtener la URL base del proyecto
function getBaseUrl() {
    // Detecta automáticamente la URL base basada en la ubicación actual
    const pathSegments = window.location.pathname.split('/').filter(segment => segment);
    const projectIndex = pathSegments.indexOf('NEXO');

    if (projectIndex !== -1) {
        // Si encuentra NEXO en la ruta, construye la URL base
        const basePath = '/' + pathSegments.slice(0, projectIndex + 1).join('/');
        return window.location.origin + basePath;
    } else {
        // Fallback: asume que está en la raíz o ajusta según necesidad
        return window.location.origin + '/NEXO';
    }
}

// Función helper para hacer fetch con la URL base correcta
function apiFetch(endpoint, options = {}) {
    const baseUrl = getBaseUrl();
    const fullUrl = baseUrl + endpoint;

    // Log para debugging (puedes remover en producción)
    console.log('API Fetch:', fullUrl);

    return fetch(fullUrl, options);
}

// Función para obtener URLs de assets (imágenes, CSS, etc.)
function getAssetUrl(path) {
    const baseUrl = getBaseUrl();
    return baseUrl + path;
}

// Exportar funciones si se usa con módulos (opcional)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        getBaseUrl,
        apiFetch,
        getAssetUrl
    };
}