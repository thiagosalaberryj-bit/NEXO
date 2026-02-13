<?php
/**
 * Buscar Usuarios - Endpoint para búsqueda de usuarios en tiempo real
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => true, 'users' => []]);
    exit();
}

$conn = conectarDB();

// Buscar usuarios por username o nombre/apellido, activos, limit 10
$stmt = $conn->prepare("SELECT id_usuario, username, nombre, apellido FROM usuarios WHERE (username LIKE ? OR CONCAT(nombre, ' ', apellido) LIKE ?) AND activo = TRUE LIMIT 10");
$searchTerm = '%' . $query . '%';
$stmt->bind_param('ss', $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id_usuario'],
        'username' => $row['username'],
        'name' => $row['nombre'] . ' ' . $row['apellido']
    ];
}

$stmt->close();
cerrarConexion($conn);

echo json_encode(['success' => true, 'users' => $users]);
?>