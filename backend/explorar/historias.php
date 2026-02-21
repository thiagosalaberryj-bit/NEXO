<?php
/**
 * Listado de historias para explorar con filtros y paginación
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'recent';
$interaction = isset($_GET['interaction']) ? trim($_GET['interaction']) : 'all';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

$isLogged = isLoggedIn();
$userId = $isLogged ? (int) getCurrentUserId() : 0;

$conn = conectarDB();

$where = ["h.estado = 'publicada'"];
$types = '';
$params = [];

if ($q !== '') {
    $where[] = "(h.titulo LIKE ? OR h.descripcion LIKE ? OR h.genero LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.username LIKE ?)";
    $like = '%' . $q . '%';
    $types .= 'ssssss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($genre !== '') {
    $where[] = "h.genero = ?";
    $types .= 's';
    $params[] = $genre;
}

if ($interaction === 'favorites') {
    if (!$isLogged) {
        echo json_encode([
            'success' => true,
            'stories' => [],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'total_pages' => 0
            ]
        ]);
        cerrarConexion($conn);
        exit();
    }

    $where[] = 'EXISTS (SELECT 1 FROM likes l2 WHERE l2.id_historia = h.id_historia AND l2.id_usuario = ?)';
    $types .= 'i';
    $params[] = $userId;
}

if ($interaction === 'commented') {
    if (!$isLogged) {
        echo json_encode([
            'success' => true,
            'stories' => [],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'total_pages' => 0
            ]
        ]);
        cerrarConexion($conn);
        exit();
    }

    $where[] = 'EXISTS (SELECT 1 FROM comentarios c2 WHERE c2.id_historia = h.id_historia AND c2.id_usuario = ?)';
    $types .= 'i';
    $params[] = $userId;
}

$whereSql = implode(' AND ', $where);

switch ($sort) {
    case 'popular':
        $orderBy = 'COALESCE(v.total_vistas, 0) DESC, h.fecha_creacion DESC';
        break;
    case 'liked':
        $orderBy = 'COALESCE(l.total_likes, 0) DESC, h.fecha_creacion DESC';
        break;
    case 'title':
        $orderBy = 'h.titulo ASC';
        break;
    case 'recent':
    default:
        $orderBy = 'h.fecha_creacion DESC';
        break;
}

$countSql = "
    SELECT COUNT(*) AS total
    FROM historias h
    INNER JOIN usuarios u ON u.id_usuario = h.id_autor
    WHERE {$whereSql}
";

$countStmt = $conn->prepare($countSql);
bindDynamicParams($countStmt, $types, $params);
$countStmt->execute();
$totalRow = $countStmt->get_result()->fetch_assoc();
$countStmt->close();

$total = (int) $totalRow['total'];
$totalPages = (int) ceil($total / $perPage);

$userSelectSql = $isLogged
    ? "
        EXISTS (SELECT 1 FROM likes lu WHERE lu.id_historia = h.id_historia AND lu.id_usuario = {$userId}) AS liked_by_user,
        EXISTS (SELECT 1 FROM comentarios cu WHERE cu.id_historia = h.id_historia AND cu.id_usuario = {$userId}) AS commented_by_user,
        EXISTS (SELECT 1 FROM visualizaciones vu WHERE vu.id_historia = h.id_historia AND vu.id_usuario = {$userId}) AS viewed_by_user,
      "
    : "
        0 AS liked_by_user,
        0 AS commented_by_user,
        0 AS viewed_by_user,
      ";

$sql = "
    SELECT
        h.id_historia,
        h.titulo,
        h.descripcion,
        h.genero,
        h.portada,
        h.archivo_twine,
        h.fecha_creacion,
        u.nombre,
        u.apellido,
        u.username,
        COALESCE(v.total_vistas, 0) AS total_vistas,
        COALESCE(l.total_likes, 0) AS total_likes,
        COALESCE(c.total_comentarios, 0) AS total_comentarios,
        EXISTS (SELECT 1 FROM formularios f WHERE f.id_historia = h.id_historia) AS has_formulario,
        {$userSelectSql}
        h.id_historia AS _order_guard
    FROM historias h
    INNER JOIN usuarios u ON u.id_usuario = h.id_autor
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
    WHERE {$whereSql}
    ORDER BY {$orderBy}
    LIMIT ? OFFSET ?
";

$mainTypes = $types . 'ii';
$mainParams = $params;
$mainParams[] = $perPage;
$mainParams[] = $offset;

$stmt = $conn->prepare($sql);
bindDynamicParams($stmt, $mainTypes, $mainParams);
$stmt->execute();
$result = $stmt->get_result();

$stories = [];
while ($row = $result->fetch_assoc()) {
    $authorName = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''));
    if ($authorName === '') {
        $authorName = $row['username'];
    }

    $stories[] = [
        'id_historia' => (int) $row['id_historia'],
        'titulo' => $row['titulo'],
        'descripcion' => $row['descripcion'],
        'genero' => $row['genero'],
        'portada' => $row['portada'],
        'archivo_twine' => $row['archivo_twine'],
        'fecha_creacion' => $row['fecha_creacion'],
        'autor' => $authorName,
        'total_vistas' => (int) $row['total_vistas'],
        'total_likes' => (int) $row['total_likes'],
        'total_comentarios' => (int) $row['total_comentarios'],
        'has_formulario' => (bool) $row['has_formulario'],
        'liked_by_user' => (bool) $row['liked_by_user'],
        'commented_by_user' => (bool) $row['commented_by_user'],
        'viewed_by_user' => (bool) $row['viewed_by_user']
    ];
}

$stmt->close();
cerrarConexion($conn);

echo json_encode([
    'success' => true,
    'stories' => $stories,
    'pagination' => [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages
    ]
]);

function bindDynamicParams($stmt, $types, $params) {
    if ($types === '' || empty($params)) {
        return;
    }

    $bindArgs = [];
    $bindArgs[] = $types;

    foreach ($params as $index => $value) {
        $bindArgs[] = &$params[$index];
    }

    call_user_func_array([$stmt, 'bind_param'], $bindArgs);
}
