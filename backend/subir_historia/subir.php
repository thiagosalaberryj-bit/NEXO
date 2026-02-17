<?php
/**
 * Subir Historia - Endpoint para procesar subida de historias
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'subir_historia') {
    handleSubirHistoria();
} else {
    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}

function handleSubirHistoria() {
    // Verificar sesión
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para subir una historia']);
        return;
    }

    $userId = getCurrentUserId();

    // Obtener datos del formulario
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $genero = isset($_POST['genero']) ? trim($_POST['genero']) : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : 'borrador'; // 'borrador' o 'publicada'
    $colaboradores = isset($_POST['colaboradores']) ? json_decode($_POST['colaboradores'], true) : [];
    $nombreCarpetaRecursos = isset($_POST['nombre_carpeta_recursos']) ? trim($_POST['nombre_carpeta_recursos']) : 'contenido';

    // Validaciones básicas
    if (empty($titulo) || empty($descripcion) || empty($genero)) {
        echo json_encode(['success' => false, 'message' => 'Título, descripción y género son requeridos']);
        return;
    }

    if (!in_array($estado, ['borrador', 'publicada'])) {
        echo json_encode(['success' => false, 'message' => 'Estado inválido']);
        return;
    }

    // Verificar archivos
    if (!isset($_FILES['archivo_html']) || !isset($_FILES['portada'])) {
        echo json_encode(['success' => false, 'message' => 'Archivos HTML y portada son requeridos']);
        return;
    }

    $htmlFile = $_FILES['archivo_html'];
    $portadaFile = $_FILES['portada'];

    // Validar tipos y tamaños
    if ($htmlFile['error'] !== UPLOAD_ERR_OK || $portadaFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al subir archivos']);
        return;
    }

    if (!isValidHtmlFile($htmlFile) || !isValidImageFile($portadaFile)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo inválido']);
        return;
    }

    $totalSize = $htmlFile['size'] + $portadaFile['size'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Procesar recursos adicionales si existen
    $recursos = isset($_FILES['recursos']) ? $_FILES['recursos'] : [];
    if (!empty($recursos['name'][0])) {
        for ($i = 0; $i < count($recursos['name']); $i++) {
            if ($recursos['error'][$i] !== UPLOAD_ERR_OK) continue;
            $totalSize += $recursos['size'][$i];
            if ($totalSize > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Los archivos exceden el límite total de 5MB']);
                return;
            }
        }
    }

    if ($totalSize > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Los archivos exceden el límite de 5MB']);
        return;
    }

    // Crear directorio para la historia basado en el título
    $tituloSlug = preg_replace('/[^a-zA-Z0-9-_]/', '_', $titulo); // Sanitizar título para nombre de carpeta
    if ($tituloSlug === '') {
        $tituloSlug = 'historia';
    }

    $uploadsBaseDir = __DIR__ . '/../../uploads/';
    $folderName = $tituloSlug;
    $historiaDir = $uploadsBaseDir . $folderName;

    if (is_dir($historiaDir)) {
        $folderName = $tituloSlug . '_' . date('Ymd_His') . '_' . str_replace('.', '', uniqid('', true));
        $historiaDir = $uploadsBaseDir . $folderName;
    }

    if (!mkdir($historiaDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Error al crear directorio']);
        return;
    }

    // Convertir rutas absolutas a relativas para DB
    $baseUrl = '/uploads/' . $folderName;

    // Subir archivos
    $htmlPath = $historiaDir . '/' . basename($htmlFile['name']);
    $portadaPath = $historiaDir . '/' . basename($portadaFile['name']);

    if (!move_uploaded_file($htmlFile['tmp_name'], $htmlPath) ||
        !move_uploaded_file($portadaFile['tmp_name'], $portadaPath)) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar archivos']);
        return;
    }

    // Convertir rutas absolutas a relativas para DB
    $htmlRelPath = $baseUrl . '/' . basename($htmlFile['name']);
    $portadaRelPath = $baseUrl . '/' . basename($portadaFile['name']);

    $conn = conectarDB();

    // Insertar historia
    $stmt = $conn->prepare("INSERT INTO historias (titulo, descripcion, id_autor, portada, archivo_twine, estado, genero) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssissss', $titulo, $descripcion, $userId, $portadaRelPath, $htmlRelPath, $estado, $genero);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la historia']);
        $stmt->close();
        cerrarConexion($conn);
        return;
    }

    $idHistoria = $conn->insert_id;
    $stmt->close();

    // Si hay recursos, crear carpeta por defecto para la historia
    if (!empty($recursos['name'][0])) {
        // Crear carpeta con el nombre dado para esta historia
        $stmt = $conn->prepare("INSERT INTO carpetas_historia (id_historia, nombre_carpeta) VALUES (?, ?)");
        $stmt->bind_param('is', $idHistoria, $nombreCarpetaRecursos);
        $stmt->execute();
        $idCarpeta = $conn->insert_id;
        $stmt->close();
    }

    // Insertar recursos
    if (!empty($recursos['name'][0])) {
        for ($i = 0; $i < count($recursos['name']); $i++) {
            if ($recursos['error'][$i] !== UPLOAD_ERR_OK) continue;

            $recursoPath = $historiaDir . '/' . basename($recursos['name'][$i]);
            if (move_uploaded_file($recursos['tmp_name'][$i], $recursoPath)) {
                $recursoRelPath = $baseUrl . '/' . basename($recursos['name'][$i]);
                $tipo = getFileType($recursos['type'][$i]);
                $extension = pathinfo($recursos['name'][$i], PATHINFO_EXTENSION);

                $stmt = $conn->prepare("INSERT INTO contenido_historia (id_historia, id_carpeta, nombre_archivo, ruta_archivo, tipo_archivo, extension, tamano_bytes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('iissssi', $idHistoria, $idCarpeta, $recursos['name'][$i], $recursoRelPath, $tipo, $extension, $recursos['size'][$i]);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Insertar invitaciones a colaboradores
    if (!empty($colaboradores)) {
        foreach ($colaboradores as $colaborador) {
            // Asumir que colaboradores es array de usernames
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE username = ? AND activo = TRUE LIMIT 1");
            $stmt->bind_param('s', $colaborador);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $idColaborador = $row['id_usuario'];
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO invitaciones_colaboradores (id_historia, id_invitador, id_invitado) VALUES (?, ?, ?)");
                $stmt->bind_param('iii', $idHistoria, $userId, $idColaborador);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt->close();
            }
        }
    }

    cerrarConexion($conn);

    echo json_encode(['success' => true, 'message' => 'Historia subida correctamente', 'id_historia' => $idHistoria]);
}

function isValidHtmlFile($file) {
    return $file['type'] === 'text/html' || pathinfo($file['name'], PATHINFO_EXTENSION) === 'html';
}

function isValidImageFile($file) {
    return in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
}

function getFileType($mime) {
    if (strpos($mime, 'image/') === 0) return 'imagen';
    if (strpos($mime, 'audio/') === 0) return 'audio';
    if (strpos($mime, 'video/') === 0) return 'video';
    if (in_array($mime, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) return 'documento';
    return 'otro';
}
?>