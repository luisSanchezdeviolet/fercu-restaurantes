<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
    '../config/cloudinary.php',
    '../controllers/ProductoController.php'
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
    require_once '../config/cloudinary.php';
    require_once '../controllers/ProductoController.php';

    if (!class_exists('Database')) {
        throw new Exception('Clase Database no encontrada');
    }

    if (!class_exists('ProductoController')) {
        throw new Exception('Clase ProductoController no encontrada');
    }

    if (!class_exists('CloudinaryUploader')) {
        throw new Exception('Clase CloudinaryUploader no encontrada');
    }

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Error al conectar con la base de datos');
    }

    $productoController = new ProductoController($db, $configuracion_id);
    $cloudinary = new CloudinaryUploader();

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($productoController);
            break;
        case 'POST':
            handlePost($productoController, $cloudinary);
            break;
        case 'PUT':
            handlePut($productoController, $cloudinary);
            break;
        case 'DELETE':
            handleDelete($productoController, $cloudinary);
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
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $categoria_id = isset($_GET['categoria_id']) && $_GET['categoria_id'] !== '' ? intval($_GET['categoria_id']) : null;
                $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
                $precio_min = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? floatval($_GET['precio_min']) : null;
                $precio_max = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? floatval($_GET['precio_max']) : null;

                $result = $controller->findAllExtended($page, $limit, $search, $categoria_id, $estado, $precio_min, $precio_max);
                echo json_encode($result);
                break;

            case 'search':
                $filters = [
                    'page' => $_GET['page'] ?? 1,
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'categoria_id' => $_GET['categoria_id'] ?? null,
                    'estado' => $_GET['estado'] ?? '',
                    'precio_min' => $_GET['precio_min'] ?? null,
                    'precio_max' => $_GET['precio_max'] ?? null
                ];

                $result = $controller->searchProducts($filters);
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

            case 'findByCategoria':
                $categoria_id = $_GET['categoria_id'] ?? null;
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;

                if ($categoria_id) {
                    $result = $controller->findAllExtended($page, $limit, '', $categoria_id);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID de categoría requerido']);
                }
                break;

            case 'health':
                echo json_encode([
                    'success' => true,
                    'message' => 'Servidor de productos funcionando correctamente',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'version' => '1.0.0'
                ]);
                break;

            case 'stats':
                $result = $controller->getProductStats();
                echo json_encode($result);
                break;

            case 'filterByEstado':
                $estado = $_GET['estado'] ?? '';
                if ($estado) {
                    $result = $controller->filterByEstado($estado);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Estado requerido']);
                }
                break;
            case 'filterByCategoria':
                $categoria_id = $_GET['categoria_id'] ?? null;
                if ($categoria_id) {
                    $result = $controller->filterByCategoria($categoria_id);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID de categoría requerido']);
                }
                break;
            case 'searchByName':
                $nombre = $_GET['nombre'] ?? '';
                if ($nombre) {
                    $result = $controller->searchByName($nombre);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Nombre requerido']);
                }
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

function handlePost($controller, $cloudinary)
{
    try {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isMultipart = strpos($contentType, 'multipart/form-data') !== false;
        $isJson = strpos($contentType, 'application/json') !== false;

        if ($isMultipart) {
            $action = $_POST['action'] ?? 'create';
            $nombre = $_POST['nombre'] ?? '';
            $precio = $_POST['precio'] ?? '';
            $categoria_id = $_POST['categoria_id'] ?? null;
            $descripcion = $_POST['descripcion'] ?? null;
            $ingredientes = $_POST['ingredientes'] ?? '[]';
            $estado = $_POST['estado'];

            if (empty($nombre)) {
                throw new Exception('El nombre es requerido');
            }

            if (empty($precio) || !is_numeric($precio)) {
                throw new Exception('El precio es requerido y debe ser numérico');
            }

            $ingredientesArray = [];
            if (!empty($ingredientes)) {
                $ingredientesArray = json_decode($ingredientes, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Formato de ingredientes inválido');
                }
            }

            $imagenUrl = null;

            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['imagen'];

                if (!file_exists($file['tmp_name'])) {
                    throw new Exception('Archivo temporal no encontrado');
                }

                if ($file['size'] === 0) {
                    throw new Exception('El archivo está vacío');
                }

                if ($file['size'] > 5 * 1024 * 1024) {
                    throw new Exception('El archivo es demasiado grande (máximo 5MB)');
                }

                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml', 'image/webp'];
                if (!in_array($file['type'], $allowedTypes)) {
                    throw new Exception('Tipo de archivo no permitido: ' . $file['type']);
                }

                if (!method_exists($cloudinary, 'uploadImageSigned')) {
                    throw new Exception('Método uploadImageSigned no disponible en CloudinaryUploader');
                }

                $uploadResult = $cloudinary->uploadImageSigned($_FILES['imagen'], 'productos');

                if ($uploadResult['success']) {
                    $imagenUrl = $uploadResult['url'];
                } else {
                    throw new Exception('Error al subir imagen: ' . $uploadResult['message']);
                }
            } else if (isset($_FILES['imagen'])) {
                $errorCodes = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño permitido por PHP',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño permitido por el formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Directorio temporal no encontrado',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
                    UPLOAD_ERR_EXTENSION => 'Extensión de PHP bloqueó la subida'
                ];

                $errorCode = $_FILES['imagen']['error'];
                $errorMessage = $errorCodes[$errorCode] ?? 'Error desconocido en la subida';
                throw new Exception('Error en archivo: ' . $errorMessage);
            }

            $result = $controller->createProducto($nombre, $precio, $categoria_id, $descripcion, $imagenUrl, $ingredientesArray, $estado);
        } else if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }

            $action = $input['action'] ?? 'create';
            $nombre = $input['nombre'] ?? '';
            $precio = $input['precio'] ?? '';
            $categoria_id = $input['categoria_id'] ?? null;
            $descripcion = $input['descripcion'] ?? null;
            $ingredientes = $input['ingredientes'] ?? [];
            $estado = $input['estado'];

            if (empty($nombre)) {
                throw new Exception('El nombre es requerido');
            }

            if (empty($precio) || !is_numeric($precio)) {
                throw new Exception('El precio es requerido y debe ser numérico');
            }

            switch ($action) {
                case 'create':
                    $result = $controller->createProducto($nombre, $precio, $categoria_id, $descripcion, null, $ingredientes, $estado);
                    break;
                case 'addIngrediente':
                    $producto_id = $input['producto_id'] ?? '';
                    $ingrediente_id = $input['ingrediente_id'] ?? '';
                    $cantidad = $input['cantidad'] ?? 1;

                    if (empty($producto_id) || empty($ingrediente_id)) {
                        throw new Exception('ID del producto e ingrediente son requeridos');
                    }

                    $result = $controller->addIngrediente($producto_id, $ingrediente_id, $cantidad);
                    break;
                case 'removeIngrediente':
                    $producto_id = $input['producto_id'] ?? '';
                    $ingrediente_id = $input['ingrediente_id'] ?? '';

                    if (empty($producto_id) || empty($ingrediente_id)) {
                        throw new Exception('ID del producto e ingrediente son requeridos');
                    }

                    $result = $controller->removeIngrediente($producto_id, $ingrediente_id);
                    break;
                default:
                    $result = $controller->createProducto($nombre, $precio, $categoria_id, $descripcion, null, $ingredientes);
                    break;
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

function handlePut($controller, $cloudinary)
{
    try {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isMultipart = strpos($contentType, 'multipart/form-data') !== false;
        $isJson = strpos($contentType, 'application/json') !== false;

        if ($isMultipart) {
            preg_match('/boundary=(.*)$/', $contentType, $matches);
            $boundary = $matches[1] ?? '';

            if (empty($boundary)) {
                throw new Exception('Boundary no encontrado en multipart data');
            }

            $putData = parseMultipartData($boundary);
            $id = $putData['id'] ?? '';
            $nombre = $putData['nombre'] ?? '';
            $precio = $putData['precio'] ?? '';
            $categoria_id = $putData['categoria_id'] ?? null;
            $descripcion = $putData['descripcion'] ?? null;
            $ingredientes = $putData['ingredientes'] ?? '[]';
            $estado = $putData['estado'];

            if (empty($id) || empty($nombre) || empty($precio)) {
                throw new Exception('ID, nombre y precio son requeridos');
            }

            if (!is_numeric($precio)) {
                throw new Exception('El precio debe ser numérico');
            }

            $ingredientesArray = [];
            if (!empty($ingredientes)) {
                $ingredientesArray = json_decode($ingredientes, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Formato de ingredientes inválido');
                }
            }

            $imagenUrl = null;
            $mantenerImagenActual = true;

            if (isset($putData['imagen_file']) && !empty($putData['imagen_file'])) {
                $tempFile = tmpfile();
                if (!$tempFile) {
                    throw new Exception('No se pudo crear archivo temporal');
                }

                fwrite($tempFile, $putData['imagen_file']);
                $tempPath = stream_get_meta_data($tempFile)['uri'];

                $fileInfo = [
                    'tmp_name' => $tempPath,
                    'type' => $putData['imagen_type'] ?? 'image/jpeg',
                    'size' => strlen($putData['imagen_file']),
                    'error' => UPLOAD_ERR_OK
                ];

                $uploadResult = $cloudinary->uploadImageSigned($fileInfo, 'productos');

                if ($uploadResult['success']) {
                    $imagenUrl = $uploadResult['url'];
                    $mantenerImagenActual = false;
                } else {
                    fclose($tempFile);
                    throw new Exception('Error al subir imagen: ' . $uploadResult['message']);
                }

                fclose($tempFile);
            }

            if ($mantenerImagenActual) {
                $productoActual = $controller->findById($id);
                if ($productoActual['success'] && isset($productoActual['data']['imagen'])) {
                    $imagenUrl = $productoActual['data']['imagen'];
                }
            }

            $result = $controller->updateProducto($id, $nombre, $precio, $categoria_id, $descripcion, $imagenUrl, $ingredientesArray, $estado);
        } else if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }

            $id = $input['id'] ?? '';
            $nombre = $input['nombre'] ?? '';
            $precio = $input['precio'] ?? '';
            $categoria_id = $input['categoria_id'] ?? null;
            $descripcion = $input['descripcion'] ?? null;
            $ingredientes = $input['ingredientes'] ?? [];
            $estado = $input['estado'];

            if (empty($id) || empty($nombre) || empty($precio)) {
                throw new Exception('ID, nombre y precio son requeridos');
            }

            if (!is_numeric($precio)) {
                throw new Exception('El precio debe ser numérico');
            }

            $imagenUrl = null;
            $productoActual = $controller->findById($id);
            if ($productoActual['success'] && isset($productoActual['data']['imagen'])) {
                $imagenUrl = $productoActual['data']['imagen'];
            }

            $result = $controller->updateProducto($id, $nombre, $precio, $categoria_id, $descripcion, $imagenUrl, $ingredientes, $estado);
        } else {
            throw new Exception('Formato de datos no válido para PUT');
        }

        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDelete($controller, $cloudinary)
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

            $producto = $controller->findById($id);
            if ($producto['success'] && !empty($producto['data']['imagen'])) {
                $imageUrl = $producto['data']['imagen'];
                $publicId = extractPublicIdFromUrl($imageUrl);

                if ($publicId) {
                    $cloudinary->deleteImage($publicId);
                }
            }

            $result = $controller->deleteProducto($id);
            echo json_encode($result);
        } else {
            throw new Exception('Acción no válida para DELETE');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function parseMultipartData($boundary)
{
    try {
        $data = [];
        $input = file_get_contents('php://input');

        $blocks = preg_split("/-+$boundary/", $input);
        array_pop($blocks);

        foreach ($blocks as $block) {
            if (empty($block)) continue;

            $parts = preg_split("/\r?\n\r?\n/", $block, 2);
            if (count($parts) < 2) continue;

            $header = $parts[0];
            $content = $parts[1];

            if (preg_match('/name="([^"]*)"/', $header, $matches)) {
                $name = $matches[1];

                if (strpos($header, 'filename=') !== false) {
                    if (preg_match('/Content-Type: (.*)/', $header, $typeMatches)) {
                        $data[$name . '_type'] = trim($typeMatches[1]);
                    }
                    $data[$name . '_file'] = $content;
                } else {
                    $data[$name] = $content;
                }
            }
        }

        return $data;
    } catch (Exception $e) {
        throw $e;
    }
}

function extractPublicIdFromUrl($url)
{
    $pattern = '/\/([^\/]+\/[^\/]+)\.(jpg|jpeg|png|gif|webp|svg)$/i';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return null;
}
