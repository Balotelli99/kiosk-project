<?php 
// 1. Directe database verbinding om foutmeldingen te voorkomen
$host = "localhost";
$user = "root";
$password = "";
$database = "kioskv2";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database connectie mislukt: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
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
                <div class="screen__content">
                    
                    <div class="topBar">
                        <h2 id="cart-header" style="color: white; font-weight: 800; font-size: 24px;">WINKELWAGEN</h2>
                    </div>

                    <div id="cart-list" class="scrollArea" style="flex: 1; padding: 20px;">
                        <p id="empty-msg" style="text-align: center; color: white;">Je winkelwagen is nog leeg.</p>
                    </div>

                    <div class="pillRow" style="flex-direction: column; gap: 15px; padding-bottom: 40px;">
                        <button onclick="checkout()" class="btn btn--green" id="checkout-btn" style="display: none; width: 80%;">AFREKENEN</button>
                        <a href="menu.php" class="btn btn--orange" style="text-decoration: none; width: 80%; text-align: center;">VERDER WINKELEN</a>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        // Haal de ID's op uit de sessie
        let cart = JSON.parse(sessionStorage.getItem('kiosk_cart')) || [];
        const cartList = document.getElementById('cart-list');
        const checkoutBtn = document.getElementById('checkout-btn');
        const emptyMsg = document.getElementById('empty-msg');

        if (cart.length > 0) {
            emptyMsg.style.display = 'none';
            checkoutBtn.style.display = 'block';
            
            // In een echte app zou je hier een fetch() doen naar een PHP script 
            // om de productnamen/prijzen op te halen. Voor nu tonen we het aantal:
            cartList.innerHTML = `
                <div style="background: white; padding: 20px; border-radius: 20px; text-align: center;">
                    <h1 style="font-size: 60px; color: #8cd003; margin: 0;">${cart.length}</h1>
                    <p style="font-weight: bold; color: #333;">ITEMS IN JE MANDJE</p>
                </div>
            `;
        }

        function checkout() {
            // Sla een willekeurig bestelnummer op voor de volgende pagina
            const orderNum = Math.floor(Math.random() * 50) + 1;
            sessionStorage.setItem('order_number', orderNum);
            window.location.href = 'thanks.php';
        }
    </script>
</body>
</html>