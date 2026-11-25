<?php
require_once __DIR__ . '/includes/init.php';

if (isLoggedIn()) {
    redirect(HOME_PAGE);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'             => post('name'),
        'email'            => post('email'),
        'phone'            => post('phone'),
        'password'         => $_POST['password'] ?? '',          // not trimmed: spaces may be intentional
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'address'          => post('address'),
    ];

    $user = new User();

    if (!verifyCsrf()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    } else {
        $errors = $user->validateRegistration($data);
    }

    if (empty($errors)) {
        $user->register($data);
        $user->login($data['email'], $data['password']);   // log them straight in
        setFlash('success', 'Welcome to Fleur, ' . $data['name'] . '!');
        redirect(HOME_PAGE);
    }
}

$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-5">
    <h1 class="h3 mb-4">Create your account</h1>
    <?php if (isset($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>

    <form method="post" id="register-form" novalidate>
      <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label" for="name">Full name</label>
        <input class="form-control<?= invalid($errors, 'name') ?>" id="name" name="name" value="<?= old('name') ?>" maxlength="100" required>
        <?= fieldError($errors, 'name') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control<?= invalid($errors, 'email') ?>" type="email" id="email" name="email" value="<?= old('email') ?>" maxlength="150" required>
        <?= fieldError($errors, 'email') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="phone">Phone</label>
        <input class="form-control<?= invalid($errors, 'phone') ?>" type="tel" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="05XXXXXXXX" required>
        <?= fieldError($errors, 'phone') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="address">Delivery address</label>
        <input class="form-control<?= invalid($errors, 'address') ?>" id="address" name="address" value="<?= old('address') ?>" maxlength="255" required>
        <?= fieldError($errors, 'address') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input class="form-control<?= invalid($errors, 'password') ?>" type="password" id="password" name="password" minlength="8" required>
        <?= fieldError($errors, 'password') ?>
      </div>
      <div class="mb-4">
        <label class="form-label" for="confirm_password">Confirm password</label>
        <input class="form-control<?= invalid($errors, 'confirm_password') ?>" type="password" id="confirm_password" name="confirm_password" required>
        <?= fieldError($errors, 'confirm_password') ?>
      </div>
      <button class="btn btn-dark w-100" type="submit">Create account</button>
    </form>
    <p class="mt-3 text-center">Already have an account? <a href="<?= url('login.php') ?>">Log in</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
