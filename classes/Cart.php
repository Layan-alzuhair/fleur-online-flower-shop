<?php
/**
 * Cart — the shopping cart, stored in the PHP session.
 *
 * Session format:  $_SESSION['cart'] = [product_id => quantity, ...]
 *
 * Prices are always read fresh from the database, never trusted from the browser,
 * so a user can't change a price in JavaScript and pay less.
 */
class Cart
{
    private Product $products;

    public function __construct()
    {
        $this->products = new Product();
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Adds a product (or more of it). Returns an error message, or null on success.
     */
    public function add(int $productId, int $qty = 1): ?string
    {
        $product = $this->products->find($productId);
        if ($product === null) {
            return 'Product not found.';
        }
        if ($qty < 1) {
            return 'Quantity must be at least 1.';
        }

        $newQty = ($_SESSION['cart'][$productId] ?? 0) + $qty;
        if ($newQty > $product['stock']) {
            return $product['stock'] > 0
                ? "Only {$product['stock']} left in stock."
                : 'Sorry, this bouquet is out of stock.';
        }

        $_SESSION['cart'][$productId] = $newQty;
        return null;
    }

    /** Sets an exact quantity. 0 removes the item. Returns an error message, or null. */
    public function update(int $productId, int $qty): ?string
    {
        if ($qty <= 0) {
            $this->remove($productId);
            return null;
        }
        if (!isset($_SESSION['cart'][$productId])) {
            return 'This item is not in your cart.';
        }

        $product = $this->products->find($productId);
        if ($product === null) {
            $this->remove($productId);
            return 'This product is no longer available.';
        }
        if ($qty > $product['stock']) {
            return "Only {$product['stock']} left in stock.";
        }

        $_SESSION['cart'][$productId] = $qty;
        return null;
    }

    public function remove(int $productId): void
    {
        unset($_SESSION['cart'][$productId]);
    }

    public function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    public function isEmpty(): bool
    {
        return count($_SESSION['cart']) === 0;
    }

    /**
     * Cart lines with current product data.
     * Shape matches what js/main.js already uses: id, name, price, img, qty
     * (+ stock and line_total for the cart/checkout pages).
     */
    public function items(): array
    {
        $items = [];
        foreach ($_SESSION['cart'] as $productId => $qty) {
            $product = $this->products->find((int) $productId);
            if ($product === null) {          // product was deleted by the admin
                $this->remove((int) $productId);
                continue;
            }
            $items[] = [
                'id'         => (int) $product['product_id'],
                'name'       => $product['name'],
                'price'      => (float) $product['price'],
                'img'        => $product['image'],
                'qty'        => (int) $qty,
                'stock'      => (int) $product['stock'],
                'line_total' => round($product['price'] * $qty, 2),
            ];
        }
        return $items;
    }

    public function count(): int
    {
        return array_sum($_SESSION['cart']);
    }

    public function total(): float
    {
        return round(array_sum(array_column($this->items(), 'line_total')), 2);
    }

    /** Everything the JavaScript needs to redraw the cart drawer, in one array. */
    public function toArray(): array
    {
        $items = $this->items();
        return [
            'items' => $items,
            'count' => array_sum(array_column($items, 'qty')),
            'total' => round(array_sum(array_column($items, 'line_total')), 2),
        ];
    }
}
