<?php
class MesaController
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
            $query = "SELECT * FROM mesas WHERE configuracion_id = :configuracion_id ORDER BY numero_mesa ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $mesas,
                'message' => 'Mesas obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener mesas: ' . $e->getMessage()
            ];
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT * FROM mesas WHERE id = :id AND configuracion_id = :configuracion_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mesa) {
                return [
                    'success' => true,
                    'data' => $mesa,
                    'message' => 'Mesa encontrada'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Mesa no encontrada'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar mesa: ' . $e->getMessage()
            ];
        }
    }

    public function createMesa($numero_mesa, $asientos, $estado = 'Disponible')
    {
        try {
            if (empty($numero_mesa)) {
                return ['success' => false, 'message' => 'El número de mesa es obligatorio.'];
            }

            if (!is_numeric($numero_mesa) || $numero_mesa <= 0) {
                return ['success' => false, 'message' => 'El número de mesa debe ser un número válido mayor a 0.'];
            }

            if (!is_numeric($asientos) || $asientos <= 0) {
                return ['success' => false, 'message' => 'El número de asientos debe ser un número válido mayor a 0.'];
            }

            $estadosValidos = ['Disponible', 'Ocupada', 'Reservada'];
            if (!in_array($estado, $estadosValidos)) {
                return ['success' => false, 'message' => 'Estado no válido. Use: Disponible, Ocupada o Reservada.'];
            }

            // Verificar si el número de mesa ya existe
            $check = $this->conn->prepare("SELECT COUNT(*) FROM mesas WHERE numero_mesa = :numero_mesa AND configuracion_id = :configuracion_id");
            $check->bindParam(':numero_mesa', $numero_mesa);
            $check->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'El número de mesa ya está registrado.'];
            }

            $stmt = $this->conn->prepare("
                INSERT INTO mesas (numero_mesa, asientos, estado, configuracion_id, created_at) 
                VALUES (:numero_mesa, :asientos, :estado, :configuracion_id, NOW())
            ");

            $stmt->bindParam(':numero_mesa', $numero_mesa, PDO::PARAM_INT);
            $stmt->bindParam(':asientos', $asientos, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Mesa creada correctamente.',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => 'Error al crear la mesa.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function updateMesa($id, $numero_mesa, $asientos, $estado)
    {
        try {
            if (empty($id) || empty($numero_mesa)) {
                return ['success' => false, 'message' => 'ID y número de mesa son obligatorios.'];
            }

            if (!is_numeric($numero_mesa) || $numero_mesa <= 0) {
                return ['success' => false, 'message' => 'El número de mesa debe ser un número válido mayor a 0.'];
            }

            if (!is_numeric($asientos) || $asientos <= 0) {
                return ['success' => false, 'message' => 'El número de asientos debe ser un número válido mayor a 0.'];
            }

            $estadosValidos = ['Disponible', 'Ocupada', 'Reservada'];
            if (!in_array($estado, $estadosValidos)) {
                return ['success' => false, 'message' => 'Estado no válido. Use: Disponible, Ocupada o Reservada.'];
            }

            $id = (int)$id;

            // Verificar si la mesa existe
            $checkMesa = $this->conn->prepare("SELECT COUNT(*) FROM mesas WHERE id = :id AND configuracion_id = :configuracion_id");
            $checkMesa->bindParam(':id', $id);
            $checkMesa->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkMesa->execute();
            if ($checkMesa->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'La mesa no existe.'];
            }

            // Verificar si el número de mesa ya existe en otra mesa
            $checkNumero = $this->conn->prepare("SELECT COUNT(*) FROM mesas WHERE numero_mesa = :numero_mesa AND id != :id AND configuracion_id = :configuracion_id");
            $checkNumero->bindParam(':numero_mesa', $numero_mesa);
            $checkNumero->bindParam(':id', $id);
            $checkNumero->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $checkNumero->execute();
            if ($checkNumero->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'El número de mesa ya está registrado en otra mesa.'];
            }

            $stmt = $this->conn->prepare("
                UPDATE mesas
                SET numero_mesa = :numero_mesa, asientos = :asientos, estado = :estado
                WHERE id = :id AND configuracion_id = :configuracion_id
            ");

            $stmt->bindParam(':numero_mesa', $numero_mesa, PDO::PARAM_INT);
            $stmt->bindParam(':asientos', $asientos, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Mesa actualizada correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar la mesa.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function deleteMesa($id)
    {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de la mesa es obligatorio.'];
            }

            $stmt = $this->conn->prepare("DELETE FROM mesas WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return ['success' => true, 'message' => 'Mesa eliminada correctamente.'];
                } else {
                    return ['success' => false, 'message' => 'Mesa no encontrada.'];
                }
            } else {
                return ['success' => false, 'message' => 'Error al eliminar la mesa.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function filterByEstado($estado)
    {
        try {
            $estadosValidos = ['Disponible', 'Ocupada', 'Reservada'];
            if (!in_array($estado, $estadosValidos)) {
                return ['success' => false, 'message' => 'Estado no válido. Use: Disponible, Ocupada o Reservada.'];
            }

            $query = "SELECT * FROM mesas WHERE estado = :estado AND configuracion_id = :configuracion_id ORDER BY numero_mesa ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $mesas,
                'message' => 'Mesas filtradas por estado obtenidas correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al filtrar mesas: ' . $e->getMessage()
            ];
        }
    }

    public function findByNumeroMesa($numero_mesa)
    {
        try {
            $query = "SELECT * FROM mesas WHERE numero_mesa = :numero_mesa AND configuracion_id = :configuracion_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':numero_mesa', $numero_mesa);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mesa) {
                return [
                    'success' => true,
                    'data' => $mesa,
                    'message' => 'Mesa encontrada'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Mesa no encontrada'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar mesa: ' . $e->getMessage()
            ];
        }
    }

    public function cambiarEstadoMesa($id, $estado)
    {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de la mesa es obligatorio.'];
            }

            $estadosValidos = ['Disponible', 'Ocupada', 'Reservada'];
            if (!in_array($estado, $estadosValidos)) {
                return ['success' => false, 'message' => 'Estado no válido. Use: Disponible, Ocupada o Reservada.'];
            }

            if(!is_numeric($id) || $id <= 0) {
                return ['success' => false, 'message' => 'ID de la mesa debe ser un número válido mayor a 0.'];
            }

            $query = "SELECT COUNT(*) FROM mesas WHERE id = :id AND configuracion_id = :configuracion_id";
            $check = $this->conn->prepare($query);
            $check->bindParam(':id', $id);
            $check->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $check->execute();

            if ($check->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'La mesa no existe.'];
            }

            $stmt = $this->conn->prepare("UPDATE mesas SET estado = :estado WHERE id = :id AND configuracion_id = :configuracion_id");
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Estado de la mesa actualizado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el estado de la mesa.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function findByEstado($estado)
    {
        try {
            $estadosValidos = ['Disponible', 'Ocupada', 'Reservada'];
            if (!in_array($estado, $estadosValidos)) {
                return ['success' => false, 'message' => 'Estado no válido. Use: Disponible, Ocupada o Reservada.'];
            }

            $query = "SELECT * FROM mesas WHERE estado = :estado AND configuracion_id = :configuracion_id ORDER BY numero_mesa ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindValue(':configuracion_id', $this->configuracion_id, PDO::PARAM_INT);
            $stmt->execute();

            $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $mesas,
                'message' => 'Mesas encontradas por estado'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar mesas por estado: ' . $e->getMessage()
            ];
        }
    }
}
