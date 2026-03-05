<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Bedankt - Happy Herbivore</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="kiosk">
        <div class="kiosk__device">
            <div class="screen">
                <div class="screen__bg bg-thanks"></div>
                
                <div class="screen__content">
                    <div class="thanksWrap">
                        
                        <div class="brand">
                            <img class="brand__logo" src="images/logo.webp" alt="Logo">
                        </div>

                        <div class="checkBubble">✓</div>

                        <div class="thanksText">
                            <h2>bestelling voltooid</h2>
                            <p>Geniet ervan</p>
                        </div>

                        <div class="orderNo">
                            <span id="display-order-number">--</span>
                        </div>

                        <button onclick="finish()" class="btn btnWide">Verder</button>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Haal het pickup_number op dat save_order.php heeft teruggestuurd
        const orderNum = sessionStorage.getItem('order_number') || "00";
        document.getElementById('display-order-number').innerText = orderNum;

        function finish() {
            // Maak alles leeg voor de volgende klant
            sessionStorage.clear();
            window.location.href = 'index.php';
        }

        // Na 10 seconden automatisch terug naar het begin scherm
        setTimeout(finish, 10000);
    </script>
</body>
</html>