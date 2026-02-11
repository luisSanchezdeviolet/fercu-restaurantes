<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function getUserData() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'is_super_admin' => $_SESSION['is_super_admin'] ?? 0
        ];
    }
    return null;
}

function isSuperAdmin() {
    return isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] == 1;
}
?>