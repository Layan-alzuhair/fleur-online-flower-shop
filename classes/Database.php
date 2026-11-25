<?php
/**
 * Database — one shared PDO connection for the whole site.
 *
 * Usage:
 *   require_once __DIR__ . '/../classes/Database.php';
 *   $db = Database::getInstance();
 *
 *   // SELECT many rows
 *   $products = $db->fetchAll('SELECT * FROM products WHERE color = ?', ['pink']);
 *
 *   // SELECT one row
 *   $user = $db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
 *
 *   // INSERT / UPDATE / DELETE
 *   $db->execute('UPDATE products SET stock = ? WHERE id = ?', [10, 3]);
 *   $newId = $db->lastInsertId();
 *
 * Always pass user input through the $params array (prepared statements),
 * never by concatenating it into the SQL string.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $configFile = __DIR__ . '/../config/database.php';
        if (!file_exists($configFile)) {
            die('Missing config/database.php — copy config/database.example.php and fill it in.');
        }
        $config = require $configFile;

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Log the real reason; show visitors a message without credentials or server details
            error_log('[Fleur] Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('Database connection failed. Check config/database.php and that MySQL is running in XAMPP.');
        }
    }

    /** Returns the single shared instance (Singleton pattern). */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /** Prepares and runs a query with bound parameters. */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** For INSERT / UPDATE / DELETE. Returns the number of affected rows. */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /** Transactions — used by checkout so an order and its items save together. */
    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit(); }
    public function rollBack(): void         { $this->pdo->rollBack(); }
}
