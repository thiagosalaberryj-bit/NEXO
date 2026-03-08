<?php
/**
 * Responder invitación (aceptar/rechazar)
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
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

$idInvitacion = isset($_POST['id_invitacion']) ? (int) $_POST['id_invitacion'] : 0;
$accion = isset($_POST['accion']) ? trim($_POST['accion']) : '';

if ($idInvitacion <= 0 || !in_array($accion, ['aceptar', 'rechazar'], true)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

$userId = (int) getCurrentUserId();
$estadoNuevo = $accion === 'aceptar' ? 'aceptada' : 'rechazada';

$conn = conectarDB();
mysqli_begin_transaction($conn);

try {
    $hasSeenColumns = hasSeenColumns($conn);

    $sqlInv = "
        SELECT i.id_invitacion, i.id_historia, i.id_invitador, i.estado, h.titulo
        FROM invitaciones_colaboradores i
        INNER JOIN historias h ON h.id_historia = i.id_historia
        WHERE i.id_invitacion = ? AND i.id_invitado = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sqlInv);
    $stmt->bind_param('ii', $idInvitacion, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        throw new Exception('Invitación no encontrada');
    }

    if ($row['estado'] !== 'pendiente') {
        throw new Exception('La invitación ya fue respondida');
    }

    if ($hasSeenColumns) {
        $stmt = $conn->prepare("UPDATE invitaciones_colaboradores SET estado = ?, visto_invitado = 1, visto_invitador = 0 WHERE id_invitacion = ? AND id_invitado = ?");
    } else {
        $stmt = $conn->prepare("UPDATE invitaciones_colaboradores SET estado = ? WHERE id_invitacion = ? AND id_invitado = ?");
    }
    if (!$stmt) {
        throw new Exception('Error SQL actualizando invitacion: ' . $conn->error);
    }
    $stmt->bind_param('sii', $estadoNuevo, $idInvitacion, $userId);
    $stmt->execute();
    $stmt->close();

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => $accion === 'aceptar' ? 'Invitación aceptada' : 'Invitación rechazada'
    ]);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    cerrarConexion($conn);
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
