<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "kioskv2";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connectie mislukt: " . $conn->connect_error);
}
?>