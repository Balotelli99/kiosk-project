<?php 
require_once 'includes/db.php';

// Producten ophalen voor het overzicht
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
</head>
<body>
    <main class="kiosk">
        <div class="kiosk__device">
            <div class="screen">
                <div class="screen__bg bg-cart"></div>
                <div class="screen__content screen__content--flush">
                    
                    <div class="cartHeader">
                        <img class="topBar__logo" src="images/logo.webp">
                        <div class="cartHeader__title" id="items-count">0 items in cart</div>
                    </div>

                    <div class="scrollArea cartMain">
                        <div class="cartList" id="cart-list">
                            </div>
                    </div>

                    <div class="totalRow">
                        <span>Total:</span>
                        <span class="mutedSmall">EURO:</span>
                        <span id="total-price">€0,00</span>
                    </div>

                    <div class="actions">
                        <button onclick="checkout()" class="btn btn--purple">Checkout</button>
                        <button onclick="location.href='menu.php'" class="btn btn--orange">Back to Home</button>
                    </div>

                    <div class="bottomNav">
                        <a href="index.php" class="navBtn"><img src="images/icon-home.png"></a>
                        <button class="navBtn" onclick="location.href='cart.php'">
                            <img src="images/icon-cart.png">
                        </button>
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

            // Tellen hoeveel van elk product
            const counts = {};
            cartIds.forEach(id => counts[id] = (counts[id] || 0) + 1);

            Object.keys(counts).forEach(id => {
                const p = allProducts[id];
                if(!p) return;
                const subtotal = p.price * counts[id];
                total += subtotal;

                list.innerHTML += `
                    <div class="cartItem">
                        <img src="images/${p.filename}" class="cartItem__img">
                        <div class="cartItem__info">
                            <p class="cartItem__name">${p.name}</p>
                            <p class="cartItem__price">${parseFloat(p.price).toFixed(2)} euro</p>
                            <div class="qty">
                                <button class="removeBtn" onclick="changeQty(${id}, -1)">⊖</button>
                                <span class="qty__num">${counts[id]}</span>
                                <button class="removeBtn" onclick="changeQty(${id}, 1)" style="color:var(--purple)">⊕</button>
                            </div>
                        </div>
                        <button class="removeBtn" onclick="removeItem(${id})">ⓧ</button>
                    </div>`;
            });

            document.getElementById('total-price').innerText = '€' + total.toFixed(2).replace('.', ',');
            document.getElementById('items-count').innerText = cartIds.length + " items in cart";
            sessionStorage.setItem('kiosk_cart', JSON.stringify(cartIds));
        }

        function changeQty(id, delta) {
            if (delta === 1) {
                cartIds.push(id);
            } else {
                const index = cartIds.indexOf(id);
                if (index > -1) cartIds.splice(index, 1);
            }
            renderCart();
        }

        function removeItem(id) {
            cartIds = cartIds.filter(itemId => itemId !== id);
            renderCart();
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
                    sessionStorage.removeItem('kiosk_cart'); // Leeg maken na succes
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