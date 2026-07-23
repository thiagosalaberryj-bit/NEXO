<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loginUser(int $userId, string $name, string $email, string $username = ''): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_username'] = $username;
}

function logoutUser(): void {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function getCurrentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function getCurrentUserName(): string {
    return $_SESSION['user_name'] ?? 'Usuario';
}

function getCurrentUserUsername(): string {
    return $_SESSION['user_username'] ?? '';
}

function getCurrentUserEmail(): string {
    return $_SESSION['user_email'] ?? '';
}
