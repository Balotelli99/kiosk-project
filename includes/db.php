<?php
$host = "localhost";
$user = "u240787_kiosk-project";
$password = "BtcXPj8xnqfxRyhfwTE8";
$database = "u240787_kiosk-project";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "error" => "DB connectie mislukt: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// PDO Connectie voor de nieuwe API
try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "error" => "PDO connectie mislukt: " . $e->getMessage()]));
}
?>