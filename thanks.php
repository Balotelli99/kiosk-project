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
            <div class="screen" style="background-color: #8cd003;">
                <div class="screen__content" style="justify-content: center; align-items: center; text-align: center; color: white;">
                    
                    <div class="brand__bubble" style="margin-bottom: 20px; background: white;">
                        <img class="brand__logo" src="images/logo.webp" alt="Logo" style="width: 80px;">
                    </div>

                    <h1 style="font-weight: 800; font-size: 32px; margin-bottom: 10px;">BEDANKT!</h1>
                    <p style="font-weight: 600; opacity: 0.9;">JE BESTELLING WORDT BEREID</p>

                    <div style="background: white; color: #8cd003; width: 150px; height: 150px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 30px 0; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                        <span id="display-order-number" style="font-size: 70px; font-weight: 900;">--</span>
                    </div>

                    <p style="font-size: 14px; margin-bottom: 40px;">ONTHOUD DIT NUMMER</p>

                    <button onclick="finish()" class="btn btn--green" style="background: white; color: #8cd003; border: none;">NIEUWE BESTELLING</button>

                </div>
            </div>
        </div>
    </main>

    <script>
        // Haal het nummer op dat we in cart.php hebben gegenereerd
        const orderNum = sessionStorage.getItem('order_number') || "??";
        document.getElementById('display-order-number').innerText = orderNum;

        function finish() {
            // Maak de winkelwagen leeg voor de volgende klant
            sessionStorage.clear();
            window.location.href = 'index.php';
        }

        // Automatisch terug naar start na 15 seconden (optioneel)
        setTimeout(finish, 15000);
    </script>
</body>
</html>