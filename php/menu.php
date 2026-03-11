<?php
require_once 'includes/lang.php';
require_once 'includes/db.php';

// 2. Haal alle categorieën op uit de tabel 'categories'
$cat_query = $conn->query("SELECT category_id, name, name_nl FROM categories ORDER BY category_id ASC");

// 3. Bepaal welke categorie we moeten tonen (standaard de eerste: Breakfast)
$active_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 1;

// 4. Haal alleen producten op die bij de actieve categorie horen
$sql = "SELECT p.product_id, p.name, p.name_nl, p.description, p.description_nl, p.price, i.filename
        FROM products p
        LEFT JOIN images i ON p.image_id = i.image_id
        WHERE p.available = 1 AND p.category_id = $active_cat";
$result = $conn->query($sql);

// 5. Haal de naam van de huidige categorie op voor de titel bovenin
$current_cat_res = $conn->query("SELECT name, name_nl FROM categories WHERE category_id = $active_cat");
$current_cat_row = $current_cat_res->fetch_assoc();
if ($current_cat_row) {
    $page_title = ($lang === 'nl' && !empty($current_cat_row['name_nl'])) ? $current_cat_row['name_nl'] : $current_cat_row['name'];
} else {
    $page_title = "MENU";
}

// Helper to get category display name
function catName($cat, $lang) {
    if ($lang === 'nl' && !empty($cat['name_nl'])) return $cat['name_nl'];
    return $cat['name'];
}
?>
<!DOCTYPE html>
<html lang="nl" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <title><?php echo $page_title; ?> - Happy Herbivore</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-badge { position: absolute; top: -5px; right: -8px; background: red; color: white; border-radius: 50%; padding: 2px 7px; font-size: 12px; display: none; border: 2px solid white; }
        .navBtn { position: relative; }

        /* Product modal overlay */
        .product-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }
        .product-modal.open { display: flex; }
        .product-modal__inner {
            background: #fff;
            border-radius: 28px;
            width: min(700px, 90vw);
            max-height: 90vh;
            overflow-y: auto;
            padding: 32px;
            position: relative;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35);
        }
        .product-modal__close {
            position: absolute;
            top: 18px; right: 18px;
            width: 52px; height: 52px;
            border-radius: 999px;
            border: none;
            background: #f0f0f0;
            font-size: 28px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .product-modal__img {
            width: 100%;
            height: 340px;
            object-fit: cover;
            border-radius: 18px;
            margin-bottom: 20px;
        }
        .product-modal__title {
            font-size: 34px;
            font-weight: 900;
            color: #ff7520;
            margin: 0 0 12px;
        }
        .product-modal__desc {
            font-size: 22px;
            line-height: 1.5;
            color: rgba(5,54,49,0.75);
            margin: 0 0 24px;
        }
        .product-modal__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .product-modal__price {
            font-size: 32px;
            font-weight: 900;
            color: #053631;
        }
        .product-modal__add {
            background: #053631;
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 18px 40px;
            font-size: 24px;
            font-weight: 900;
            cursor: pointer;
        }
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
                        <div class="langMini">
                            <a href="?lang=nl&cat=<?php echo $active_cat; ?>" class="langMiniBtn" aria-pressed="<?php echo $lang==='nl'?'true':'false'; ?>">
                                <img src="images/flag-nl.png" alt="NL">
                            </a>
                            <a href="?lang=en&cat=<?php echo $active_cat; ?>" class="langMiniBtn" aria-pressed="<?php echo $lang==='en'?'true':'false'; ?>">
                                <img src="images/flag-en.png" alt="EN">
                            </a>
                        </div>
                    </div>

                    <div class="layoutProducts">
                        <div class="cats">
                            <?php while($cat = $cat_query->fetch_assoc()): ?>
                                <button class="catBtn"
                                        onclick="location.href='menu.php?cat=<?php echo $cat['category_id']; ?>&lang=<?php echo $lang; ?>'"
                                        aria-pressed="<?php echo ($active_cat == $cat['category_id']) ? 'true' : 'false'; ?>">
                                    <span><?php echo catName($cat, $lang); ?></span>
                                </button>
                            <?php endwhile; ?>
                        </div>

                        <div class="productsArea scrollArea">
                            <div class="grid">
                                <?php if($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <?php
                                            $display_name = ($lang === 'nl' && !empty($row['name_nl'])) ? $row['name_nl'] : $row['name'];
                                            $display_desc = ($lang === 'nl' && !empty($row['description_nl'])) ? $row['description_nl'] : $row['description'];
                                        ?>
                                        <div class="card" onclick="openModal(<?php echo $row['product_id']; ?>, '<?php echo addslashes($display_name); ?>', '<?php echo addslashes($display_desc); ?>', '<?php echo number_format($row['price'], 2, ',', '.'); ?>', 'images/<?php echo $row['filename']; ?>')" style="cursor:pointer;">
                                            <img class="card__img" src="images/<?php echo $row['filename']; ?>">
                                            <div class="card__title"><?php echo $display_name; ?></div>
                                            <div class="card__desc"><?php echo $display_desc; ?></div>
                                            <div class="card__meta">
                                                <div class="card__price">€<?php echo number_format($row['price'], 2, ',', '.'); ?></div>
                                                <div class="circleBtn">+</div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p style="padding: 20px; color: white;"><?php echo t('no_products'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Product detail modal -->
                    <div class="product-modal" id="productModal" onclick="closeModal(event)">
                        <div class="product-modal__inner">
                            <button class="product-modal__close" onclick="closeModalDirect()">✕</button>
                            <img class="product-modal__img" id="modal-img" src="" alt="">
                            <div class="product-modal__title" id="modal-title"></div>
                            <div class="product-modal__desc" id="modal-desc"></div>
                            <div class="product-modal__footer">
                                <div class="product-modal__price" id="modal-price"></div>
                                <button class="product-modal__add" id="modal-add-btn" onclick="modalAddToCart()">+ <?php echo t('add_modal'); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="bottomNav">
                        <a href="index.php?lang=<?php echo $lang; ?>" class="navBtn"><img src="images/icon-home.png"></a>
                        <button class="navBtn" onclick="location.href='cart.php?lang=<?php echo $lang; ?>'">
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

        let modalProductId = null;

        function openModal(id, name, desc, price, img) {
            modalProductId = id;
            document.getElementById('modal-img').src = img;
            document.getElementById('modal-title').textContent = name;
            document.getElementById('modal-desc').textContent = desc;
            document.getElementById('modal-price').textContent = '€' + price;
            document.getElementById('productModal').classList.add('open');
        }

        function closeModal(e) {
            if (e.target === document.getElementById('productModal')) {
                document.getElementById('productModal').classList.remove('open');
            }
        }

        function closeModalDirect() {
            document.getElementById('productModal').classList.remove('open');
        }

        function modalAddToCart() {
            if (modalProductId) {
                addToCart(modalProductId);
                closeModalDirect();
            }
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