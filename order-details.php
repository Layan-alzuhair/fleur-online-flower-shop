<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$orderModel = new Order();
$orderId    = (int) ($_GET['id'] ?? 0);

// Admins can open any order; customers only their own
$order = isAdmin()
    ? $orderModel->find($orderId)
    : $orderModel->find($orderId, $_SESSION['user_id']);

if ($order === null) {
    http_response_code(404);
    setFlash('error', 'Order not found.');
    redirect(isAdmin() ? 'admin/orders.php' : 'my-orders.php');
}

$items = $orderModel->items($orderId);

$pageTitle = 'Order #' . $orderId;
require __DIR__ . '/includes/header.php';
?>
<p><a href="<?= url(isAdmin() ? 'admin/orders.php' : 'my-orders.php') ?>">&larr; Back to orders</a></p>
<h1 class="h3 mb-1">Order #<?= $order['order_id'] ?></h1>
<p class="mb-4">Status: <span class="badge text-bg-secondary"><?= e($order['status']) ?></span></p>

<div class="row g-4 g-xl-5">
  <div class="col-lg-7">
    <table class="table align-middle">
      <thead><tr><th></th><th>Bouquet</th><th>Price</th><th>Qty</th><th class="text-end">Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><img class="thumb" src="<?= url($item['image']) ?>" alt=""></td>
          <td><?= e($item['name']) ?></td>
          <td><?= money($item['price']) ?></td>
          <td><?= (int) $item['quantity'] ?></td>
          <td class="text-end"><?= money($item['line_total']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot><tr><th colspan="4">Total</th><th class="text-end"><?= money($order['total_amount']) ?></th></tr></tfoot>
    </table>
  </div>
  <div class="col-lg-5">
    <h2 class="h5">Delivery</h2>
    <dl class="row">
      <dt class="col-5">Recipient</dt><dd class="col-7"><?= e($order['recipient_name']) ?></dd>
      <dt class="col-5">Phone</dt><dd class="col-7"><?= e($order['phone']) ?></dd>
      <dt class="col-5">Address</dt><dd class="col-7"><?= e($order['delivery_address']) ?></dd>
      <dt class="col-5">Delivery date</dt><dd class="col-7"><?= e(date('d M Y', strtotime($order['delivery_date']))) ?></dd>
      <dt class="col-5">Ordered on</dt><dd class="col-7"><?= e(date('d M Y, H:i', strtotime($order['order_date']))) ?></dd>
      <?php if ($order['notes']): ?>
        <dt class="col-5">Notes</dt><dd class="col-7"><?= e($order['notes']) ?></dd>
      <?php endif; ?>
    </dl>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
