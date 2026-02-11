<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$requiredFiles = [
    '../config/database.php',
    '../controllers/InventarioController.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Archivo requerido no encontrado: $file"]);
        exit;
    }
}

try {
    require_once '../config/database.php';
    require_once '../controllers/InventarioController.php';

    if (!class_exists('Database')) {
        throw new Exception('Clase Database no encontrada');
    }

    if (!class_exists('InventarioController')) {
        throw new Exception('Clase InventarioController no encontrada');
    }

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Error al conectar con la base de datos');
    }

    $inventarioController = new InventarioController($db);
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($inventarioController);
            break;
        case 'POST':
            handlePost($inventarioController);
            break;
        case 'PUT':
            handlePut($inventarioController);
            break;
        case 'DELETE':
            handleDelete($inventarioController);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

function handleGet($controller)
{
    $action = $_GET['action'] ?? 'findAll';

    try {
        switch ($action) {
            case 'findAll':
                $result = $controller->findAll();
                echo json_encode($result);
                break;
            case 'findById':
                $id = $_GET['id'] ?? null;
                if ($id) {
                    $result = $controller->findById($id);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID requerido']);
                }
                break;
            case 'health':
                echo json_encode(['success' => true, 'message' => 'Servidor funcionando correctamente']);
                break;
            case 'filterByEstado':
                $estado = $_GET['estado'] ?? '';
                if ($estado) {
                    $result = $controller->filterByEstado($estado);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Estado requerido']);
                }
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error en GET: ' . $e->getMessage()]);
    }
}

function handlePost($controller)
{
    try {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }

            $action = $input['action'] ?? 'create';
            $nombre = $input['nombre'] ?? '';
            $cantidad = $input['cantidad'] ?? 0;
            $estado = $input['estado'] ?? '';
            $unidad = $input['unidadMedida'] ?? '';

            if (empty($nombre)) {
                throw new Exception('El nombre es requerido');
            }

            if (!is_numeric($cantidad) || $cantidad < 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'La cantidad debe ser un numero valido mayor o igual a 0'
                ]);
                exit;
            }

            if (empty($estado)) {
                throw new Exception('El estado es requerido');
            }

            if (empty($unidad)) {
                throw new Exception('La unidad de medida es requerida');
            }

            $result = $controller->createIngredient($nombre, $cantidad, $estado, $unidad);
        } else {
            throw new Exception('Formato de datos no válido. Content-Type: ' . $contentType);
        }

        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function handlePut($controller)
{
    try {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }

            $id = $input['id'] ?? '';
            $nombre = $input['nombre'] ?? '';
            $cantidad = $input['cantidad'] ?? 0;
            $estado = $input['estado'] ?? '';
            $unidad = $input['unidadMedida'] ?? '';

            if (empty($id) || empty($nombre)) {
                throw new Exception('ID y nombre son requeridos');
            }

            if (!is_numeric($cantidad) || $cantidad < 0) {
                throw new Exception('La cantidad debe ser un número válido mayor o igual a 0');
            }

            if (empty($estado)) {
                throw new Exception('El estado es requerido');
            }

            if (empty($unidad)) {
                throw new Exception('La unidad de medida es requerida');
            }

            $result = $controller->updateIngredient($id, $nombre, $cantidad, $estado, $unidad);
        } else {
            throw new Exception('Formato de datos no válido para PUT');
        }

        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDelete($controller)
{
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido: ' . json_last_error_msg());
        }

        $action = $input['action'] ?? 'delete';

        if ($action === 'delete') {
            $id = $input['id'] ?? '';

            if (empty($id)) {
                throw new Exception('ID es requerido para eliminar');
            }

            $result = $controller->deleteIngredient($id);
            echo json_encode($result);
        } else {
            throw new Exception('Acción no válida para DELETE');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}