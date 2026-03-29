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
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para subir una historia']);
        return;
    }

    $userId = (int) getCurrentUserId();

    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $genero = isset($_POST['genero']) ? trim($_POST['genero']) : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : 'borrador';

    $colaboradoresRaw = isset($_POST['colaboradores']) ? json_decode($_POST['colaboradores'], true) : [];
    $colaboradores = is_array($colaboradoresRaw) ? $colaboradoresRaw : [];

    $nombreCarpetaRecursos = isset($_POST['nombre_carpeta_recursos']) ? trim($_POST['nombre_carpeta_recursos']) : 'contenido';
    $nombreCarpetaRecursos = sanitizeFolderName($nombreCarpetaRecursos);
    if ($nombreCarpetaRecursos === '') {
        $nombreCarpetaRecursos = 'contenido';
    }

    if ($titulo === '' || $descripcion === '' || $genero === '') {
        echo json_encode(['success' => false, 'message' => 'Título, descripción y género son requeridos']);
        return;
    }

    if (!in_array($estado, ['borrador', 'publicada'], true)) {
        echo json_encode(['success' => false, 'message' => 'Estado inválido']);
        return;
    }

    if (!isset($_FILES['archivo_html']) || !isset($_FILES['portada'])) {
        echo json_encode(['success' => false, 'message' => 'Archivos HTML y portada son requeridos']);
        return;
    }

    $htmlFile = $_FILES['archivo_html'];
    $portadaFile = $_FILES['portada'];

    if ($htmlFile['error'] !== UPLOAD_ERR_OK || $portadaFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al subir archivos']);
        return;
    }

    if (!isValidHtmlFile($htmlFile) || !isValidImageFile($portadaFile)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo inválido']);
        return;
    }

    $totalSize = (int) $htmlFile['size'] + (int) $portadaFile['size'];
    $maxSize = 5 * 1024 * 1024;

    $recursos = isset($_FILES['recursos']) ? $_FILES['recursos'] : [];
    $recursosValidos = [];

    if (isset($recursos['name']) && is_array($recursos['name']) && !empty($recursos['name'][0])) {
        $totalRecursos = count($recursos['name']);
        for ($i = 0; $i < $totalRecursos; $i++) {
            if (!isset($recursos['error'][$i]) || $recursos['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $fileSize = isset($recursos['size'][$i]) ? (int) $recursos['size'][$i] : 0;
            $totalSize += $fileSize;

            if ($totalSize > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Los archivos exceden el límite total de 5MB']);
                return;
            }

            $recursosValidos[] = [
                'name' => (string) $recursos['name'][$i],
                'tmp_name' => (string) $recursos['tmp_name'][$i],
                'type' => isset($recursos['type'][$i]) ? (string) $recursos['type'][$i] : '',
                'size' => $fileSize
            ];
        }
    }

    if ($totalSize > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Los archivos exceden el límite de 5MB']);
        return;
    }

    $tituloSlug = preg_replace('/[^a-zA-Z0-9-_]/', '_', $titulo);
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

    $conn = null;
    $transactionStarted = false;
    $historiaDirCreado = false;

    try {
        $conn = conectarDB();
        mysqli_begin_transaction($conn);
        $transactionStarted = true;

        if (!mkdir($historiaDir, 0755, true)) {
            throw new RuntimeException('Error al crear directorio');
        }
        $historiaDirCreado = true;

        $baseUrl = '/uploads/' . $folderName;

        $htmlFileName = basename((string) $htmlFile['name']);
        $portadaFileName = basename((string) $portadaFile['name']);

        $htmlPath = $historiaDir . '/' . $htmlFileName;
        $portadaPath = $historiaDir . '/' . $portadaFileName;

        if (!move_uploaded_file($htmlFile['tmp_name'], $htmlPath) || !move_uploaded_file($portadaFile['tmp_name'], $portadaPath)) {
            throw new RuntimeException('Error al guardar archivos');
        }

        $htmlRelPath = $baseUrl . '/' . $htmlFileName;
        $portadaRelPath = $baseUrl . '/' . $portadaFileName;

        $recursosMovidos = [];
        if (!empty($recursosValidos)) {
            $recursosDir = $historiaDir . '/' . $nombreCarpetaRecursos;
            if (!is_dir($recursosDir) && !mkdir($recursosDir, 0755, true)) {
                throw new RuntimeException('Error al crear la carpeta de recursos');
            }

            $recursosBaseUrl = $baseUrl . '/' . $nombreCarpetaRecursos;

            foreach ($recursosValidos as $recurso) {
                $recursoNombre = basename($recurso['name']);
                $recursoPath = $recursosDir . '/' . $recursoNombre;

                if (!move_uploaded_file($recurso['tmp_name'], $recursoPath)) {
                    throw new RuntimeException('Error al guardar archivos de recursos');
                }

                $recursosMovidos[] = [
                    'name' => $recursoNombre,
                    'type' => $recurso['type'],
                    'size' => $recurso['size'],
                    'relative_path' => $recursosBaseUrl . '/' . $recursoNombre
                ];
            }
        }

        $stmtHistoria = $conn->prepare("INSERT INTO historias (titulo, descripcion, id_autor, portada, archivo_twine, estado, genero) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmtHistoria) {
            throw new RuntimeException('Error al preparar el guardado de la historia');
        }
        $stmtHistoria->bind_param('ssissss', $titulo, $descripcion, $userId, $portadaRelPath, $htmlRelPath, $estado, $genero);
        if (!$stmtHistoria->execute()) {
            $stmtHistoria->close();
            throw new RuntimeException('Error al guardar la historia');
        }
        $idHistoria = (int) $conn->insert_id;
        $stmtHistoria->close();

        if (!empty($recursosMovidos)) {
            $stmtCarpeta = $conn->prepare("INSERT INTO carpetas_historia (id_historia, nombre_carpeta) VALUES (?, ?)");
            if (!$stmtCarpeta) {
                throw new RuntimeException('Error al preparar la carpeta de recursos');
            }
            $stmtCarpeta->bind_param('is', $idHistoria, $nombreCarpetaRecursos);
            if (!$stmtCarpeta->execute()) {
                $stmtCarpeta->close();
                throw new RuntimeException('Error al guardar la carpeta de recursos');
            }
            $idCarpeta = (int) $conn->insert_id;
            $stmtCarpeta->close();

            $stmtContenido = $conn->prepare("INSERT INTO contenido_historia (id_historia, id_carpeta, nombre_archivo, ruta_archivo, tipo_archivo, extension, tamano_bytes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmtContenido) {
                throw new RuntimeException('Error al preparar el contenido de recursos');
            }

            foreach ($recursosMovidos as $recursoMovido) {
                $nombreArchivo = $recursoMovido['name'];
                $rutaArchivo = $recursoMovido['relative_path'];
                $tipo = getFileType($recursoMovido['type']);
                $extension = pathinfo($recursoMovido['name'], PATHINFO_EXTENSION);
                $tamanoBytes = (int) $recursoMovido['size'];

                $stmtContenido->bind_param('iissssi', $idHistoria, $idCarpeta, $nombreArchivo, $rutaArchivo, $tipo, $extension, $tamanoBytes);
                if (!$stmtContenido->execute()) {
                    $stmtContenido->close();
                    throw new RuntimeException('Error al guardar los recursos de la historia');
                }
            }
            $stmtContenido->close();
        }

        if (!empty($colaboradores)) {
            $stmtBuscarUsuario = $conn->prepare("SELECT id_usuario FROM usuarios WHERE username = ? AND activo = TRUE LIMIT 1");
            if (!$stmtBuscarUsuario) {
                throw new RuntimeException('Error al preparar búsqueda de colaboradores');
            }

            $stmtInvitacion = $conn->prepare("INSERT INTO invitaciones_colaboradores (id_historia, id_invitador, id_invitado) VALUES (?, ?, ?)");
            if (!$stmtInvitacion) {
                $stmtBuscarUsuario->close();
                throw new RuntimeException('Error al preparar invitaciones de colaboradores');
            }

            foreach ($colaboradores as $colaborador) {
                $usernameColaborador = trim((string) $colaborador);
                if ($usernameColaborador === '') {
                    continue;
                }

                $stmtBuscarUsuario->bind_param('s', $usernameColaborador);
                if (!$stmtBuscarUsuario->execute()) {
                    $stmtInvitacion->close();
                    $stmtBuscarUsuario->close();
                    throw new RuntimeException('Error al buscar colaboradores');
                }

                $result = $stmtBuscarUsuario->get_result();
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $idColaborador = (int) $row['id_usuario'];

                    $stmtInvitacion->bind_param('iii', $idHistoria, $userId, $idColaborador);
                    if (!$stmtInvitacion->execute()) {
                        $stmtInvitacion->close();
                        $stmtBuscarUsuario->close();
                        throw new RuntimeException('Error al guardar invitaciones de colaboradores');
                    }
                }
            }

            $stmtInvitacion->close();
            $stmtBuscarUsuario->close();
        }

        mysqli_commit($conn);
        $transactionStarted = false;
        cerrarConexion($conn);

        echo json_encode(['success' => true, 'message' => 'Historia subida correctamente', 'id_historia' => $idHistoria]);
    } catch (Throwable $e) {
        if ($transactionStarted && $conn) {
            mysqli_rollback($conn);
        }

        if ($conn) {
            cerrarConexion($conn);
        }

        if ($historiaDirCreado && is_dir($historiaDir)) {
            deleteDirectoryRecursive($historiaDir);
        }

        error_log('Subir historia error: ' . $e->getMessage());

        $safeMessage = $e instanceof RuntimeException
            ? $e->getMessage()
            : 'No se pudo completar la subida de la historia';

        echo json_encode(['success' => false, 'message' => $safeMessage]);
    }
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

function deleteDirectoryRecursive($dir) {
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            deleteDirectoryRecursive($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($dir);
}

function sanitizeFolderName($name) {
    $clean = preg_replace('/[^a-zA-Z0-9-_]/', '_', $name);
    $clean = trim($clean, '_');
    return $clean;
}
?>