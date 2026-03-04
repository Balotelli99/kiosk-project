<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "kioskv2";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database verbinding mislukt: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>