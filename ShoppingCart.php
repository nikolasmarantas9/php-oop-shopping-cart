
<?php

class ShoppingCart
{
    private array $cart = [];

    /**
     * Προσθέτει ένα προϊόν ή αυξάνει την ποσότητά του.
     */
    public function addProduct(string $name, $price): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Το όνομα του προϊόντος δεν μπορεί να είναι κενό.'
            );
        }

        if (!is_numeric($price) || !is_finite((float) $price) || (float) $price < 0) {
            throw new InvalidArgumentException(
                'Η τιμή πρέπει να είναι έγκυρος, μη αρνητικός αριθμός.'
            );
        }

        $price = round((float) $price, 2);

        foreach ($this->cart as &$product) {
            if ($product['name'] === $name) {
                if ($product['price'] !== $price) {
                    unset($product);

                    throw new InvalidArgumentException(
                        'Το ίδιο προϊόν δεν μπορεί να έχει διαφορετική τιμή.'
                    );
                }

                $product['quantity']++;
                unset($product);
                return;
            }
        }

        unset($product);

        $this->cart[] = [
            'name' => $name,
            'price' => $price,
            'quantity' => 1
        ];
    }

    /**
     * Μειώνει την ποσότητα κατά ένα.
     * Αν φτάσει στο μηδέν, αφαιρεί το προϊόν.
     */
    public function decreaseQuantity(string $name): bool
    {
        foreach ($this->cart as $key => &$product) {
            if ($product['name'] === $name) {
                $product['quantity']--;

                if ($product['quantity'] <= 0) {
                    unset($product);
                    unset($this->cart[$key]);
                    $this->cart = array_values($this->cart);
                    return true;
                }

                unset($product);
                return true;
            }
        }

        unset($product);
        return false;
    }

    /**
     * Αφαιρεί ολόκληρο το προϊόν από το καλάθι.
     */
    public function removeProduct(string $name): bool
    {
        foreach ($this->cart as $key => $product) {
            if ($product['name'] === $name) {
                unset($this->cart[$key]);
                $this->cart = array_values($this->cart);
                return true;
            }
        }

        return false;
    }

    /**
     * Επιστρέφει τα προϊόντα του καλαθιού.
     */
    public function getCart(): array
    {
        return array_values($this->cart);
    }

    /**
     * Υπολογίζει τη συνολική αξία.
     */
    public function getTotalPrice(): float
    {
        $total = 0;

        foreach ($this->cart as $product) {
            $total += $product['price'] * $product['quantity'];
        }

        return round($total, 2);
    }

    /**
     * Επιστρέφει το πλήθος τεμαχίων.
     */
    public function getTotalQuantity(): int
    {
        $quantity = 0;

        foreach ($this->cart as $product) {
            $quantity += $product['quantity'];
        }

        return $quantity;
    }

    /**
     * Ταξινομεί τα προϊόντα με βάση την τιμή.
     */
    public function sortByPrice(bool $descending = false): void
    {
        usort($this->cart, function ($a, $b) use ($descending) {
            $result = $a['price'] <=> $b['price'];

            return $descending ? -$result : $result;
        });
    }

    /**
     * Αδειάζει ολόκληρο το καλάθι.
     */
    public function clearCart(): void
    {
        $this->cart = [];
    }

    /**
     * Εμφανίζει το καλάθι σε μορφή HTML.
     */
    public function displayCart(): void
    {
        if (empty($this->cart)) {
            echo '<p>Το καλάθι είναι άδειο.</p>';
            return;
        }

        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Προϊόν</th>';
        echo '<th>Τιμή</th>';
        echo '<th>Ποσότητα</th>';
        echo '<th>Σύνολο</th>';
        echo '</tr></thead><tbody>';

        foreach ($this->cart as $product) {
            $name = htmlspecialchars(
                $product['name'],
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $price = number_format($product['price'], 2, ',', '.');
            $subtotal = number_format(
                $product['price'] * $product['quantity'],
                2,
                ',',
                '.'
            );

            echo '<tr>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $price . ' €</td>';
            echo '<td>' . $product['quantity'] . '</td>';
            echo '<td>' . $subtotal . ' €</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        echo '<p><strong>Συνολική τιμή: '
            . number_format($this->getTotalPrice(), 2, ',', '.')
            . ' €</strong></p>';
    }
}
