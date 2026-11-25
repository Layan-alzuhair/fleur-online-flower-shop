<?php
// Read-only list of messages sent from the Contact page.
require_once __DIR__ . '/../includes/init.php';
requireAdmin();

$messages = (new Contact())->all();

$pageTitle = 'Messages';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Messages (<?= count($messages) ?>)</h1>

<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Message</th></tr></thead>
    <tbody>
    <?php foreach ($messages as $m): ?>
      <tr>
        <td class="text-nowrap"><?= e(date('d M Y, H:i', strtotime($m['created_at']))) ?></td>
        <td><?= e($m['name']) ?></td>
        <td><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></td>
        <td style="white-space: pre-line; min-width: 260px"><?= e($m['message']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($messages)): ?><tr><td colspan="4" class="text-muted">No messages yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
