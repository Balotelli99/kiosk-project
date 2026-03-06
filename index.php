<?php require_once 'includes/lang.php'; ?>
<!DOCTYPE html>
<html lang="nl" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="google" content="notranslate">
    <title>Happy Herbivore Kiosk</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="kiosk">
        <div class="kiosk__device">
            <div class="screen">
                <div class="screen__bg bg-start"></div>
                <div class="veggies" style="background-image: url('images/image.png');" aria-hidden="true"></div>
                <div class="screen__content">
                    <div class="brand">
                        <div class="brand__bubble"><img class="brand__logo" src="images/logo.webp"></div>
                    </div>
                    <div class="hero"><img src="images/hero-food-drinks.png"></div>
                    <div class="pillRow">
                        <a href="menu.php?lang=<?php echo $lang; ?>" class="btn btn--green" style="text-decoration:none;"><?php echo t('eat_here'); ?></a>
                        <a href="menu.php?lang=<?php echo $lang; ?>" class="btn btn--green" style="text-decoration:none;"><?php echo t('take_away'); ?></a>
                    </div>
                    <div class="langRow">
                        <a href="index.php?lang=nl" class="langBtn" style="text-decoration:none;">
                            <img src="images/flag-nl.png">
                            <span>NEDERLANDS</span>
                        </a>
                        <a href="index.php?lang=en" class="langBtn" style="text-decoration:none;">
                            <img src="images/flag-en.png">
                            <span>ENGLISH</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
