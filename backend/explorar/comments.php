<?php
/**
 * Listado de comentarios por historia
 */

require_once __DIR__ . '/../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$idHistoria = isset($_GET['id_historia']) ? (int) $_GET['id_historia'] : 0;

if ($idHistoria <= 0) {
    echo json_encode(['success' => false, 'message' => 'Historia inválida', 'comments' => []]);
    exit();
}

$conn = conectarDB();

$stmt = $conn->prepare("\n    SELECT\n        c.id_comentario,\n        c.contenido,\n        c.fecha_comentario,\n        u.nombre,\n        u.apellido,\n        u.username,\n        u.tipo_usuario\n    FROM comentarios c\n    INNER JOIN usuarios u ON u.id_usuario = c.id_usuario\n    INNER JOIN historias h ON h.id_historia = c.id_historia\n    WHERE c.id_historia = ? AND h.estado = 'publicada'\n    ORDER BY c.fecha_comentario DESC\n    LIMIT 100\n");
$stmt->bind_param('i', $idHistoria);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $autor = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''));
    if ($autor === '') {
        $autor = $row['username'] ?? 'Usuario';
    }

    $comments[] = [
        'id_comentario' => (int) $row['id_comentario'],
        'contenido' => $row['contenido'],
        'fecha_comentario' => $row['fecha_comentario'],
        'autor' => $autor,
        'username' => $row['username'] ?? '',
        'tipo_usuario' => $row['tipo_usuario'] ?? 'estudiante'
    ];
}

$stmt->close();
cerrarConexion($conn);

echo json_encode([
    'success' => true,
    'comments' => $comments
]);
