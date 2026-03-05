<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Alleen POST toegestaan"]);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$items = $data['items'] ?? [];

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Geen items opgegeven']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Pickup number bepalen
    $stmt = $pdo->query("SELECT MAX(pickup_number) AS last FROM orders");
    $row = $stmt->fetch();
    $pickup = (isset($row['last']) && $row['last'] < 99) ? $row['last'] + 1 : 1;

    // 2. Hoofdorder aanmaken
    $stmt = $pdo->prepare("INSERT INTO orders (order_status_id, pickup_number, price_total) VALUES (1, ?, 0)");
    $stmt->execute([$pickup]);
    $orderId = $pdo->lastInsertId();

    $total = 0;

    // 3. Producten groeperen en toevoegen
    $counts = array_count_values($items);

    $stmtPrice = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
    $stmtItem = $pdo->prepare("INSERT INTO order_product (order_id, product_id, price) VALUES (?, ?, ?)");

    foreach ($counts as $productId => $quantity) {
        $stmtPrice->execute([$productId]);
        $p = $stmtPrice->fetch();

        if ($p) {
            $price = $p['price'];
            $total += ($price * $quantity);

            // Replicating save_order.php logic: insert once per product per order
            $stmtItem->execute([$orderId, $productId, $price]);
        }
    }

    // 4. Totaalprijs updaten
    $stmtUpdate = $pdo->prepare("UPDATE orders SET price_total = ? WHERE order_id = ?");
    $stmtUpdate->execute([$total, $orderId]);

    $pdo->commit();

    echo json_encode(['success' => true, 'pickup_number' => $pickup, 'order_id' => $orderId]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server fout: ' . $e->getMessage()]);
}
?>