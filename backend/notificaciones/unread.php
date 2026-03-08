<?php
/**
 * Obtener notificaciones no leídas del usuario logueado
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

$userId = (int) getCurrentUserId();
$conn = conectarDB();

$stmt = $conn->prepare("SELECT id_notificacion, tipo, titulo, mensaje, fecha_creacion FROM notificaciones WHERE id_usuario = ? AND leida = 0 AND tipo IN ('invitacion_aceptada', 'invitacion_rechazada') ORDER BY fecha_creacion DESC LIMIT 20");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$notificaciones = [];
$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = (int) $row['id_notificacion'];
    $notificaciones[] = [
        'id_notificacion' => (int) $row['id_notificacion'],
        'tipo' => $row['tipo'],
        'titulo' => $row['titulo'],
        'mensaje' => $row['mensaje'],
        'fecha_creacion' => $row['fecha_creacion']
    ];
}
$stmt->close();

if (!empty($ids)) {
    $idsSql = implode(',', array_map('intval', $ids));
    $conn->query("UPDATE notificaciones SET leida = 1 WHERE id_notificacion IN ({$idsSql})");
}

cerrarConexion($conn);

echo json_encode([
    'success' => true,
    'notificaciones' => $notificaciones
]);
