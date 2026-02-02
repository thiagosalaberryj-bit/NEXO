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
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Email y contraseña son requeridos']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        return;
    }

    $conn = conectarDB();

    $stmt = $conn->prepare('SELECT id_usuario, nombre, email, password_hash FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
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

    loginUser($user['id_usuario'], $user['nombre'], $user['email']);

    $stmt->close();
    cerrarConexion($conn);

    echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso']);
}

function handleRegister() {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($name === '' || $email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        return;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        return;
    }

    $conn = conectarDB();

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

    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Separar nombre y apellido (asumiendo que viene como "Nombre Apellido")
    $nameParts = explode(' ', $name, 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, apellido, email, password_hash) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $firstName, $lastName, $email, $hash);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registro exitoso, ahora puedes iniciar sesión']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar usuario']);
    }

    $stmt->close();
    cerrarConexion($conn);
}
