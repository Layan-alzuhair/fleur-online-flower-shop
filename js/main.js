/* =====================================================================
   Fleur: vanilla JavaScript (no jQuery, no plugins)
   One function per feature. Guard clauses. Reduced-motion aware.
   ===================================================================== */
(function () {
  "use strict";

  var prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  // Site root, set by includes/footer.php (e.g. "/Web-project/") so AJAX and
  // image paths also work from pages inside admin/. Empty on static pages.
  var BASE = window.FLEUR_BASE || "";
  var money = function (n) { return "SAR " + n.toFixed(2); };

  /* ---------------------------------------------------------- Sticky nav */
  function initStickyNav() {
    var nav = document.querySelector(".site-nav");
    if (!nav) return;
    var onScroll = function () {
      nav.classList.toggle("scrolled", window.scrollY > 30);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ---------------------------------------------------------- Mobile nav */
  function initMobileNav() {
    var toggle = document.querySelector(".nav-toggle");
    var links = document.querySelector(".nav-links");
    var backdrop = document.querySelector(".nav-backdrop");
    if (!toggle || !links) return;

    var setOpen = function (open) {
      links.classList.toggle("open", open);
      if (backdrop) backdrop.classList.toggle("open", open);
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      document.body.style.overflow = open ? "hidden" : "";
    };
    toggle.addEventListener("click", function () {
      setOpen(!links.classList.contains("open"));
    });
    if (backdrop) backdrop.addEventListener("click", function () { setOpen(false); });
    links.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () { setOpen(false); });
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") setOpen(false);
    });
  }

  /* ------------------------------------------------------ Scroll reveal */
  function initReveal() {
    var items = document.querySelectorAll(".reveal");
    if (!items.length) return;
    if (prefersReduced || !("IntersectionObserver" in window)) {
      items.forEach(function (el) { el.classList.add("in"); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("in");
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    items.forEach(function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------------ Count-up */
  function initCountUp() {
    var nums = document.querySelectorAll("[data-count]");
    if (!nums.length) return;
    if (prefersReduced || !("IntersectionObserver" in window)) {
      nums.forEach(function (el) { el.textContent = el.getAttribute("data-count") + (el.getAttribute("data-suffix") || ""); });
      return;
    }
    var run = function (el) {
      var target = parseFloat(el.getAttribute("data-count"));
      var suffix = el.getAttribute("data-suffix") || "";
      var dur = 1400, start = null;
      var step = function (ts) {
        if (!start) start = ts;
        var p = Math.min((ts - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased) + suffix;
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { run(entry.target); io.unobserve(entry.target); }
      });
    }, { threshold: 0.6 });
    nums.forEach(function (el) { io.observe(el); });
  }

  /* ------------------------------------------------ Occasion / tag filter */
  function initFilter() {
    var bars = document.querySelectorAll("[data-filter-bar]");
    bars.forEach(function (bar) {
      var targetSel = bar.getAttribute("data-filter-bar");
      var grid = document.querySelector(targetSel);
      if (!grid) return;
      var btns = bar.querySelectorAll(".filter-btn");
      btns.forEach(function (btn) {
        btn.addEventListener("click", function () {
          btns.forEach(function (b) { b.classList.remove("active"); b.setAttribute("aria-pressed", "false"); });
          btn.classList.add("active");
          btn.setAttribute("aria-pressed", "true");
          var val = btn.getAttribute("data-filter");
          grid.querySelectorAll("[data-occasion]").forEach(function (card) {
            var show = val === "all" || card.getAttribute("data-occasion") === val;
            card.style.display = show ? "" : "none";
          });
        });
      });
    });
  }

  /* --------------------------------------------------------- Cart drawer + AJAX */
  function initCart() {
    var drawer = document.querySelector(".cart-drawer");
    var overlay = document.querySelector(".cart-overlay");
    var itemsWrap = document.querySelector(".cart-items");
    var totalEl = document.querySelector("[data-cart-total]");
    var countEls = document.querySelectorAll(".cart-count");
    if (!drawer || !itemsWrap) return;

    var cart = [];
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfMeta ? csrfMeta.content : "";
    var checkoutLink = drawer.querySelector(".cart-foot a");
    if (checkoutLink) checkoutLink.href = BASE + "checkout.php";
    var money = function (n) { return "SAR " + Number(n || 0).toFixed(2); };
    var escapeHtml = function (value) {
      return String(value == null ? "" : value).replace(/[&<>"']/g, function (c) {
        return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c];
      });
    };
    var open = function () {
      drawer.classList.add("open");
      if (overlay) overlay.classList.add("open");
      drawer.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
      var closeButton = drawer.querySelector(".cart-close");
      if (closeButton) closeButton.focus();
    };
    var close = function () {
      drawer.classList.remove("open");
      if (overlay) overlay.classList.remove("open");
      drawer.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
    };
    var showCartMessage = function (message, isError) {
      var node = drawer.querySelector("[data-cart-message]");
      if (!node) {
        node = document.createElement("p");
        node.setAttribute("data-cart-message", "");
        node.setAttribute("role", "status");
        itemsWrap.parentNode.insertBefore(node, itemsWrap);
      }
      node.textContent = message || "";
      node.style.color = isError ? "#a8323f" : "";
    };
    var render = function () {
      var count = cart.reduce(function (sum, item) { return sum + Number(item.qty || 0); }, 0);
      var total = cart.reduce(function (sum, item) { return sum + Number(item.line_total != null ? item.line_total : item.price * item.qty); }, 0);
      countEls.forEach(function (el) {
        el.textContent = count;
        el.style.display = count ? "" : "none";
      });
      if (totalEl) totalEl.textContent = money(total);
      if (!cart.length) {
        itemsWrap.innerHTML = '<div class="cart-empty"><p>Your bag is empty</p><span style="font-size:.9rem;color:var(--stone)">Add a bouquet to brighten someone’s day.</span></div>';
        return;
      }
      itemsWrap.innerHTML = cart.map(function (item) {
        return '<div class="cart-item">' +
          '<img src="' + escapeHtml(BASE + item.img) + '" alt="' + escapeHtml(item.name) + '" width="64" height="64">' +
          '<div><h4>' + escapeHtml(item.name) + '</h4><div class="ci-price">' + money(item.price) + '</div>' +
          '<div class="qty" role="group" aria-label="Quantity for ' + escapeHtml(item.name) + '">' +
          '<button type="button" aria-label="Decrease quantity" data-cart-action="update" data-id="' + escapeHtml(item.id) + '" data-qty="' + Math.max(0, Number(item.qty) - 1) + '">−</button>' +
          '<span aria-live="polite">' + Number(item.qty) + '</span>' +
          '<button type="button" aria-label="Increase quantity" data-cart-action="update" data-id="' + escapeHtml(item.id) + '" data-qty="' + (Number(item.qty) + 1) + '">+</button></div></div>' +
          '<button type="button" class="ci-remove" data-cart-action="remove" data-id="' + escapeHtml(item.id) + '">Remove</button></div>';
      }).join("");
    };
    var applyResponse = function (res) {
      if (res && res.cart && Array.isArray(res.cart.items)) cart = res.cart.items;
      render();
      showCartMessage(res && res.message ? res.message : "", !res || !res.success);
    };
    // Requests are queued so they reach the server one at a time, in click order,
    // and the drawer shows a loading state while one is in progress.
    var queue = Promise.resolve();
    var pending = 0;
    var setBusy = function (busy) {
      drawer.setAttribute("aria-busy", busy ? "true" : "false");
      drawer.classList.toggle("is-busy", busy);
    };
    var cartRequest = function (data) {
      pending += 1; setBusy(true);
      var run = queue.then(function () { return sendCartRequest(data); });
      queue = run.then(function () {}, function () {});
      return run.then(function (res) {
        pending -= 1; if (!pending) setBusy(false);
        return res;
      });
    };
    var sendCartRequest = function (data) {
      if (!csrf) {
        showCartMessage("Security token is missing. Please refresh the page.", true);
        return Promise.resolve(null);
      }
      var body = new URLSearchParams(data);
      return fetch(BASE + "ajax/cart.php", {
        method: "POST",
        headers: { "X-CSRF-Token": csrf, "Accept": "application/json" },
        body: body,
        credentials: "same-origin"
      }).then(function (response) {
        return response.json().then(function (json) {
          if (!response.ok && json.success !== false) json.success = false;
          return json;
        });
      }).then(function (res) { applyResponse(res); return res; })
        .catch(function () { showCartMessage("Could not update the cart. Please try again.", true); return null; });
    };

    itemsWrap.addEventListener("click", function (event) {
      var button = event.target.closest("button[data-cart-action]");
      if (!button) return;
      cartRequest({ action: button.getAttribute("data-cart-action"), product_id: button.getAttribute("data-id"), qty: button.getAttribute("data-qty") || "" });
    });
    document.querySelectorAll(".add-btn").forEach(function (button) {
      button.addEventListener("click", function () {
        var id = button.getAttribute("data-id");
        if (!/^\d+$/.test(String(id || ""))) {
          showCartMessage("This product is not connected to the database yet.", true);
          open();
          return;
        }
        if (button.disabled) return;
        button.disabled = true;                       // no double "Add" while the request runs
        cartRequest({ action: "add", product_id: id, qty: 1 }).then(function (res) {
          button.disabled = false;
          if (res && res.success) open();
          var label = button.querySelector(".add-label");
          if (res && res.success && label) {
            var old = label.textContent;
            label.textContent = "Added";
            button.classList.add("added");
            window.setTimeout(function () { label.textContent = old; button.classList.remove("added"); }, 1200);
          }
        });
      });
    });
    document.querySelectorAll("[data-cart-open]").forEach(function (button) {
      button.addEventListener("click", function (event) { event.preventDefault(); open(); });
    });
    var closeBtn = drawer.querySelector(".cart-close");
    if (closeBtn) closeBtn.addEventListener("click", close);
    if (overlay) overlay.addEventListener("click", close);
    document.addEventListener("keydown", function (event) { if (event.key === "Escape" && drawer.classList.contains("open")) close(); });

    fetch(BASE + "ajax/cart.php", { credentials: "same-origin", headers: { "Accept": "application/json" } })
      .then(function (response) { if (!response.ok) throw new Error("Cart request failed"); return response.json(); })
      .then(function (res) { applyResponse(res); })
      .catch(function () { cart = []; render(); });
  }

  /* ----------------------------------------------------- Gallery lightbox */
  function initLightbox() {
    var triggers = Array.prototype.slice.call(document.querySelectorAll("[data-lightbox]"));
    var box = document.querySelector(".lightbox");
    if (!triggers.length || !box) return;

    var imgEl = box.querySelector("img");
    var capEl = box.querySelector("figcaption");
    var current = 0;
    var lastFocus = null;

    var show = function (i) {
      current = (i + triggers.length) % triggers.length;
      var t = triggers[current];
      var full = t.getAttribute("data-full") || (t.querySelector("img") && t.querySelector("img").src);
      var cap = t.getAttribute("data-caption") || "";
      imgEl.src = full;
      imgEl.alt = cap;
      capEl.textContent = cap;
    };
    var open = function (i) {
      lastFocus = document.activeElement;
      show(i);
      box.classList.add("open");
      box.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
      box.querySelector(".lb-close").focus();
    };
    var close = function () {
      box.classList.remove("open");
      box.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
      if (lastFocus) lastFocus.focus();
    };

    triggers.forEach(function (t, i) {
      t.addEventListener("click", function (e) { e.preventDefault(); open(i); });
    });
    box.querySelector(".lb-close").addEventListener("click", close);
    box.querySelector(".lb-prev").addEventListener("click", function () { show(current - 1); });
    box.querySelector(".lb-next").addEventListener("click", function () { show(current + 1); });
    box.addEventListener("click", function (e) { if (e.target === box) close(); });
    document.addEventListener("keydown", function (e) {
      if (!box.classList.contains("open")) return;
      if (e.key === "Escape") close();
      else if (e.key === "ArrowLeft") show(current - 1);
      else if (e.key === "ArrowRight") show(current + 1);
    });
  }

  /* ------------------------------------------------ Shop advanced filters */
  function initShopFilters() {
    var grid = document.querySelector("[data-shop-grid]");
    if (!grid) return;
    var cards = Array.prototype.slice.call(grid.querySelectorAll(".product-card"));
    var occInputs = document.querySelectorAll("[data-shop-occasion]");
    var colInputs = document.querySelectorAll("[data-shop-color]");
    var priceInputs = document.querySelectorAll("[data-shop-price]");
    var sortSel = document.querySelector("[data-shop-sort]");
    var countEl = document.querySelector("[data-shop-count]");
    var emptyEl = grid.querySelector(".empty-note");

    var checked = function (list) {
      return Array.prototype.slice.call(list)
        .filter(function (i) { return i.checked; })
        .map(function (i) { return i.value; });
    };

    var inPrice = function (price, ranges) {
      if (!ranges.length) return true;
      return ranges.some(function (r) {
        var parts = r.split("-");
        var min = parseFloat(parts[0]);
        var max = parts[1] === "plus" ? Infinity : parseFloat(parts[1]);
        return price >= min && price <= max;
      });
    };

    var apply = function () {
      var occ = checked(occInputs);
      var col = checked(colInputs);
      var pr = checked(priceInputs);
      var visible = 0;
      cards.forEach(function (card) {
        var cardOcc = (card.getAttribute("data-occasion") || "").split(" ");
        var cardCol = card.getAttribute("data-color") || "";
        var cardPrice = parseFloat(card.getAttribute("data-price"));
        var ok = (!occ.length || occ.some(function (o) { return cardOcc.indexOf(o) > -1; })) &&
                 (!col.length || col.indexOf(cardCol) > -1) &&
                 inPrice(cardPrice, pr);
        card.style.display = ok ? "" : "none";
        if (ok) visible++;
      });
      if (countEl) countEl.textContent = visible;
      if (emptyEl) emptyEl.hidden = visible !== 0;
      sortCards();
    };

    var sortCards = function () {
      if (!sortSel) return;
      var mode = sortSel.value;
      var shown = cards.filter(function (c) { return c.style.display !== "none"; });
      shown.sort(function (a, b) {
        var pa = parseFloat(a.getAttribute("data-price"));
        var pb = parseFloat(b.getAttribute("data-price"));
        if (mode === "price-low") return pa - pb;
        if (mode === "price-high") return pb - pa;
        if (mode === "name") return a.getAttribute("data-name").localeCompare(b.getAttribute("data-name"));
        return (+a.getAttribute("data-order")) - (+b.getAttribute("data-order"));
      });
      shown.forEach(function (c) { grid.appendChild(c); });
    };

    [occInputs, colInputs, priceInputs].forEach(function (list) {
      Array.prototype.slice.call(list).forEach(function (i) { i.addEventListener("change", apply); });
    });
    if (sortSel) sortSel.addEventListener("change", sortCards);
    // Filters pre-ticked by the server (e.g. shop.php?occasion=birthday) apply straight away
    if (checked(occInputs).length || checked(colInputs).length || checked(priceInputs).length) apply();

    var clearBtn = document.querySelector("[data-shop-clear]");
    if (clearBtn) clearBtn.addEventListener("click", function () {
      [occInputs, colInputs, priceInputs].forEach(function (list) {
        Array.prototype.slice.call(list).forEach(function (i) { i.checked = false; });
      });
      apply();
    });

    // Mobile filter drawer
    var panel = document.querySelector(".shop-filters");
    var openBtn = document.querySelector("[data-filters-open]");
    if (panel && openBtn) {
      openBtn.addEventListener("click", function () { panel.classList.toggle("open"); });
      var cl = panel.querySelector("[data-filters-close]");
      if (cl) cl.addEventListener("click", function () { panel.classList.remove("open"); });
    }

    apply();
  }

  /* --------------------------------------------------------- Form validation + AJAX */
  function initForms() {
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var phonePattern = /^05\d{8}$/;
    var setError = function (input, message) {
      input.setCustomValidity(message || "");
      if (message) input.setAttribute("aria-invalid", "true");
      else input.removeAttribute("aria-invalid");
    };
    var validateForm = function (form) {
      var valid = true;
      var email = form.querySelector('input[type="email"]');
      if (email && email.value.trim() && !emailPattern.test(email.value.trim())) setError(email, "Please enter a valid email address.");
      var phone = form.querySelector('input[name="phone"]');
      if (phone && phone.value.trim() && !phonePattern.test(phone.value.trim())) setError(phone, "Enter a valid Saudi mobile number (05XXXXXXXX).");
      var password = form.querySelector('input[name="password"]');
      var confirm = form.querySelector('input[name="confirm_password"]');
      if (password && password.value.length < 8) setError(password, "Password must be at least 8 characters.");
      if (confirm && password && confirm.value !== password.value) setError(confirm, "Passwords do not match.");
      var date = form.querySelector('input[name="delivery_date"]');
      if (date) {
        var tomorrow = new Date(); tomorrow.setHours(0, 0, 0, 0); tomorrow.setDate(tomorrow.getDate() + 1);
        var minDate = tomorrow.getFullYear() + "-" + String(tomorrow.getMonth() + 1).padStart(2, "0") + "-" + String(tomorrow.getDate()).padStart(2, "0");
        date.min = minDate;
        if (date.value && date.value < minDate) setError(date, "Delivery date must be tomorrow or later.");
      }
      var price = form.querySelector('input[name="price"]');
      if (price && price.value !== "" && Number(price.value) <= 0) setError(price, "Price must be greater than zero.");
      var stock = form.querySelector('input[name="stock"]');
      if (stock && stock.value !== "" && (!Number.isInteger(Number(stock.value)) || Number(stock.value) < 0)) setError(stock, "Stock must be a whole number greater than or equal to zero.");
      form.querySelectorAll("input, select, textarea").forEach(function (input) {
        if (!input.checkValidity()) valid = false;
      });
      if (!valid) {
        form.reportValidity();
        var firstInvalid = form.querySelector(":invalid");
        if (firstInvalid) firstInvalid.focus();
      }
      return valid;
    };

    ["register-form", "login-form", "checkout-form", "product-form", "contact-form"].forEach(function (id) {
      var form = document.getElementById(id);
      if (!form) return;
      form.querySelectorAll("input, select, textarea").forEach(function (input) {
        input.addEventListener("input", function () { setError(input, ""); });
        input.addEventListener("change", function () { setError(input, ""); });
      });
      form.addEventListener("submit", function (event) {
        if (!validateForm(form)) { event.preventDefault(); return; }
        // Valid: lock the submit button so a double click can't send the form twice
        var submit = form.querySelector('[type="submit"]');
        if (submit) {
          if (submit.disabled) { event.preventDefault(); return; }
          window.setTimeout(function () { submit.disabled = true; submit.setAttribute("aria-busy", "true"); }, 0);
        }
      });
    });

    document.querySelectorAll("form.news-form[data-validate]").forEach(function (form) {
      var email = form.querySelector('input[type="email"]');
      var msg = form.querySelector(".form-msg");
      form.addEventListener("submit", function (event) {
        event.preventDefault();
        if (!email || !emailPattern.test(email.value.trim())) {
          if (email) { setError(email, "Please enter a valid email address."); email.reportValidity(); }
          return;
        }
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (!tokenMeta || !tokenMeta.content) {
          if (msg) msg.textContent = "Please refresh the page and try again.";
          return;
        }
        var submit = form.querySelector('[type="submit"]');
        if (submit) submit.disabled = true;
        fetch(BASE + "ajax/newsletter.php", {
          method: "POST",
          headers: { "X-CSRF-Token": tokenMeta.content, "Accept": "application/json" },
          body: new URLSearchParams({ email: email.value.trim() }),
          credentials: "same-origin"
        }).then(function (response) { return response.json(); })
          .then(function (res) {
            if (msg) { msg.textContent = res.message || (res.success ? "Thank you for subscribing!" : "Subscription failed."); msg.style.color = res.success ? "#fff" : "#ffe0e0"; }
            if (res.success) form.reset();
          }).catch(function () {
            if (msg) { msg.textContent = "Could not subscribe right now. Please try again."; msg.style.color = "#ffe0e0"; }
          }).finally(function () { if (submit) submit.disabled = false; });
      });
    });
  }

  /* ---------------------------------------------------------------- Init */
  function init() {
    initStickyNav();
    initMobileNav();
    initReveal();
    initCountUp();
    initFilter();
    initCart();
    initLightbox();
    initShopFilters();
    initForms();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
