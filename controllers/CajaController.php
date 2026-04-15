<?php
class CajaController
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
            $query = "SELECT c.*, 
                      (SELECT COUNT(*) FROM caja_productos cp WHERE cp.caja_id = c.id) as total_productos
                      FROM cajas c 
                      WHERE c.configuracion_id = :configuracion_id
                      ORDER BY c.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $cajas,
                'count' => count($cajas)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener cajas: ' . $e->getMessage()
            ];
        }
    }

    public function findById($id)
    {
        try {

            $query = "SELECT * FROM cajas WHERE id = :id AND configuracion_id = :configuracion_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $caja = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$caja) {
                return [
                    'success' => false,
                    'message' => 'Caja no encontrada'
                ];
            }


            $queryProductos = "SELECT cp.*, p.nombre as producto_nombre, p.descripcion
                              FROM caja_productos cp
                              INNER JOIN productos p ON cp.producto_id = p.id
                              WHERE cp.caja_id = :caja_id
                              AND p.configuracion_id = :configuracion_id
                              ORDER BY p.nombre";

            $stmtProductos = $this->conn->prepare($queryProductos);
            $stmtProductos->bindParam(':caja_id', $id);
            $stmtProductos->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtProductos->execute();

            $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

            $caja['productos'] = $productos;

            return [
                'success' => true,
                'data' => $caja
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener caja: ' . $e->getMessage()
            ];
        }
    }

    public function findByDate($date)
    {
        try {
            $query = "SELECT c.*, 
                      (SELECT COUNT(*) FROM caja_productos cp WHERE cp.caja_id = c.id) as total_productos
                      FROM cajas c 
                      WHERE DATE(c.fecha_cierre) = :date
                      AND c.configuracion_id = :configuracion_id
                      ORDER BY c.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $cajas,
                'count' => count($cajas)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener cajas por fecha: ' . $e->getMessage()
            ];
        }
    }

    public function closeBox($encargado, $updateInventory = true)
    {
        try {
            $this->conn->beginTransaction();


            $queryOrdenes = "SELECT o.id, o.total, o.metodo_pago 
                           FROM ordenes o 
                           WHERE o.estado IN ('Pendiente', 'Listo para servir', 'Entregado')
                           AND o.configuracion_id = :configuracion_id";

            $stmtOrdenes = $this->conn->prepare($queryOrdenes);
            $stmtOrdenes->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtOrdenes->execute();
            $ordenes = $stmtOrdenes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ordenes)) {
                $this->conn->rollBack();
                return [
                    'success' => false,
                    'message' => 'No hay órdenes para cerrar caja'
                ];
            }

            $ordenIds = array_column($ordenes, 'id');
            $totalCaja = array_sum(array_column($ordenes, 'total'));


            $queryProductos = "SELECT op.product_id, p.nombre, p.precio, SUM(op.cantidad) as total_cantidad
                             FROM order_products op
                             INNER JOIN productos p ON op.product_id = p.id
                             WHERE op.order_id IN (" . implode(',', array_fill(0, count($ordenIds), '?')) . ")
                             GROUP BY op.product_id, p.nombre, p.precio";

            $stmtProductos = $this->conn->prepare($queryProductos);
            $stmtProductos->execute($ordenIds);
            $productosVendidos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);


            $queryInsertCaja = "INSERT INTO cajas (encargado, fecha_cierre, total, estado_inventario, configuracion_id) 
                    VALUES (:encargado, CURDATE(), :total, :estado_inventario, :configuracion_id)";
            $stmtInsertCaja = $this->conn->prepare($queryInsertCaja);
            $stmtInsertCaja->bindParam(':encargado', $encargado);
            $stmtInsertCaja->bindParam(':total', $totalCaja);
            $estadoInventario = $updateInventory ? 'Si' : 'No';
            $stmtInsertCaja->bindParam(':estado_inventario', $estadoInventario);
            $stmtInsertCaja->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtInsertCaja->execute();

            $cajaId = $this->conn->lastInsertId();

            $queryInsertProducto = "INSERT INTO caja_productos (caja_id, producto_id, cantidad, precio, total) 
                                  VALUES (:caja_id, :producto_id, :cantidad, :precio, :total)";
            $stmtInsertProducto = $this->conn->prepare($queryInsertProducto);

            foreach ($productosVendidos as $producto) {
                $totalProducto = $producto['precio'] * $producto['total_cantidad'];

                $stmtInsertProducto->bindParam(':caja_id', $cajaId);
                $stmtInsertProducto->bindParam(':producto_id', $producto['product_id']);
                $stmtInsertProducto->bindParam(':cantidad', $producto['total_cantidad']);
                $stmtInsertProducto->bindParam(':precio', $producto['precio']);
                $stmtInsertProducto->bindParam(':total', $totalProducto);
                $stmtInsertProducto->execute();
            }


            if ($updateInventory) {
                foreach ($productosVendidos as $producto) {
                    $this->updateIngredientInventory($producto['product_id'], $producto['total_cantidad']);
                }
            }


            $queryUpdateOrdenes = "UPDATE ordenes SET estado = 'Completado', updated_at = CURRENT_TIMESTAMP 
                                 WHERE id IN (" . implode(',', array_fill(0, count($ordenIds), '?')) . ")";
            $stmtUpdateOrdenes = $this->conn->prepare($queryUpdateOrdenes);
            $stmtUpdateOrdenes->execute($ordenIds);

            $queryUpdateMesas = "UPDATE mesas SET estado = 'Disponible' WHERE estado IN ('Ocupada', 'Reservada') AND configuracion_id = :configuracion_id";
            $stmtUpdateMesas = $this->conn->prepare($queryUpdateMesas);
            $stmtUpdateMesas->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtUpdateMesas->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Caja cerrada exitosamente',
                'data' => [
                    'caja_id' => $cajaId,
                    'total' => $totalCaja,
                    'ordenes_procesadas' => count($ordenes),
                    'productos_vendidos' => count($productosVendidos),
                    'inventario_actualizado' => $updateInventory
                ]
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Error al cerrar caja: ' . $e->getMessage()
            ];
        }
    }

    private function updateIngredientInventory($productId, $quantity)
    {
        try {

            $queryIngredientes = "SELECT pi.ingrediente_id, pi.cantidad, i.nombre 
                                FROM producto_ingredientes pi
                                INNER JOIN ingredientes i ON pi.ingrediente_id = i.id
                                WHERE pi.producto_id = :product_id
                                AND i.configuracion_id = :configuracion_id";

            $stmtIngredientes = $this->conn->prepare($queryIngredientes);
            $stmtIngredientes->bindParam(':product_id', $productId);
            $stmtIngredientes->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtIngredientes->execute();
            $ingredientes = $stmtIngredientes->fetchAll(PDO::FETCH_ASSOC);


            $queryUpdateIngrediente = "UPDATE ingredientes 
                                     SET cantidad = GREATEST(0, cantidad - :cantidad_usar),
                                         estado = CASE 
                                             WHEN (cantidad - :cantidad_usar) <= 0 THEN 'Agotado' 
                                             ELSE 'Disponible' 
                                         END,
                                         updatedAt = CURRENT_TIMESTAMP
                                     WHERE id = :ingrediente_id
                                     AND configuracion_id = :configuracion_id";

            $stmtUpdateIngrediente = $this->conn->prepare($queryUpdateIngrediente);

            foreach ($ingredientes as $ingrediente) {
                $cantidadUsar = $ingrediente['cantidad'] * $quantity;

                $stmtUpdateIngrediente->bindParam(':cantidad_usar', $cantidadUsar);
                $stmtUpdateIngrediente->bindParam(':ingrediente_id', $ingrediente['ingrediente_id']);
                $stmtUpdateIngrediente->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
                $stmtUpdateIngrediente->execute();
            }
        } catch (Exception $e) {
            throw new Exception('Error actualizando inventario: ' . $e->getMessage());
        }
    }

    public function beforeClose()
    {
        try {
            $queryOrdenes = "SELECT o.id, o.total, o.metodo_pago, o.numero_mesa, o.estado, o.created_at,
                           u.nombre as usuario_nombre
                           FROM ordenes o 
                           LEFT JOIN usuarios u ON o.user_id = u.id
                           WHERE o.estado IN ('Pendiente', 'Listo para servir', 'Entregado')
                           AND o.configuracion_id = :configuracion_id
                           ORDER BY o.created_at DESC";

            $stmtOrdenes = $this->conn->prepare($queryOrdenes);
            $stmtOrdenes->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtOrdenes->execute();
            $ordenes = $stmtOrdenes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ordenes)) {
                return [
                    'success' => true,
                    'message' => 'No hay órdenes pendientes para cerrar',
                    'data' => [
                        'resumen' => [
                            'total_ordenes' => 0,
                            'total_ventas' => 0,
                            'promedio_por_orden' => 0,
                            'productos_vendidos' => 0,
                            'tipos_productos' => 0
                        ],
                        'ordenes' => [],
                        'productos' => [],
                        'metodos_pago' => []
                    ]
                ];
            }

            $ordenIds = array_column($ordenes, 'id');
            $totalVentas = array_sum(array_column($ordenes, 'total'));

            $queryProductos = "SELECT op.product_id, p.nombre, p.precio, 
                             SUM(op.cantidad) as total_cantidad,
                             SUM(op.cantidad * p.precio) as total_producto,
                             c.nombre as categoria_nombre
                             FROM order_products op
                             INNER JOIN productos p ON op.product_id = p.id
                             LEFT JOIN categorias c ON p.categoria_id = c.id
                             WHERE op.order_id IN (" . implode(',', array_fill(0, count($ordenIds), '?')) . ")
                             GROUP BY op.product_id, p.nombre, p.precio, c.nombre
                             ORDER BY total_cantidad DESC";

            $stmtProductos = $this->conn->prepare($queryProductos);
            $stmtProductos->execute($ordenIds);
            $productosVendidos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

            $metodosPago = [];
            foreach ($ordenes as $orden) {
                $metodo = $orden['metodo_pago'] ?? 'Pendiente';
                if (!isset($metodosPago[$metodo])) {
                    $metodosPago[$metodo] = [
                        'metodo' => $metodo,
                        'cantidad_ordenes' => 0,
                        'total' => 0
                    ];
                }
                $metodosPago[$metodo]['cantidad_ordenes']++;
                $metodosPago[$metodo]['total'] += $orden['total'];
            }

            $estadosPorOrden = [];
            foreach ($ordenes as $orden) {
                $estado = $orden['estado'];
                if (!isset($estadosPorOrden[$estado])) {
                    $estadosPorOrden[$estado] = [
                        'estado' => $estado,
                        'cantidad' => 0,
                        'total' => 0
                    ];
                }
                $estadosPorOrden[$estado]['cantidad']++;
                $estadosPorOrden[$estado]['total'] += $orden['total'];
            }

            $topProductos = array_slice($productosVendidos, 0, 5);

            $totalProductosVendidos = array_sum(array_column($productosVendidos, 'total_cantidad'));

            $resumen = [
                'total_ordenes' => count($ordenes),
                'total_ventas' => round($totalVentas, 2),
                'promedio_por_orden' => count($ordenes) > 0 ? round($totalVentas / count($ordenes), 2) : 0,
                'productos_vendidos' => $totalProductosVendidos,
                'tipos_productos' => count($productosVendidos),
                'fecha_consulta' => date('Y-m-d H:i:s')
            ];

            return [
                'success' => true,
                'message' => 'Resumen de ventas antes del cierre obtenido exitosamente',
                'data' => [
                    'resumen' => $resumen,
                    'ordenes' => $ordenes,
                    'productos' => $productosVendidos,
                    'top_productos' => $topProductos,
                    'metodos_pago' => array_values($metodosPago),
                    'estados_ordenes' => array_values($estadosPorOrden)
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener resumen previo al cierre: ' . $e->getMessage()
            ];
        }
    }

    public function getBoxSummary($cajaId)
    {
        try {
            $caja = $this->findById($cajaId);

            if (!$caja['success']) {
                return $caja;
            }

            $cajaData = $caja['data'];


            $totalProductos = 0;
            $categorias = [];

            foreach ($cajaData['productos'] as $producto) {
                $totalProductos += $producto['cantidad'];
                // if (!isset($categorias[$producto['categoria_nombre']])) {
                //     $categorias[$producto['categoria_nombre']] = 0;
                // }
                // $categorias[$producto['categoria_nombre']] += $producto['cantidad'];
            }

            $resumen = [
                'caja_info' => [
                    'id' => $cajaData['id'],
                    'encargado' => $cajaData['encargado'],
                    'fecha_cierre' => $cajaData['fecha_cierre'],
                    'total' => $cajaData['total'],
                    'created_at' => $cajaData['created_at']
                ],
                'estadisticas' => [
                    'total_productos_vendidos' => $totalProductos,
                    'tipos_productos' => count($cajaData['productos']),
                    'promedio_por_producto' => count($cajaData['productos']) > 0 ?
                        round($cajaData['total'] / count($cajaData['productos']), 2) : 0
                ],
                'productos' => $cajaData['productos']
            ];

            return [
                'success' => true,
                'data' => $resumen
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ];
        }
    }

    public function getProductosAntesDeCerrarCaja()
    {
        try {
            $queryOrdenes = "SELECT id FROM ordenes WHERE estado IN ('Pendiente', 'Listo para servir', 'Entregado') AND configuracion_id = :configuracion_id";
            $stmtOrdenes = $this->conn->prepare($queryOrdenes);
            $stmtOrdenes->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmtOrdenes->execute();
            $ordenes = $stmtOrdenes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ordenes)) {
                return [
                    'success' => true,
                    'data' => [],
                    'total_vendido' => 0,
                    'message' => 'No hay productos vendidos pendientes de cierre'
                ];
            }

            $ordenIds = array_column($ordenes, 'id');

            $queryProductos = "SELECT 
                p.nombre, 
                SUM(op.cantidad) as cantidad_total, 
                p.precio, 
                SUM(op.cantidad * p.precio) as subtotal
            FROM order_products op
            INNER JOIN productos p ON op.product_id = p.id
            WHERE op.order_id IN (" . implode(',', array_fill(0, count($ordenIds), '?')) . ")
            GROUP BY op.product_id, p.nombre, p.precio
            ORDER BY cantidad_total DESC";

            $stmtProductos = $this->conn->prepare($queryProductos);
            $stmtProductos->execute($ordenIds);
            $productosVendidos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

            $totalVendido = array_sum(array_column($productosVendidos, 'subtotal'));

            return [
                'success' => true,
                'data' => $productosVendidos,
                'total_vendido' => $totalVendido
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener productos vendidos: ' . $e->getMessage()
            ];
        }
    }
}
