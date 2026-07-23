<?php

// ─────────────── .Env loader ───────────────

function loadEnv(): array {
    static $vars = null;
    if ($vars !== null) return $vars;

    $vars = [];
    $path = __DIR__ . '/../../.env';
    if (!file_exists($path)) return $vars;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        $vars[$key] = $value;
    }

    return $vars;
}

function env(string $key, string $default = ''): string {
    static $vars = null;
    if ($vars === null) $vars = loadEnv();
    return $vars[$key] ?? $default;
}

// ─────────────── CORS ───────────────

function setupCors(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}

// ─────────────── Respuestas JSON ───────────────

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function jsonError(string $message, int $status = 400): void {
    jsonResponse(['success' => false, 'message' => $message], $status);
}

function jsonSuccess(string $message, array $extra = []): void {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $extra));
}

// ─────────────── Validación ───────────────

function validateId(mixed $value): int {
    $id = (int) $value;
    if ($id <= 0) jsonError('ID inválido');
    return $id;
}

function requireMethod(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        jsonError('Método no permitido', 405);
    }
}

function requireAuth(): int {
    require_once __DIR__ . '/../auth/session.php';
    $userId = getCurrentUserId();
    if (!$userId) jsonError('Sesión no válida', 401);
    return $userId;
}

// ─────────────── Rutas de archivos ───────────────

function getUploadsPath(): string {
    $path = env('UPLOADS_PATH', '../uploads');
    $absolute = realpath(__DIR__ . '/../../' . $path);
    if ($absolute === false) {
        $absolute = __DIR__ . '/../../' . $path;
        if (!is_dir($absolute)) {
            mkdir($absolute, 0755, true);
        }
    }
    return $absolute;
}

function getUploadsUrl(): string {
    $base = env('UPLOADS_PATH', 'uploads');
    return '/' . ltrim($base, '../');
}

// ─────────────── Misc ───────────────

function escapeHtml(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitizeFolderName(string $name): string {
    $clean = preg_replace('/[^a-zA-Z0-9-_]/', '_', $name);
    return trim($clean, '_') ?: 'contenido';
}
