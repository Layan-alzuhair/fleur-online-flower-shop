<?php
/**
 * ajax/cart.php — the cart API that js/main.js talks to (Member 2).
 *
 * GET  ajax/cart.php                                   -> current cart
 * POST ajax/cart.php  action=add     product_id=3 [qty=1]
 * POST ajax/cart.php  action=update  product_id=3 qty=2   (qty 0 removes)
 * POST ajax/cart.php  action=remove  product_id=3
 * POST ajax/cart.php  action=clear
 *
 * Every POST must send the CSRF token, either as a form field "csrf_token"
 * or as the header "X-CSRF-Token" (the token is in <meta name="csrf-token">).
 *
 * Every response is JSON:
 *   {
 *     "success": true,
 *     "message": "Added to cart.",
 *     "cart": {
 *       "items": [ { "id": 1, "name": "...", "price": 68, "img": "img/...", "qty": 2, "stock": 20, "line_total": 136 } ],
 *       "count": 2,
 *       "total": 136
 *     }
 *   }
 * On failure "success" is false and "message" explains why; "cart" is still the current cart.
 */
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

function respond(bool $success, string $message, Cart $cart, int $status = 200): void
{
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'cart'    => $cart->toArray(),
    ]);
    exit;
}

$cart = new Cart();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(true, '', $cart);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.', $cart, 405);
}

if (!verifyCsrf()) {
    respond(false, 'Session expired. Please refresh the page.', $cart, 419);
}

$action    = post('action');
$productId = (int) post('product_id');
$qty       = post('qty') === '' ? 1 : (int) post('qty');

switch ($action) {
    case 'add':
        $error = $cart->add($productId, $qty);
        respond($error === null, $error ?? 'Added to cart.', $cart, $error ? 422 : 200);

    case 'update':
        $error = $cart->update($productId, $qty);
        respond($error === null, $error ?? 'Cart updated.', $cart, $error ? 422 : 200);

    case 'remove':
        $cart->remove($productId);
        respond(true, 'Removed from cart.', $cart);

    case 'clear':
        $cart->clear();
        respond(true, 'Cart cleared.', $cart);

    default:
        respond(false, 'Unknown action.', $cart, 400);
}
