<?php
/**
 * Toggle like de historia
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
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para dar like']);
    exit();
}

$idHistoria = isset($_POST['id_historia']) ? (int) $_POST['id_historia'] : 0;
if ($idHistoria <= 0) {
    echo json_encode(['success' => false, 'message' => 'Historia inválida']);
    exit();
}

$userId = getCurrentUserId();
$conn = conectarDB();

$stmt = $conn->prepare("SELECT id_like FROM likes WHERE id_historia = ? AND id_usuario = ? LIMIT 1");
$stmt->bind_param('ii', $idHistoria, $userId);
$stmt->execute();
$result = $stmt->get_result();
$alreadyLiked = $result->num_rows > 0;
$stmt->close();

if ($alreadyLiked) {
    $stmt = $conn->prepare("DELETE FROM likes WHERE id_historia = ? AND id_usuario = ?");
    $stmt->bind_param('ii', $idHistoria, $userId);
    $stmt->execute();
    $stmt->close();
    $liked = false;
} else {
    $stmt = $conn->prepare("INSERT INTO likes (id_historia, id_usuario) VALUES (?, ?)");
    $stmt->bind_param('ii', $idHistoria, $userId);
    $stmt->execute();
    $stmt->close();
    $liked = true;
}

$stmt = $conn->prepare("SELECT COUNT(*) AS total_likes FROM likes WHERE id_historia = ?");
$stmt->bind_param('i', $idHistoria);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$stmt->close();

cerrarConexion($conn);

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'total_likes' => (int) $totalResult['total_likes']
]);
