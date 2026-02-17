<?php
/**
 * Perfil Historias - Endpoint para listar historias del usuario logueado
 */

require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../../session/session_manager.php';

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

$userId = getCurrentUserId();
$conn = conectarDB();

$sql = "
    SELECT
        h.id_historia,
        h.titulo,
        h.descripcion,
        h.genero,
        h.estado,
        h.fecha_creacion,
        COALESCE(v.total_vistas, 0) AS total_vistas,
        COALESCE(l.total_likes, 0) AS total_likes,
        COALESCE(c.total_comentarios, 0) AS total_comentarios
    FROM historias h
    LEFT JOIN (
        SELECT id_historia, COUNT(*) AS total_vistas
        FROM visualizaciones
        GROUP BY id_historia
    ) v ON v.id_historia = h.id_historia
    LEFT JOIN (
        SELECT id_historia, COUNT(*) AS total_likes
        FROM likes
        GROUP BY id_historia
    ) l ON l.id_historia = h.id_historia
    LEFT JOIN (
        SELECT id_historia, COUNT(*) AS total_comentarios
        FROM comentarios
        GROUP BY id_historia
    ) c ON c.id_historia = h.id_historia
    WHERE h.id_autor = ?
    ORDER BY h.fecha_creacion DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$historias = [];
while ($row = $result->fetch_assoc()) {
    $historias[] = [
        'id_historia' => (int) $row['id_historia'],
        'titulo' => $row['titulo'],
        'descripcion' => $row['descripcion'],
        'genero' => $row['genero'],
        'estado' => $row['estado'],
        'fecha_creacion' => $row['fecha_creacion'],
        'total_vistas' => (int) $row['total_vistas'],
        'total_likes' => (int) $row['total_likes'],
        'total_comentarios' => (int) $row['total_comentarios']
    ];
}

$stmt->close();
cerrarConexion($conn);

echo json_encode([
    'success' => true,
    'historias' => $historias
]);
