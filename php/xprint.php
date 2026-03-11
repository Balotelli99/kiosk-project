<?php
header('Content-Type: application/json');

// Printer configuration - adjust these values for your setup
$PRINTER_HOST = '192.168.1.100';  // Change to your printer IP
$PRINTER_PORT = 9100;             // Default port for most thermal printers

// Handle test connection
if (isset($_GET['test'])) {
    $socket = @fsockopen($PRINTER_HOST, $PRINTER_PORT, $errno, $errstr, 5);
    if ($socket) {
        fclose($socket);
        echo json_encode(['success' => true, 'message' => 'Printer connected']);
    } else {
        echo json_encode(['success' => false, 'error' => "Connection failed: $errstr"]);
    }
    exit;
}

// Handle print request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['receipt'])) {
        echo json_encode(['success' => false, 'error' => 'No receipt data']);
        exit;
    }
    
    $receipt = $data['receipt'];
    
    // Connect to printer and send data
    $socket = @fsockopen($PRINTER_HOST, $PRINTER_PORT, $errno, $errstr, 10);
    
    if (!$socket) {
        echo json_encode(['success' => false, 'error' => "Cannot connect to printer: $errstr"]);
        exit;
    }
    
    // Write receipt to printer
    $written = fwrite($socket, $receipt);
    
    if ($written === false) {
        fclose($socket);
        echo json_encode(['success' => false, 'error' => 'Failed to send data to printer']);
        exit;
    }
    
    // Give printer time to process
    usleep(500000); // 0.5 seconds
    
    fclose($socket);
    echo json_encode(['success' => true, 'message' => 'Receipt printed']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
