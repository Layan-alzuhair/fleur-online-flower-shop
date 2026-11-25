<?php
// CREATE and UPDATE — one form for both.
//   admin/product-form.php        -> add a new product
//   admin/product-form.php?id=5   -> edit product 5
require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$productModel = new Product();
$categories   = $productModel->categories();
$productId    = (int) ($_GET['id'] ?? 0);
$isNew        = $productId === 0;
$product      = null;
$errors       = [];

if (!$isNew) {
    $product = $productModel->find($productId);
    if ($product === null) {
        setFlash('error', 'Product not found.');
        redirect('admin/products.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'        => post('name'),
        'description' => post('description'),
        'price'       => post('price'),
        'stock'       => post('stock'),
        'category_id' => post('category_id'),
        'color'       => post('color'),
        'tag'         => post('tag'),
    ];
    $file = $_FILES['image'] ?? null;

    if (!verifyCsrf()) {
        $errors['form'] = 'Your session expired. Please submit again.';
    } else {
        $errors = $productModel->validate($data, $file, $isNew);
    }

    if (empty($errors)) {
        try {
            if ($isNew) {
                $productModel->create($data, $file);
                setFlash('success', "\"{$data['name']}\" was added.");
            } else {
                $productModel->update($productId, $data, $file);
                setFlash('success', "\"{$data['name']}\" was updated.");
            }
            redirect('admin/products.php');
        } catch (AppException $e) {
            $errors['form'] = $e->getMessage();          // safe, user-friendly message
        } catch (PDOException $e) {
            error_log('[Fleur] Product save failed: ' . $e->getMessage());
            $errors['form'] = 'The product could not be saved. Please try again.';
        }
    }
}

// Value to show in each field: what was just submitted, else the saved product, else empty
$val = fn(string $key) => old($key, (string) ($product[$key] ?? ''));

$pageTitle = $isNew ? 'Add product' : 'Edit product';
require __DIR__ . '/../includes/header.php';
?>
<p><a href="<?= url('admin/products.php') ?>">&larr; Back to products</a></p>
<h1 class="h3 mb-4"><?= $isNew ? 'Add product' : 'Edit: ' . e($product['name']) ?></h1>
<?php if (isset($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" id="product-form" class="row g-3" style="max-width: 760px" novalidate>
  <?= csrfField() ?>
  <div class="col-12">
    <label class="form-label" for="name">Name</label>
    <input class="form-control<?= invalid($errors, 'name') ?>" id="name" name="name" maxlength="100" value="<?= $val('name') ?>" required>
    <?= fieldError($errors, 'name') ?>
  </div>
  <div class="col-12">
    <label class="form-label" for="description">Description</label>
    <textarea class="form-control<?= invalid($errors, 'description') ?>" id="description" name="description" maxlength="2000" rows="3" required><?= $val('description') ?></textarea>
    <?= fieldError($errors, 'description') ?>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="price">Price (SAR)</label>
    <input class="form-control<?= invalid($errors, 'price') ?>" type="number" step="0.01" min="0.01" max="999999.99" id="price" name="price" value="<?= $val('price') ?>" required>
    <?= fieldError($errors, 'price') ?>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="stock">Stock</label>
    <input class="form-control<?= invalid($errors, 'stock') ?>" type="number" min="0" max="1000000" step="1" id="stock" name="stock" value="<?= $val('stock') ?>" required>
    <?= fieldError($errors, 'stock') ?>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="tag">Tag <span class="text-muted">(optional)</span></label>
    <input class="form-control<?= invalid($errors, 'tag') ?>" id="tag" name="tag" maxlength="30" value="<?= $val('tag') ?>" placeholder="Bestseller">
    <?= fieldError($errors, 'tag') ?>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="category_id">Occasion</label>
    <select class="form-select<?= invalid($errors, 'category_id') ?>" id="category_id" name="category_id" required>
      <option value="">Choose…</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['category_id'] ?>" <?= $val('category_id') === (string) $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?= fieldError($errors, 'category_id') ?>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="color">Colour</label>
    <select class="form-select<?= invalid($errors, 'color') ?>" id="color" name="color" required>
      <option value="">Choose…</option>
      <?php foreach (Product::COLORS as $color): ?>
        <option value="<?= $color ?>" <?= $val('color') === $color ? 'selected' : '' ?>><?= ucfirst($color) ?></option>
      <?php endforeach; ?>
    </select>
    <?= fieldError($errors, 'color') ?>
  </div>
  <div class="col-12">
    <label class="form-label" for="image">Image <?= $isNew ? '' : '<span class="text-muted">(leave empty to keep the current one)</span>' ?></label>
    <?php if (!$isNew): ?><div class="mb-2"><img class="thumb" src="<?= url($product['image']) ?>" alt=""></div><?php endif; ?>
    <input class="form-control<?= invalid($errors, 'image') ?>" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" <?= $isNew ? 'required' : '' ?>>
    <?= fieldError($errors, 'image') ?>
  </div>
  <div class="col-12">
    <button class="btn btn-dark" type="submit"><?= $isNew ? 'Add product' : 'Save changes' ?></button>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
