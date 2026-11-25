<?php
/**
 * header.php: the site navbar, shared by every page.
 * Loads one stylesheet (css/style.css), so all pages look consistent.
 *
 * Variables a page can set before including this file:
 *   $pageTitle        (required) shown in the browser tab
 *   $pageDescription  (optional) meta description
 *   $fullWidth        (optional) true for pages whose sections span the full width
 *                     (home, shop); other pages get a centred container
 */
$flash = getFlash();
$user  = currentUser();

/** Marks the current page's link as active in the navbar. */
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');
$active  = fn(string $file) => $current === $file ? ' class="active"' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('js')</script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
  <title><?= e($pageTitle ?? 'Fleur') ?> | Fleur</title>
  <meta name="description" content="<?= e($pageDescription ?? 'Fleur online flower shop: bouquets for every occasion.') ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= url('css/vendor/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>

<header>
  <nav class="site-nav scrolled" aria-label="Primary">
    <div class="container">
      <a class="nav-brand" href="<?= url('index.php') ?>">Fleur<span class="mark">.</span></a>

      <ul class="nav-links" id="primary-menu">
        <?php if (isAdmin()): ?>
          <li><a href="<?= url('admin/dashboard.php') ?>"<?= $active('dashboard.php') ?>>Dashboard</a></li>
          <li><a href="<?= url('admin/products.php') ?>"<?= $active('products.php') ?>>Products</a></li>
          <li><a href="<?= url('admin/orders.php') ?>"<?= $active('orders.php') ?>>Orders</a></li>
          <li><a href="<?= url('admin/users.php') ?>"<?= $active('users.php') ?>>Customers</a></li>
          <li><a href="<?= url('admin/messages.php') ?>"<?= $active('messages.php') ?>>Messages</a></li>
        <?php else: ?>
          <li><a href="<?= url('index.php') ?>"<?= $active('index.php') ?>>Home</a></li>
          <li><a href="<?= url('shop.php') ?>"<?= $active('shop.php') ?>>Shop</a></li>
          <li><a href="<?= url('contact.php') ?>"<?= $active('contact.php') ?>>Contact</a></li>
          <?php if ($user): ?>
            <li><a href="<?= url('my-orders.php') ?>"<?= $active('my-orders.php') ?>>My Orders</a></li>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($user): ?>
          <li>
            <form method="post" action="<?= url('logout.php') ?>" class="logout-form">
              <?= csrfField() ?>
              <button type="submit" class="nav-link-button">Log out</button>
            </form>
          </li>
        <?php else: ?>
          <li><a href="<?= url('login.php') ?>"<?= $active('login.php') ?>>Log in</a></li>
          <li><a href="<?= url('register.php') ?>"<?= $active('register.php') ?>>Register</a></li>
        <?php endif; ?>
      </ul>

      <div class="nav-actions">
        <?php if (!isAdmin()): ?>
          <button type="button" class="cart-pill" data-cart-open aria-label="Open shopping bag">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
              <path d="M3 6h18"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <span>Bag</span>
            <span class="cart-count" aria-live="polite">0</span>
          </button>
        <?php endif; ?>

        <?php if ($user): ?>
          <span class="nav-hello d-none d-lg-inline">Hi, <?= e($user['name']) ?></span>
        <?php endif; ?>

        <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="primary-menu" aria-label="Toggle menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </nav>
  <div class="nav-backdrop" aria-hidden="true"></div>
</header>

<?php if (!empty($fullWidth)): ?>
<main>
  <?php if ($flash): ?>
    <div class="flash-float alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible" role="status">
      <?= e($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
<?php else: ?>
<main class="container page-body">
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>" role="status"><?= e($flash['message']) ?></div>
  <?php endif; ?>
<?php endif; ?>
