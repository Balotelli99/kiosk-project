<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll();

    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Fout bij ophalen categorieën: " . $e->getMessage()]);
}
?>