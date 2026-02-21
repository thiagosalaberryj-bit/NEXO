<?php
/**
 * Registrar visualización de historia (solo con sesión)
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
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para registrar visualización']);
    exit();
}

$idHistoria = isset($_POST['id_historia']) ? (int) $_POST['id_historia'] : 0;
if ($idHistoria <= 0) {
    echo json_encode(['success' => false, 'message' => 'Historia inválida']);
    exit();
}

$userId = getCurrentUserId();
$conn = conectarDB();

$stmt = $conn->prepare("INSERT IGNORE INTO visualizaciones (id_historia, id_usuario) VALUES (?, ?)");
$stmt->bind_param('ii', $idHistoria, $userId);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS total_vistas FROM visualizaciones WHERE id_historia = ?");
$stmt->bind_param('i', $idHistoria);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$stmt->close();

cerrarConexion($conn);

echo json_encode([
    'success' => true,
    'total_vistas' => (int) $totalResult['total_vistas']
]);
