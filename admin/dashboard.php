<?php
require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$productModel = new Product();
$orderModel   = new Order();

$stats     = $orderModel->stats();
$customers = (new User())->countCustomers();
$products  = $productModel->count();
$recent    = $orderModel->recent(5);
$lowStock  = $productModel->lowStock(5);

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Dashboard</h1>

<div class="row g-3 mb-5">
  <?php
  $cards = [
      'Products'       => $products,
      'Customers'      => $customers,
      'Orders'         => $stats['total_orders'],
      'Pending orders' => $stats['pending_orders'],
      'Revenue'        => money($stats['revenue']),
  ];
  foreach ($cards as $label => $value): ?>
    <div class="col-6 col-md">
      <div class="card h-100"><div class="card-body">
        <div class="text-muted small"><?= e($label) ?></div>
        <div class="fs-3 fw-semibold"><?= e($value) ?></div>
      </div></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4 g-xl-5">
  <div class="col-lg-7">
    <h2 class="h5">Recent orders</h2>
    <table class="table align-middle">
      <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $o): ?>
        <tr>
          <td><a href="<?= url('order-details.php?id=' . $o['order_id']) ?>">#<?= $o['order_id'] ?></a></td>
          <td><?= e($o['customer_name']) ?></td>
          <td><?= money($o['total_amount']) ?></td>
          <td><?= e($o['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($recent)): ?><tr><td colspan="4" class="text-muted">No orders yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="col-lg-5">
    <h2 class="h5">Low stock (5 or fewer)</h2>
    <ul class="list-group">
      <?php foreach ($lowStock as $p): ?>
        <li class="list-group-item d-flex justify-content-between">
          <a href="<?= url('admin/product-form.php?id=' . $p['product_id']) ?>"><?= e($p['name']) ?></a>
          <span class="badge text-bg-warning"><?= (int) $p['stock'] ?></span>
        </li>
      <?php endforeach; ?>
      <?php if (empty($lowStock)): ?><li class="list-group-item text-muted">All products are well stocked.</li><?php endif; ?>
    </ul>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
