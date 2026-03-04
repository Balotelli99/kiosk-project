<?php
include("includes/db.php");

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Happy Herbivore Kiosk</title>

    <link rel="stylesheet" href="./css/styles.css" />
  </head>

  <body>
    <main class="kiosk">
      <div class="kiosk__device">

        <div id="screen" class="screen">

          <?php while($row = $result->fetch_assoc()): ?>

            <div class="product-card">
              <h2><?= $row['name']; ?></h2>
              <p>€<?= $row['price']; ?></p>
            </div>

          <?php endwhile; ?>

        </div>

      </div>
    </main>

  </body>
</html>