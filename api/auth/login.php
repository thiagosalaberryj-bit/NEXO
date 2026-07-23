<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';

setupCors();
requireMethod('POST');

$identifier = trim($_POST['identifier'] ?? '');
$password   = $_POST['password'] ?? '';

if ($identifier === '' || $password === '') {
    jsonError('Usuario/email y contraseña son requeridos');
}

$conn = getConn();
$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
$field = $isEmail ? 'email' : 'username';

$stmt = $conn->prepare("SELECT id_usuario, nombre, apellido, username, email, password_hash FROM usuarios WHERE $field = ? AND activo = TRUE LIMIT 1");
$stmt->bind_param('s', $identifier);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonError('Credenciales incorrectas', 401);
}

loginUser(
    (int) $user['id_usuario'],
    $user['nombre'] . ' ' . $user['apellido'],
    $user['email'],
    $user['username']
);

jsonSuccess('Inicio de sesión exitoso');
