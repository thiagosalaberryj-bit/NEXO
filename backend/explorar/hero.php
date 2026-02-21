<?php
/**
 * Hero stats para explorar
 */

require_once __DIR__ . '/../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$conn = conectarDB();

$sql = "
    SELECT
        (SELECT COUNT(*) FROM historias WHERE estado = 'publicada') AS total_historias,
        (SELECT COUNT(*) FROM usuarios WHERE activo = TRUE) AS total_usuarios,
        (SELECT COUNT(DISTINCT id_autor) FROM historias WHERE estado = 'publicada') AS total_escritores
";

$result = $conn->query($sql);
$row = $result ? $result->fetch_assoc() : null;

cerrarConexion($conn);

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'No se pudieron obtener estadísticas']);
    exit();
}

echo json_encode([
    'success' => true,
    'stats' => [
        'total_historias' => (int) $row['total_historias'],
        'total_usuarios' => (int) $row['total_usuarios'],
        'total_escritores' => (int) $row['total_escritores']
    ]
]);
