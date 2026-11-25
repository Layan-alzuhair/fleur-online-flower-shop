<?php
// Read-only list of customers. Admins can't see or change passwords.
require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$customers = (new User())->allCustomers();

$pageTitle = 'Customers';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Customers (<?= count($customers) ?>)</h1>

<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Orders</th></tr></thead>
    <tbody>
    <?php foreach ($customers as $c): ?>
      <tr>
        <td><?= $c['user_id'] ?></td>
        <td><?= e($c['name']) ?></td>
        <td><?= e($c['email']) ?></td>
        <td><?= e($c['phone']) ?></td>
        <td><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
        <td><?= (int) $c['order_count'] ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($customers)): ?><tr><td colspan="6" class="text-muted">No customers yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
