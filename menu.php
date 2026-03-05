<?php 
// 1. Verbinding maken via je centrale db bestand
require_once 'includes/db.php';

// 2. Haal alle categorieën op uit de tabel 'categories'
$cat_query = $conn->query("SELECT * FROM categories ORDER BY category_id ASC");

// 3. Bepaal welke categorie we moeten tonen (standaard de eerste: Breakfast)
$active_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 1;

// 4. Haal alleen producten op die bij de actieve categorie horen
$sql = "SELECT p.product_id, p.name, p.description, p.price, i.filename 
        FROM products p 
        LEFT JOIN images i ON p.image_id = i.image_id 
        WHERE p.available = 1 AND p.category_id = $active_cat";
$result = $conn->query($sql);

// 5. Haal de naam van de huidige categorie op voor de titel bovenin
$current_cat_res = $conn->query("SELECT name FROM categories WHERE category_id = $active_cat");
$current_cat_row = $current_cat_res->fetch_assoc();
$page_title = ($current_cat_row) ? $current_cat_row['name'] : "MENU";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?> - Happy Herbivore</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-badge { position: absolute; top: -5px; right: -8px; background: red; color: white; border-radius: 50%; padding: 2px 7px; font-size: 12px; display: none; border: 2px solid white; }
        .navBtn { position: relative; }
        /* Style om te zien welke categorie geselecteerd is */
        .catBtn[aria-pressed="true"] { background-color: #6c4ab6; color: white; border: 2px solid white; }
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
                        <div class="titlePill"><?php echo strtoupper($page_title); ?></div>
                    </div>

                    <div class="layoutProducts">
                        <div class="cats">
                            <?php while($cat = $cat_query->fetch_assoc()): ?>
                                <button class="catBtn" 
                                        onclick="location.href='menu.php?cat=<?php echo $cat['category_id']; ?>'" 
                                        aria-pressed="<?php echo ($active_cat == $cat['category_id']) ? 'true' : 'false'; ?>">
                                    <span><?php echo $cat['name']; ?></span>
                                </button>
                            <?php endwhile; ?>
                        </div>

                        <div class="productsArea scrollArea">
                            <div class="grid">
                                <?php if($result->num_rows > 0): ?>
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
                                <?php else: ?>
                                    <p style="padding: 20px; color: white;">Geen producten gevonden in deze categorie.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="bottomNav">
                        <a href="index.php" class="navBtn"><img src="images/icon-home.png"></a>
                        <button class="navBtn" onclick="location.href='cart.php'">
                            <img src="images/icon-cart.png">
                            <span id="cart-count" class="cart-badge">0</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        let cart = JSON.parse(sessionStorage.getItem('kiosk_cart')) || [];
        updateUI();

        function addToCart(id) { 
            cart.push(id); 
            sessionStorage.setItem('kiosk_cart', JSON.stringify(cart)); 
            updateUI(); 
        }

        function updateUI() { 
            const b = document.getElementById('cart-count'); 
            if(cart.length > 0) { 
                b.innerText = cart.length; 
                b.style.display = 'block'; 
            } else {
                b.style.display = 'none';
            }
        }
    </script>
</body>
</html>