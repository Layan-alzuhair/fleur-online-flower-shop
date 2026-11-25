<?php
require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$orderModel = new Order();

// Status change submitted from the dropdown in the table
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status  = post('status');

    if (!verifyCsrf()) {
        setFlash('error', 'Your session expired. Please try again.');
    } else {
        try {
            $ok = $orderModel->updateStatus($orderId, $status);
            setFlash($ok ? 'success' : 'error', $ok ? "Order #$orderId is now \"$status\"." : 'Could not update that order.');
        } catch (AppException $e) {
            // e.g. re-opening a cancelled order when the stock has already been sold
            setFlash('error', $e->getMessage());
        } catch (PDOException $e) {
            error_log('[Fleur] Order status update failed: ' . $e->getMessage());
            setFlash('error', "Order #$orderId could not be updated. Please try again.");
        }
    }
    $filter = post('filter');
    redirect('admin/orders.php' . ($filter !== '' ? '?status=' . urlencode($filter) : ''));
}

$filter = $_GET['status'] ?? '';
$orders = $orderModel->all($filter !== '' ? $filter : null);

$pageTitle = 'Orders';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <h1 class="h3 m-0">Orders (<?= count($orders) ?>)</h1>
  <form method="get" class="d-flex gap-2">
    <select class="form-select" name="status" onchange="this.form.submit()" aria-label="Filter orders by status">
      <option value="">All statuses</option>
      <?php foreach (Order::STATUSES as $s): ?>
        <option <?= $filter === $s ? 'selected' : '' ?>><?= e($s) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="table-responsive">
  <table class="table align-middle">
    <thead>
      <tr><th>Order</th><th>Customer</th><th>Ordered</th><th>Delivery</th><th>Total</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="<?= url('order-details.php?id=' . $o['order_id']) ?>">#<?= $o['order_id'] ?></a></td>
        <td><?= e($o['customer_name']) ?></td>
        <td><?= e(date('d M Y', strtotime($o['order_date']))) ?></td>
        <td><?= e(date('d M Y', strtotime($o['delivery_date']))) ?></td>
        <td><?= money($o['total_amount']) ?></td>
        <td>
          <form method="post" class="d-flex gap-2">
            <?= csrfField() ?>
            <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
            <input type="hidden" name="filter" value="<?= e($filter) ?>">
            <select class="form-select form-select-sm" name="status" style="min-width: 150px" aria-label="Status for order #<?= $o['order_id'] ?>">
              <?php foreach (Order::STATUSES as $s): ?>
                <option <?= $o['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-dark" type="submit">Save</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?><tr><td colspan="6" class="text-muted">No orders found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
