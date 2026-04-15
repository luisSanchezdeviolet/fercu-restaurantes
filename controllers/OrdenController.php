<?php
class OrdenController
{
    private $conn;
    private $configuracion_id;

    public function __construct($db, $configuracion_id = null)
    {
        $this->conn = $db;
        $this->configuracion_id = $configuracion_id !== null ? (int)$configuracion_id : null;
    }

    public function findAll()
    {
        try {
            $query = "SELECT o.*, u.nombre as mesero_nombre, m.numero_mesa as mesa_numero 
                     FROM ordenes o 
                     LEFT JOIN usuarios u ON o.user_id = u.id 
                     LEFT JOIN mesas m ON o.numero_mesa = m.id 
                     WHERE o.configuracion_id = :configuracion_id
                     ORDER BY o.id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordenes as &$orden) {
                $orden['productos'] = $this->getOrderProducts($orden['id']);
            }

            return [
                'success' => true,
                'data' => $ordenes,
                'message' => 'Órdenes obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener órdenes: ' . $e->getMessage()
            ];
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT o.*, u.nombre as mesero_nombre, m.numero_mesa as mesa_numero, o.numero_mesa as mesa_id
                     FROM ordenes o 
                     LEFT JOIN usuarios u ON o.user_id = u.id 
                     LEFT JOIN mesas m ON o.numero_mesa = m.id 
                     WHERE o.id = :id AND o.configuracion_id = :configuracion_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $orden = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($orden) {
                $orden['productos'] = $this->getOrderProducts($orden['id']);

                return [
                    'success' => true,
                    'data' => $orden,
                    'message' => 'Orden encontrada'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Orden no encontrada'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar orden: ' . $e->getMessage()
            ];
        }
    }

    public function createOrder($user_id, $numero_mesa, $productos, $estado = 'Pendiente', $notas = null, $total = 0)
    {
        try {
            if (empty($user_id) || empty($numero_mesa) || empty($productos)) {
                return ['success' => false, 'message' => 'Usuario, mesa y productos son obligatorios.'];
            }

            $checkUser = $this->conn->prepare("SELECT COUNT(*) FROM usuarios WHERE id = :user_id AND configuracion_id = :configuracion_id");
            $checkUser->bindParam(':user_id', $user_id);
            $checkUser->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkUser->execute();
            if ($checkUser->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'El usuario no existe.'];
            }

            $checkMesa = $this->conn->prepare("SELECT COUNT(*) FROM mesas WHERE id = :numero_mesa AND configuracion_id = :configuracion_id");
            $checkMesa->bindParam(':numero_mesa', $numero_mesa);
            $checkMesa->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkMesa->execute();
            if ($checkMesa->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'La mesa no existe.'];
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                INSERT INTO ordenes (user_id, numero_mesa, estado, notas, total, configuracion_id) 
                VALUES (:user_id, :numero_mesa, :estado, :notas, :total, :configuracion_id)
            ");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':numero_mesa', $numero_mesa);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':notas', $notas);
            $stmt->bindParam(':total', $total);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $orderId = $this->conn->lastInsertId();

                $productInserted = $this->insertOrderProducts($orderId, $productos);

                if ($productInserted['success']) {
                    $this->conn->commit();

                    return [
                        'success' => true,
                        'message' => 'Orden creada correctamente.',
                        'id' => $orderId
                    ];
                } else {
                    $this->conn->rollback();
                    return $productInserted;
                }
            } else {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Error al guardar la orden.'];
            }
        } catch (PDOException $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function updateOrder($id, $user_id = null, $numero_mesa = null, $estado = null, $notas = null, $total = null, $productos = null)
    {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de la orden es obligatorio.'];
            }

            $id = (int)$id;

            $checkOrder = $this->conn->prepare("SELECT COUNT(*) FROM ordenes WHERE id = :id AND configuracion_id = :configuracion_id");
            $checkOrder->bindParam(':id', $id);
            $checkOrder->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkOrder->execute();
            if ($checkOrder->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'La orden no existe.'];
            }

            $this->conn->beginTransaction();

            $fields = [];
            $params = [':id' => $id];

            if ($user_id !== null) {
                $fields[] = "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            if ($numero_mesa !== null) {
                $fields[] = "numero_mesa = :numero_mesa";
                $params[':numero_mesa'] = $numero_mesa;
            }
            if ($estado !== null) {
                $fields[] = "estado = :estado";
                $params[':estado'] = $estado;
            }
            if ($notas !== null) {
                $fields[] = "notas = :notas";
                $params[':notas'] = $notas;
            }
            if ($total !== null) {
                $fields[] = "total = :total";
                $params[':total'] = $total;
            }

            if (!empty($fields)) {
                $query = "UPDATE ordenes SET " . implode(', ', $fields) . " WHERE id = :id AND configuracion_id = :configuracion_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

                foreach ($params as $param => $value) {
                    $stmt->bindValue($param, $value);
                }

                if (!$stmt->execute()) {
                    $this->conn->rollback();
                    return ['success' => false, 'message' => 'Error al actualizar la orden.'];
                }
            }

            if ($productos !== null && is_array($productos)) {
                $deleteStmt = $this->conn->prepare("DELETE FROM order_products WHERE order_id = :order_id");
                $deleteStmt->bindParam(':order_id', $id);

                if (!$deleteStmt->execute()) {
                    $this->conn->rollback();
                    return ['success' => false, 'message' => 'Error al eliminar productos existentes.'];
                }

                if (!empty($productos)) {
                    $productResult = $this->insertOrderProducts($id, $productos);
                    if (!$productResult['success']) {
                        $this->conn->rollback();
                        return $productResult;
                    }
                }
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Orden actualizada correctamente.'];
        } catch (PDOException $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function deleteOrder($id)
    {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de la orden es obligatorio.'];
            }

            $this->conn->beginTransaction();

            $stmtProducts = $this->conn->prepare("DELETE FROM order_products WHERE order_id = :id");
            $stmtProducts->bindParam(':id', $id);
            $stmtProducts->execute();

            $stmt = $this->conn->prepare("DELETE FROM ordenes WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->conn->commit();
                return ['success' => true, 'message' => 'Orden eliminada correctamente.'];
            } else {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Error al eliminar la orden.'];
            }
        } catch (PDOException $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function filterByEstado($estado)
    {
        try {
            $query = "SELECT o.*, u.nombre as mesero_nombre, m.numero_mesa as mesa_numero, o.created_at as fecha 
                     FROM ordenes o 
                     LEFT JOIN usuarios u ON o.user_id = u.id 
                     LEFT JOIN mesas m ON o.numero_mesa = m.id 
                     WHERE o.estado = :estado
                     AND o.configuracion_id = :configuracion_id
                     ORDER BY o.id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordenes as &$orden) {
                $orden['productos'] = $this->getOrderProducts($orden['id']);
            }

            return [
                'success' => true,
                'data' => $ordenes,
                'message' => 'Órdenes filtradas por estado obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al filtrar órdenes: ' . $e->getMessage()
            ];
        }
    }

    public function getOrdersByMesa($numero_mesa)
    {
        try {
            $query = "SELECT o.*, u.nombre as mesero_nombre, m.numero as mesa_numero 
                     FROM ordenes o 
                     LEFT JOIN usuarios u ON o.user_id = u.id 
                     LEFT JOIN mesas m ON o.numero_mesa = m.id 
                     WHERE o.numero_mesa = :numero_mesa
                     AND o.configuracion_id = :configuracion_id
                     ORDER BY o.id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':numero_mesa', $numero_mesa);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordenes as &$orden) {
                $orden['productos'] = $this->getOrderProducts($orden['id']);
            }

            return [
                'success' => true,
                'data' => $ordenes,
                'message' => 'Órdenes de la mesa obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener órdenes de la mesa: ' . $e->getMessage()
            ];
        }
    }

    private function getOrderProducts($orderId)
    {
        try {
            $query = "SELECT op.*, p.nombre, p.precio, p.imagen 
                     FROM order_products op 
                     LEFT JOIN productos p ON op.product_id = p.id 
                     WHERE op.order_id = :order_id
                     AND p.configuracion_id = :configuracion_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function insertOrderProducts($orderId, $productos)
    {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO order_products (order_id, product_id, cantidad) 
                VALUES (:order_id, :product_id, :cantidad)
            ");

            foreach ($productos as $producto) {
                $checkProduct = $this->conn->prepare("SELECT COUNT(*) FROM productos WHERE id = :product_id AND configuracion_id = :configuracion_id");
                $checkProduct->bindParam(':product_id', $producto['id']);
                $checkProduct->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
                $checkProduct->execute();
                if ($checkProduct->fetchColumn() === 0) {
                    return ['success' => false, 'message' => 'El producto con ID ' . $producto['id'] . ' no existe.'];
                }

                $stmt->bindParam(':order_id', $orderId);
                $stmt->bindParam(':product_id', $producto['id']);
                $stmt->bindParam(':cantidad', $producto['cantidad']);

                if (!$stmt->execute()) {
                    return ['success' => false, 'message' => 'Error al insertar producto en la orden.'];
                }
            }

            return ['success' => true, 'message' => 'Productos insertados correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al insertar productos: ' . $e->getMessage()];
        }
    }

    public function filterByPilar($pilar, $orderId = null)
    {
        try {
            $subcategorias = FilterConfig::getSubcategoriesByPilar($pilar);

            if (empty($subcategorias)) {
                return [
                    'success' => false,
                    'message' => "El pilar '{$pilar}' no existe en la configuración."
                ];
            }

            $placeholders = ':subcategoria' . implode(', :subcategoria', range(0, count($subcategorias) - 1));

            $baseQuery = "SELECT DISTINCT o.*, u.nombre as mesero_nombre, m.numero as mesa_numero 
                         FROM ordenes o 
                         LEFT JOIN usuarios u ON o.user_id = u.id 
                         LEFT JOIN mesas m ON o.numero_mesa = m.id 
                         INNER JOIN order_products op ON o.id = op.order_id
                         INNER JOIN productos p ON op.product_id = p.id 
                         WHERE p.subcategoria IN ($placeholders)
                         AND o.configuracion_id = :configuracion_id
                         AND p.configuracion_id = :configuracion_id";

            if ($orderId) {
                $baseQuery .= " AND o.id = :order_id";
            }

            $baseQuery .= " ORDER BY o.id DESC";

            $stmt = $this->conn->prepare($baseQuery);


            foreach ($subcategorias as $index => $subcategoria) {
                $stmt->bindValue(':subcategoria' . $index, $subcategoria);
            }


            if ($orderId) {
                $stmt->bindValue(':order_id', $orderId);
            }
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            $stmt->execute();
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordenes as &$orden) {
                $orden['productos'] = $this->getOrderProductsByPilar($orden['id'], $pilar);
            }

            return [
                'success' => true,
                'data' => $ordenes,
                'message' => "Órdenes filtradas por pilar '{$pilar}' obtenidas correctamente",
                'pilar' => $pilar,
                'subcategorias_incluidas' => $subcategorias
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al filtrar órdenes por pilar: ' . $e->getMessage()
            ];
        }
    }

    public function filterBySubcategoria($subcategoria, $orderId = null)
    {
        try {
            $baseQuery = "SELECT DISTINCT o.*, u.nombre as mesero_nombre, m.numero as mesa_numero 
                         FROM ordenes o 
                         LEFT JOIN usuarios u ON o.user_id = u.id 
                         LEFT JOIN mesas m ON o.numero_mesa = m.id 
                         INNER JOIN order_products op ON o.id = op.order_id
                         INNER JOIN productos p ON op.product_id = p.id 
                         WHERE p.subcategoria = :subcategoria
                         AND o.configuracion_id = :configuracion_id
                         AND p.configuracion_id = :configuracion_id";

            if ($orderId) {
                $baseQuery .= " AND o.id = :order_id";
            }

            $baseQuery .= " ORDER BY o.id DESC";

            $stmt = $this->conn->prepare($baseQuery);
            $stmt->bindParam(':subcategoria', $subcategoria);

            if ($orderId) {
                $stmt->bindValue(':order_id', $orderId);
            }
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            $stmt->execute();
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordenes as &$orden) {
                $orden['productos'] = $this->getOrderProductsBySubcategoria($orden['id'], $subcategoria);
            }

            $pilar = FilterConfig::findPilarBySubcategoria($subcategoria);

            return [
                'success' => true,
                'data' => $ordenes,
                'message' => "Órdenes filtradas por subcategoría '{$subcategoria}' obtenidas correctamente",
                'subcategoria' => $subcategoria,
                'pilar' => $pilar
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al filtrar órdenes por subcategoría: ' . $e->getMessage()
            ];
        }
    }

    private function getOrderProductsByPilar($orderId, $pilar)
    {
        try {
            $subcategorias = FilterConfig::getSubcategoriesByPilar($pilar);

            if (empty($subcategorias)) {
                return [];
            }

            $placeholders = ':subcategoria' . implode(', :subcategoria', range(0, count($subcategorias) - 1));

            $query = "SELECT op.*, p.nombre, p.precio, p.imagen, p.subcategoria 
                     FROM order_products op 
                     LEFT JOIN productos p ON op.product_id = p.id 
                     WHERE op.order_id = :order_id 
                     AND p.configuracion_id = :configuracion_id
                     AND p.subcategoria IN ($placeholders)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            foreach ($subcategorias as $index => $subcategoria) {
                $stmt->bindValue(':subcategoria' . $index, $subcategoria);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function getOrderProductsBySubcategoria($orderId, $subcategoria)
    {
        try {
            $query = "SELECT op.*, p.nombre, p.precio, p.imagen, p.subcategoria 
                     FROM order_products op 
                     LEFT JOIN productos p ON op.product_id = p.id 
                     WHERE op.order_id = :order_id 
                     AND p.configuracion_id = :configuracion_id
                     AND p.subcategoria = :subcategoria";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':subcategoria', $subcategoria);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getFilterConfig()
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'pilares' => FilterConfig::getAllPilares(),
                    'configuracion_completa' => FilterConfig::load()
                ],
                'message' => 'Configuración de filtros obtenida correctamente'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener configuración: ' . $e->getMessage()
            ];
        }
    }

    public function getOrdersSummaryByPilar($fechaInicio = null, $fechaFin = null)
    {
        try {
            $pilares = FilterConfig::getAllPilares();
            $resumen = [];

            foreach ($pilares as $pilar) {
                $resultado = $this->filterByPilar($pilar);

                if ($resultado['success']) {
                    $totalOrdenes = count($resultado['data']);
                    $totalProductos = 0;

                    foreach ($resultado['data'] as $orden) {
                        $totalProductos += count($orden['productos']);
                    }

                    $resumen[$pilar] = [
                        'total_ordenes' => $totalOrdenes,
                        'total_productos' => $totalProductos,
                        'subcategorias' => FilterConfig::getSubcategoriesByPilar($pilar)
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $resumen,
                'message' => 'Resumen por pilares obtenido correctamente'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar resumen: ' . $e->getMessage()
            ];
        }
    }

    public function filterAllOrdersByPilar($pilar, $estado)
    {
        try {
            $subcategorias = FilterConfig::getSubcategoriesByPilar($pilar);

            if (empty($subcategorias)) {
                return [
                    'success' => false,
                    'message' => "El pilar '{$pilar}' no existe en la configuración."
                ];
            }

            $placeholders = ':subcategoria' . implode(', :subcategoria', range(0, count($subcategorias) - 1));

            $query = "SELECT DISTINCT o.id, o.created_at as fecha, o.estado, o.total, 
                             o.numero_mesa, o.user_id, o.notas,
                             u.nombre as mesero_nombre, m.numero_mesa as mesa_numero
                      FROM ordenes o
                      LEFT JOIN usuarios u ON o.user_id = u.id 
                      LEFT JOIN mesas m ON o.numero_mesa = m.id
                      INNER JOIN order_products op ON o.id = op.order_id
                      INNER JOIN productos p ON op.product_id = p.id
                      INNER JOIN categorias c ON p.categoria_id = c.id
                      WHERE c.nombre IN ($placeholders)
                      AND o.estado = :estado
                      AND o.configuracion_id = :configuracion_id
                      AND p.configuracion_id = :configuracion_id
                      ORDER BY o.id DESC";

            $stmt = $this->conn->prepare($query);

            foreach ($subcategorias as $index => $subcategoria) {
                $stmt->bindValue(':subcategoria' . $index, $subcategoria);
            }

            $stmt->bindValue(':estado', $estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordenes as &$orden) {
                $orden['productos'] = $this->getOrderProductsByPilarWithCategories($orden['id'], $subcategorias);
            }

            return [
                'success' => true,
                'data' => $ordenes,
                'pilar' => $pilar,
                'subcategorias_incluidas' => $subcategorias,
                'total_ordenes' => count($ordenes),
                'message' => "Todas las órdenes con productos del pilar '{$pilar}' obtenidas correctamente"
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al filtrar órdenes por pilar: ' . $e->getMessage()
            ];
        }
    }

    private function getOrderProductsByPilarWithCategories($orderId, $categorias)
    {
        try {
            if (empty($categorias)) {
                return [];
            }

            $placeholders = ':categoria' . implode(', :categoria', range(0, count($categorias) - 1));

            $query = "SELECT op.*, p.nombre, p.precio, p.imagen, p.descripcion, c.nombre as categoria_nombre 
                     FROM order_products op 
                     INNER JOIN productos p ON op.product_id = p.id 
                     INNER JOIN categorias c ON p.categoria_id = c.id
                     WHERE op.order_id = :order_id 
                     AND p.configuracion_id = :configuracion_id
                     AND c.configuracion_id = :configuracion_id
                     AND c.nombre IN ($placeholders)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            foreach ($categorias as $index => $categoria) {
                $stmt->bindValue(':categoria' . $index, $categoria);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getOrdenById($id)
    {
        try {
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de orden requerido']);
                exit;
            }

            $ordenId = intval($_GET['id']);

            try {

                $stmt = $this->conn->prepare("
            SELECT 
                o.*,
                m.nombre as mesero_nombre,
                me.numero_mesa as mesa_numero
            FROM ordenes o
            LEFT JOIN usuarios m ON o.user_id = m.id
            LEFT JOIN mesas me ON o.numero_mesa = me.id
            WHERE o.id = ? AND o.configuracion_id = ?
        ");

                $stmt->execute([$ordenId, $this->configuracion_id]);
                $orden = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$orden) {
                    echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
                    exit;
                }

                $stmt = $this->conn->prepare("
            SELECT 
                p.*,
                op.cantidad,
                (p.precio * op.cantidad) as subtotal
            FROM order_products op
            INNER JOIN productos p ON op.product_id = p.id
            WHERE op.order_id = ? AND p.configuracion_id = ?
            ORDER BY p.nombre
        ");

                $stmt->execute([$ordenId, $this->configuracion_id]);
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $orden['productos'] = $productos;
                $orden['notas'] = htmlspecialchars($orden['notas'], ENT_QUOTES, 'UTF-8');

                if (empty($orden['total'])) {
                    $orden['total'] = array_sum(array_column($productos, 'subtotal'));
                }

                echo json_encode([
                    'success' => true,
                    'data' => $orden,
                    'message' => 'Orden obtenida exitosamente'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al obtener la orden: ' . $e->getMessage()
                ]);
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener la orden: ' . $e->getMessage()
            ];
        }
    }

    public function updateOrderEstado($id, $nuevo_estado)
    {
        try {
            if (empty($id) || empty($nuevo_estado)) {
                return ['success' => false, 'message' => 'ID de la orden y nuevo estado son obligatorios.'];
            }

            $stmt = $this->conn->prepare("UPDATE ordenes SET estado = :estado WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Estado de la orden actualizado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el estado de la orden.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function finalizarOrden($id, $metodoPago, $nuevoEstado)
    {
        try {
            if (empty($id) || empty($metodoPago) || empty($nuevoEstado)) {
                return ['success' => false, 'message' => 'ID de la orden, método de pago y nuevo estado son obligatorios.'];
            }

            $stmt = $this->conn->prepare("UPDATE ordenes SET estado = :estado, metodo_pago = :metodo_pago WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':metodo_pago', $metodoPago);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Orden finalizada correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al finalizar la orden.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function getOrderStatistics($fechaInicio = null, $fechaFin = null)
    {
        try {
            $whereClause = "";
            $params = [];

            if ($fechaInicio && $fechaFin) {
                $whereClause = "WHERE DATE(o.created_at) BETWEEN :fecha_inicio AND :fecha_fin AND o.configuracion_id = :configuracion_id";
                $params[':fecha_inicio'] = $fechaInicio;
                $params[':fecha_fin'] = $fechaFin;
            } elseif ($fechaInicio) {
                $whereClause = "WHERE DATE(o.created_at) >= :fecha_inicio AND o.configuracion_id = :configuracion_id";
                $params[':fecha_inicio'] = $fechaInicio;
            } elseif ($fechaFin) {
                $whereClause = "WHERE DATE(o.created_at) <= :fecha_fin AND o.configuracion_id = :configuracion_id";
                $params[':fecha_fin'] = $fechaFin;
            } else {
                $whereClause = "WHERE o.configuracion_id = :configuracion_id";
            }
            $params[':configuracion_id'] = $this->configuracion_id;

            $query = "SELECT 
                o.estado,
                COUNT(*) as total_ordenes,
                COALESCE(SUM(o.total), 0) as total_ventas
              FROM ordenes o 
              $whereClause
              GROUP BY o.estado
              ORDER BY total_ordenes DESC";

            $stmt = $this->conn->prepare($query);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();
            $estadisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $queryTotal = "SELECT 
                     COUNT(*) as total_ordenes,
                     COALESCE(SUM(total), 0) as total_ventas
                   FROM ordenes o 
                   $whereClause";

            $stmtTotal = $this->conn->prepare($queryTotal);

            foreach ($params as $param => $value) {
                $stmtTotal->bindValue($param, $value);
            }

            $stmtTotal->execute();
            $totales = $stmtTotal->fetch(PDO::FETCH_ASSOC);

            $queryPorDia = "SELECT 
                      DATE(o.created_at) as fecha,
                      COUNT(*) as total_ordenes,
                      COALESCE(SUM(o.total), 0) as total_ventas
                    FROM ordenes o 
                    WHERE DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    AND o.configuracion_id = :configuracion_id
                    GROUP BY DATE(o.created_at)
                    ORDER BY fecha DESC";

            $stmtPorDia = $this->conn->prepare($queryPorDia);
            $stmtPorDia->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtPorDia->execute();
            $estadisticasPorDia = $stmtPorDia->fetchAll(PDO::FETCH_ASSOC);

            $estadisticasFormateadas = [
                'pendiente' => 0,
                'en_preparacion' => 0,
                'listo_para_servir' => 0,
                'entregado' => 0,
                'cancelado' => 0
            ];

            $ventasFormateadas = [
                'pendiente' => 0,
                'en_preparacion' => 0,
                'listo_para_servir' => 0,
                'entregado' => 0,
                'cancelado' => 0
            ];

            $mapeoEstados = [
                'Pendiente' => 'pendiente',
                'En preparación' => 'en_preparacion',
                'Listo para servir' => 'listo_para_servir',
                'Entregado' => 'entregado',
                'Cancelado' => 'cancelado'
            ];

            foreach ($estadisticas as $stat) {
                $estadoBD = $stat['estado'];
                if (isset($mapeoEstados[$estadoBD])) {
                    $estadoFormateado = $mapeoEstados[$estadoBD];
                    $estadisticasFormateadas[$estadoFormateado] = (int)$stat['total_ordenes'];
                    $ventasFormateadas[$estadoFormateado] = (float)$stat['total_ventas'];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'estadisticas_por_estado' => $estadisticasFormateadas,
                    'ventas_por_estado' => $ventasFormateadas,
                    'estadisticas_originales' => $estadisticas,
                    'totales' => $totales,
                    'estadisticas_por_dia' => $estadisticasPorDia
                ],
                'message' => 'Estadísticas obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }

    public function getFinishedOrders()
    {
        try {
            $query = "SELECT o.*, u.nombre as mesero_nombre, m.numero_mesa as mesa_numero 
                 FROM ordenes o 
                 LEFT JOIN usuarios u ON o.user_id = u.id 
                 LEFT JOIN mesas m ON o.numero_mesa = m.id 
                 WHERE o.estado IN ('Entregado', 'Cancelado', 'Listo para servir', 'Completado') 
                 AND o.configuracion_id = :configuracion_id
                 ORDER BY o.id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordenes as &$orden) {
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM order_products WHERE order_id = :order_id");
                $stmt->bindParam(':order_id', $orden['id']);
                $stmt->execute();
                $orden['total_productos'] = $stmt->fetchColumn();
            }

            return [
                'success' => true,
                'data' => $ordenes,
                'message' => 'Órdenes finalizadas obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener órdenes finalizadas: ' . $e->getMessage()
            ];
        }
    }
}
