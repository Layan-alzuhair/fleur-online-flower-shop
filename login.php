<?php
require_once __DIR__ . '/includes/init.php';

/**
 * Only allow redirects back to pages on THIS site.
 * "?redirect=https://evil.com" would otherwise send users to another website.
 */
function safeRedirectTarget(string $target): ?string
{
    if ($target === '' || !str_starts_with($target, '/') || str_starts_with($target, '//') || str_contains($target, '\\')) {
        return null;
    }
    return $target;
}

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : HOME_PAGE);
}

$error    = null;
$redirect = safeRedirectTarget($_GET['redirect'] ?? $_POST['redirect'] ?? '');

// Slow down password guessing: after 5 wrong attempts in this session, wait 60 seconds.
// (Session-based, so it limits casual guessing; a per-account limit would need a database table.)
const MAX_LOGIN_FAILURES = 5;
const LOGIN_LOCK_SECONDS = 60;
$lockedFor = max(0, ($_SESSION['login_locked_until'] ?? 0) - time());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($lockedFor > 0) {
        $error = "Too many failed attempts. Please try again in $lockedFor seconds.";
    } else {
        $user = (new User())->login(post('email'), $_POST['password'] ?? '');

        if ($user === null) {
            $_SESSION['login_failures'] = ($_SESSION['login_failures'] ?? 0) + 1;
            if ($_SESSION['login_failures'] >= MAX_LOGIN_FAILURES) {
                $_SESSION['login_locked_until'] = time() + LOGIN_LOCK_SECONDS;
                $_SESSION['login_failures'] = 0;
                $error = 'Too many failed attempts. Please try again in ' . LOGIN_LOCK_SECONDS . ' seconds.';
            } else {
                $error = 'Incorrect email or password.';
            }
        } elseif ($user['role'] === 'admin') {
            redirect('admin/dashboard.php');
        } elseif ($redirect !== null) {
            header('Location: ' . $redirect);   // already a full path like /Web-project/checkout.php
            exit;
        } else {
            redirect(HOME_PAGE);
        }
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-4">
    <h1 class="h3 mb-4">Log in</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="post" id="login-form" novalidate>
      <?= csrfField() ?>
      <input type="hidden" name="redirect" value="<?= e($redirect ?? '') ?>">
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" required>
      </div>
      <button class="btn btn-dark w-100" type="submit">Log in</button>
    </form>
    <p class="mt-3 text-center">New to Fleur? <a href="<?= url('register.php') ?>">Create an account</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
