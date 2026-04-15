<?php
class CategoriaController
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
            $query = "SELECT * FROM categorias WHERE configuracion_id = :configuracion_id ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorías obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ];
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT * FROM categorias WHERE id = :id AND configuracion_id = :configuracion_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($categoria) {
                return [
                    'success' => true,
                    'data' => $categoria,
                    'message' => 'Categoría encontrada'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar categoría: ' . $e->getMessage()
            ];
        }
    }

    public function createCategory($name, $estado, $imagen = null)
    {
        try {
            if (empty($name) || !isset($estado)) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
            }

            $check = $this->conn->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre AND configuracion_id = :configuracion_id");
            $check->bindParam(':nombre', $name);
            $check->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'La categoría ya está registrada.'];
            }

            $stmt = $this->conn->prepare("
                INSERT INTO categorias (nombre, estado, imagen, configuracion_id) 
                VALUES (:nombre, :estado, :imagen, :configuracion_id)
            ");

            $stmt->bindParam(':nombre', $name);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':imagen', $imagen);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Categoría agregada correctamente.',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => 'Error al guardar la categoría.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function updateCategory($id, $name, $estado, $imagen = null)
    {
        try {
            if (empty($id) || empty($name) || !isset($estado)) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
            }

            $id = (int)$id;

            $checkUser = $this->conn->prepare("SELECT COUNT(*) FROM categorias WHERE id = :id AND configuracion_id = :configuracion_id");
            $checkUser->bindParam(':id', $id);
            $checkUser->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkUser->execute();
            if ($checkUser->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'La categoría no existe.'];
            }

            if ($imagen !== null) {
                $stmt = $this->conn->prepare("
                    UPDATE categorias
                    SET nombre = :nombre, estado = :estado, imagen = :imagen
                    WHERE id = :id AND configuracion_id = :configuracion_id
                ");
                $stmt->bindParam(':imagen', $imagen);
            } else {
                $stmt = $this->conn->prepare("
                    UPDATE categorias
                    SET nombre = :nombre, estado = :estado
                    WHERE id = :id AND configuracion_id = :configuracion_id
                ");
            }

            $stmt->bindParam(':nombre', $name);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Categoría actualizada correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar la categoría.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function deleteCategory($id)
    {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de la categoría es obligatorio.'];
            }

            $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Categoría eliminada correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar la categoría.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function filterByEstado($estado)
    {
        try {
            $query = "SELECT * FROM categorias WHERE estado = :estado AND configuracion_id = :configuracion_id ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorías filtradas por estado obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al filtrar categorías: ' . $e->getMessage()
            ];
        }
    }
}
