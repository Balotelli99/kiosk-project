<?php
require_once 'includes/lang.php';
require_once 'includes/db.php';

// Producten ophalen voor het overzicht
$res = $conn->query("SELECT p.product_id, p.name, p.price, i.filename FROM products p LEFT JOIN images i ON p.image_id = i.image_id");
$all_products = [];
while($row = $res->fetch_assoc()) { 
    $all_products[$row['product_id']] = $row; 
}
?>
<!DOCTYPE html>
<html lang="nl" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="google" content="notranslate">
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
                        <div class="cartHeader__title" id="items-count">0 <?php echo t('items_in_cart'); ?></div>
                    </div>

                    <div class="scrollArea cartMain">
                        <div class="cartList" id="cart-list">
                            </div>
                    </div>

                    <div class="totalRow">
                        <span><?php echo t('total'); ?></span>
                        <span class="mutedSmall"><?php echo t('euro_label'); ?></span>
                        <span id="total-price">€0,00</span>
                    </div>

                    <div class="actions">
                        <button onclick="checkout()" class="btn btn--purple"><?php echo t('checkout_btn'); ?></button>
                        <button onclick="location.href='menu.php?lang=<?php echo $lang; ?>'" class="btn btn--orange"><?php echo t('back_to_menu'); ?></button>
                    </div>

                    <div class="bottomNav">
                        <a href="index.php?lang=<?php echo $lang; ?>" class="navBtn"><img src="images/icon-home.png"></a>
                        <button class="navBtn" onclick="location.href='cart.php?lang=<?php echo $lang; ?>'">
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
            document.getElementById('items-count').innerText = cartIds.length + " <?php echo t('items_in_cart'); ?>";
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
            if (cartIds.length === 0) return alert("<?php echo t('cart_empty'); ?>");
            
            // Show loading
            const btn = document.querySelector('.btn--purple');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Even geduld...';
            btn.disabled = true;
            
            try {
                // First, build order summary for receipt
                const counts = {};
                cartIds.forEach(id => counts[id] = (counts[id] || 0) + 1);
                const summary = Object.keys(counts).map(id => ({
                    name: allProducts[id]?.name || id,
                    qty: counts[id],
                    price: allProducts[id]?.price || 0
                }));
                
                // Calculate total
                let total = 0;
                summary.forEach(item => total += (item.price * item.qty));

                const response = await fetch('save_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: cartIds })
                });
                const result = await response.json();
                if (result.success) {
                    sessionStorage.setItem('order_number', result.pickup_number);
                    sessionStorage.setItem('order_items', JSON.stringify(summary));
                    sessionStorage.setItem('order_time', new Date().toISOString());
                    
                    // Print receipt via USB
                    try {
                        await printReceiptUSB(result.pickup_number, summary, total);
                    } catch(e) {
                        console.log('Print cancelled or failed');
                    }
                    
                    // Show success message
                    btn.innerHTML = '✓ Bestelling opgeslagen!';
                    
                    sessionStorage.removeItem('kiosk_cart');
                    
                    // Brief delay to show success, then redirect
                    await new Promise(r => setTimeout(r, 800));
                    window.location.href = 'thanks.php?lang=<?php echo $lang; ?>';
                } else {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert("Fout: " + result.error);
                }
            } catch (e) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert("Kon geen verbinding maken met de server.");
            }
        }

        // USB Bonprinter functie
        async function printReceiptUSB(pickupNumber, items, total) {
            try {
                if (!navigator.usb) {
                    console.log('WebUSB niet ondersteund, geen bon geprint');
                    return false;
                }

                // Request USB device (Xprinter vendor ID: 0x0483)
                const device = await navigator.usb.requestDevice({
                    filters: [{ vendorId: 0x0483 }]
                });

                await device.open();
                await device.selectConfiguration(1);
                await device.claimInterface(0);

                // Build receipt text
                let receipt = "\x1B\x40";  // Initialize
                receipt += "\n\n";
                receipt += "\x1B\x61\x01";  // Center
                receipt += "Happy Herbivore\n";
                receipt += "Order #" + pickupNumber + "\n";
                receipt += "\x1B\x61\x00";  // Left
                receipt += "------------------------------------\n";
                
                items.forEach(item => {
                    const line = item.qty + 'x ' + item.name;
                    const padLen = 36 - (item.price * item.qty).toFixed(2).length - 6;
                    receipt += line.padEnd(padLen, ' ') + ' EUR ' + (item.price * item.qty).toFixed(2) + "\n";
                });
                
                receipt += "------------------------------------\n";
                receipt += 'Totaal:'.padEnd(28, ' ') + ' EUR ' + total.toFixed(2) + "\n";
                receipt += "\n\n";
                receipt += "Ophaalnummer: " + pickupNumber + "\n";
                receipt += "Bedankt voor uw bezoek!\n";
                receipt += "\n\n\n\n\n";
                receipt += "\x1D\x56\x00";  // Cut

                const encoder = new TextEncoder();
                await device.transferOut(1, encoder.encode(receipt));
                
                console.log('Bonnetje geprint!');
                return true;
            } catch (error) {
                console.log('USB print fout: ' + error.message);
                // Show message to user
                alert('Let op: Bonprinter niet aangesloten. Uw bestelling is wel opgeslagen!');
                return false;
            }
        }

        renderCart();
    </script>
</body>
</html>