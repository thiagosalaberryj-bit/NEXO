<?php
/**
 * Crear comentario en historia
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para comentar']);
    exit();
}

$idHistoria = isset($_POST['id_historia']) ? (int) $_POST['id_historia'] : 0;
$contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';

if ($idHistoria <= 0) {
    echo json_encode(['success' => false, 'message' => 'Historia inválida']);
    exit();
}

if ($contenido === '') {
    echo json_encode(['success' => false, 'message' => 'El comentario no puede estar vacío']);
    exit();
}

$userId = getCurrentUserId();
$conn = conectarDB();

$stmt = $conn->prepare("INSERT INTO comentarios (id_historia, id_usuario, contenido) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $idHistoria, $userId, $contenido);
$ok = $stmt->execute();
$insertedCommentId = $stmt->insert_id;
$stmt->close();

if (!$ok) {
    cerrarConexion($conn);
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar el comentario']);
    exit();
}

$stmt = $conn->prepare("SELECT COUNT(*) AS total_comentarios FROM comentarios WHERE id_historia = ?");
$stmt->bind_param('i', $idHistoria);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$stmt->close();

$commentData = null;
if ($insertedCommentId > 0) {
    $stmt = $conn->prepare("\n        SELECT\n            c.id_comentario,\n            c.contenido,\n            c.fecha_comentario,\n            u.nombre,\n            u.apellido,\n            u.username,\n            u.tipo_usuario\n        FROM comentarios c\n        INNER JOIN usuarios u ON u.id_usuario = c.id_usuario\n        WHERE c.id_comentario = ?\n        LIMIT 1\n    ");
    $stmt->bind_param('i', $insertedCommentId);
    $stmt->execute();
    $commentRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($commentRow) {
        $autor = trim(($commentRow['nombre'] ?? '') . ' ' . ($commentRow['apellido'] ?? ''));
        if ($autor === '') {
            $autor = $commentRow['username'] ?? 'Usuario';
        }

        $commentData = [
            'id_comentario' => (int) $commentRow['id_comentario'],
            'contenido' => $commentRow['contenido'],
            'fecha_comentario' => $commentRow['fecha_comentario'],
            'autor' => $autor,
            'username' => $commentRow['username'] ?? '',
            'tipo_usuario' => $commentRow['tipo_usuario'] ?? 'estudiante'
        ];
    }
}

cerrarConexion($conn);

echo json_encode([
    'success' => true,
    'message' => 'Comentario publicado',
    'total_comentarios' => (int) $totalResult['total_comentarios'],
    'comment' => $commentData
]);
