<?php
require_once __DIR__ . '/includes/init.php';

$productModel = new Product();
$featured     = $productModel->featured(8);          // featured bouquets come from the database
$fromPrice    = $productModel->minPriceByOccasion();  // "From SAR ..." on each occasion card
$productCount = $productModel->count();

$pageTitle       = 'Online Flower Shop';
$pageDescription = 'Fleur is an online flower shop: browse bouquets by occasion, add them to your cart and choose a delivery date at checkout.';
$fullWidth       = true;                              // homepage sections span the full width
require __DIR__ . '/includes/header.php';
?>

    <!-- ======================================================== HERO -->
    <section class="hero" id="home">
      <div class="container">
        <div class="hero-grid">
          <div class="hero-copy">
            <span class="eyebrow">Fleur Online Flower Shop</span>
            <h1>Fresh flowers for <em>every</em> occasion</h1>
            <p class="hero-lead">Browse hand-tied bouquets for birthdays, weddings, anniversaries and more. Add your favourites to the cart, choose a delivery date, and follow your order online.</p>
            <div class="hero-cta">
              <a href="shop.php" class="btn btn-rose">Shop Bouquets</a>
              <a href="#occasions" class="btn btn-outline">Shop by Occasion</a>
            </div>
            <div class="hero-meta">
              <div>
                <div class="num"><span data-count="<?= $productCount ?>"><?= $productCount ?></span></div>
                <div class="lbl">Bouquets in the shop</div>
              </div>
              <div>
                <div class="num"><span data-count="<?= count($fromPrice) ?>"><?= count($fromPrice) ?></span></div>
                <div class="lbl">Occasions</div>
              </div>
              <div>
                <div class="num">24/7</div>
                <div class="lbl">Online ordering</div>
              </div>
            </div>
          </div>

          <div class="hero-figure">
            <img src="img/hero-bouquet.jpg" alt="Bright orange poppies in a sunny field" width="720" height="900" fetchpriority="high">
            <div class="hero-badge">
              <span class="dot" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              </span>
              <div>
                <strong>You pick the day</strong>
                <span>Choose your delivery date at checkout</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- =================================================== OCCASIONS -->
    <section class="section bg-cream2" id="occasions">
      <div class="container">
        <div class="text-center reveal">
          <span class="eyebrow center">Shop by Occasion</span>
          <h2 class="section-title">A bouquet for every moment</h2>
          <p class="section-lead">Browse bouquets grouped by occasion, then filter by colour and price in the shop.</p>
        </div>

        <div class="occasion-grid" style="margin-top:3rem">
          <a class="occasion-card reveal" href="shop.php?occasion=birthday">
            <img src="img/occasion-birthday.jpg" alt="Heart-shaped bouquet of pink roses and yellow flowers held in two hands" width="360" height="480" loading="lazy">
            <div class="oc-body">
              <span><?= isset($fromPrice['birthday']) ? 'From ' . money($fromPrice['birthday']) : 'Coming soon' ?></span>
              <h3>Birthday</h3>
            </div>
          </a>
          <a class="occasion-card reveal d1" href="shop.php?occasion=wedding">
            <img src="img/occasion-wedding.jpg" alt="Flower stand with buckets of fresh flowers and potted plants" width="360" height="480" loading="lazy">
            <div class="oc-body">
              <span><?= isset($fromPrice['wedding']) ? 'From ' . money($fromPrice['wedding']) : 'Coming soon' ?></span>
              <h3>Wedding</h3>
            </div>
          </a>
          <a class="occasion-card reveal d2" href="shop.php?occasion=sympathy">
            <img src="img/occasion-sympathy.jpg" alt="Bunch of multicoloured roses" width="360" height="480" loading="lazy">
            <div class="oc-body">
              <span><?= isset($fromPrice['sympathy']) ? 'From ' . money($fromPrice['sympathy']) : 'Coming soon' ?></span>
              <h3>Sympathy</h3>
            </div>
          </a>
          <a class="occasion-card reveal" href="shop.php?occasion=anniversary">
            <img src="img/occasion-anniversary.jpg" alt="Close-up of red roses" width="360" height="480" loading="lazy">
            <div class="oc-body">
              <span><?= isset($fromPrice['anniversary']) ? 'From ' . money($fromPrice['anniversary']) : 'Coming soon' ?></span>
              <h3>Anniversary</h3>
            </div>
          </a>
          <a class="occasion-card reveal d1" href="shop.php?occasion=get-well">
            <img src="img/occasion-getwell.jpg" alt="A tall sunflower in bloom" width="360" height="480" loading="lazy">
            <div class="oc-body">
              <span><?= isset($fromPrice['get-well']) ? 'From ' . money($fromPrice['get-well']) : 'Coming soon' ?></span>
              <h3>Get Well</h3>
            </div>
          </a>
          <a class="occasion-card reveal d2" href="shop.php?occasion=just-because">
            <img src="img/occasion-justbecause.jpg" alt="Pink blossoms against a blue sky" width="360" height="480" loading="lazy">
            <div class="oc-body">
              <span><?= isset($fromPrice['just-because']) ? 'From ' . money($fromPrice['just-because']) : 'Coming soon' ?></span>
              <h3>Just Because</h3>
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- ==================================================== FEATURED -->
    <section class="section" id="shop">
      <div class="container">
        <div class="text-center reveal">
          <span class="eyebrow center">This Week's Blooms</span>
          <h2 class="section-title">Featured bouquets</h2>
          <p class="section-lead">Hand-tied in the studio and ready to send today. Filter by occasion to find the right one.</p>
        </div>

        <div class="filter-bar reveal" data-filter-bar="#featured-grid" role="group" aria-label="Filter bouquets by occasion" style="margin-top:2.4rem">
          <button type="button" class="filter-btn active" data-filter="all" aria-pressed="true">All</button>
          <button type="button" class="filter-btn" data-filter="birthday" aria-pressed="false">Birthday</button>
          <button type="button" class="filter-btn" data-filter="anniversary" aria-pressed="false">Anniversary</button>
          <button type="button" class="filter-btn" data-filter="wedding" aria-pressed="false">Wedding</button>
          <button type="button" class="filter-btn" data-filter="sympathy" aria-pressed="false">Sympathy</button>
          <button type="button" class="filter-btn" data-filter="get-well" aria-pressed="false">Get Well</button>
          <button type="button" class="filter-btn" data-filter="just-because" aria-pressed="false">Just Because</button>
        </div>

        <div class="product-grid" id="featured-grid">
          <?php foreach ($featured as $i => $p): ?>
          <article class="product-card reveal<?= ['', ' d1', ' d2', ' d3'][$i % 4] ?>" data-occasion="<?= e($p['category_slug']) ?>">
            <div class="product-media">
              <?php if (!empty($p['tag'])): ?><span class="product-tag"><?= e($p['tag']) ?></span><?php endif; ?>
              <a href="product.php?id=<?= (int) $p['product_id'] ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?> bouquet" width="400" height="400" loading="lazy"></a>
            </div>
            <div class="product-body">
              <h3><a href="product.php?id=<?= (int) $p['product_id'] ?>" class="product-link"><?= e($p['name']) ?></a></h3>
              <p class="note"><?= e($p['description']) ?></p>
              <div class="product-foot">
                <span class="product-price"><?= money($p['price']) ?></span>
                <button type="button" class="add-btn" data-id="<?= (int) $p['product_id'] ?>" data-name="<?= e($p['name']) ?>" data-price="<?= e($p['price']) ?>" data-img="<?= e($p['image']) ?>">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                  <span class="add-label">Add</span>
                </button>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

        <div class="text-center reveal" style="margin-top:3rem">
          <a href="shop.php" class="btn btn-outline">View the full shop</a>
        </div>
      </div>
    </section>

    <!-- ======================================================= ABOUT -->
    <section class="section" id="story">
      <div class="container">
        <div class="story-grid">
          <div class="story-figure reveal">
            <img src="img/story-studio.jpg" alt="Heart-shaped bouquet of roses held in two hands" width="560" height="672" loading="lazy">
          </div>
          <div class="reveal d1">
            <span class="eyebrow">About Fleur</span>
            <h2 class="section-title">Flowers, ordered the easy way</h2>
            <p>Fleur is an online flower shop built for customers who want to send flowers without visiting a physical store. Every bouquet in the shop shows its price, occasion, colour and available stock, so it is easy to find the right one.</p>
            <p>Customers create an account, add bouquets to their cart, and check out with the recipient's details, a delivery date and an optional card message. After ordering, they can follow each order's status from the My Orders page.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ================================================ HOW IT WORKS -->
    <section class="section bg-blush" id="services">
      <div class="container">
        <div class="text-center reveal">
          <span class="eyebrow center">How It Works</span>
          <h2 class="section-title">Three steps to send flowers</h2>
          <p class="section-lead">From choosing a bouquet to following the delivery, everything happens on the website.</p>
        </div>

        <div class="service-grid" style="margin-top:3rem">
          <div class="service-card reveal">
            <div class="service-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2s3.5 3.5 3.5 7a3.5 3.5 0 0 1-7 0C8.5 5.5 12 2 12 2z"/><path d="M5 13c2 0 4 1 4 4M19 13c-2 0-4 1-4 4M12 22v-5"/></svg>
            </div>
            <h3>1. Choose a bouquet</h3>
            <p>Filter the shop by occasion, colour and price, sort the results, and add bouquets to your cart.</p>
            <a href="shop.php" class="link-arrow">Go to the shop <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>

          <div class="service-card reveal d1">
            <div class="service-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <h3>2. Check out</h3>
            <p>Enter the recipient's name, phone and address, pick a delivery date from tomorrow onwards, and add a card message.</p>
            <a href="checkout.php" class="link-arrow">Checkout <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>

          <div class="service-card reveal d2">
            <div class="service-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <h3>3. Follow your order</h3>
            <p>See every order you placed with its items, total and current status, from pending to delivered.</p>
            <a href="my-orders.php" class="link-arrow">My Orders <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================================================== GALLERY -->
    <section class="section" id="gallery">
      <div class="container">
        <div class="text-center reveal">
          <span class="eyebrow center">Gallery</span>
          <h2 class="section-title">Arrangement gallery</h2>
          <p class="section-lead">Arrangement ideas for different occasions. Select any image to view it larger.</p>
        </div>

        <div class="gallery-grid" style="margin-top:3rem">
          <button type="button" class="gallery-item tall reveal" data-lightbox data-full="img/gallery-01.jpg" data-caption="Flower stand with buckets of fresh flowers">
            <img src="img/gallery-01.jpg" alt="Flower stand with buckets of fresh flowers" width="300" height="400" loading="lazy">
            <span class="zoom" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4M11 8v6M8 11h6"/></svg></span>
          </button>
          <button type="button" class="gallery-item reveal d1" data-lightbox data-full="img/gallery-02.jpg" data-caption="Multicoloured roses">
            <img src="img/gallery-02.jpg" alt="Bunch of multicoloured roses" width="300" height="200" loading="lazy">
            <span class="zoom" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4M11 8v6M8 11h6"/></svg></span>
          </button>
          <button type="button" class="gallery-item wide reveal d2" data-lightbox data-full="img/gallery-03.jpg" data-caption="Orange poppies">
            <img src="img/gallery-03.jpg" alt="Orange poppies in a sunny field" width="600" height="200" loading="lazy">
            <span class="zoom" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4M11 8v6M8 11h6"/></svg></span>
          </button>
          <button type="button" class="gallery-item reveal" data-lightbox data-full="img/gallery-04.jpg" data-caption="Sunflowers">
            <img src="img/gallery-04.jpg" alt="Field of sunflowers" width="300" height="200" loading="lazy">
            <span class="zoom" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4M11 8v6M8 11h6"/></svg></span>
          </button>
          <button type="button" class="gallery-item reveal d1" data-lightbox data-full="img/gallery-05.jpg" data-caption="Red roses">
            <img src="img/gallery-05.jpg" alt="Close-up of red roses" width="300" height="200" loading="lazy">
            <span class="zoom" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4M11 8v6M8 11h6"/></svg></span>
          </button>
          <button type="button" class="gallery-item tall reveal d2" data-lightbox data-full="img/gallery-06.jpg" data-caption="Orange roses and snapdragons">
            <img src="img/gallery-06.jpg" alt="Bouquet of orange roses and snapdragons in a glass jar" width="300" height="400" loading="lazy">
            <span class="zoom" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4M11 8v6M8 11h6"/></svg></span>
          </button>
        </div>
      </div>
    </section>

    <!-- ==================================================== DELIVERY -->
    <section class="section bg-cream2" id="delivery">
      <div class="container">
        <div class="delivery-grid">
          <div class="reveal">
            <span class="eyebrow">Delivery</span>
            <h2 class="section-title">Delivered on the date you choose</h2>
            <p class="section-lead">At checkout you enter where the bouquet should go and choose a delivery date. Orders can be scheduled from tomorrow onwards.</p>
            <ul class="area-list">
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Delivery date of your choice</li>
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Recipient name and phone</li>
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Optional card message</li>
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Order status you can follow</li>
            </ul>
          </div>

          <div class="delivery-info reveal d1">
            <div class="row-item">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              <div>
                <h3 class="h4">Choose the date</h3>
                <p>Pick any delivery date from tomorrow onwards when you check out.</p>
              </div>
            </div>
            <div class="row-item">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <div>
                <h3 class="h4">Add a card message</h3>
                <p>Write a short note to go with the bouquet, up to 255 characters.</p>
              </div>
            </div>
            <div class="row-item">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
              <div>
                <h3 class="h4">Follow the status</h3>
                <p>Each order moves through pending, confirmed, preparing, out for delivery and delivered.</p>
              </div>
            </div>
            <div class="row-item">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
              <div>
                <h3 class="h4">Questions?</h3>
                <p>Send us a message from the <a href="contact.php">Contact page</a> and we will reply by email.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================================================== NEWSLETTER -->
    <section class="section-sm" id="newsletter">
      <div class="container">
        <div class="newsletter reveal">
          <span class="eyebrow center" style="color:rgba(255,255,255,.85)">Newsletter</span>
          <h2>New bouquets in your inbox</h2>
          <p>Subscribe to hear about new bouquets and seasonal picks.</p>
          <form class="news-form" data-validate novalidate>
            <label for="news-email" class="visually-hidden">Email address</label>
            <input type="email" id="news-email" name="email" placeholder="you@example.com" required autocomplete="email">
            <button type="submit" class="btn">Subscribe</button>
          </form>
          <p class="form-msg" role="status" aria-live="polite"></p>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
