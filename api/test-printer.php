<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Datos inválidos');
    }
    
    $printerIP = $input['ip'] ?? '';
    $printerPort = $input['port'] ?? 9100;
    
    if (empty($printerIP)) {
        throw new Exception('IP de impresora es requerida');
    }
    
    if (!filter_var($printerIP, FILTER_VALIDATE_IP)) {
        throw new Exception('Dirección IP inválida');
    }
    
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!$socket) {
        throw new Exception('No se pudo crear socket de conexión');
    }
    
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 3, 'usec' => 0));
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 3, 'usec' => 0));
    
    $start_time = microtime(true);
    $result = @socket_connect($socket, $printerIP, $printerPort);
    $connection_time = round((microtime(true) - $start_time) * 1000, 2);
    
    if (!$result) {
        $error = socket_strerror(socket_last_error($socket));
        socket_close($socket);
        throw new Exception("Conexión fallida: $error");
    }

    $test_command = "\x10\x04\x01";
    @socket_write($socket, $test_command, strlen($test_command));
    
    $response = @socket_read($socket, 10, PHP_NORMAL_READ);
    
    socket_close($socket);
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexión exitosa con la impresora',
        'connection_time' => $connection_time,
        'printer_ip' => $printerIP,
        'printer_port' => $printerPort,
        'response_length' => $response ? strlen($response) : 0
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'CONNECTION_ERROR'
    ]);
}
?>
