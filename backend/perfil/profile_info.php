<?php
/**
 * Profile Info - Endpoint para obtener información del perfil del usuario logueado
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

$userId = getCurrentUserId();
$conn = conectarDB();

$sql = "
	SELECT
		u.id_usuario,
		u.nombre,
		u.apellido,
		u.username,
		u.email,
		(
			SELECT COUNT(*)
			FROM historias h
			WHERE h.id_autor = u.id_usuario
		) AS total_historias,
		(
			SELECT COUNT(*)
			FROM likes l
			INNER JOIN historias h ON h.id_historia = l.id_historia
			WHERE h.id_autor = u.id_usuario
		) AS total_likes,
		(
			SELECT COUNT(*)
			FROM comentarios c
			INNER JOIN historias h ON h.id_historia = c.id_historia
			WHERE h.id_autor = u.id_usuario
		) AS total_comentarios
	FROM usuarios u
	WHERE u.id_usuario = ? AND u.activo = TRUE
	LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
	http_response_code(404);
	echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
	$stmt->close();
	cerrarConexion($conn);
	exit();
}

$row = $result->fetch_assoc();

$data = [
	'id' => (int) $row['id_usuario'],
	'nombre' => $row['nombre'],
	'apellido' => $row['apellido'],
	'username' => $row['username'],
	'email' => $row['email'],
	'total_historias' => (int) $row['total_historias'],
	'total_likes' => (int) $row['total_likes'],
	'total_comentarios' => (int) $row['total_comentarios']
];

$stmt->close();
cerrarConexion($conn);

echo json_encode(['success' => true, 'profile' => $data]);

