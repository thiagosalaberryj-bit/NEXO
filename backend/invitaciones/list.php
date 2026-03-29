<?php
/**
 * Listar invitaciones (enviadas y recibidas) para el modal de notificaciones.
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : 'all';
$fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : 'recent';

$allowedEstado = ['all', 'pendiente', 'aceptada', 'rechazada'];
$allowedFecha = ['recent', 'oldest'];

if (!in_array($estado, $allowedEstado, true)) {
    $estado = 'all';
}
if (!in_array($fecha, $allowedFecha, true)) {
    $fecha = 'recent';
}

$conn = conectarDB();

try {
    $hasSeenColumns = hasSeenColumns($conn);

    $selectVistoInvitador = $hasSeenColumns ? 'i.visto_invitador' : '0 AS visto_invitador';
    $selectVistoInvitado = $hasSeenColumns ? 'i.visto_invitado' : '0 AS visto_invitado';

    $sql = "
    SELECT
        i.id_invitacion,
        i.id_historia,
        i.id_invitador,
        i.id_invitado,
        i.estado,
        {$selectVistoInvitador},
        {$selectVistoInvitado},
        i.fecha_invitacion,
        h.titulo AS titulo_historia,
        u_inv.nombre AS nombre_invitador,
        u_inv.apellido AS apellido_invitador,
        u_inv.username AS username_invitador,
        u_invd.nombre AS nombre_invitado,
        u_invd.apellido AS apellido_invitado,
        u_invd.username AS username_invitado
    FROM invitaciones_colaboradores i
    INNER JOIN historias h ON h.id_historia = i.id_historia
    INNER JOIN usuarios u_inv ON u_inv.id_usuario = i.id_invitador
    INNER JOIN usuarios u_invd ON u_invd.id_usuario = i.id_invitado
    WHERE (i.id_invitador = ? OR i.id_invitado = ?)
";

    $types = 'ii';
    $params = [$userId, $userId];

    if ($estado !== 'all') {
        $sql .= " AND i.estado = ?";
        $types .= 's';
        $params[] = $estado;
    }

    if ($search !== '') {
        $sql .= " AND (
        h.titulo LIKE ?
        OR u_inv.nombre LIKE ?
        OR u_inv.apellido LIKE ?
        OR u_inv.username LIKE ?
        OR u_invd.nombre LIKE ?
        OR u_invd.apellido LIKE ?
        OR u_invd.username LIKE ?
    )";
        $like = '%' . $search . '%';
        $types .= 'sssssss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= $fecha === 'oldest' ? ' ORDER BY i.fecha_invitacion ASC' : ' ORDER BY i.fecha_invitacion DESC';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error SQL listando invitaciones: ' . $conn->error);
    }
    bindDynamicParams($stmt, $types, $params);
    $stmt->execute();
    $result = $stmt->get_result();

    $invitaciones = [];
    while ($row = $result->fetch_assoc()) {
    $isInvitador = ((int) $row['id_invitador']) === $userId;

    $invitador = trim(($row['nombre_invitador'] ?? '') . ' ' . ($row['apellido_invitador'] ?? ''));
    if ($invitador === '') {
        $invitador = $row['username_invitador'] ?? 'Usuario';
    }

    $invitado = trim(($row['nombre_invitado'] ?? '') . ' ' . ($row['apellido_invitado'] ?? ''));
    if ($invitado === '') {
        $invitado = $row['username_invitado'] ?? 'Usuario';
    }

    $vistoActual = $isInvitador ? (int) $row['visto_invitador'] : (int) $row['visto_invitado'];

        $invitaciones[] = [
        'id_invitacion' => (int) $row['id_invitacion'],
        'id_historia' => (int) $row['id_historia'],
        'es_invitador' => $isInvitador,
        'estado' => $row['estado'],
        'visto_actual' => $vistoActual === 1,
        'titulo_historia' => $row['titulo_historia'],
        'invitador' => $invitador,
        'invitado' => $invitado,
        'fecha_invitacion' => $row['fecha_invitacion']
        ];
    }
    $stmt->close();

    $countSql = $hasSeenColumns ? "
    SELECT COUNT(*) AS total
    FROM invitaciones_colaboradores i
    WHERE (
        (i.id_invitador = ? AND i.visto_invitador = 0)
        OR
        (i.id_invitado = ? AND i.visto_invitado = 0)
    )
" : "
    SELECT COUNT(*) AS total
    FROM invitaciones_colaboradores i
    WHERE i.id_invitado = ? AND i.estado = 'pendiente'
";

    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) {
        throw new Exception('Error SQL contando invitaciones: ' . $conn->error);
    }
    if ($hasSeenColumns) {
        $countStmt->bind_param('ii', $userId, $userId);
    } else {
        $countStmt->bind_param('i', $userId);
    }
    $countStmt->execute();
    $countRow = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();

    echo json_encode([
        'success' => true,
        'unseen_count' => (int) ($countRow['total'] ?? 0),
        'invitaciones' => $invitaciones
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('Invitaciones list error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'No se pudieron obtener las invitaciones'
    ]);
} finally {
    cerrarConexion($conn);
}

/**
 * Helper para bind_param dinámico en mysqli.
 */
function bindDynamicParams(mysqli_stmt $stmt, string $types, array $params): void {
    $bind = [$types];
    foreach ($params as $idx => $value) {
        $bind[] = &$params[$idx];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind);
}

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
