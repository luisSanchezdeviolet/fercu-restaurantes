<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit();
}

require_once '../../config/database.php';
require_once './model/Personal.php';

$conn = new Database();
$conn = $conn->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
        exit();
    }

    if(!is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
        exit();
    }

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID debe ser mayor que cero.']);
        exit();
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        echo json_encode(['success' => false, 'message' => 'ID no encontrado.']);
        exit();
    }

    if ($id == 1) {
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar al usuario administrador.']);
        exit();
    }
    
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propio usuario.']);
        exit();
    }

    $personal = new Personal($conn);
    $response = $personal->deleteEmployee($id);
    echo json_encode($response);
    exit();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}
