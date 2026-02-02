<?php
/**
 * Session Manager - Gestor de Sesiones
 * Proyecto NEXO - Escuela Secundaria Técnica N°1
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function loginUser($userId, $userName, $userEmail) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
    $_SESSION['user_email'] = $userEmail;
    $_SESSION['login_time'] = time();
}

function logoutUser() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

function getCurrentUserName() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Usuario';
}
