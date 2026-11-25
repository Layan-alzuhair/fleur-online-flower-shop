<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$orders = (new Order())->forUser($_SESSION['user_id']);

$pageTitle = 'My orders';
require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-4">My orders</h1>

<?php if (empty($orders)): ?>
  <p>You haven't placed any orders yet. <a href="<?= url('shop.php') ?>">Browse bouquets</a></p>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr><th>Order</th><th>Date</th><th>Delivery</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= $o['order_id'] ?></td>
          <td><?= e(date('d M Y', strtotime($o['order_date']))) ?></td>
          <td><?= e(date('d M Y', strtotime($o['delivery_date']))) ?></td>
          <td><?= (int) $o['item_count'] ?></td>
          <td><?= money($o['total_amount']) ?></td>
          <td><span class="badge text-bg-secondary"><?= e($o['status']) ?></span></td>
          <td><a href="<?= url('order-details.php?id=' . $o['order_id']) ?>">View</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
