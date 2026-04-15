<?php
class ProductoController
{
    private $conn;
    private $configuracion_id;

    public function __construct($db, $configuracion_id = null)
    {
        $this->conn = $db;
        $this->configuracion_id = $configuracion_id !== null ? (int)$configuracion_id : null;
    }

    public function findAll($page = 1, $limit = 10, $search = '', $categoria_id = null, $estado = '')
    {
        try {
            $page = max(1, intval($page));
            $limit = max(1, intval($limit));
            $offset = ($page - 1) * $limit;

            $whereConditions = [];
            $params = [];
            $whereConditions[] = "p.configuracion_id = ?";
            $params[] = $this->configuracion_id;

            if (!empty($search)) {
                $whereConditions[] = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($categoria_id && is_numeric($categoria_id)) {
                $whereConditions[] = "p.categoria_id = ?";
                $params[] = intval($categoria_id);
            }

            if (!empty($estado)) {
                $whereConditions[] = "p.estado = ?";
                $params[] = $estado;
            }

            $whereClause = '';
            if (!empty($whereConditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            }

            $countQuery = "
            SELECT COUNT(*) as total 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            $whereClause
        ";

            $query = "
            SELECT 
                p.*,
                c.nombre as categoria_nombre
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            $whereClause
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $offset
        ";

            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute($params);
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $productos,
                'total' => intval($totalRecords),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalRecords / $limit),
                'message' => 'Productos obtenidos correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ];
        }
    }

    public function searchProducts($filters = [])
    {
        try {
            $page = isset($filters['page']) ? max(1, intval($filters['page'])) : 1;
            $limit = isset($filters['limit']) ? max(1, intval($filters['limit'])) : 10;
            $search = isset($filters['search']) ? trim($filters['search']) : '';
            $categoria_id = isset($filters['categoria_id']) ? intval($filters['categoria_id']) : null;
            $estado = isset($filters['estado']) ? trim($filters['estado']) : '';
            $precio_min = isset($filters['precio_min']) ? floatval($filters['precio_min']) : null;
            $precio_max = isset($filters['precio_max']) ? floatval($filters['precio_max']) : null;

            return $this->findAll($page, $limit, $search, $categoria_id, $estado, $precio_min, $precio_max);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ];
        }
    }


    public function findAllExtended($page = 1, $limit = 10, $search = '', $categoria_id = null, $estado = '', $precio_min = null, $precio_max = null)
    {
        try {
            $page = max(1, intval($page));
            $limit = max(1, intval($limit));
            $offset = ($page - 1) * $limit;

            $whereConditions = [];
            $params = [];
            $whereConditions[] = "p.configuracion_id = ?";
            $params[] = $this->configuracion_id;

            if (!empty($search)) {
                $whereConditions[] = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($categoria_id && is_numeric($categoria_id)) {
                $whereConditions[] = "p.categoria_id = ?";
                $params[] = intval($categoria_id);
            }

            if (!empty($estado)) {
                $whereConditions[] = "p.estado = ?";
                $params[] = $estado;
            }

            if ($precio_min !== null && is_numeric($precio_min)) {
                $whereConditions[] = "p.precio >= ?";
                $params[] = floatval($precio_min);
            }

            if ($precio_max !== null && is_numeric($precio_max)) {
                $whereConditions[] = "p.precio <= ?";
                $params[] = floatval($precio_max);
            }

            $whereClause = '';
            if (!empty($whereConditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            }

            $countQuery = "
            SELECT COUNT(*) as total 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            $whereClause
        ";

            $query = "
            SELECT 
                p.*,
                c.nombre as categoria_nombre
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            $whereClause
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $offset
        ";

            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute($params);
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $productos,
                'total' => intval($totalRecords),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalRecords / $limit),
                'filters' => [
                    'search' => $search,
                    'categoria_id' => $categoria_id,
                    'estado' => $estado,
                    'precio_min' => $precio_min,
                    'precio_max' => $precio_max
                ],
                'message' => 'Productos obtenidos correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ];
        }
    }

    public function getProductStats()
    {
        try {
            $stats = [];

            $totalQuery = "SELECT COUNT(*) as total FROM productos WHERE configuracion_id = :configuracion_id";
            $totalStmt = $this->conn->prepare($totalQuery);
            $totalStmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $totalStmt->execute();
            $stats['total_productos'] = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $estadoQuery = "SELECT estado, COUNT(*) as cantidad FROM productos WHERE configuracion_id = :configuracion_id GROUP BY estado";
            $estadoStmt = $this->conn->prepare($estadoQuery);
            $estadoStmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $estadoStmt->execute();
            $stats['por_estado'] = $estadoStmt->fetchAll(PDO::FETCH_ASSOC);

            $categoriaQuery = "
            SELECT c.nombre as categoria, COUNT(p.id) as cantidad 
            FROM categorias c 
            LEFT JOIN productos p ON c.id = p.categoria_id 
            WHERE c.configuracion_id = :configuracion_id
            GROUP BY c.id, c.nombre
            ORDER BY cantidad DESC
        ";
            $categoriaStmt = $this->conn->prepare($categoriaQuery);
            $categoriaStmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $categoriaStmt->execute();
            $stats['por_categoria'] = $categoriaStmt->fetchAll(PDO::FETCH_ASSOC);

            $precioQuery = "SELECT AVG(precio) as precio_promedio, MIN(precio) as precio_min, MAX(precio) as precio_max FROM productos WHERE precio > 0 AND configuracion_id = :configuracion_id";
            $precioStmt = $this->conn->prepare($precioQuery);
            $precioStmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $precioStmt->execute();
            $precioStats = $precioStmt->fetch(PDO::FETCH_ASSOC);
            $stats['precios'] = [
                'promedio' => round($precioStats['precio_promedio'], 2),
                'minimo' => $precioStats['precio_min'],
                'maximo' => $precioStats['precio_max']
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }

    public function getTopProducts($limit = 10)
    {
        try {
            $query = "
            SELECT p.*, c.nombre as categoria_nombre, COUNT(dv.producto_id) as total_vendido
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN detalle_ventas dv ON p.id = dv.producto_id
            WHERE p.configuracion_id = :configuracion_id
            GROUP BY p.id
            ORDER BY total_vendido DESC, p.nombre ASC
            LIMIT :limit
        ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $productos,
                'message' => 'Productos más vendidos obtenidos correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener productos más vendidos: ' . $e->getMessage()
            ];
        }
    }

    public function changeStatus($id, $estado)
    {
        try {
            if (empty($id) || empty($estado)) {
                return ['success' => false, 'message' => 'ID y estado son obligatorios.'];
            }

            $validStates = ['activo', 'inactivo', 'agotado', 'descontinuado'];
            if (!in_array($estado, $validStates)) {
                return ['success' => false, 'message' => 'Estado no válido.'];
            }

            $stmt = $this->conn->prepare("UPDATE productos SET estado = :estado, updated_at = NOW() WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Estado actualizado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el estado.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function searchSuggestions($term, $limit = 5)
    {
        try {
            if (empty($term)) {
                return ['success' => false, 'message' => 'Término de búsqueda requerido.'];
            }

            $query = "
            SELECT DISTINCT nombre
            FROM productos 
            WHERE nombre LIKE :term 
            AND estado = 'activo'
            AND configuracion_id = :configuracion_id
            ORDER BY nombre ASC
            LIMIT :limit
        ";

            $stmt = $this->conn->prepare($query);
            $searchTerm = "%$term%";
            $stmt->bindParam(':term', $searchTerm);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return [
                'success' => true,
                'data' => $suggestions,
                'message' => 'Sugerencias obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener sugerencias: ' . $e->getMessage()
            ];
        }
    }

    public function getRelatedProducts($producto_id, $limit = 5)
    {
        try {
            if (empty($producto_id)) {
                return ['success' => false, 'message' => 'ID del producto requerido.'];
            }

            $categoriaQuery = "SELECT categoria_id FROM productos WHERE id = :id AND configuracion_id = :configuracion_id";
            $categoriaStmt = $this->conn->prepare($categoriaQuery);
            $categoriaStmt->bindParam(':id', $producto_id);
            $categoriaStmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $categoriaStmt->execute();
            $categoria = $categoriaStmt->fetch(PDO::FETCH_ASSOC);

            if (!$categoria) {
                return ['success' => false, 'message' => 'Producto no encontrado.'];
            }

            $query = "
            SELECT p.*, c.nombre as categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.categoria_id = :categoria_id 
            AND p.id != :producto_id
            AND p.estado = 'activo'
            AND p.configuracion_id = :configuracion_id
            ORDER BY RAND()
            LIMIT :limit
        ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_id', $categoria['categoria_id']);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $productos,
                'message' => 'Productos relacionados obtenidos correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener productos relacionados: ' . $e->getMessage()
            ];
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.id = :id AND p.configuracion_id = :configuracion_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                $ingredientesQuery = "SELECT pi.*, i.nombre as ingrediente_nombre, i.unidad 
                                    FROM producto_ingredientes pi 
                                    JOIN ingredientes i ON pi.ingrediente_id = i.id 
                                    WHERE pi.producto_id = :id
                                    AND i.configuracion_id = :configuracion_id";
                $ingredientesStmt = $this->conn->prepare($ingredientesQuery);
                $ingredientesStmt->bindParam(':id', $id);
                $ingredientesStmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
                $ingredientesStmt->execute();

                $producto['ingredientes'] = $ingredientesStmt->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'data' => $producto,
                    'message' => 'Producto encontrado'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar producto: ' . $e->getMessage()
            ];
        }
    }

    public function createProducto($nombre, $precio, $categoria_id, $descripcion = null, $imagen = null, $ingredientes = [], $estado)
    {
        try {
            if (empty($nombre)) {
                return ['success' => false, 'message' => 'El nombre del producto es obligatorio.'];
            }

            if (!is_numeric($precio) || $precio < 0) {
                return ['success' => false, 'message' => 'El precio debe ser un número válido mayor o igual a 0.'];
            }

            $check = $this->conn->prepare("SELECT COUNT(*) FROM productos WHERE nombre = :nombre AND configuracion_id = :configuracion_id");
            $check->bindParam(':nombre', $nombre);
            $check->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'El producto ya está registrado.'];
            }

            if (!empty($categoria_id)) {
                $checkCategoria = $this->conn->prepare("SELECT COUNT(*) FROM categorias WHERE id = :categoria_id AND configuracion_id = :configuracion_id");
                $checkCategoria->bindParam(':categoria_id', $categoria_id);
                $checkCategoria->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
                $checkCategoria->execute();
                if ($checkCategoria->fetchColumn() === 0) {
                    return ['success' => false, 'message' => 'La categoría especificada no existe.'];
                }
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                INSERT INTO productos (nombre, precio, categoria_id, descripcion, imagen, configuracion_id, created_at, updated_at, estado) 
                VALUES (:nombre, :precio, :categoria_id, :descripcion, :imagen, :configuracion_id, NOW(), NOW(), :estado)
            ");

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':imagen', $imagen);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);

            if ($stmt->execute()) {
                $producto_id = $this->conn->lastInsertId();

                if (!empty($ingredientes) && is_array($ingredientes)) {
                    $this->addIngredientesToProduct($producto_id, $ingredientes);
                }

                $this->conn->commit();

                return [
                    'success' => true,
                    'message' => 'Producto agregado correctamente.',
                    'id' => $producto_id
                ];
            } else {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Error al guardar el producto.'];
            }
        } catch (PDOException $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function updateProducto($id, $nombre, $precio, $categoria_id, $descripcion = null, $imagen = null, $ingredientes = [], $estado)
    {
        try {
            if (empty($id) || empty($nombre)) {
                return ['success' => false, 'message' => 'ID y nombre del producto son obligatorios.'];
            }

            if (!is_numeric($precio) || $precio < 0) {
                return ['success' => false, 'message' => 'El precio debe ser un número válido mayor o igual a 0.'];
            }

            $id = (int)$id;

            $checkProducto = $this->conn->prepare("SELECT COUNT(*) FROM productos WHERE id = :id AND configuracion_id = :configuracion_id");
            $checkProducto->bindParam(':id', $id);
            $checkProducto->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkProducto->execute();
            if ($checkProducto->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'El producto no existe.'];
            }

            if (!empty($categoria_id)) {
                $checkCategoria = $this->conn->prepare("SELECT COUNT(*) FROM categorias WHERE id = :categoria_id AND configuracion_id = :configuracion_id");
                $checkCategoria->bindParam(':categoria_id', $categoria_id);
                $checkCategoria->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
                $checkCategoria->execute();
                if ($checkCategoria->fetchColumn() === 0) {
                    return ['success' => false, 'message' => 'La categoría especificada no existe.'];
                }
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                UPDATE productos
                SET nombre = :nombre, precio = :precio, categoria_id = :categoria_id, 
                    descripcion = :descripcion, imagen = :imagen, updated_at = NOW(), estado = :estado
                WHERE id = :id AND configuracion_id = :configuracion_id
            ");

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':imagen', $imagen);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if (!empty($ingredientes) && is_array($ingredientes)) {
                    $deleteIngredientes = $this->conn->prepare("DELETE pi FROM producto_ingredientes pi INNER JOIN productos p ON pi.producto_id = p.id WHERE pi.producto_id = :producto_id AND p.configuracion_id = :configuracion_id");
                    $deleteIngredientes->bindParam(':producto_id', $id);
                    $deleteIngredientes->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
                    $deleteIngredientes->execute();

                    $this->addIngredientesToProduct($id, $ingredientes);
                }

                $this->conn->commit();
                return ['success' => true, 'message' => 'Producto actualizado correctamente.'];
            } else {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Error al actualizar el producto.'];
            }
        } catch (PDOException $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function deleteProducto($id)
    {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID del producto es obligatorio.'];
            }

            $this->conn->beginTransaction();

            $deleteIngredientes = $this->conn->prepare("DELETE pi FROM producto_ingredientes pi INNER JOIN productos p ON pi.producto_id = p.id WHERE pi.producto_id = :id AND p.configuracion_id = :configuracion_id");
            $deleteIngredientes->bindParam(':id', $id);
            $deleteIngredientes->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $deleteIngredientes->execute();

            $stmt = $this->conn->prepare("DELETE FROM productos WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->conn->commit();
                return ['success' => true, 'message' => 'Producto eliminado correctamente.'];
            } else {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Error al eliminar el producto.'];
            }
        } catch (PDOException $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function findByCategoria($categoria_id)
    {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.categoria_id = :categoria_id 
                     AND p.configuracion_id = :configuracion_id
                     ORDER BY p.nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $productos,
                'message' => 'Productos obtenidos correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener productos por categoría: ' . $e->getMessage()
            ];
        }
    }

    private function addIngredientesToProduct($producto_id, $ingredientes)
    {
        $checkIngrediente = $this->conn->prepare("SELECT COUNT(*) FROM ingredientes WHERE id = :ingrediente_id AND configuracion_id = :configuracion_id");
        $stmt = $this->conn->prepare("
            INSERT INTO producto_ingredientes (producto_id, ingrediente_id, cantidad) 
            VALUES (:producto_id, :ingrediente_id, :cantidad)
        ");

        foreach ($ingredientes as $ingrediente) {
            if (isset($ingrediente['ingrediente_id']) && isset($ingrediente['cantidad'])) {
                $checkIngrediente->bindParam(':ingrediente_id', $ingrediente['ingrediente_id']);
                $checkIngrediente->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
                $checkIngrediente->execute();
                if ((int)$checkIngrediente->fetchColumn() === 0) {
                    continue;
                }

                $stmt->bindParam(':producto_id', $producto_id);
                $stmt->bindParam(':ingrediente_id', $ingrediente['ingrediente_id']);
                $stmt->bindParam(':cantidad', $ingrediente['cantidad']);
                $stmt->execute();
            }
        }
    }

    public function addIngrediente($producto_id, $ingrediente_id, $cantidad = 1)
    {
        try {
            if (empty($producto_id) || empty($ingrediente_id)) {
                return ['success' => false, 'message' => 'ID del producto e ingrediente son obligatorios.'];
            }

            if (!is_numeric($cantidad) || $cantidad <= 0) {
                return ['success' => false, 'message' => 'La cantidad debe ser un número mayor a 0.'];
            }

            $checkProducto = $this->conn->prepare("SELECT COUNT(*) FROM productos WHERE id = :producto_id AND configuracion_id = :configuracion_id");
            $checkProducto->bindParam(':producto_id', $producto_id);
            $checkProducto->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkProducto->execute();
            if ((int)$checkProducto->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'Producto no válido para esta cuenta.'];
            }

            $checkIngrediente = $this->conn->prepare("SELECT COUNT(*) FROM ingredientes WHERE id = :ingrediente_id AND configuracion_id = :configuracion_id");
            $checkIngrediente->bindParam(':ingrediente_id', $ingrediente_id);
            $checkIngrediente->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkIngrediente->execute();
            if ((int)$checkIngrediente->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'Ingrediente no válido para esta cuenta.'];
            }

            $check = $this->conn->prepare("SELECT COUNT(*) FROM producto_ingredientes WHERE producto_id = :producto_id AND ingrediente_id = :ingrediente_id");
            $check->bindParam(':producto_id', $producto_id);
            $check->bindParam(':ingrediente_id', $ingrediente_id);
            $check->execute();

            if ($check->fetchColumn() > 0) {
                $stmt = $this->conn->prepare("UPDATE producto_ingredientes SET cantidad = :cantidad WHERE producto_id = :producto_id AND ingrediente_id = :ingrediente_id");
            } else {
                $stmt = $this->conn->prepare("INSERT INTO producto_ingredientes (producto_id, ingrediente_id, cantidad) VALUES (:producto_id, :ingrediente_id, :cantidad)");
            }

            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':ingrediente_id', $ingrediente_id);
            $stmt->bindParam(':cantidad', $cantidad);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Ingrediente agregado/actualizado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al agregar ingrediente.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function removeIngrediente($producto_id, $ingrediente_id)
    {
        try {
            if (empty($producto_id) || empty($ingrediente_id)) {
                return ['success' => false, 'message' => 'ID del producto e ingrediente son obligatorios.'];
            }

            $checkProducto = $this->conn->prepare("SELECT COUNT(*) FROM productos WHERE id = :producto_id AND configuracion_id = :configuracion_id");
            $checkProducto->bindParam(':producto_id', $producto_id);
            $checkProducto->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkProducto->execute();
            if ((int)$checkProducto->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'Producto no válido para esta cuenta.'];
            }

            $stmt = $this->conn->prepare("DELETE FROM producto_ingredientes WHERE producto_id = :producto_id AND ingrediente_id = :ingrediente_id");
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':ingrediente_id', $ingrediente_id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Ingrediente eliminado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar ingrediente.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function filterByEstado($estado) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM productos WHERE estado = :estado AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':estado', $estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function filterByCategoria($categoria_id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM productos WHERE categoria_id = :categoria_id AND estado = 'Disponible' AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function searchByName($name) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM productos WHERE nombre LIKE :name AND estado = 'Disponible' AND configuracion_id = :configuracion_id");
            $searchTerm = "%$name%";
            $stmt->bindParam(':name', $searchTerm);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }
}
