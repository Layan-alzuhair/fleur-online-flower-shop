<?php
/**
 * auth.php — who is logged in, and page protection.
 * Loaded automatically by init.php.
 *
 * At the top of a customer-only page:  requireLogin();
 * At the top of an admin page:         requireAdmin();
 */

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Basic info about the logged-in user (from the session), or null. */
function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'name'    => $_SESSION['name'],
        'role'    => $_SESSION['role'],
    ];
}

/** Sends guests to the login page, then back here after they log in. */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to continue.');
        $back = $_SERVER['REQUEST_URI'] ?? '';
        redirect('login.php?redirect=' . urlencode($back));
    }
}

/** Blocks everyone except admins — typing /admin/... in the URL is not enough. */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        setFlash('error', 'You do not have access to that page.');
        redirect(HOME_PAGE);
    }
}
