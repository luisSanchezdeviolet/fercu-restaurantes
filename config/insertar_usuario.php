<?php
require_once '../config/database.php';

// Inserción de un usuario administrador con contraseña segura

$usuarios = [
    [
        'nombre' => 'Administrador estudiovioleta',
        'email' => 'admin@estudiovioleta.com',
        'password' => 'admin123',
        'rol' => 'Administrador',
        'activo' => 1
    ]
];

$pdo = (new Database())->getConnection();
$sql = "INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (:nombre, :email, :pass, :rol, :activo)";
$stmt = $pdo->prepare($sql);
foreach ($usuarios as $usuario) {
    $hash = password_hash($usuario['password'], PASSWORD_BCRYPT);
    $stmt->execute([
        ':nombre' => $usuario['nombre'],
        ':email' => $usuario['email'],
        ':pass' => $hash,
        ':rol' => $usuario['rol'],
        ':activo' => $usuario['activo']
    ]);
}
echo "✅ Usuario insertado con contraseña segura";
