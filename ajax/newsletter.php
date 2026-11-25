<?php
/**
 * ajax/newsletter.php — homepage subscribe form.
 *
 * POST email=someone@example.com  (+ csrf_token field or X-CSRF-Token header)
 * Response: { "success": true, "message": "Thanks for subscribing! ..." }
 */
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!verifyCsrf()) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh the page.']);
    exit;
}

[$success, $message] = (new Newsletter())->subscribe(post('email'));

http_response_code($success ? 200 : 422);
echo json_encode(['success' => $success, 'message' => $message]);
