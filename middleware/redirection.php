<?php

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    session_start();
    $_SESSION['error'] = 'Acceso denegado. Por favor, inicia sesión.';
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_role'] !== 'Administrador') {
    session_start();
    $_SESSION['error'] = 'No tienes permisos para acceder a esta página.';
    header('Location: index.php');
    exit();
}
