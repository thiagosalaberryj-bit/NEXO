<?php
/**
 * Marcar invitaciones como vistas para el invitador actual.
 * Regla de negocio: el invitado no marca por abrir modal, solo al responder.
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit();
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesion no valida']);
    exit();
}

$userId = (int) getCurrentUserId();
$conn = conectarDB();

if (hasSeenColumns($conn)) {
    $stmt = $conn->prepare("UPDATE invitaciones_colaboradores SET visto_invitador = 1 WHERE id_invitador = ? AND visto_invitador = 0");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
}

cerrarConexion($conn);

echo json_encode([
    'success' => true
]);

/**
 * Detecta si existen las columnas de visto para compatibilidad de migraciones.
 */
function hasSeenColumns(mysqli $conn): bool {
    $result = $conn->query("SHOW COLUMNS FROM invitaciones_colaboradores LIKE 'visto_invitador'");
    $hasInvitador = $result && $result->num_rows > 0;
    if ($result) {
        $result->free();
    }

    $result = $conn->query("SHOW COLUMNS FROM invitaciones_colaboradores LIKE 'visto_invitado'");
    $hasInvitado = $result && $result->num_rows > 0;
    if ($result) {
        $result->free();
    }

    return $hasInvitador && $hasInvitado;
}
