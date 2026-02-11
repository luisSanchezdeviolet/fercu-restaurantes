<?php

class Personal
{
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createEmployee($name, $email, $password, $role)
    {
        try {
            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
            }

            $check = $this->conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
            $check->bindParam(':email', $email);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'El correo ya está registrado.'];
            }

            $stmt = $this->conn->prepare("
                INSERT INTO usuarios (nombre, email, password, rol) 
                VALUES (:nombre, :email, :password, :rol)
            ");

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt->bindParam(':nombre', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':rol', $role);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Empleado agregado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al guardar el empleado.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function updateEmployee($id, $name, $email, $role)
    {
        try {
            if (empty($id) || empty($name) || empty($email) || empty($role)) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
            }

            $id = (int)$id;

            $check = $this->conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id != :id");
            $check->bindParam(':email', $email);
            $check->bindParam(':id', $id);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'El correo ya está registrado por otro usuario.'];
            }

            $checkUser = $this->conn->prepare("SELECT COUNT(*) FROM usuarios WHERE id = :id");
            $checkUser->bindParam(':id', $id);
            $checkUser->execute();
            if ($checkUser->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'El usuario no existe.'];
            }

            $stmt = $this->conn->prepare("
                UPDATE usuarios 
                SET nombre = :nombre, email = :email, rol = :rol 
                WHERE id = :id
            ");
            $stmt->bindParam(':nombre', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':rol', $role);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Empleado actualizado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el empleado.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }


    public function deleteEmployee($id) {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID del empleado es obligatorio.'];
            }

            $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Empleado eliminado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar el empleado.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }
}
