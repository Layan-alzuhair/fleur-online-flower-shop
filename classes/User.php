<?php
/**
 * User — registration, login, logout and customer lookups.
 */
class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Validates the registration form.
     * Returns an array of errors keyed by field name (empty array = valid).
     */
    public function validateRegistration(array $data): array
    {
        $errors = [];

        // Limits match the column sizes in database/fleur.sql (full_name 100, email 150, address 255)
        if (mb_strlen($data['name']) < 2 || mb_strlen($data['name']) > 100) {
            $errors['name'] = 'Please enter your full name (2 to 100 characters).';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ($this->emailExists($data['email'])) {
            $errors['email'] = 'An account with this email already exists.';
        }
        if (!preg_match('/^05\d{8}$/', $data['phone'])) {
            $errors['phone'] = 'Phone must be 10 digits starting with 05.';
        }
        if (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        if (mb_strlen($data['address']) < 5 || mb_strlen($data['address']) > 255) {
            $errors['address'] = 'Please enter your delivery address (5 to 255 characters).';
        }

        return $errors;
    }

    public function emailExists(string $email): bool
    {
        // Emails are stored in lower case, so compare in lower case too
        return $this->db->fetch('SELECT id FROM users WHERE email = ?', [strtolower(trim($email))]) !== null;
    }

    /** Creates a customer account. The password is hashed, never stored as plain text. */
    public function register(array $data): int
    {
        $this->db->execute(
            'INSERT INTO users (full_name, email, password, phone, address, role)
             VALUES (?, ?, ?, ?, ?, \'customer\')',
            [
                $data['name'],
                strtolower($data['email']),
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['phone'],
                $data['address'],
            ]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Checks email + password. On success stores the user in the session and returns the user row.
     * Returns null on wrong email OR wrong password (same message for both, so attackers
     * can't find out which emails are registered).
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->db->fetch('SELECT *, id AS user_id, full_name AS name FROM users WHERE email = ?', [strtolower($email)]);

        if ($user === null || !password_verify($password, $user['password'])) {
            return null;
        }

        session_regenerate_id(true); // new session id after login (prevents session fixation)
        unset($_SESSION['login_failures'], $_SESSION['login_locked_until']);  // reset the wrong-password counter
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        return $user;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function find(int $userId): ?array
    {
        return $this->db->fetch(
            'SELECT id AS user_id, full_name AS name, email, phone, address, role, created_at FROM users WHERE id = ?',
            [$userId]
        );
    }

    /** For admin/users.php — every customer with how many orders they placed. */
    public function allCustomers(): array
    {
        return $this->db->fetchAll(
            'SELECT u.id AS user_id, u.full_name AS name, u.email, u.phone, u.created_at,
                    COUNT(o.id) AS order_count
             FROM users u
             LEFT JOIN orders o ON o.user_id = u.id
             WHERE u.role = \'customer\'
             GROUP BY u.id
             ORDER BY u.created_at DESC'
        );
    }

    public function countCustomers(): int
    {
        return (int) $this->db->fetch('SELECT COUNT(*) AS n FROM users WHERE role = \'customer\'')['n'];
    }
}
