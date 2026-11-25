<?php
/**
 * Newsletter — homepage "Subscribe" form.
 */
class Newsletter
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Returns [true, message] on success or [false, message] on failure.
     */
    public function subscribe(string $email): array
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Please enter a valid email address.'];
        }
        if ($this->db->fetch('SELECT id FROM newsletter_subscribers WHERE email = ?', [$email])) {
            return [false, 'You are already subscribed — thank you!'];
        }

        $this->db->execute('INSERT INTO newsletter_subscribers (email) VALUES (?)', [$email]);
        return [true, 'Thanks for subscribing! Look out for our seasonal picks.'];
    }
}
