
<?php

require_once __DIR__ . '/ShoppingCart.php';

session_start();

if (!isset($_SESSION['cart']) || !($_SESSION['cart'] instanceof ShoppingCart)) {
    $_SESSION['cart'] = new ShoppingCart();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cart = $_SESSION['cart'];

$products = [
    'Laptop' => [
        'price' => 1000.00,
        'description' => 'Ιδανικό για εργασία και καθημερινή χρήση.',
        'icon' => '💻'
    ],
    'Mouse' => [
        'price' => 30.00,
        'description' => 'Άνετος και ακριβής χειρισμός.',
        'icon' => '🖱️'
    ],
    'Keyboard' => [
        'price' => 55.00,
        'description' => 'Πληκτρολόγιο για κάθε γραφείο.',
        'icon' => '⌨️'
    ],
    'Monitor' => [
        'price' => 220.00,
        'description' => 'Καθαρή εικόνα για εργασία και ψυχαγωγία.',
        'icon' => '🖥️'
    ],
    'Headphones' => [
        'price' => 45.00,
        'description' => 'Απόλαυσε τον ήχο σου παντού.',
        'icon' => '🎧'
    ]
];

function escapeHtml($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function euro($amount): string
{
    return number_format((float) $amount, 2, ',', '.') . ' €';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = 'index.php';

    try {
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            throw new RuntimeException(
                'Η συνεδρία έληξε ή το αίτημα δεν είναι έγκυρο. Δοκίμασε ξανά.'
            );
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add':
                $name = $_POST['product'] ?? '';

                if (!is_string($name) || !isset($products[$name])) {
                    throw new InvalidArgumentException(
                        'Το προϊόν που επέλεξες δεν υπάρχει.'
                    );
                }

                $cart->addProduct($name, $products[$name]['price']);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => $name . ' προστέθηκε στο καλάθι.'
                ];
                break;

            case 'decrease':
                $name = $_POST['product'] ?? '';

                if (!is_string($name) || !$cart->decreaseQuantity($name)) {
                    throw new InvalidArgumentException(
                        'Το προϊόν δεν βρέθηκε στο καλάθι.'
                    );
                }

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Η ποσότητα ενημερώθηκε.'
                ];
                break;

            case 'remove':
                $name = $_POST['product'] ?? '';

                if (!is_string($name) || !$cart->removeProduct($name)) {
                    throw new InvalidArgumentException(
                        'Το προϊόν δεν βρέθηκε στο καλάθι.'
                    );
                }

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Το προϊόν αφαιρέθηκε.'
                ];
                break;

            case 'clear':
                $cart->clearCart();

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Το καλάθι άδειασε.'
                ];
                break;

            case 'sort_asc':
                $cart->sortByPrice(false);

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Τα προϊόντα ταξινομήθηκαν από τη χαμηλότερη τιμή.'
                ];
                break;

            case 'sort_desc':
                $cart->sortByPrice(true);

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Τα προϊόντα ταξινομήθηκαν από την υψηλότερη τιμή.'
                ];
                break;

            default:
                throw new InvalidArgumentException(
                    'Η ενέργεια που επέλεξες δεν είναι έγκυρη.'
                );
        }
    } catch (Throwable $e) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Δεν ήταν δυνατή η ολοκλήρωση της ενέργειας.'
        ];
    }

    $_SESSION['cart'] = $cart;

    header('Location: ' . $redirect);
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$cartItems = $cart->getCart();
$total = $cart->getTotalPrice();
$totalQuantity = $cart->getTotalQuantity();

?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart | PHP OOP Project</title>

    <meta
        name="description"
        content="Ένα διαδραστικό καλάθι αγορών κατασκευασμένο με PHP και αντικειμενοστραφή προγραμματισμό."
    >

    <style>
        :root {
            --background: #0b1120;
            --surface: #151e30;
            --surface-light: #1c2940;
            --primary: #6c8cff;
            --primary-hover: #8ba3ff;
            --text: #f1f5f9;
            --muted: #a4b0c4;
            --border: #2b3952;
            --success: #54d6a0;
            --danger: #ff7777;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
        }

        button,
        input {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        .container {
            width: min(1150px, calc(100% - 36px));
            margin: 0 auto;
        }

        header {
            border-bottom: 1px solid var(--border);
            background: rgba(11, 17, 32, 0.95);
        }

        .navbar {
            min-height: 76px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .logo {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo span {
            color: var(--primary);
        }

        .cart-counter {
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 30px;
            background: var(--surface);
            color: var(--muted);
            font-size: 0.9rem;
        }

        .cart-counter strong {
            color: var(--text);
        }

        .hero {
            padding: 54px 0 34px;
        }

        .eyebrow {
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        h1 {
            margin-top: 10px;
            font-size: clamp(2rem, 5vw, 3.3rem);
            line-height: 1.15;
        }

        .hero p {
            max-width: 620px;
            margin-top: 14px;
            color: var(--muted);
        }

        .section-title {
            margin-bottom: 20px;
            font-size: 1.45rem;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .product-card,
        .cart-panel {
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--surface);
        }

        .product-card {
            display: flex;
            flex-direction: column;
            padding: 22px;
            transition: transform 0.2s, border-color 0.2s;
        }

        .product-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
        }

        .product-icon {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: var(--surface-light);
            font-size: 1.8rem;
        }

        .product-card h3 {
            margin-top: 18px;
            font-size: 1.15rem;
        }

        .product-card p {
            flex: 1;
            margin: 8px 0 18px;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .product-bottom {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .price {
            font-size: 1.15rem;
            font-weight: 800;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            padding: 10px 14px;
            border: 0;
            border-radius: 9px;
            font-weight: 700;
            transition: background 0.2s, transform 0.2s;
        }

        .btn:focus-visible,
        a:focus-visible {
            outline: 3px solid var(--primary-hover);
            outline-offset: 3px;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            border: 1px solid var(--border);
            background: var(--surface-light);
            color: var(--text);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
        }

        .btn-danger {
            background: rgba(255, 119, 119, 0.12);
            color: var(--danger);
        }

        .btn-danger:hover {
            background: rgba(255, 119, 119, 0.22);
        }

        .cart-section {
            padding: 52px 0 70px;
        }

        .cart-panel {
            padding: 25px;
        }

        .cart-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }

        .cart-heading h2 {
            font-size: 1.5rem;
        }

        .cart-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        th {
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .item-name {
            font-weight: 700;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-number {
            min-width: 18px;
            text-align: center;
            font-weight: 700;
        }

        .btn-small {
            width: 32px;
            height: 32px;
            padding: 0;
            font-size: 1.1rem;
        }

        .remove-form {
            display: inline;
        }

        .remove-link {
            border: 0;
            background: none;
            color: var(--danger);
            font-weight: 600;
        }

        .remove-link:hover {
            text-decoration: underline;
        }

        .cart-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding-top: 24px;
        }

        .cart-note {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .total-label {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .total-price {
            color: var(--success);
            font-size: 1.9rem;
            font-weight: 800;
        }

        .empty-cart {
            padding: 35px 10px;
            text-align: center;
        }

        .empty-cart .empty-icon {
            font-size: 2.5rem;
        }

        .empty-cart h3 {
            margin-top: 10px;
        }

        .empty-cart p {
            margin-top: 6px;
            color: var(--muted);
        }

        .alert {
            margin-bottom: 24px;
            padding: 13px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
        }

        .alert-success {
            border-color: rgba(84, 214, 160, 0.35);
            background: rgba(84, 214, 160, 0.08);
            color: var(--success);
        }

        .alert-error {
            border-color: rgba(255, 119, 119, 0.35);
            background: rgba(255, 119, 119, 0.08);
            color: var(--danger);
        }

        footer {
            padding: 24px 0;
            border-top: 1px solid var(--border);
            color: var(--muted);
            text-align: center;
            font-size: 0.85rem;
        }

        form {
            margin: 0;
        }

        @media (max-width: 850px) {
            .products-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .container {
                width: calc(100% - 24px);
            }

            .navbar {
                min-height: 68px;
            }

            .logo {
                font-size: 1.1rem;
            }

            .hero {
                padding-top: 38px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-card {
                padding: 18px;
            }

            .cart-panel {
                padding: 15px;
            }

            .cart-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .cart-actions {
                width: 100%;
            }

            .cart-actions .btn {
                flex: 1;
            }

            th,
            td {
                padding: 13px 9px;
            }

            .cart-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .total-price {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="container navbar">
        <div class="logo">PHP <span>Shopping Cart</span></div>

        <div class="cart-counter">
            Καλάθι:
            <strong><?= $totalQuantity ?></strong>
            τεμ.
        </div>
    </div>
</header>

<main class="container">

    <section class="hero">
        <div class="eyebrow">PHP · OOP · Shopping Cart</div>
        <h1>Το δικό σου καλάθι αγορών.</h1>
        <p>
            Πρόσθεσε προϊόντα, διαχειρίσου τις ποσότητες
            και υπολόγισε το συνολικό κόστος εύκολα.
        </p>
    </section>

    <?php if (is_array($flash)): ?>
        <div
            class="alert <?= ($flash['type'] ?? '') === 'success'
                ? 'alert-success'
                : 'alert-error' ?>"
            role="status"
        >
            <?= escapeHtml($flash['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section aria-labelledby="products-title">
        <h2 class="section-title" id="products-title">
            Διαθέσιμα προϊόντα
        </h2>

        <div class="products-grid">
            <?php foreach ($products as $name => $product): ?>
                <article class="product-card">
                    <div class="product-icon" aria-hidden="true">
                        <?= escapeHtml($product['icon']) ?>
                    </div>

                    <h3><?= escapeHtml($name) ?></h3>

                    <p><?= escapeHtml($product['description']) ?></p>

                    <div class="product-bottom">
                        <span class="price">
                            <?= euro($product['price']) ?>
                        </span>

                        <form method="post">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                            >

                            <input type="hidden" name="action" value="add">

                            <input
                                type="hidden"
                                name="product"
                                value="<?= escapeHtml($name) ?>"
                            >

                            <button class="btn btn-primary" type="submit">
                                + Προσθήκη
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cart-section" aria-labelledby="cart-title">
        <div class="cart-panel">

            <div class="cart-heading">
                <h2 id="cart-title">Το καλάθι σου</h2>

                <?php if (!empty($cartItems)): ?>
                    <div class="cart-actions">
                        <form method="post">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                            >
                            <input
                                type="hidden"
                                name="action"
                                value="sort_asc"
                            >
                            <button class="btn btn-secondary" type="submit">
                                Τιμή ↑
                            </button>
                        </form>

                        <form method="post">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                            >
                            <input
                                type="hidden"
                                name="action"
                                value="sort_desc"
                            >
                            <button class="btn btn-secondary" type="submit">
                                Τιμή ↓
                            </button>
                        </form>

                        <form
                            method="post"
                            onsubmit="return confirm('Θέλεις να αδειάσεις το καλάθι;');"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                            >
                            <input
                                type="hidden"
                                name="action"
                                value="clear"
                            >
                            <button class="btn btn-danger" type="submit">
                                Άδειασμα
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (empty($cartItems)): ?>

                <div class="empty-cart">
                    <div class="empty-icon" aria-hidden="true">🛒</div>
                    <h3>Το καλάθι σου είναι άδειο</h3>
                    <p>Πρόσθεσε ένα προϊόν από την παραπάνω λίστα για να ξεκινήσεις.</p>
                </div>

            <?php else: ?>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Προϊόν</th>
                                <th>Τιμή</th>
                                <th>Ποσότητα</th>
                                <th>Μερικό σύνολο</th>
                                <th>Ενέργεια</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td class="item-name">
                                        <?= escapeHtml($item['name']) ?>
                                    </td>

                                    <td><?= euro($item['price']) ?></td>

                                    <td>
                                        <div class="quantity-controls">

                                            <form method="post">
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                                                >
                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="decrease"
                                                >
                                                <input
                                                    type="hidden"
                                                    name="product"
                                                    value="<?= escapeHtml($item['name']) ?>"
                                                >

                                                <button
                                                    class="btn btn-secondary btn-small"
                                                    type="submit"
                                                    aria-label="Μείωση ποσότητας <?= escapeHtml($item['name']) ?>"
                                                >−</button>
                                            </form>

                                            <span class="quantity-number">
                                                <?= (int) $item['quantity'] ?>
                                            </span>

                                            <form method="post">
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                                                >
                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="add"
                                                >
                                                <input
                                                    type="hidden"
                                                    name="product"
                                                    value="<?= escapeHtml($item['name']) ?>"
                                                >

                                                <button
                                                    class="btn btn-secondary btn-small"
                                                    type="submit"
                                                    aria-label="Αύξηση ποσότητας <?= escapeHtml($item['name']) ?>"
                                                >+</button>
                                            </form>

                                        </div>
                                    </td>

                                    <td>
                                        <?= euro($item['price'] * $item['quantity']) ?>
                                    </td>

                                    <td>
                                        <form
                                            method="post"
                                            class="remove-form"
                                            onsubmit="return confirm('Να αφαιρεθεί αυτό το προϊόν;');"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                                            >
                                            <input
                                                type="hidden"
                                                name="action"
                                                value="remove"
                                            >
                                            <input
                                                type="hidden"
                                                name="product"
                                                value="<?= escapeHtml($item['name']) ?>"
                                            >

                                            <button class="remove-link" type="submit">
                                                Αφαίρεση
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-footer">
                    <p class="cart-note">
                        <?= $totalQuantity ?> τεμάχια συνολικά
                    </p>

                    <div>
                        <div class="total-label">Συνολικό κόστος</div>
                        <div class="total-price"><?= euro($total) ?></div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<footer>
    <div class="container">
        PHP OOP Shopping Cart · Portfolio Project
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const savedScroll = sessionStorage.getItem('shoppingCartScrollY');

    if (savedScroll !== null) {
        window.scrollTo(0, Number(savedScroll));
        sessionStorage.removeItem('shoppingCartScrollY');
    }

    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function (form) {
        form.addEventListener('submit', function () {
            sessionStorage.setItem('shoppingCartScrollY', String(window.scrollY));
        });
    });
});
</script>

</body>
</html>
