<?php
/**
 * Navbar State API - Devuelve el estado dinámico del navbar
 * Proyecto NEXO - Escuela Secundaria Técnica N°1
 */

require_once __DIR__ . '/session_manager.php';

header('Content-Type: application/json');

// Determinar la página actual para los enlaces activos
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'explorar';

// Función para determinar si un enlace está activo
function isActive($page, $current) {
    return $page === $current ? 'active' : '';
}

// Generar el HTML del navbar
function generateNavbarHTML($currentPage) {
    $html = '<div class="nav-container">
        <div class="nav-left">
            <a href="explorar.php" class="brand" aria-label="Ir a explorar">
                <img src="../components/Logo-2.png" alt="NEXO" class="logo" />
            </a>
        </div>

        <button id="menu-toggle" class="menu-toggle" aria-label="Abrir menú de navegación">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav-center">
            <a href="explorar.php" class="nav-link ' . isActive('explorar', $currentPage) . '">Explorar</a>
            <a href="perfil.php" class="nav-link ' . isActive('perfil', $currentPage) . '">Mi Perfil</a>
            <a href="subir-historia.php" class="nav-link ' . isActive('subir-historia', $currentPage) . '">
                <i class="fas fa-plus"></i>
                Subir Historia
            </a>';

    // Botones móviles según estado de sesión
    if (!isLoggedIn()) {
        $html .= '
            <button class="login-btn login-btn-mobile" data-open-login>Iniciar sesión</button>';
    } else {
        $html .= '
            <a href="#" class="logout-btn logout-btn-mobile" onclick="handleAjaxLogout(event)">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </a>';
    }

    $html .= '
        </nav>

        <div class="nav-right">';

    if (isLoggedIn()) {
        $html .= '
            <div class="user-menu user-menu-desktop">
                <button id="user-toggle" class="user-toggle" aria-label="Menú de usuario">
                    <i class="fas fa-user"></i>
                    <span>' . htmlspecialchars(getCurrentUserName(), ENT_QUOTES, 'UTF-8') . '</span>
                </button>
                <div class="user-dropdown">
                    <a href="estadisticas.php"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                    <a href="#" onclick="handleAjaxLogout(event)"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>';
    } else {
        $html .= '
            <button class="login-btn login-btn-desktop" data-open-login>Iniciar sesión</button>';
    }

    $html .= '
            <button id="theme-toggle" class="theme-toggle" aria-label="Cambiar tema claro/oscuro">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>
    </div>';

    return $html;
}

// Devolver respuesta JSON
echo json_encode([
    'success' => true,
    'loggedIn' => isLoggedIn(),
    'userName' => isLoggedIn() ? getCurrentUserName() : null,
    'navbarHTML' => generateNavbarHTML($currentPage)
]);
?>