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
    
    $printerIP = $input['printerIP'] ?? '';
    $printerPort = $input['printerPort'] ?? 9100;
    $escCode = base64_decode($input['escCode'] ?? '');
    $orderData = $input['orderData'] ?? null;
    
    if (empty($printerIP) || empty($escCode)) {
        throw new Exception('IP de impresora y código ESC/POS son requeridos');
    }
    
    if (!filter_var($printerIP, FILTER_VALIDATE_IP)) {
        throw new Exception('Dirección IP inválida');
    }
    
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!$socket) {
        throw new Exception('No se pudo crear socket de conexión');
    }
    
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 5, 'usec' => 0));
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 5, 'usec' => 0));
    
    $result = @socket_connect($socket, $printerIP, $printerPort);
    if (!$result) {
        $error = socket_strerror(socket_last_error($socket));
        socket_close($socket);
        throw new Exception("No se pudo conectar a la impresora: $error");
    }

    $bytes_sent = @socket_write($socket, $escCode, strlen($escCode));
    if ($bytes_sent === false) {
        $error = socket_strerror(socket_last_error($socket));
        socket_close($socket);
        throw new Exception("Error enviando datos: $error");
    }

    socket_close($socket);
    
    if ($orderData) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'printer_ip' => $printerIP,
            'order_id' => $orderData['id'] ?? 'N/A',
            'total' => $orderData['total'] ?? 0,
            'bytes_sent' => $bytes_sent,
            'status' => 'success'
        ];
        
        $logFile = __DIR__ . '/../logs/thermal_prints.log';
        if (is_dir(dirname($logFile))) {
            file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ticket enviado correctamente a la impresora',
        'bytes_sent' => $bytes_sent,
        'printer_ip' => $printerIP
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'PRINT_ERROR'
    ]);
}
?>
