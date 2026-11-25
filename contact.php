<?php
require_once __DIR__ . '/includes/init.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'    => post('name'),
        'email'   => post('email'),
        'message' => post('message'),
    ];

    if (!verifyCsrf()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    } elseif (post('website') !== '') {
        // "website" is a hidden honeypot field: people never see it, simple spam bots fill it in.
        // Pretend it worked so the bot learns nothing, but save nothing.
        setFlash('success', 'Thank you! Your message has been sent.');
        redirect('contact.php');
    } else {
        $contact = new Contact();
        $errors  = $contact->validate($data);
        if (empty($errors)) {
            $contact->create($data);
            setFlash('success', 'Thank you! Your message has been sent. We will reply by email.');
            redirect('contact.php');   // redirect after POST so a refresh doesn't send it twice
        }
    }
}

// Logged-in customers don't need to retype their name and email
$profile = isLoggedIn() ? (new User())->find($_SESSION['user_id']) : null;

$pageTitle = 'Contact us';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <h1 class="h3 mb-2">Contact us</h1>
    <p class="text-muted mb-4">Questions about an order or a custom bouquet? Send us a message.</p>
    <?php if (isset($errors['form'])): ?><div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>

    <form method="post" id="contact-form" novalidate>
      <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label" for="name">Your name</label>
        <input class="form-control<?= invalid($errors, 'name') ?>" id="name" name="name" maxlength="100" required
               value="<?= old('name', $profile['name'] ?? '') ?>" autocomplete="name">
        <?= fieldError($errors, 'name') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control<?= invalid($errors, 'email') ?>" type="email" id="email" name="email" maxlength="150" required
               value="<?= old('email', $profile['email'] ?? '') ?>" autocomplete="email">
        <?= fieldError($errors, 'email') ?>
      </div>
      <div class="mb-4">
        <label class="form-label" for="message">Message</label>
        <textarea class="form-control<?= invalid($errors, 'message') ?>" id="message" name="message" rows="5" minlength="10" maxlength="2000" required><?= old('message') ?></textarea>
        <?= fieldError($errors, 'message') ?>
      </div>
      <!-- Honeypot: hidden from people and screen readers, left empty by real visitors -->
      <div class="visually-hidden" aria-hidden="true">
        <label for="website">Leave this field empty</label>
        <input id="website" name="website" tabindex="-1" autocomplete="off">
      </div>
      <button class="btn btn-dark w-100" type="submit">Send message</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
