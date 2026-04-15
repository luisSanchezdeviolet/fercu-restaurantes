<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../layouts/session.php';
if (!isLoggedIn() || empty($_SESSION['configuracion_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}
$configuracion_id = (int)$_SESSION['configuracion_id'];

$requiredFiles = [
    '../config/database.php',
    '../controllers/CajaController.php'
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
    require_once '../controllers/CajaController.php';

    if (!class_exists('Database')) {
        throw new Exception('Clase Database no encontrada');
    }

    if (!class_exists('CajaController')) {
        throw new Exception('Clase CajaController no encontrada');
    }

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Error al conectar con la base de datos');
    }

    $cajaController = new CajaController($db, $configuracion_id);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($cajaController);
            break;
        case 'POST':
            handlePost($cajaController);
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
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID requerido']);
                }
                break;
                
            case 'findByDate':
                $date = $_GET['date'] ?? null;
                if ($date) {
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido. Use YYYY-MM-DD']);
                        break;
                    }
                    $result = $controller->findByDate($date);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Fecha requerida']);
                }
                break;
                
            case 'summary':
                $id = $_GET['id'] ?? null;
                if ($id) {
                    $result = $controller->getBoxSummary($id);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID de caja requerido']);
                }
                break;
                
            case 'health':
                echo json_encode(['success' => true, 'message' => 'Servidor funcionando correctamente']);
                break;
                
            case 'beforeClose':
                $result = $controller->beforeClose();
                echo json_encode($result);
                break;

            case 'getProductosAntesDeCerrarCaja':
                $result = $controller->getProductosAntesDeCerrarCaja();
                echo json_encode($result);
                break;
                
            default:
                http_response_code(400);
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

            $action = $input['action'] ?? null;

            if (!$action) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Acción requerida']);
                return;
            }

            switch ($action) {
                case 'closeBox':
                    $encargado = $input['encargado'] ?? null;
                    $updateInventory = $input['updateInventory'] ?? true;
                    
                    if (!$encargado) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Encargado requerido']);
                        return;
                    }
                    
                    // Validar que encargado sea string y no esté vacío
                    if (!is_string($encargado) || trim($encargado) === '') {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Nombre del encargado debe ser válido']);
                        return;
                    }
                    
                    // Validar que updateInventory sea boolean
                    if (!is_bool($updateInventory)) {
                        $updateInventory = filter_var($updateInventory, FILTER_VALIDATE_BOOLEAN);
                    }
                    
                    $result = $controller->closeBox(trim($encargado), $updateInventory);
                    
                    if ($result['success']) {
                        http_response_code(201);
                    } else {
                        http_response_code(400);
                    }
                    
                    echo json_encode($result);
                    break;
                    
                case 'closeBoxWithoutInventory':
                    $encargado = $input['encargado'] ?? null;
                    
                    if (!$encargado) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Encargado requerido']);
                        return;
                    }
                    
                    if (!is_string($encargado) || trim($encargado) === '') {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Nombre del encargado debe ser válido']);
                        return;
                    }
                    
                    $result = $controller->closeBox(trim($encargado), false);
                    
                    if ($result['success']) {
                        http_response_code(201);
                    } else {
                        http_response_code(400);
                    }
                    
                    echo json_encode($result);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Acción no válida para POST']);
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Formato de datos no válido. Se requiere application/json']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error en POST: ' . $e->getMessage()
        ]);
    }
}
