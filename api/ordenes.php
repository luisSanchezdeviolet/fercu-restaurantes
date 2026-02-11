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
    '../controllers/OrdenController.php',
    '../config/FilterConfig.php',
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
    require_once '../controllers/OrdenController.php';
    require_once '../config/FilterConfig.php';
    require_once '../controllers/MesaController.php';

    if (!class_exists('Database')) {
        throw new Exception('Clase Database no encontrada');
    }

    if (!class_exists('OrdenController')) {
        throw new Exception('Clase OrdenController no encontrada');
    }

    if (!class_exists('FilterConfig')) {
        throw new Exception('Clase FilterConfig no encontrada');
    }

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Error al conectar con la base de datos');
    }

    $ordenController = new OrdenController($db);
    $mesaController = new MesaController($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($ordenController);
            break;
        case 'POST':
            handlePost($ordenController, $mesaController);
            break;
        case 'PUT':
            handlePut($ordenController, $mesaController);
            break;
        case 'DELETE':
            handleDelete($ordenController, $mesaController);
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
                $estado = $_GET['estado'] ?? null;
                if ($estado) {
                    $result = $controller->filterByEstado($estado);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Estado requerido']);
                }
                break;

            case 'getOrdersByMesa':
                $numero_mesa = $_GET['numero_mesa'] ?? null;
                if ($numero_mesa) {
                    $result = $controller->getOrdersByMesa($numero_mesa);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Número de mesa requerido']);
                }
                break;

            case 'filterByPilar':
                $pilar = $_GET['pilar'] ?? null;
                $orderId = $_GET['order_id'] ?? null;
                if ($pilar) {
                    $result = $controller->filterByPilar($pilar, $orderId);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Pilar requerido']);
                }
                break;

            case 'filterBySubcategoria':
                $subcategoria = $_GET['subcategoria'] ?? null;
                $orderId = $_GET['order_id'] ?? null;
                if ($subcategoria) {
                    $result = $controller->filterBySubcategoria($subcategoria, $orderId);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Subcategoría requerida']);
                }
                break;

            case 'getFilterConfig':
                $result = $controller->getFilterConfig();
                echo json_encode($result);
                break;

            case 'getOrdersSummaryByPilar':
                $fechaInicio = $_GET['fecha_inicio'] ?? null;
                $fechaFin = $_GET['fecha_fin'] ?? null;
                $result = $controller->getOrdersSummaryByPilar($fechaInicio, $fechaFin);
                echo json_encode($result);
                break;

            case 'filterAllOrdersByPilar':
                $pilar = $_GET['pilar'] ?? null;
                $estado = $_GET['estado'] ?? null;
                $result = $controller->filterAllOrdersByPilar($pilar, $estado);
                echo json_encode($result);
                break;
            case 'getOrdenById':
                $id = $_GET['id'] ?? null;
                if ($id) {
                    $result = $controller->getOrdenById($id);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID requerido']);
                }
                break;
            case 'getOrderStatistics':
                $fechaInicio = $_GET['fecha_inicio'] ?? null;
                $fechaFin = $_GET['fecha_fin'] ?? null;
                $result = $controller->getOrderStatistics($fechaInicio, $fechaFin);
                echo json_encode($result);
                break;
            case 'getFinishedOrders':
                $response = $controller->getFinishedOrders();
                echo json_encode($response);
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

function handlePost($controller, $mesaController)
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

            switch ($action) {
                case 'create':
                    $user_id = $input['user_id'] ?? null;
                    $numero_mesa = $input['numero_mesa'] ?? null;
                    $productos = $input['productos'] ?? [];
                    $estado = $input['estado'] ?? 'Pendiente';
                    $notas = $input['notas'] ?? null;
                    $total = $input['total'] ?? 0;

                    if (empty($user_id)) {
                        throw new Exception('El user_id es requerido');
                    }

                    if (empty($numero_mesa)) {
                        throw new Exception('El número de mesa es requerido');
                    }

                    if (empty($productos) || !is_array($productos)) {
                        throw new Exception('Los productos son requeridos y deben ser un array');
                    }

                    foreach ($productos as $index => $producto) {
                        if (!isset($producto['id']) || !isset($producto['cantidad'])) {
                            throw new Exception("El producto en el índice $index debe tener 'id' y 'cantidad'");
                        }

                        if (!is_numeric($producto['cantidad']) || $producto['cantidad'] <= 0) {
                            throw new Exception("La cantidad del producto en el índice $index debe ser un número mayor a 0");
                        }
                    }

                    $result = $controller->createOrder($user_id, $numero_mesa, $productos, $estado, $notas, $total);
                    if ($result['success']) {
                        $mesaResult = $mesaController->cambiarEstadoMesa($numero_mesa, 'Ocupada');
                        if (!$mesaResult['success']) {
                            throw new Exception('Error al actualizar el estado de la mesa: ' . $mesaResult['message']);
                        }
                    }
                    break;

                case 'updateEstado':
                    $id = $input['id'] ?? null;
                    $nuevoEstado = $input['nuevo_estado'] ?? null;

                    if (empty($id)) {
                        throw new Exception('El ID de la orden es requerido');
                    }

                    if (empty($nuevoEstado)) {
                        throw new Exception('El nuevo estado es requerido');
                    }

                    $obtainMesa = $controller->findById($id);

                    error_log("DEBUG - obtainMesa completo: " . json_encode($obtainMesa));

                    if (!$obtainMesa['success']) {
                        throw new Exception('Error al obtener la orden: ' . $obtainMesa['message']);
                    }

                    $numero_mesa = $obtainMesa['data']['mesa_id'] ?? null;

                    if (empty($numero_mesa)) {
                        throw new Exception('Número de mesa no encontrado en la orden');
                    }
                    
                    if($nuevoEstado === 'Cancelado') {
                        $mesaResult = $mesaController->cambiarEstadoMesa($numero_mesa, 'Disponible');
                        if (!$mesaResult['success']) {
                            throw new Exception('Error al actualizar el estado de la mesa: ' . $mesaResult['message']);
                        }
                        $result = $controller->finalizarOrden($id, 'Pendiente', 'Cancelado');
                    } else if ($nuevoEstado === 'Pendiente') {
                        $mesaResult = $mesaController->cambiarEstadoMesa($numero_mesa, 'Ocupada');
                        if (!$mesaResult['success']) {
                            throw new Exception('Error al actualizar el estado de la mesa: ' . $mesaResult['message']);
                        }
                        $result = $controller->finalizarOrden($id, 'Pendiente', 'Pendiente');
                    }
                    break;
                case 'finalizarOrden':
                    $id = $input['id'] ?? null;
                    $metodoPago = $input['metodo_pago'] ?? null;
                    $nuevoEstado = $input['nuevo_estado'] ?? 'Finalizada';

                    if (empty($id)) {
                        throw new Exception('El ID de la orden es requerido');
                    }

                    $obtainMesa = $controller->findById($id);
                    if (!$obtainMesa['success']) {
                        throw new Exception('Error al obtener la orden: ' . $obtainMesa['message']);
                    }

                    $numero_mesa = $obtainMesa['data']['numero_mesa'] ?? null;

                    $result = $controller->finalizarOrden($id, $metodoPago, $nuevoEstado);
                    if ($result['success']) {
                        $mesaResult = $mesaController->cambiarEstadoMesa($numero_mesa, 'Disponible');
                        if (!$mesaResult['success']) {
                            throw new Exception('Error al actualizar el estado de la mesa: ' . $mesaResult['message']);
                        }
                    }
                    break;
                default:
                    throw new Exception('Acción no válida para POST');
            }
        } else {
            throw new Exception('Formato de datos no válido. Se requiere application/json');
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

function handlePut($controller, $mesaController)
{
    try {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }

            $action = $input['action'] ?? 'update';

            switch ($action) {
                case 'update':
                    $id = $input['id'] ?? null;
                    $user_id = $input['user_id'] ?? null;
                    $numero_mesa = $input['numero_mesa'] ?? null;
                    $estado = $input['estado'] ?? null;
                    $notas = $input['notas'] ?? null;
                    $total = $input['total'] ?? null;
                    $productos = $input['productos'] ?? null;

                    if (empty($id)) {
                        throw new Exception('El ID de la orden es requerido');
                    }

                    if ($user_id === null && $numero_mesa === null && $estado === null && $notas === null && $total === null && $productos === null) {
                        throw new Exception('Al menos un campo debe ser proporcionado para actualizar');
                    }

                    $result = $controller->updateOrder($id, $user_id, $numero_mesa, $estado, $notas, $total, $productos);
                    break;
                default:
                    throw new Exception('Acción no válida para PUT');
            }
        } else {
            throw new Exception('Formato de datos no válido para PUT. Se requiere application/json');
        }

        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDelete($controller, $mesaController)
{
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido: ' . json_last_error_msg());
        }

        $action = $input['action'] ?? 'delete';

        switch ($action) {
            case 'delete':
                $id = $input['id'] ?? null;

                if (empty($id)) {
                    throw new Exception('ID es requerido para eliminar');
                }

                $result = $controller->deleteOrder($id);
                break;

            default:
                throw new Exception('Acción no válida para DELETE');
        }

        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
