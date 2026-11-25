<?php
// READ — list all products, with links to edit and a delete button
require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$products = (new Product())->all();

$pageTitle = 'Products';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 m-0">Products (<?= count($products) ?>)</h1>
  <a class="btn btn-dark" href="<?= url('admin/product-form.php') ?>">+ Add product</a>
</div>

<div class="table-responsive">
  <table class="table align-middle">
    <thead>
      <tr><th>ID</th><th></th><th>Name</th><th>Occasion</th><th>Colour</th><th>Price</th><th>Stock</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?= $p['product_id'] ?></td>
        <td><img class="thumb" src="<?= url($p['image']) ?>" alt=""></td>
        <td><?= e($p['name']) ?><?php if ($p['tag']): ?> <span class="badge text-bg-light"><?= e($p['tag']) ?></span><?php endif; ?></td>
        <td><?= e($p['category_name']) ?></td>
        <td><?= e(ucfirst($p['color'])) ?></td>
        <td><?= money($p['price']) ?></td>
        <td class="<?= $p['stock'] <= 5 ? 'text-danger fw-semibold' : '' ?>"><?= (int) $p['stock'] ?></td>
        <td class="text-nowrap">
          <a class="btn btn-sm btn-outline-secondary" href="<?= url('admin/product-form.php?id=' . $p['product_id']) ?>">Edit</a>
          <form class="d-inline" method="post" action="<?= url('admin/delete-product.php') ?>"
                onsubmit="return confirm('Delete <?= e(addslashes($p['name'])) ?>? This cannot be undone.');">
            <?= csrfField() ?>
            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
