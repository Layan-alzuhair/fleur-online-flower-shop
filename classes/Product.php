<?php
/**
 * Product — reading products for the shop, and admin CRUD.
 */
class Product
{
    public const COLORS = ['pink', 'red', 'white', 'yellow', 'purple', 'mixed'];

    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =====================================================
    // READ
    // =====================================================

    /**
     * All products with their category, for shop.php.
     * Member 1 loops over this and prints one product card per row,
     * keeping the data-occasion / data-color / data-price attributes so main.js filters still work:
     *
     *   foreach ((new Product())->all() as $p):
     *     <article class="product-card" data-occasion="<?= e($p['category_slug']) ?>"
     *              data-color="<?= e($p['color']) ?>" data-price="<?= e($p['price']) ?>" ...>
     */
    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, p.id AS product_id, p.occasion_id AS category_id, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN occasions c ON c.id = p.occasion_id
             ORDER BY p.id'
        );
    }

    /**
     * Search by name or description (shop.php?q=...).
     * The search text is bound as a parameter, so it cannot change the SQL.
     */
    public function search(string $term): array
    {
        // Escape LIKE wildcards with "!" so "%" or "_" typed by the user are matched literally
        // ("!" works the same in MySQL, MariaDB and SQLite, unlike a backslash).
        $like = '%' . strtr(mb_strtolower($term), ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        return $this->db->fetchAll(
            "SELECT p.*, p.id AS product_id, p.occasion_id AS category_id, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN occasions c ON c.id = p.occasion_id
             WHERE LOWER(p.name) LIKE ? ESCAPE '!' OR LOWER(p.description) LIKE ? ESCAPE '!'
             ORDER BY p.id",
            [$like, $like]
        );
    }

    /** A few products for the homepage "featured" grid. */
    public function featured(int $limit = 4): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, p.id AS product_id, p.occasion_id AS category_id, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN occasions c ON c.id = p.occasion_id
             WHERE p.stock > 0
             ORDER BY (p.tag IS NULL), p.id
             LIMIT ' . max(1, $limit)
        );
    }

    public function find(int $productId): ?array
    {
        return $this->db->fetch(
            'SELECT p.*, p.id AS product_id, p.occasion_id AS category_id, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN occasions c ON c.id = p.occasion_id
             WHERE p.id = ?',
            [$productId]
        );
    }

    /** Lowest price per occasion slug, for the "From SAR ..." labels on the homepage. */
    public function minPriceByOccasion(): array
    {
        $rows = $this->db->fetchAll(
            'SELECT c.slug, MIN(p.price) AS min_price
             FROM occasions c
             JOIN products p ON p.occasion_id = c.id
             GROUP BY c.slug'
        );
        return array_column($rows, 'min_price', 'slug');
    }

    public function categories(): array
    {
        return $this->db->fetchAll('SELECT id AS category_id, name, slug FROM occasions ORDER BY id');
    }

    public function count(): int
    {
        return (int) $this->db->fetch('SELECT COUNT(*) AS n FROM products')['n'];
    }

    public function lowStock(int $threshold = 5): array
    {
        return $this->db->fetchAll(
            'SELECT id AS product_id, name, stock FROM products WHERE stock <= ? ORDER BY stock',
            [$threshold]
        );
    }

    // =====================================================
    // CREATE / UPDATE / DELETE  (admin only)
    // =====================================================

    /** Validates the admin product form. Image is required only when creating. */
    public function validate(array $data, ?array $file, bool $isNew): array
    {
        $errors = [];

        // Limits match the products table: name VARCHAR(100), tag VARCHAR(30),
        // price DECIMAL(8,2) (0.01 to 999,999.99), stock INT
        if (mb_strlen($data['name']) < 3 || mb_strlen($data['name']) > 100) {
            $errors['name'] = 'Name must be 3 to 100 characters.';
        }
        if (mb_strlen($data['description']) < 10 || mb_strlen($data['description']) > 2000) {
            $errors['description'] = 'Description must be 10 to 2000 characters.';
        }
        if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $data['price']) || (float) $data['price'] < 0.01) {
            $errors['price'] = 'Price must be between 0.01 and 999999.99 SAR, with at most 2 decimals.';
        }
        if (!ctype_digit($data['stock']) || strlen($data['stock']) > 7 || (int) $data['stock'] > 1000000) {
            $errors['stock'] = 'Stock must be a whole number from 0 to 1,000,000.';
        }
        if (mb_strlen($data['tag']) > 30) {
            $errors['tag'] = 'Tag must be 30 characters or fewer.';
        }
        if (!in_array($data['color'], self::COLORS, true)) {
            $errors['color'] = 'Please choose a colour.';
        }
        $categoryIds = array_column($this->categories(), 'category_id');
        if (!in_array((int) $data['category_id'], array_map('intval', $categoryIds), true)) {
            $errors['category_id'] = 'Please choose an occasion.';
        }

        $hasFile = $file && $file['error'] !== UPLOAD_ERR_NO_FILE;
        if ($isNew && !$hasFile) {
            $errors['image'] = 'Please upload a product image.';
        } elseif ($hasFile) {
            $imageError = $this->checkImage($file);
            if ($imageError) {
                $errors['image'] = $imageError;
            }
        }

        return $errors;
    }

    private function checkImage(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Image upload failed. Please try again.';
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            return 'Image must be smaller than 2 MB.';
        }
        // Check the real file content, not just the extension the user typed
        $info = @getimagesize($file['tmp_name']);
        if ($info === false || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return 'Image must be a JPG, PNG or WEBP file.';
        }
        return null;
    }

    /** Saves the uploaded image into img/uploads/ and returns its path for the database. */
    private function saveImage(array $file): string
    {
        $ext = match (getimagesize($file['tmp_name'])['mime']) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $filename = 'product-' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target   = __DIR__ . '/../img/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new AppException('Could not save the uploaded image. Please try again.');
        }
        return 'img/uploads/' . $filename;
    }

    /**
     * Adds a product. The image is saved first; if the INSERT then fails,
     * the new file is removed so no orphan upload is left behind.
     */
    public function create(array $data, array $file): int
    {
        $image = $this->saveImage($file);

        try {
            $this->db->execute(
                'INSERT INTO products (occasion_id, name, description, price, image, color, tag, stock)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    (int) $data['category_id'],
                    $data['name'],
                    $data['description'],
                    $data['price'],
                    $image,
                    $data['color'],
                    $data['tag'] !== '' ? $data['tag'] : null,
                    (int) $data['stock'],
                ]
            );
        } catch (Throwable $e) {
            $this->deleteUploadedImage($image);
            throw $e;
        }
        return $this->db->lastInsertId();
    }

    /**
     * Updates a product. If no new image was uploaded, the old one is kept.
     *
     * Order of operations when the image is replaced:
     *   1. the new image was already validated by validate()
     *   2. save the new file
     *   3. update the database row
     *   4. only after the update succeeded, delete the old file
     * If the update fails, the new file is removed and the old image stays
     * in place, so the product never points at a missing file.
     */
    public function update(int $productId, array $data, ?array $file): void
    {
        $existing = $this->find($productId);
        if ($existing === null) {
            throw new AppException('Product not found.');
        }

        $oldImage = $existing['image'];
        $newImage = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $newImage = $this->saveImage($file);
        }

        try {
            $this->db->execute(
                'UPDATE products
                 SET occasion_id = ?, name = ?, description = ?, price = ?, image = ?, color = ?, tag = ?, stock = ?
                 WHERE id = ?',
                [
                    (int) $data['category_id'],
                    $data['name'],
                    $data['description'],
                    $data['price'],
                    $newImage ?? $oldImage,
                    $data['color'],
                    $data['tag'] !== '' ? $data['tag'] : null,
                    (int) $data['stock'],
                    $productId,
                ]
            );
        } catch (Throwable $e) {
            if ($newImage !== null) {
                $this->deleteUploadedImage($newImage);
            }
            throw $e;
        }

        if ($newImage !== null) {
            $this->deleteUploadedImage($oldImage);
        }
    }

    /**
     * Deletes a product. Returns false if the product appears in past orders —
     * the foreign key protects order history, so the admin should set stock to 0 instead.
     */
    public function delete(int $productId): bool
    {
        $product = $this->find($productId);
        if ($product === null) {
            return false;
        }

        try {
            $this->db->execute('DELETE FROM products WHERE id = ?', [$productId]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // foreign key violation
                return false;
            }
            throw $e;
        }

        $this->deleteUploadedImage($product['image']);
        return true;
    }

    /** Only removes images the admin uploaded — never the template's own images. */
    private function deleteUploadedImage(string $path): void
    {
        if (str_starts_with($path, 'img/uploads/')) {
            $full = __DIR__ . '/../' . $path;
            if (is_file($full)) {
                unlink($full);
            }
        }
    }
}
