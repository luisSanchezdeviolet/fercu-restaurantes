<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Administrador') {
    exit(json_encode(['success' => false, 'message' => 'No autorizado.']));
}

require_once '../../config/database.php';
require_once './model/Personal.php';

$conn = new Database();
$conn = $conn->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';

    $personal = new Personal($conn);
    $response = $personal->updateEmployee($id, $name, $email, $role);
    echo json_encode($response);
    exit();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}
