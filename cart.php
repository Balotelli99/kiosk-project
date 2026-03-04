<?php 
// 1. Directe verbinding met de database
$host = "localhost";
$user = "root";
$password = "";
$database = "kioskv2";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database verbinding mislukt: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 2. Producten ophalen voor het overzicht in de winkelwagen
$res = $conn->query("SELECT p.product_id, p.name, p.price, i.filename FROM products p LEFT JOIN images i ON p.image_id = i.image_id");
$all_products = [];
while($row = $res->fetch_assoc()) { 
    $all_products[$row['product_id']] = $row; 
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Winkelwagen - Happy Herbivore</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-container { background: #8cd003; height: 100vh; padding: 20px; display: flex; flex-direction: column; border-radius: 40px; }
        .item-card { background: #d4cf65; border-radius: 20px; padding: 15px; display: flex; align-items: center; gap: 15px; margin-bottom: 10px; color: #1a3c34; }
        .item-img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; background: white; }
        .btn-checkout { background: #6c4ab6; color: white; border-radius: 30px; padding: 15px; border: none; font-weight: 800; width: 100%; cursor: pointer; font-size: 18px; }
        .total-section { text-align: center; font-weight: 800; margin: 20px 0; font-size: 20px; color: #1a3c34; }
    </style>
</head>
<body>
    <main class="kiosk">
        <div class="kiosk__device">
            <div class="screen">
                <div class="cart-container">
                    <h2 style="text-align:center; color:#1a3c34;" id="items-count">0 items in cart</h2>
                    
                    <div id="cart-list" style="flex:1; overflow-y:auto; padding: 5px;">
                        </div>

                    <div class="total-section">
                        Total: <span style="font-size: 14px; opacity: 0.7;">EURO:</span> <span id="total-price">€0,00</span>
                    </div>

                    <div style="padding-bottom: 20px;">
                        <button onclick="checkout()" class="btn-checkout">Checkout</button>
                        <a href="menu.php" style="text-align:center; display:block; color:white; margin-top:15px; text-decoration: none; font-weight: bold;">Back to Menu</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const allProducts = <?php echo json_encode($all_products); ?>;
        let cartIds = JSON.parse(sessionStorage.getItem('kiosk_cart')) || [];

        function renderCart() {
            const list = document.getElementById('cart-list');
            let total = 0;
            list.innerHTML = '';

            const counts = {};
            cartIds.forEach(id => counts[id] = (counts[id] || 0) + 1);

            Object.keys(counts).forEach(id => {
                const p = allProducts[id];
                if(!p) return;
                const subtotal = p.price * counts[id];
                total += subtotal;
                list.innerHTML += `
                    <div class="item-card">
                        <img src="images/${p.filename}" class="item-img">
                        <div style="flex:1">
                            <b>${p.name}</b><br>
                            €${parseFloat(p.price).toFixed(2).replace('.', ',')} (x${counts[id]})
                        </div>
                        <div style="font-weight:bold">€${subtotal.toFixed(2).replace('.', ',')}</div>
                    </div>`;
            });

            document.getElementById('total-price').innerText = '€' + total.toFixed(2).replace('.', ',');
            document.getElementById('items-count').innerText = cartIds.length + " items in cart";
        }

        async function checkout() {
            if (cartIds.length === 0) return alert("Je mandje is leeg!");
            
            try {
                const response = await fetch('save_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: cartIds })
                });

                const result = await response.json();

                if (result.success) {
                    sessionStorage.setItem('order_number', result.pickup_number);
                    window.location.href = 'thanks.php';
                } else {
                    alert("Fout: " + result.error);
                }
            } catch (e) {
                alert("Kon geen verbinding maken met de server.");
            }
        }

        renderCart();
    </script>
</body>
</html>