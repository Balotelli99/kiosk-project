<?php
header('Content-Type: application/json');
require_once 'includes/db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$items = $data['items'] ?? [];

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Geen items']);
    exit;
}

try {
    // 1. Pickup number bepalen
    $res = $conn->query("SELECT MAX(pickup_number) AS last FROM orders");
    $row = $res->fetch_assoc();
    $pickup = (isset($row['last']) && $row['last'] < 99) ? $row['last'] + 1 : 1;

    // 2. Hoofdorder aanmaken
    $stmt = $conn->prepare("INSERT INTO orders (order_status_id, pickup_number, price_total) VALUES (1, ?, 0)");
    $stmt->bind_param("i", $pickup);
    $stmt->execute();
    $orderId = $conn->insert_id;

    $total = 0;

    // 3. PRODUCTEN GROEPEREN (Dit voorkomt de 'Duplicate entry' fout)
    // We maken van [14, 14, 15] een lijstje: Product 14 (2x) en Product 15 (1x)
    $counts = array_count_values($items); 

    foreach ($counts as $id => $quantity) {
        $id = intval($id);
        $pRes = $conn->query("SELECT price FROM products WHERE product_id = $id");
        
        if ($p = $pRes->fetch_assoc()) {
            $price = $p['price'];
            $total += ($price * $quantity);

            // We voeren het product nu SLECHTS ÉÉN KEER in per order.
            // De database accepteert dit omdat de combinatie (order_id, product_id) nu uniek blijft.
            $stmt_item = $conn->prepare("INSERT INTO order_product (order_id, product_id, price) VALUES (?, ?, ?)");
            $stmt_item->bind_param("iid", $orderId, $id, $price);
            $stmt_item->execute();
        }
    }

    // 4. Totaalprijs updaten
    $conn->query("UPDATE orders SET price_total = $total WHERE order_id = $orderId");

    echo json_encode(['success' => true, 'pickup_number' => $pickup]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server fout: ' . $e->getMessage()]);
}
?>