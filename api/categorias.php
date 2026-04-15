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
    '../controllers/CategoriaController.php'
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
    require_once '../controllers/CategoriaController.php';

    if (!class_exists('Database')) {
        throw new Exception('Clase Database no encontrada');
    }

    if (!class_exists('CategoriaController')) {
        throw new Exception('Clase CategoriaController no encontrada');
    }

    if (!class_exists('CloudinaryUploader')) {
        throw new Exception('Clase CloudinaryUploader no encontrada');
    }

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Error al conectar con la base de datos');
    }

    $categoriaController = new CategoriaController($db, $configuracion_id);
    $cloudinary = new CloudinaryUploader();

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($categoriaController);
            break;
        case 'POST':
            handlePost($categoriaController, $cloudinary);
            break;
        case 'PUT':
            handlePut($categoriaController, $cloudinary);
            break;
        case 'DELETE':
            handleDelete($categoriaController, $cloudinary);
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
            default:
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
            $estado = $_POST['estado'] ?? '';

            if (empty($nombre)) {
                throw new Exception('El nombre es requerido');
            }

            if (!isset($_POST['estado'])) {
                throw new Exception('El estado es requerido');
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

                $uploadResult = $cloudinary->uploadImageSigned($_FILES['imagen'], 'categorias');

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

            $result = $controller->createCategory($nombre, $estado, $imagenUrl);
        } else if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }

            $action = $input['action'] ?? 'create';
            $nombre = $input['nombre'] ?? '';
            $estado = $input['estado'] ?? '';

            if (empty($nombre)) {
                throw new Exception('El nombre es requerido');
            }

            if (!isset($input['estado']) || $input['estado'] === '') {
                throw new Exception('El estado es requerido');
            }

            $result = $controller->createCategory($nombre, $estado, null);
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
            $estado = $putData['estado'] ?? '';

            if (empty($id) || empty($nombre) || !isset($putData['estado'])) {
                throw new Exception('ID, nombre y estado son requeridos');
            }

            $imagenUrl = null;

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

                $uploadResult = $cloudinary->uploadImageSigned($fileInfo, 'categorias');

                if ($uploadResult['success']) {
                    $imagenUrl = $uploadResult['url'];
                } else {
                    fclose($tempFile);
                    throw new Exception('Error al subir imagen: ' . $uploadResult['message']);
                }

                fclose($tempFile);
            }

            $result = $controller->updateCategory($id, $nombre, $estado, $imagenUrl);
        } else if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }

            $id = $input['id'] ?? '';
            $nombre = $input['nombre'] ?? '';
            $estado = $input['estado'] ?? '';

            if (empty($id) || empty($nombre) || !isset($input['estado'])) {
                throw new Exception('ID, nombre y estado son requeridos');
            }

            $result = $controller->updateCategory($id, $nombre, $estado, null);
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

            $categoria = $controller->findById($id);
            if ($categoria['success'] && !empty($categoria['data']['imagen'])) {
                $imageUrl = $categoria['data']['imagen'];
                $publicId = extractPublicIdFromUrl($imageUrl);

                if ($publicId) {
                    $cloudinary->deleteImage($publicId);
                }
            }

            $result = $controller->deleteCategory($id);
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
