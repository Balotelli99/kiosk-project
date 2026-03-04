<?php
// Directe verbinding
$conn = new mysqli("localhost", "root", "", "kioskv2");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}

// Ontvang JSON data
$data = json_decode(file_get_contents('php://input'), true);
$items = $data['items'] ?? [];

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Geen items']);
    exit;
}

// 1. Bestelnummer bepalen (Pickup number)
$res = $conn->query("SELECT MAX(pickup_number) AS last FROM orders");
$row = $res->fetch_assoc();
$pickup = ($row['last'] >= 99) ? 1 : $row['last'] + 1;

// 2. Hoofdorder aanmaken
$stmt = $conn->prepare("INSERT INTO orders (order_status_id, pickup_number, price_total) VALUES (1, ?, 0)");
$stmt->bind_param("i", $pickup);
$stmt->execute();
$orderId = $conn->insert_id;

$total = 0;

// 3. Producten koppelen
foreach ($items as $id) {
    $pRes = $conn->query("SELECT price FROM products WHERE product_id = " . intval($id));
    if ($p = $pRes->fetch_assoc()) {
        $price = $p['price'];
        $total += $price;
        $conn->query("INSERT INTO order_product (order_id, product_id, price) VALUES ($orderId, $id, $price)");
    }
}

// 4. Totaalprijs updaten
$conn->query("UPDATE orders SET price_total = $total WHERE order_id = $orderId");

echo json_encode(['success' => true, 'pickup_number' => $pickup]);
?>