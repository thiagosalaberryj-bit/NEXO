<?php

require_once __DIR__ . '/helpers.php';

function getConn(): mysqli {
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'nexo_database');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($host, $user, $pass, $name, (int) $port);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error de conexión'], 500);
        exit();
    }
}
