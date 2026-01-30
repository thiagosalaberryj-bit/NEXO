<?php
/**
 * Archivo de conexión a la base de datos MySQLi
 *
 * Este archivo contiene la configuración y función para conectar
 * a la base de datos usando MySQLi
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');        // Servidor de MySQL
define('DB_NAME', 'nexo_database');    // Nombre de la base de datos
define('DB_USER', 'root');            // Usuario de MySQL
define('DB_PASS', '');                // Contraseña de MySQL (vacía por defecto en XAMPP)
define('DB_CHARSET', 'utf8mb4');      // Charset para soporte de caracteres especiales

/**
 * Función que establece la conexión con la base de datos
 * usando MySQLi (programación estructurada)
 *
 * @return mysqli|false Retorna el objeto de conexión o false si falla
 */
function conectarDB() {
    // Crear conexión usando mysqli_connect
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Verificar si la conexión falló
    if (!$conexion) {
        // Mostrar error detallado (solo en desarrollo)
        die("Error de conexión: " . mysqli_connect_error());
    }

    // Establecer charset para evitar problemas con caracteres especiales
    if (!mysqli_set_charset($conexion, DB_CHARSET)) {
        die("Error al establecer charset: " . mysqli_error($conexion));
    }

    // Retornar el objeto de conexión
    return $conexion;
}

/**
 * Función para cerrar la conexión con la base de datos
 *
 * @param mysqli $conexion Objeto de conexión a cerrar
 * @return void
 */
function cerrarConexion($conexion) {
    // Verificar que la conexión existe antes de cerrarla
    if ($conexion) {
        mysqli_close($conexion);
    }
}
?>
