<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/session.php';

setupCors();
requireMethod('POST');

logoutUser();

jsonSuccess('Sesión cerrada correctamente');
