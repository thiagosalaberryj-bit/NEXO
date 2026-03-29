<?php
/**
 * Logout - Cierre de sesión
 */

require_once __DIR__ . '/session_manager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ]);
    exit();
}

// Verificar si es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

logoutUser();

if ($isAjax) {
    // Respuesta JSON para AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente'
    ]);
} else {
    // Redireccion normal para navegacion directa (evita ruta hardcodeada)
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $projectBase = rtrim(dirname(dirname(dirname($scriptName))), '/');
    $redirectUrl = ($projectBase !== '' ? $projectBase : '') . '/frontend/explorar.php?logout=success';

    header('Location: ' . $redirectUrl);
}
exit();
