<?php require_once 'includes/lang.php'; ?>
<!DOCTYPE html>
<html lang="nl" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="google" content="notranslate">
    <title><?php echo $lang === 'en' ? 'Thank you' : 'Bedankt'; ?> - Happy Herbivore</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .receipt {
            background: rgba(255,255,255,0.92);
            border-radius: clamp(12px, 2vw, 22px);
            padding: clamp(14px, 2.5vw, 28px);
            width: min(560px, 88vw);
            box-shadow: 0 14px 36px rgba(0,0,0,0.14);
            display: flex;
            flex-direction: column;
            gap: clamp(8px, 1.4vh, 16px);
        }
        .receipt__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: clamp(12px, 1.5vw, 18px);
            color: rgba(5,54,49,0.65);
            font-weight: 700;
            border-bottom: 2px solid rgba(5,54,49,0.1);
            padding-bottom: clamp(6px, 1vh, 12px);
        }
        .receipt__items {
            display: flex;
            flex-direction: column;
            gap: clamp(6px, 1vh, 10px);
        }
        .receipt__item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: clamp(13px, 1.6vw, 20px);
            font-weight: 700;
        }
        .receipt__item span:last-child {
            color: #053631;
            font-weight: 900;
        }
        .receipt__total {
            display: flex;
            justify-content: space-between;
            font-size: clamp(15px, 2vw, 24px);
            font-weight: 900;
            border-top: 2px solid rgba(5,54,49,0.15);
            padding-top: clamp(6px, 1vh, 12px);
        }
    </style>
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
                            <h2><?php echo t('order_done'); ?></h2>
                            <p><?php echo t('enjoy'); ?></p>
                        </div>

                        <div class="orderNo">
                            <span id="display-order-number">--</span>
                        </div>

                        <!-- Receipt -->
                        <div class="receipt">
                            <div class="receipt__header">
                                <span id="receipt-date"></span>
                                <span id="receipt-time"></span>
                            </div>
                            <div class="receipt__items" id="receipt-items"></div>
                            <div class="receipt__total">
                                <span><?php echo t('total'); ?></span>
                                <span id="receipt-total">€0,00</span>
                            </div>
                        </div>

                        <button onclick="finish()" class="btn btnWide"><?php echo t('continue_btn'); ?></button>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const orderNum = sessionStorage.getItem('order_number') || "00";
        document.getElementById('display-order-number').innerText = orderNum;

        // Show date and time
        const orderTimeStr = sessionStorage.getItem('order_time');
        const orderTime = orderTimeStr ? new Date(orderTimeStr) : new Date();
        document.getElementById('receipt-date').innerText = orderTime.toLocaleDateString('<?php echo $lang === 'en' ? 'en-GB' : 'nl-NL'; ?>', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
        document.getElementById('receipt-time').innerText = orderTime.toLocaleTimeString('<?php echo $lang === 'en' ? 'en-GB' : 'nl-NL'; ?>', {
            hour: '2-digit', minute: '2-digit'
        });

        // Show ordered items
        const items = JSON.parse(sessionStorage.getItem('order_items') || '[]');
        const itemsEl = document.getElementById('receipt-items');
        let total = 0;
        items.forEach(item => {
            const subtotal = parseFloat(item.price) * item.qty;
            total += subtotal;
            itemsEl.innerHTML += `<div class="receipt__item">
                <span>${item.qty}× ${item.name}</span>
                <span>€${subtotal.toFixed(2).replace('.', ',')}</span>
            </div>`;
        });
        document.getElementById('receipt-total').innerText = '€' + total.toFixed(2).replace('.', ',');

        function finish() {
            sessionStorage.clear();
            window.location.href = 'index.php';
        }

        setTimeout(finish, 15000);
    </script>
</body>
</html>
