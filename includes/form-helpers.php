<?php
/**
 * Small helpers for redisplaying forms after a validation error.
 *   <input name="email" value="<?= old('email') ?>">
 *   <?= fieldError($errors, 'email') ?>
 */

/** The value the user typed last time (escaped), so they don't retype everything. */
function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

/** Prints the error message under a field, if there is one. */
function fieldError(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<div class="field-error">' . e($errors[$key]) . '</div>' : '';
}

/** Adds Bootstrap's red border to a field that has an error. */
function invalid(array $errors, string $key): string
{
    return isset($errors[$key]) ? ' is-invalid' : '';
}
