<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
$conn = new Database();
$conn = $conn->getConnection();
$id = $_GET['id'] ?? null;

if ($id === null) {
    echo json_encode(['error' => 'ID not provided']);
    exit;
}

if (!isset($conn) || $conn === null) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Employee not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'General error: ' . $e->getMessage()]);
}
?>