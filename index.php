<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Καλάθι Αγορών - PHP OOP</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f9; 
            color: #333; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .cart-box { 
            background: #fff; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            width: 550px; 
        }
        h2 { color: #007bff; margin-top: 0; text-align: center; }
    </style>
</head>
<body>

<div class="cart-box">
    <h2>🛒 Το Καλάθι μου</h2>
    <?php
    require_once 'ShoppingCart.php';

    $cart = new ShoppingCart();
    $cart->addProduct('Laptop', 1000);
    $cart->addProduct('Mouse', 30);
    $cart->addProduct('Mouse', 30); // Αυξάνει την ποσότητα σε 2

    // Εμφάνιση καλαθιού (μόνο μία φορά)
    $cart->displayCart();
    ?>
</div>

</body>
</html>