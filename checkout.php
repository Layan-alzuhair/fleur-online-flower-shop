<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();   // guests are sent to login, then brought back here

$cart   = new Cart();
$items  = $cart->items();
$errors = [];

if ($cart->isEmpty()) {
    setFlash('error', 'Your cart is empty. Add a bouquet first.');
    redirect('shop.php');
}

// Pre-fill the form from the customer's profile
$profile = (new User())->find($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'recipient_name'   => post('recipient_name'),
        'phone'            => post('phone'),
        'delivery_address' => post('delivery_address'),
        'delivery_date'    => post('delivery_date'),
        'notes'            => post('notes'),
    ];

    $order = new Order();

    if (!verifyCsrf()) {
        $errors['form'] = 'Your session expired. Please submit again.';
    } else {
        $errors = $order->validateCheckout($data);
    }

    if (empty($errors)) {
        try {
            $orderId = $order->create($_SESSION['user_id'], $items, $data);
            $cart->clear();
            setFlash('success', "Thank you! Your order #$orderId has been placed.");
            redirect('order-details.php?id=' . $orderId);
        } catch (AppException $e) {
            $errors['form'] = $e->getMessage();   // e.g. "only 2 left in stock"
            $items = $cart->items();               // refresh with current stock
        } catch (PDOException $e) {
            error_log('[Fleur] Checkout failed: ' . $e->getMessage());
            $errors['form'] = 'Your order could not be placed. Please try again.';
            $items = $cart->items();
        }
    }
}

$total     = $cart->total();
$minDate   = (new DateTime('tomorrow'))->format('Y-m-d');
$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-4">Checkout</h1>
<?php if (isset($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>

<div class="row g-4 g-xl-5">
  <div class="col-lg-7">
    <h2 class="h5 mb-3">Delivery details</h2>
    <form method="post" id="checkout-form" novalidate>
      <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label" for="recipient_name">Recipient name</label>
        <input class="form-control<?= invalid($errors, 'recipient_name') ?>" id="recipient_name" name="recipient_name" maxlength="100"
               value="<?= old('recipient_name', $profile['name'] ?? '') ?>" required>
        <?= fieldError($errors, 'recipient_name') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="phone">Phone</label>
        <input class="form-control<?= invalid($errors, 'phone') ?>" type="tel" id="phone" name="phone"
               value="<?= old('phone', $profile['phone'] ?? '') ?>" placeholder="05XXXXXXXX" required>
        <?= fieldError($errors, 'phone') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="delivery_address">Delivery address</label>
        <input class="form-control<?= invalid($errors, 'delivery_address') ?>" id="delivery_address" name="delivery_address" maxlength="255"
               value="<?= old('delivery_address', $profile['address'] ?? '') ?>" required>
        <?= fieldError($errors, 'delivery_address') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="delivery_date">Delivery date</label>
        <input class="form-control<?= invalid($errors, 'delivery_date') ?>" type="date" id="delivery_date" name="delivery_date"
               min="<?= $minDate ?>" value="<?= old('delivery_date', $minDate) ?>" required>
        <?= fieldError($errors, 'delivery_date') ?>
      </div>
      <div class="mb-4">
        <label class="form-label" for="notes">Card message / notes <span class="text-muted">(optional)</span></label>
        <textarea class="form-control<?= invalid($errors, 'notes') ?>" id="notes" name="notes" rows="3" maxlength="255"><?= old('notes') ?></textarea>
        <?= fieldError($errors, 'notes') ?>
      </div>
      <button class="btn btn-dark btn-lg w-100" type="submit">Place order — <?= money($total) ?></button>
    </form>
  </div>

  <div class="col-lg-5">
    <h2 class="h5 mb-3">Your order</h2>
    <table class="table align-middle">
      <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><img class="thumb" src="<?= url($item['img']) ?>" alt=""></td>
          <td><?= e($item['name']) ?><br><small class="text-muted"><?= money($item['price']) ?> × <?= $item['qty'] ?></small></td>
          <td class="text-end"><?= money($item['line_total']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th colspan="2">Total</th><th class="text-end"><?= money($total) ?></th></tr>
      </tfoot>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
