function getBaseUrl() {
    const pathSegments = window.location.pathname.split('/').filter(segment => segment);
    const projectIndex = pathSegments.indexOf('NEXO');

    if (projectIndex !== -1) {
        const basePath = '/' + pathSegments.slice(0, projectIndex + 1).join('/');
        return window.location.origin + basePath;
    }

    return window.location.origin;
}

function apiFetch(endpoint, options = {}) {
    const baseUrl = getBaseUrl();
    const fullUrl = baseUrl + endpoint;

    console.log('API Fetch:', fullUrl);

    return fetch(fullUrl, options);
}

function getAssetUrl(path) {
    return getBaseUrl() + path;
}
