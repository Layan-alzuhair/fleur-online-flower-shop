<?php
/**
 * Logout accepts only POST with a valid CSRF token, so another website
 * cannot log a user out with a link or an <img> tag.
 * Opening this page directly (GET) shows a confirmation button instead.
 */
require_once __DIR__ . '/includes/init.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('error', 'Your session expired. Please try logging out again.');
        redirect('logout.php');
    }

    (new User())->logout();

    // Fresh session (with a new ID and cookie) just to carry the message
    session_start();
    session_regenerate_id(true);
    setFlash('success', 'You have been logged out.');
    redirect('login.php');
}

$pageTitle = 'Log out';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-4 text-center">
    <h1 class="h3 mb-3">Log out</h1>
    <p class="text-muted mb-4">Are you sure you want to log out?</p>
    <form method="post" action="<?= url('logout.php') ?>">
      <?= csrfField() ?>
      <button class="btn btn-dark w-100" type="submit">Log out</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
