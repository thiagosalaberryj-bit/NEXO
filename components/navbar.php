<?php
// Navbar include - imprime el navbar usando funciones de session_manager
require_once __DIR__ . '/../backend/session/session_manager.php';

function render_navbar($currentPage = null) {
    if (!$currentPage) {
        $currentPage = basename($_SERVER['PHP_SELF']);
    }

    $isLogged = function_exists('isLoggedIn') && isLoggedIn();
    $userName = $isLogged && function_exists('getCurrentUserName') ? getCurrentUserName() : null;

    // Enlaces relativos desde las páginas en /frontend
    $brandHref = '../index.html#top';
    $explorarHref = 'explorar.php';
    $perfilHref = 'perfil.php';
    $subirHref = 'subir-historia.php';

    $nav = "<div class=\"nav-container\">\n";
    $nav .= "    <div class=\"nav-left\">\n";
    $nav .= "        <a href=\"{$brandHref}\" class=\"brand\" aria-label=\"Ir al inicio de la página\">\n";
    $nav .= "            <img src=\"../components/Logo-2.png\" alt=\"NEXO\" class=\"logo\" />\n";
    $nav .= "        </a>\n";
    $nav .= "    </div>\n";

    $nav .= "    <button id=\"menu-toggle\" class=\"menu-toggle\" aria-label=\"Abrir menú de navegación\">\n";
    $nav .= "        <span></span>\n        <span></span>\n        <span></span>\n    </button>\n";

    $nav .= "    <nav class=\"nav-center\">\n";
    $nav .= "        <a href=\"{$explorarHref}\" class=\"nav-link\">Explorar</a>\n";
    $nav .= "        <a href=\"{$perfilHref}\" class=\"nav-link\">Mi Perfil</a>\n";
    $nav .= "        <a href=\"{$subirHref}\" class=\"nav-link\"><i class=\"fas fa-plus\"></i> Subir Historia</a>\n";

    if (!$isLogged) {
        $nav .= "        <button class=\"login-btn login-btn-mobile\" data-open-login>Iniciar sesión</button>\n";
    } else {
        $nav .= "        <a href=\"#\" class=\"logout-btn logout-btn-mobile\" onclick=\"handleAjaxLogout(event)\">Cerrar Sesión</a>\n";
    }

    $nav .= "    </nav>\n";

    $nav .= "    <div class=\"nav-right\">\n";
    if ($isLogged) {
        $safeName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $nav .= "        <div class=\"user-menu user-menu-desktop\">\n";
        $nav .= "            <button id=\"user-toggle\" class=\"user-toggle\" aria-label=\"Menú de usuario\">\n";
        $nav .= "                <i class=\"fas fa-user\"></i> <span>{$safeName}</span>\n";
        $nav .= "            </button>\n";
        $nav .= "            <div class=\"user-dropdown\">\n";
        $nav .= "                <a href=\"estadisticas.php\"><i class=\"fas fa-chart-bar\"></i> Estadísticas</a>\n";
        $nav .= "                <a href=\"#\" onclick=\"handleAjaxLogout(event)\"><i class=\"fas fa-sign-out-alt\"></i> Cerrar Sesión</a>\n";
        $nav .= "            </div>\n";
        $nav .= "        </div>\n";
    } else {
        $nav .= "        <button class=\"login-btn login-btn-desktop\" data-open-login>Iniciar sesión</button>\n";
    }

    $nav .= "        <button id=\"theme-toggle\" class=\"theme-toggle\" aria-label=\"Cambiar tema claro/oscuro\">\n";
    $nav .= "            <i class=\"fa-solid fa-moon\"></i>\n";
    $nav .= "        </button>\n";

    $nav .= "    </div>\n";
    $nav .= "</div>\n";

    echo $nav;
}

// Imprimir navbar
render_navbar();
