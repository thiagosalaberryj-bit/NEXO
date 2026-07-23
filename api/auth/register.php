<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';

setupCors();
requireMethod('POST');

$firstName = trim($_POST['firstName'] ?? '');
$lastName  = trim($_POST['lastName'] ?? '');
$username  = trim($_POST['username'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';

if ($firstName === '' || $lastName === '' || $username === '' || $email === '' || $password === '') {
    jsonError('Todos los campos son requeridos');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Email inválido');
}

if (strlen($username) < 3) {
    jsonError('El username debe tener al menos 3 caracteres');
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    jsonError('El username solo puede contener letras, números y guiones bajos');
}

if (strlen($password) < 6) {
    jsonError('La contraseña debe tener al menos 6 caracteres');
}

$conn = getConn();

$stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    jsonError('El email ya está registrado');
}
$stmt->close();

$stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    jsonError('El username ya está en uso');
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, username, email, password_hash) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('sssss', $firstName, $lastName, $username, $email, $hash);

if ($stmt->execute()) {
    $stmt->close();
    jsonSuccess('Registro exitoso, ahora puedes iniciar sesión');
} else {
    $stmt->close();
    jsonError('Error al registrar usuario', 500);
}
