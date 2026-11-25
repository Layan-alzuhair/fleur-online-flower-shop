<?php
require_once __DIR__ . '/includes/init.php';

$productModel = new Product();

// ?q=rose searches names and descriptions in the database; empty shows everything
$search   = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$products = $search !== '' ? $productModel->search($search) : $productModel->all();

// ?occasion=birthday (from the homepage occasion cards) pre-ticks that filter
$selectedOccasion = (string) ($_GET['occasion'] ?? '');

$pageTitle       = 'Shop Bouquets';
$pageDescription = 'Browse all Fleur bouquets and filter them by occasion, colour and price.';
$fullWidth       = true;
require __DIR__ . '/includes/header.php';
?>

  <section class="page-head">
    <div class="container">
      <p class="breadcrumb-line"><a href="index.php">Home</a> &nbsp;/&nbsp; Shop</p>
      <h1>The Bouquet Shop</h1>
      <p class="section-lead" style="margin:1rem auto 0">
        Filter by occasion, colour or price, sort the results, and add bouquets to your cart.
      </p>
    </div>
  </section>

  <section class="section" style="padding-top:3rem">
    <div class="container">
      <div class="shop-layout">

        <aside class="shop-filters" aria-label="Filter bouquets">
          <div class="d-flex justify-content-between align-items-center d-lg-none" style="margin-bottom:1.2rem">
            <strong style="font-family:var(--serif);font-size:1.4rem">Filters</strong>
            <button type="button" class="cart-close" data-filters-close aria-label="Close filters">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M18 6 6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="filter-group">
            <h2 class="h4">Occasion</h2>
            <label class="filter-opt"><input type="checkbox" data-shop-occasion value="birthday"<?= $selectedOccasion === 'birthday' ? ' checked' : '' ?>> Birthday</label>
            <label class="filter-opt"><input type="checkbox" data-shop-occasion value="anniversary"<?= $selectedOccasion === 'anniversary' ? ' checked' : '' ?>> Anniversary</label>
            <label class="filter-opt"><input type="checkbox" data-shop-occasion value="wedding"<?= $selectedOccasion === 'wedding' ? ' checked' : '' ?>> Wedding</label>
            <label class="filter-opt"><input type="checkbox" data-shop-occasion value="sympathy"<?= $selectedOccasion === 'sympathy' ? ' checked' : '' ?>> Sympathy</label>
            <label class="filter-opt"><input type="checkbox" data-shop-occasion value="get-well"<?= $selectedOccasion === 'get-well' ? ' checked' : '' ?>> Get Well</label>
            <label class="filter-opt"><input type="checkbox" data-shop-occasion value="just-because"<?= $selectedOccasion === 'just-because' ? ' checked' : '' ?>> Just Because</label>
          </div>

          <div class="filter-group">
            <h2 class="h4">Colour</h2>
            <label class="filter-opt"><input type="checkbox" data-shop-color value="pink"> <span class="swatch" style="background:#E8A9B6"></span> Pink</label>
            <label class="filter-opt"><input type="checkbox" data-shop-color value="red"> <span class="swatch" style="background:#A8323F"></span> Red</label>
            <label class="filter-opt"><input type="checkbox" data-shop-color value="white"> <span class="swatch" style="background:#F3EEE8"></span> White &amp; Ivory</label>
            <label class="filter-opt"><input type="checkbox" data-shop-color value="yellow"> <span class="swatch" style="background:#E7BE5A"></span> Yellow &amp; Gold</label>
            <label class="filter-opt"><input type="checkbox" data-shop-color value="purple"> <span class="swatch" style="background:#9C7BB0"></span> Purple</label>
            <label class="filter-opt"><input type="checkbox" data-shop-color value="mixed"> <span class="swatch" style="background:linear-gradient(135deg,#E8A9B6,#9DAF8F)"></span> Mixed</label>
          </div>

          <div class="filter-group">
            <h2 class="h4">Price</h2>
            <label class="filter-opt"><input type="checkbox" data-shop-price value="0-199"> Under SAR 200</label>
            <label class="filter-opt"><input type="checkbox" data-shop-price value="200-299"> SAR 200 to 299</label>
            <label class="filter-opt"><input type="checkbox" data-shop-price value="300-399"> SAR 300 to 399</label>
            <label class="filter-opt"><input type="checkbox" data-shop-price value="400-plus"> SAR 400 and up</label>
          </div>

          <button type="button" class="btn btn-ghost btn-block" data-shop-clear>Clear all filters</button>
        </aside>

        <div>
          <div class="shop-toolbar">
            <button type="button" class="btn btn-outline btn-sm filter-toggle-mobile" data-filters-open>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 6h16M7 12h10M10 18h4"/>
              </svg>
              Filters
            </button>

            <p class="shop-count"><b data-shop-count><?= count($products) ?></b> bouquets</p>

            <label class="d-flex align-items-center gap-2" style="gap:.5rem">
              <span class="visually-hidden">Sort by</span>
              <select class="sort-select" data-shop-sort aria-label="Sort bouquets">
                <option value="featured">Featured</option>
                <option value="price-low">Price: low to high</option>
                <option value="price-high">Price: high to low</option>
                <option value="name">Name: A to Z</option>
              </select>
            </label>
          </div>

          <form class="shop-search" method="get" action="shop.php" role="search">
            <label for="shop-q" class="visually-hidden">Search bouquets</label>
            <input type="search" id="shop-q" name="q" value="<?= e($search) ?>" maxlength="100" placeholder="Search bouquets, e.g. rose" class="form-control">
            <button type="submit" class="btn btn-rose btn-sm">Search</button>
            <?php if ($search !== ''): ?><a href="shop.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
          </form>
          <?php if ($search !== ''): ?>
            <p class="shop-search-note" role="status"><?= count($products) ?> result<?= count($products) === 1 ? '' : 's' ?> for &ldquo;<?= e($search) ?>&rdquo;</p>
          <?php endif; ?>

          <h2 class="visually-hidden">Bouquets</h2>
          <div class="product-grid cols-3" data-shop-grid>
            <?php foreach ($products as $index => $product): ?>
              <?php
                $productId = (int) $product['product_id'];
                $productName = (string) $product['name'];
                $productPrice = (float) $product['price'];
                $productImage = (string) $product['image'];
                $productColor = (string) $product['color'];
                $productOccasion = (string) $product['category_slug'];
                $productDescription = (string) $product['description'];
                $productTag = (string) ($product['tag'] ?? '');
                $productStock = (int) $product['stock'];
              ?>

              <article class="product-card reveal<?= ($index % 4 === 1) ? ' d1' : (($index % 4 === 2) ? ' d2' : (($index % 4 === 3) ? ' d3' : '')) ?>"
                       data-occasion="<?= e($productOccasion) ?>"
                       data-color="<?= e($productColor) ?>"
                       data-price="<?= e($productPrice) ?>"
                       data-name="<?= e($productName) ?>"
                       data-order="<?= $index + 1 ?>">

                <div class="product-media">
                  <?php if ($productTag !== ''): ?>
                    <span class="product-tag"><?= e($productTag) ?></span>
                  <?php endif; ?>
                  <a href="product.php?id=<?= $productId ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($productImage) ?>" alt="<?= e($productName) ?>" width="400" height="400" loading="lazy"></a>
                </div>

                <div class="product-body">
                  <h3><a href="product.php?id=<?= $productId ?>" class="product-link"><?= e($productName) ?></a></h3>
                  <p class="note"><?= e($productDescription) ?></p>

                  <div class="product-foot">
                    <span class="product-price"><?= money($productPrice) ?></span>

                    <?php if ($productStock > 0): ?>
                      <button type="button" class="add-btn"
                              data-id="<?= $productId ?>"
                              data-name="<?= e($productName) ?>"
                              data-price="<?= e($productPrice) ?>"
                              data-img="<?= e($productImage) ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                          <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <span class="add-label">Add</span>
                      </button>
                    <?php else: ?>
                      <button type="button" class="add-btn" disabled>
                        <span class="add-label">Out of stock</span>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>

            <p class="empty-note"<?= ($search !== '' && !$products) ? '' : ' hidden' ?>><?= ($search !== '' && !$products) ? 'No bouquets match your search. Try another word.' : 'No bouquets match those filters. Try clearing a few.' ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-sm">
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
