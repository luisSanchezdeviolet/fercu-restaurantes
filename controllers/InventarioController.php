<?php
class InventarioController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function findAll()
    {
        try {
            $query = "SELECT * FROM ingredientes ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $inventario,
                'message' => 'Inventario obtenido correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener inventario: ' . $e->getMessage()
            ];
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT * FROM ingredientes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                return [
                    'success' => true,
                    'data' => $item,
                    'message' => 'Item encontrado'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Item no encontrado'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar item: ' . $e->getMessage()
            ];
        }
    }

    public function createIngredient($nombre, $cantidad, $estado, $unidad)
    {
        try {
            if (empty($nombre) || empty($estado) || empty($unidad)) {
                return ['success' => false, 'message' => 'Nombre, estado y unidad de medida son obligatorios.'];
            }

            if (!is_numeric($cantidad) || $cantidad < 0) {
                return ['success' => false, 'message' => 'La cantidad debe ser un número válido mayor o igual a 0.'];
            }

            $check = $this->conn->prepare("SELECT COUNT(*) FROM ingredientes WHERE nombre = :nombre");
            $check->bindParam(':nombre', $nombre);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'El item ya está registrado.'];
            }

            $stmt = $this->conn->prepare("
                INSERT INTO ingredientes (nombre, cantidad, estado, unidad, createdAt, updatedAt) 
                VALUES (:nombre, :cantidad, :estado, :unidadMedida, NOW(), NOW())
            ");

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':unidadMedida', $unidad);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Item agregado correctamente.',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => 'Error al guardar el item.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function updateIngredient($id, $nombre, $cantidad, $estado, $unidad)
    {
        try {
            if (empty($id) || empty($nombre) || empty($estado) || empty($unidad)) {
                return ['success' => false, 'message' => 'ID, nombre, estado y unidad de medida son obligatorios.'];
            }

            if (!is_numeric($cantidad) || $cantidad < 0) {
                return ['success' => false, 'message' => 'La cantidad debe ser un número válido mayor o igual a 0.'];
            }

            $id = (int)$id;

            $checkItem = $this->conn->prepare("SELECT COUNT(*) FROM ingredientes WHERE id = :id");
            $checkItem->bindParam(':id', $id);
            $checkItem->execute();
            if ($checkItem->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'El item no existe.'];
            }

            $stmt = $this->conn->prepare("
                UPDATE ingredientes
                SET nombre = :nombre, cantidad = :cantidad, estado = :estado, unidad = :unidadMedida, updatedAt = NOW()
                WHERE id = :id
            ");

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':unidadMedida', $unidad);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Item actualizado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el item.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function deleteIngredient($id)
    {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID del item es obligatorio.'];
            }

            $stmt = $this->conn->prepare("DELETE FROM ingredientes WHERE id = :id");
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Item eliminado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar el item.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function filterByEstado($estado)
    {
        try {
            $query = "SELECT * FROM ingredientes WHERE estado = :estado ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();

            $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $inventario,
                'message' => 'Inventario filtrado por estado obtenido correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al filtrar inventario: ' . $e->getMessage()
            ];
        }
    }
}
