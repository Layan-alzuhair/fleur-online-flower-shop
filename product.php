<?php
// Product details: product.php?id=3
require_once __DIR__ . '/includes/init.php';

$productId = (int) ($_GET['id'] ?? 0);
$product   = $productId > 0 ? (new Product())->find($productId) : null;

if ($product === null) {
    http_response_code(404);
}

$pageTitle       = $product ? $product['name'] : 'Bouquet not found';
$pageDescription = $product ? $product['description'] : null;
require __DIR__ . '/includes/header.php';
?>
<p class="breadcrumb-line mb-4"><a href="<?= url('index.php') ?>">Home</a> &nbsp;/&nbsp; <a href="<?= url('shop.php') ?>">Shop</a><?php if ($product): ?> &nbsp;/&nbsp; <?= e($product['name']) ?><?php endif; ?></p>

<?php if ($product === null): ?>
  <h1 class="h3">Bouquet not found</h1>
  <p>This bouquet does not exist or is no longer available. <a href="<?= url('shop.php') ?>">Browse all bouquets</a></p>
<?php else: ?>
  <div class="row g-4 g-xl-5 align-items-start product-detail">
    <div class="col-md-6">
      <img class="product-detail-img" src="<?= url($product['image']) ?>" alt="<?= e($product['name']) ?> bouquet" width="600" height="600">
    </div>
    <div class="col-md-6">
      <?php if (!empty($product['tag'])): ?><span class="product-tag position-static d-inline-block mb-2"><?= e($product['tag']) ?></span><?php endif; ?>
      <h1 class="mb-2"><?= e($product['name']) ?></h1>
      <p class="product-price mb-3"><?= money($product['price']) ?></p>
      <p><?= e($product['description']) ?></p>

      <table class="table table-sm product-facts">
        <caption class="visually-hidden">Bouquet details</caption>
        <tbody>
          <tr><th scope="row">Occasion</th><td><a href="<?= url('shop.php?occasion=' . urlencode($product['category_slug'])) ?>"><?= e($product['category_name']) ?></a></td></tr>
          <tr><th scope="row">Colour</th><td><?= e(ucfirst($product['color'])) ?></td></tr>
          <tr><th scope="row">Availability</th><td><?= (int) $product['stock'] > 0 ? 'In stock (' . (int) $product['stock'] . ' left)' : 'Out of stock' ?></td></tr>
        </tbody>
      </table>

      <?php if ((int) $product['stock'] > 0): ?>
        <!-- Same button as the shop cards: js/main.js adds it to the cart with AJAX -->
        <button type="button" class="add-btn add-btn-lg"
                data-id="<?= (int) $product['product_id'] ?>"
                data-name="<?= e($product['name']) ?>"
                data-price="<?= e($product['price']) ?>"
                data-img="<?= e($product['image']) ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
          <span class="add-label">Add to cart</span>
        </button>
      <?php else: ?>
        <button type="button" class="add-btn add-btn-lg" disabled><span class="add-label">Out of stock</span></button>
      <?php endif; ?>
      <p class="mt-4"><a href="<?= url('shop.php') ?>">&larr; Back to the shop</a></p>
    </div>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
