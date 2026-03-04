<?php 
// 1. Directe verbinding maken (vervangt include 'db.php')
$host = "localhost";
$user = "root";
$password = "";
$database = "kioskv2";

$conn = new mysqli($host, $user, $password, $database);

// Check de verbinding
if ($conn->connect_error) {
    die("Database verbinding mislukt: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 2. Producten ophalen
$sql = "SELECT p.product_id, p.name, p.description, p.price, i.filename 
        FROM products p 
        LEFT JOIN images i ON p.image_id = i.image_id 
        WHERE p.available = 1";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Menu - Happy Herbivore</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-badge { position: absolute; top: -5px; right: -8px; background: red; color: white; border-radius: 50%; padding: 2px 7px; font-size: 12px; display: none; border: 2px solid white; }
        .navBtn { position: relative; }
    </style>
</head>
<body>
    <main class="kiosk">
        <div class="kiosk__device">
            <div class="screen">
                <div class="screen__bg bg-products"></div>
                <div class="screen__content screen__content--flush">
                    <div class="topBar">
                        <img class="topBar__logo" src="images/logo.webp">
                        <div class="titlePill">BURGERS</div>
                    </div>
                    <div class="layoutProducts">
                        <div class="cats"><button class="catBtn" aria-pressed="true"><img src="images/icon-coffee.png"><span>Burgers</span></button></div>
                        <div class="productsArea scrollArea">
                            <div class="grid">
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <div class="card">
                                        <img class="card__img" src="images/<?php echo $row['filename']; ?>">
                                        <div class="card__title"><?php echo $row['name']; ?></div>
                                        <div class="card__desc"><?php echo $row['description']; ?></div>
                                        <div class="card__meta">
                                            <div class="card__price">€<?php echo number_format($row['price'], 2, ',', '.'); ?></div>
                                            <button class="circleBtn" onclick="addToCart(<?php echo $row['product_id']; ?>)">+</button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                    <div class="bottomNav">
                        <a href="index.php" class="navBtn"><img src="images/icon-home.png"></a>
                        <button class="navBtn" onclick="location.href='cart.php'"><img src="images/icon-cart.png"><span id="cart-count" class="cart-badge">0</span></button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        let cart = JSON.parse(sessionStorage.getItem('kiosk_cart')) || [];
        updateUI();
        function addToCart(id) { cart.push(id); sessionStorage.setItem('kiosk_cart', JSON.stringify(cart)); updateUI(); }
        function updateUI() { const b = document.getElementById('cart-count'); if(cart.length > 0) { b.innerText = cart.length; b.style.display = 'block'; } }
    </script>
</body>
</html>