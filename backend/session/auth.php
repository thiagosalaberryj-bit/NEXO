<?php
/**
 * Auth Controller - Login y Registro
 */

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/session_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'login') {
    handleLogin();
} elseif ($action === 'register') {
    handleRegister();
} else {
    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}

function handleLogin() {
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($identifier === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Usuario/email y contraseña son requeridos']);
        return;
    }

    $conn = conectarDB();

    // Determinar si es email o username
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    $field = $isEmail ? 'email' : 'username';

    $stmt = $conn->prepare("SELECT id_usuario, nombre, apellido, username, email, password_hash FROM usuarios WHERE $field = ? AND activo = TRUE LIMIT 1");
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        $stmt->close();
        cerrarConexion($conn);
        return;
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        $stmt->close();
        cerrarConexion($conn);
        return;
    }

    loginUser($user['id_usuario'], $user['nombre'] . ' ' . $user['apellido'], $user['email'], $user['username']);

    $stmt->close();
    cerrarConexion($conn);

    echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso']);
}

function handleRegister() {
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($firstName === '' || $lastName === '' || $username === '' || $email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        return;
    }

    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'El username debe tener al menos 3 caracteres']);
        return;
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'El username solo puede contener letras, números y guiones bajos']);
        return;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        return;
    }

    $conn = conectarDB();

    // Verificar email único
    $stmt = $conn->prepare('SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        $stmt->close();
        cerrarConexion($conn);
        return;
    }

    $stmt->close();

    // Verificar username único
    $stmt = $conn->prepare('SELECT id_usuario FROM usuarios WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El username ya está en uso']);
        $stmt->close();
        cerrarConexion($conn);
        return;
    }

    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, apellido, username, email, password_hash) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $firstName, $lastName, $username, $email, $hash);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registro exitoso, ahora puedes iniciar sesión']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar usuario']);
    }

    $stmt->close();
    cerrarConexion($conn);
}
