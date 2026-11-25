<?php
/**
 * footer.php: site footer, slide-in cart drawer and scripts.
 * Shared by every page. Links use url() so they also work from admin/.
 */
?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <a href="<?= url('index.php') ?>" class="footer-brand">Fleur<span class="mark">.</span></a>
        <p>An online flower shop with bouquets for every occasion. Choose your flowers, pick a delivery date, and follow your order online.</p>
      </div>

      <div class="footer-col">
        <h2 class="footer-title">Shop</h2>
        <ul>
          <li><a href="<?= url('shop.php') ?>">All bouquets</a></li>
          <li><a href="<?= url('shop.php?occasion=birthday') ?>">Birthday</a></li>
          <li><a href="<?= url('shop.php?occasion=wedding') ?>">Wedding</a></li>
          <li><a href="<?= url('shop.php?occasion=anniversary') ?>">Anniversary</a></li>
          <li><a href="<?= url('shop.php?occasion=sympathy') ?>">Sympathy</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h2 class="footer-title">Fleur</h2>
        <ul>
          <li><a href="<?= url('index.php#story') ?>">About Fleur</a></li>
          <li><a href="<?= url('index.php#services') ?>">How it works</a></li>
          <li><a href="<?= url('index.php#gallery') ?>">Gallery</a></li>
          <li><a href="<?= url('index.php#delivery') ?>">Delivery</a></li>
          <li><a href="<?= url('contact.php') ?>">Contact us</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h2 class="footer-title">Account</h2>
        <ul>
          <?php if (isLoggedIn()): ?>
            <li><a href="<?= url('my-orders.php') ?>">My orders</a></li>
            <li>
              <form method="post" action="<?= url('logout.php') ?>" class="logout-form">
                <?= csrfField() ?>
                <button type="submit" class="footer-link-button">Log out</button>
              </form>
            </li>
          <?php else: ?>
            <li><a href="<?= url('login.php') ?>">Log in</a></li>
            <li><a href="<?= url('register.php') ?>">Create an account</a></li>
          <?php endif; ?>
          <li><a href="<?= url('checkout.php') ?>">Checkout</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> Fleur online flower shop.</span>
      <span>Demo store: no real orders, payments or deliveries are processed.</span>
    </div>
  </div>
</footer>

<div class="cart-overlay" aria-hidden="true"></div>
<aside class="cart-drawer" aria-hidden="true" aria-label="Shopping bag" role="dialog" aria-modal="true">
  <div class="cart-head">
    <h3>Your Bag</h3>
    <button type="button" class="cart-close" aria-label="Close bag">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M18 6 6 18M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="cart-items"></div>
  <div class="cart-foot">
    <div class="cart-total">
      <span class="lbl">Subtotal</span>
      <span class="amt" data-cart-total>SAR 0.00</span>
    </div>
    <p class="cart-note">Recipient details and delivery date are added at checkout.</p>
    <a href="<?= url('checkout.php') ?>" class="btn btn-rose btn-block">Checkout</a>
  </div>
</aside>


<script>window.FLEUR_BASE = <?= json_encode(BASE_URL . '/') ?>;</script>
<script src="<?= url('js/vendor/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= url('js/main.js') ?>"></script>
</body>
</html>
