<?php
class ShoppingCart {
    private $cart = [];

    public function addProduct($name, $price) {
        foreach ($this->cart as &$product) {
            if ($product['name'] === $name) {
                $product['quantity']++;
                return;
            }
        }
        $this->cart[] = ['name' => $name, 'price' => $price, 'quantity' => 1];
    }

    public function removeProduct($name) {
        foreach ($this->cart as $key => $product) {
            if ($product['name'] === $name) {
                unset($this->cart[$key]);
                $this->cart = array_values($this->cart);
                return true;
            }
        }
        return false;
    }

    public function getTotalPrice() {
        $total = 0;
        foreach ($this->cart as $product) {
            $total += $product['price'] * $product['quantity'];
        }
        return $total;
    }

    public function sortByPrice() {
        usort($this->cart, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
    }

    public function displayCart() {
        if (empty($this->cart)) {
            echo "<p style='text-align:center;'>Το καλάθι είναι άδειο.</p>";
        } else {
            echo "<table style='width:100%; border-collapse: collapse; margin-top: 15px;'>";
            echo "<tr style='background-color: #007bff; color: white;'>";
            echo "<th style='padding: 10px; text-align: left;'>Προϊόν</th>";
            echo "<th style='padding: 10px; text-align: center;'>Τιμή</th>";
            echo "<th style='padding: 10px; text-align: center;'>Ποσότητα</th>";
            echo "<th style='padding: 10px; text-align: right;'>Σύνολο</th>";
            echo "</tr>";

            foreach ($this->cart as $product) {
                $subtotal = $product['price'] * $product['quantity'];
                echo "<tr style='border-bottom: 1px solid #ddd;'>";
                echo "<td style='padding: 10px;'>{$product['name']}</td>";
                echo "<td style='padding: 10px; text-align: center;'>€{$product['price']}</td>";
                echo "<td style='padding: 10px; text-align: center;'>{$product['quantity']}</td>";
                echo "<td style='padding: 10px; text-align: right;'>€{$subtotal}</td>";
                echo "</tr>";
            }

            echo "<tr>";
            echo "<td colspan='3' style='padding: 15px; text-align: right;'><strong>Συνολικό Ποσό:</strong></td>";
            echo "<td style='padding: 15px; text-align: right; color: #28a745;'><strong>€" . $this->getTotalPrice() . "</strong></td>";
            echo "</tr>";
            echo "</table>";
        }
    }
}
?>