-- =========================================================
-- Fleur: Online Flower Shop
-- MySQL / MariaDB schema and sample data
--
-- Import in phpMyAdmin (XAMPP): Import > choose this file > Go
-- or from a terminal:            mysql -u root < fleur.sql
--
-- WARNING: this file starts with DROP DATABASE IF EXISTS fleur.
-- Importing it deletes any existing database named "fleur"
-- (and every order, user and product in it) and recreates it
-- with the sample data below.
--
-- Prices are in Saudi Riyals (SAR).
-- Demo accounts (password for both: Admin@123):
--   admin@fleur.test     (admin)
--   customer@fleur.test  (customer)
-- =========================================================

DROP DATABASE IF EXISTS fleur;
CREATE DATABASE fleur CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fleur;

-- ---------------------------------------------------------
-- users: customers and admins share one table, split by role
-- ---------------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,           -- password_hash() output
    phone       VARCHAR(20),                     -- extra (register form)
    address     VARCHAR(255),                    -- extra (register form)
    role        ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- occasions: Birthday, Wedding, ...
-- slug matches the data-occasion attribute used by main.js filters
-- ---------------------------------------------------------
CREATE TABLE occasions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    slug        VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- products
-- ---------------------------------------------------------
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    price       DECIMAL(8,2) NOT NULL,
    color       ENUM('pink','red','white','yellow','purple','mixed') NOT NULL,
    image       VARCHAR(255) NOT NULL,           -- full path, e.g. img/bouquet-peony.jpg
    stock       INT NOT NULL DEFAULT 0,
    tag         VARCHAR(30) NULL,                -- e.g. Bestseller
    occasion_id INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- extra
    CONSTRAINT fk_products_occasion FOREIGN KEY (occasion_id)
        REFERENCES occasions(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_price CHECK (price >= 0),
    CONSTRAINT chk_stock CHECK (stock >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- orders
-- ---------------------------------------------------------
CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    total            DECIMAL(10,2) NOT NULL,
    status           ENUM('pending','confirmed','preparing','out for delivery','delivered','cancelled')
                     NOT NULL DEFAULT 'pending',     -- spec values + extra values kept
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recipient_name   VARCHAR(100) NOT NULL,          -- extra (checkout form)
    phone            VARCHAR(20) NOT NULL,           -- extra (checkout form)
    delivery_address VARCHAR(255) NOT NULL,          -- extra (checkout form)
    delivery_date    DATE NOT NULL,                  -- extra (checkout form)
    notes            VARCHAR(255),                   -- extra (checkout form)
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_total CHECK (total >= 0),
    INDEX idx_orders_status (status)                 -- admin filters orders by status
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- order_items: one row per product in an order
-- unit_price is copied at checkout so old orders keep their price
-- ---------------------------------------------------------
CREATE TABLE order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL,
    unit_price  DECIMAL(8,2) NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id)
        REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_qty CHECK (quantity > 0),
    CONSTRAINT chk_unit_price CHECK (unit_price >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- contact_messages: the contact page form
-- ---------------------------------------------------------
CREATE TABLE contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    message     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- newsletter_subscribers
-- ---------------------------------------------------------
CREATE TABLE newsletter_subscribers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(150) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- Sample data
-- =========================================================

-- Admin login:    admin@fleur.test    / Admin@123
-- Customer login: customer@fleur.test / Admin@123
INSERT INTO users (full_name, email, password, phone, address, role) VALUES
('Fleur Admin',   'admin@fleur.test',    '$2y$10$hs.zr3e2h2YcprTc/1rtGOf20KYoD1eeONceKGTkD88UOfQJ8sm9O', '0500000000', 'Riyadh', 'admin'),
('Test Customer', 'customer@fleur.test', '$2y$10$l7Vgd12AqkcT2ksU049xXOgJh7SXPKj/nO.pAGMBMZbIpSxTvjhrO', '0511111111', 'Riyadh, Al Olaya', 'customer');

INSERT INTO occasions (name, slug) VALUES
('Birthday',     'birthday'),
('Anniversary',  'anniversary'),
('Wedding',      'wedding'),
('Sympathy',     'sympathy'),
('Get Well',     'get-well'),
('Just Because', 'just-because');

-- 14 sample bouquets (prices in Saudi Riyals, SAR)
INSERT INTO products (occasion_id, name, description, price, image, color, tag, stock) VALUES
(1, 'Blush Peony Bloom',          'Ruffled peonies in every shade of pink with dusty miller.',            255.00, 'img/bouquet-peony.jpg',       'pink',   'Bestseller', 20),
(2, 'Garden Rose & Eucalyptus',   'Fragrant garden roses with silver eucalyptus and waxflower.',          275.00, 'img/bouquet-garden-rose.jpg', 'pink',   NULL,         15),
(6, 'Wild Meadow Bunch',          'A loose gathering of cornflowers, scabiosa and grasses.',              195.00, 'img/bouquet-meadow.jpg',      'mixed',  NULL,         25),
(4, 'White Lily Serenity',        'Serene white oriental lilies and stock, gently scented.',              300.00, 'img/bouquet-lily.jpg',        'white',  NULL,         10),
(5, 'Sunlit Ranunculus',          'Layered ranunculus in coral and butter yellow to lift spirits.',       215.00, 'img/bouquet-ranunculus.jpg',  'yellow', NULL,         18),
(3, 'Bridal Ivory Cascade',       'A trailing cascade of ivory roses, ranunculus and amaranthus.',       545.00, 'img/bouquet-bridal.jpg',      'white',  'Bespoke',     5),
(6, 'Dusk Tulip Trio',            'Three dozen tulips in dusky plum, lilac and cream.',                   175.00, 'img/bouquet-tulip.jpg',       'purple', NULL,         30),
(5, 'Golden Freesia Basket',      'Sweetly scented freesia and mini sunflowers in a keepsake basket.',    235.00, 'img/bouquet-freesia.jpg',     'yellow', NULL,         12),
(1, 'Velvet Dahlia',              'Show-stopping dahlias in burgundy and plum with copper foliage.',      270.00, 'img/bouquet-dahlia.jpg',      'red',    NULL,         14),
(2, 'Classic Red Rose Dozen',     'Twelve long-stem red roses, the enduring way to say I love you.',      320.00, 'img/bouquet-red-rose.jpg',    'red',    'Bestseller', 40),
(4, 'White Orchid Grace',         'A potted white phalaenopsis orchid, calm and long-lasting.',           355.00, 'img/bouquet-orchid.jpg',      'white',  NULL,          8),
(6, 'Wildflower Jam Jar',         'A cheerful little posy in a reusable jam jar, ready for a windowsill.',145.00, 'img/bouquet-jamjar.jpg',      'mixed',  'Best value',  35),
(1, 'Coral Charm Posy',           'Coral peonies and pink ranunculus in a soft, romantic posy.',          205.00, 'img/bouquet-coral.jpg',       'pink',   NULL,         22),
(3, 'Sage & Stock Bundle',        'Column stock and soft sage foliage, an airy neutral for any table.',   250.00, 'img/bouquet-stock.jpg',       'white',  NULL,         16);

-- One sample order so My Orders and the admin pages have data to show
INSERT INTO orders (user_id, total, recipient_name, phone, delivery_address, delivery_date, notes, status) VALUES
(2, 780.00, 'Test Customer', '0511111111', 'Riyadh, Al Olaya', '2026-11-20', 'Please call on arrival', 'pending');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1,  1, 255.00),
(1, 10, 1, 320.00),
(1, 13, 1, 205.00);
-- 255 + 320 + 205 = 780.00 SAR (matches orders.total)

INSERT INTO contact_messages (name, email, message) VALUES
('Test Customer', 'customer@fleur.test', 'Do you deliver on Fridays?');

INSERT INTO newsletter_subscribers (email) VALUES ('subscriber@fleur.test');
