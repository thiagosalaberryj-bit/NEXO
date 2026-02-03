<?php
/**
 * Logout - Cierre de sesión
 */

require_once __DIR__ . '/session_manager.php';

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
    // Redirección normal para navegación directa
    header('Location: /PROYECTO_NEXO/index.html?logout=success');
}
exit();
