<?php
// DELETE — only accepts POST (a link/GET could be triggered by accident or by another site)
require_once __DIR__ . '/../includes/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('error', 'Invalid request.');
    redirect('admin/products.php');
}

$productModel = new Product();
$productId    = (int) ($_POST['product_id'] ?? 0);
$product      = $productModel->find($productId);

if ($product === null) {
    setFlash('error', 'Product not found.');
} elseif ($productModel->delete($productId)) {
    setFlash('success', "\"{$product['name']}\" was deleted.");
} else {
    setFlash('error', "\"{$product['name']}\" is part of past orders, so it can't be deleted. Set its stock to 0 to hide it from sale instead.");
}

redirect('admin/products.php');
