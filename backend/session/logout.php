<?php
/**
 * Logout - Cierre de sesión
 */

require_once __DIR__ . '/session_manager.php';

logoutUser();

header('Location: /PROYECTO_NEXO/index.html?logout=success');
exit();
