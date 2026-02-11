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
    '../controllers/MesaController.php'
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
    require_once '../controllers/MesaController.php';

    if (!class_exists('Database')) {
        throw new Exception('Clase Database no encontrada');
    }

    if (!class_exists('MesaController')) {
        throw new Exception('Clase MesaController no encontrada');
    }

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Error al conectar con la base de datos');
    }

    $mesaController = new MesaController($db);
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($mesaController);
            break;
        case 'POST':
            handlePost($mesaController);
            break;
        case 'PUT':
            handlePut($mesaController);
            break;
        case 'DELETE':
            handleDelete($mesaController);
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
            case 'findByNumeroMesa':
                $numero_mesa = $_GET['numero_mesa'] ?? null;
                if ($numero_mesa) {
                    $result = $controller->findByNumeroMesa($numero_mesa);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Número de mesa requerido']);
                }
                break;
            case 'health':
                echo json_encode(['success' => true, 'message' => 'API de mesas funcionando correctamente']);
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
            case 'findByEstado':
                $estado = $_GET['estado'] ?? '';
                if ($estado) {
                    $result = $controller->findByEstado($estado);
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
            
            if ($action === 'create') {
                $numero_mesa = $input['numero_mesa'] ?? '';
                $asientos = $input['asientos'] ?? 4;
                $estado = $input['estado'] ?? 'Disponible';

                if (empty($numero_mesa)) {
                    throw new Exception('El número de mesa es requerido');
                }

                if (!is_numeric($numero_mesa) || $numero_mesa <= 0) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'El número de mesa debe ser un número válido mayor a 0'
                    ]);
                    exit;
                }

                if (!is_numeric($asientos) || $asientos <= 0) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'El número de asientos debe ser un número válido mayor a 0'
                    ]);
                    exit;
                }

                $result = $controller->createMesa($numero_mesa, $asientos, $estado);
            } elseif ($action === 'cambiarEstado') {
                $id = $input['id'] ?? '';
                $estado = $input['estado'] ?? '';

                if (empty($id)) {
                    throw new Exception('El ID de la mesa es requerido');
                }

                if (empty($estado)) {
                    throw new Exception('El estado es requerido');
                }

                $result = $controller->cambiarEstadoMesa($id, $estado);
            } else {
                throw new Exception('Acción no válida para POST');
            }
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
            $numero_mesa = $input['numero_mesa'] ?? '';
            $asientos = $input['asientos'] ?? 4;
            $estado = $input['estado'] ?? 'Disponible';

            if (empty($id) || empty($numero_mesa)) {
                throw new Exception('ID y número de mesa son requeridos');
            }

            if (!is_numeric($numero_mesa) || $numero_mesa <= 0) {
                throw new Exception('El número de mesa debe ser un número válido mayor a 0');
            }

            if (!is_numeric($asientos) || $asientos <= 0) {
                throw new Exception('El número de asientos debe ser un número válido mayor a 0');
            }

            $estadosValidos = ['Disponible', 'Ocupada', 'Reservada'];
            if (!in_array($estado, $estadosValidos)) {
                throw new Exception('Estado no válido. Use: Disponible, Ocupada o Reservada');
            }

            $result = $controller->updateMesa($id, $numero_mesa, $asientos, $estado);
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

            $result = $controller->deleteMesa($id);
            echo json_encode($result);
        } else {
            throw new Exception('Acción no válida para DELETE');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}