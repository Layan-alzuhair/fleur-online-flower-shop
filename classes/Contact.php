<?php
/**
 * Contact: messages sent from the Contact page (table contact_messages).
 * Visitors create messages; admins read them in admin/messages.php.
 */
class Contact
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Validates the contact form. Returns errors keyed by field name. */
    public function validate(array $data): array
    {
        $errors = [];

        if (mb_strlen($data['name']) < 2 || mb_strlen($data['name']) > 100) {
            $errors['name'] = 'Please enter your name (2 to 100 characters).';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (mb_strlen($data['message']) < 10 || mb_strlen($data['message']) > 2000) {
            $errors['message'] = 'Your message should be between 10 and 2000 characters.';
        }

        return $errors;
    }

    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)',
            [$data['name'], strtolower($data['email']), $data['message']]
        );
        return $this->db->lastInsertId();
    }

    /** All messages, newest first (admin). */
    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM contact_messages ORDER BY created_at DESC, id DESC');
    }

    public function count(): int
    {
        return (int) $this->db->fetch('SELECT COUNT(*) AS n FROM contact_messages')['n'];
    }
}
