/* Cveta Cvetić — theme behaviour: menu, AJAX add-to-cart, wishlist, quantity steppers, checkout, FAQ. */
(() => {
  "use strict";
  const D = window.CC_DATA || {};
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const money = (n) => new Intl.NumberFormat("sr-RS").format(n) + " RSD";
  const decode = (s) => { const t = document.createElement("textarea"); t.innerHTML = s; return t.value; };
  const shortName = (name) => (name.match(/„([^“”"]+)[“”"]/)?.[1] || name.split(" – ")[0]).replace(/[„“”"]/g, "").trim();
  const page = document.body.dataset.page;

  /* ---------- Storage (never fatal) ---------- */
  const store = {
    get(key, fallback) { try { const v = JSON.parse(localStorage.getItem(key)); return v ?? fallback; } catch { return fallback; } },
    set(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* private mode */ } },
  };

  /* ---------- Toast ---------- */
  let toastTimer;
  function toast(html) {
    let el = $(".toast");
    if (!el) { el = document.createElement("div"); el.className = "toast"; el.setAttribute("role", "status"); document.body.append(el); }
    el.innerHTML = html;
    requestAnimationFrame(() => el.classList.add("show"));
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove("show"), 3200);
  }

  /* ---------- Header, drawer, cookie notice ---------- */
  const drawer = $("[data-drawer]");
  const setDrawer = (open) => { if (!drawer) return; drawer.classList.toggle("open", open); drawer.setAttribute("aria-hidden", String(!open)); document.body.style.overflow = open ? "hidden" : ""; };
  $("[data-menu-open]")?.addEventListener("click", () => setDrawer(true));
  $$("[data-menu-close]").forEach((b) => b.addEventListener("click", () => setDrawer(false)));
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") setDrawer(false); });
  const hdr = $(".header");
  // The compact header is shorter, so switching it moves the page a little. Separate on/off
  // thresholds (wider than that height change) stop it from flipping back and forth (shaking).
  let hdrTick = false;
  const onScroll = () => {
    if (!hdr || hdrTick) return;
    hdrTick = true;
    requestAnimationFrame(() => {
      hdrTick = false;
      const on = hdr.classList.contains("scrolled");
      if (!on && scrollY > 80) hdr.classList.add("scrolled");
      else if (on && scrollY < 10) hdr.classList.remove("scrolled");
    });
  };
  addEventListener("scroll", onScroll, { passive: true }); onScroll();

  if (!store.get("cc_cookie", null)) {
    const el = document.createElement("div");
    el.className = "cookie";
    el.innerHTML = `<p>Koristimo samo neophodne kolačiće (korpa, lista želja) kako bi sajt radio kako treba. <a href="${esc(D.privUrl)}">Saznajte više</a>.</p><button class="btn btn-primary btn-sm" type="button">U redu</button>`;
    $("button", el).addEventListener("click", () => { store.set("cc_cookie", 1); el.remove(); });
    document.body.append(el);
  }

  /* ---------- Wishlist (kept in this browser) ---------- */
  const Wish = {
    items() { return store.get("cc_wish", []).map(String); },
    has(id) { return this.items().includes(String(id)); },
    toggle(id) {
      id = String(id);
      const items = this.items();
      const next = items.includes(id) ? items.filter((s) => s !== id) : [...items, id];
      store.set("cc_wish", next); updateWishBadge();
      return next.includes(id);
    },
  };
  function updateWishBadge() {
    $$("[data-wish-count]").forEach((el) => { const n = Wish.items().length; el.textContent = n || ""; el.dataset.n = n; });
  }
  function syncHearts(root = document) {
    $$("[data-wish]", root).forEach((b) => { const on = Wish.has(b.dataset.wish); b.classList.toggle("on", on); b.setAttribute("aria-pressed", on); });
  }
  updateWishBadge(); syncHearts();
  addEventListener("storage", () => { updateWishBadge(); syncHearts(); });

  /* ---------- AJAX add to cart (WooCommerce wc-ajax endpoint) ---------- */
  function applyFragments(fragments) {
    Object.entries(fragments || {}).forEach(([sel, html]) => $$(sel).forEach((el) => { el.outerHTML = html; }));
    if (window.jQuery) window.jQuery(document.body).trigger("wc_fragments_refreshed");
  }
  async function addToCart(id, qty = 1, btn) {
    if (btn) btn.disabled = true;
    try {
      const body = new URLSearchParams({ product_id: id, quantity: qty });
      const res = await fetch(D.ajax.replace("%%endpoint%%", "add_to_cart"), { method: "POST", body, credentials: "same-origin" });
      const data = await res.json();
      if (!data || data.error) { location.href = data?.product_url || location.href; return; }
      applyFragments(data.fragments);
      return true;
    } catch {
      toast(`<span>Greška pri dodavanju u korpu. Pokušajte ponovo.</span>`);
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  document.addEventListener("click", async (e) => {
    const add = e.target.closest("[data-add]");
    if (add) {
      e.preventDefault();
      const name = add.closest(".product-card")?.querySelector(".title")?.textContent || "";
      if (await addToCart(add.dataset.add, 1, add)) {
        toast(`${D.icons.check}<span>„${esc(name)}“ je dodat u korpu.</span><a href="${esc(D.cartUrl)}">Pogledaj korpu</a>`);
      }
      return;
    }
    const w = e.target.closest("[data-wish]");
    if (w) {
      e.preventDefault();
      const on = Wish.toggle(w.dataset.wish);
      w.classList.toggle("on", on); w.setAttribute("aria-pressed", on);
      toast(on ? `${D.icons.heart}<span>Dodato u listu želja.</span><a href="${esc(D.wishUrl)}">Lista želja</a>` : `<span>Uklonjeno iz liste želja.</span>`);
      if (page === "lista-zelja" && !on) renderWishlist();
    }
  });

  /* ---------- Product page: quantity + add to cart ---------- */
  const buyForm = $("[data-buy-form]");
  if (buyForm) {
    const qty = $("[data-qty]", buyForm);
    buyForm.addEventListener("click", (e) => {
      const step = e.target.closest("[data-q]");
      if (step) qty.value = Math.max(1, Math.min(99, (Number(qty.value) || 1) + Number(step.dataset.q)));
    });
    buyForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const btn = $('[name="add-to-cart"]', buyForm);
      if (await addToCart(btn.value, Math.max(1, Number(qty.value) || 1), btn)) {
        toast(`${D.icons.check}<span>Dodato u korpu.</span><a href="${esc(D.cartUrl)}">Završi kupovinu</a>`);
      }
    });
  }

  /* ---------- Cart: − / + update the cart automatically ---------- */
  let cartTimer;
  document.addEventListener("click", (e) => {
    const step = e.target.closest("[data-cart-qty] [data-step]");
    if (!step) return;
    const input = $("input", step.parentElement);
    input.value = Math.max(0, Math.min(99, (Number(input.value) || 0) + Number(step.dataset.step)));
    input.dispatchEvent(new Event("change", { bubbles: true }));
    clearTimeout(cartTimer);
    cartTimer = setTimeout(() => {
      const btn = $('.woocommerce-cart-form [name="update_cart"]');
      if (btn) { btn.disabled = false; btn.click(); }
    }, 450);
  });
  document.addEventListener("change", (e) => {
    if (!e.target.matches(".woocommerce-cart-form input.qty-input") || e.isTrusted === false) return;
    clearTimeout(cartTimer);
    cartTimer = setTimeout(() => { const btn = $('.woocommerce-cart-form [name="update_cart"]'); if (btn) { btn.disabled = false; btn.click(); } }, 450);
  });

  /* ---------- Shop: category strip arrows ---------- */
  const track = $("[data-cats]");
  if (track) {
    $$("[data-cat-scroll]").forEach((b) => b.addEventListener("click", () => track.scrollBy({ left: Number(b.dataset.catScroll) * 480, behavior: "smooth" })));
    const active = $(".cat-card.active", track);
    if (active) track.scrollLeft = active.offsetLeft - track.clientWidth / 2 + active.clientWidth / 2;
  }

  /* ---------- FAQ accordion ---------- */
  const faq = $$(".faq-item");
  faq.forEach((item) => {
    const btn = $(".faq-q", item);
    btn.addEventListener("click", () => {
      const opening = !item.classList.contains("is-open");
      faq.forEach((x) => { x.classList.remove("is-open"); $(".faq-q", x).setAttribute("aria-expanded", "false"); });
      if (opening) { item.classList.add("is-open"); btn.setAttribute("aria-expanded", "true"); }
    });
  });

  /* ---------- Checkout: delivery or pickup, another recipient ---------- */
  const checkout = $("form.woocommerce-checkout");
  if (checkout) {
    const toggleDelivery = () => {
      const pickup = $('input[name="cc_delivery"]:checked', checkout)?.value === "preuzimanje";
      $$("[data-delivery-only]", checkout).forEach((el) => (el.hidden = pickup));
      const lbl = $("[data-time-label]", checkout);
      if (lbl) lbl.textContent = pickup ? "Vreme preuzimanja" : "Vreme dostave";
      const addr = $("#billing_address_1_field label", checkout);
      if (addr && !$(".required", addr) && !pickup) addr.insertAdjacentHTML("beforeend", ' <abbr class="required" title="obavezno">*</abbr>');
    };
    checkout.addEventListener("change", (e) => {
      if (e.target.name === "cc_delivery") toggleDelivery();
      // Delivery price depends on pickup / zone: refresh the order summary.
      if ((e.target.name === "cc_delivery" || e.target.name === "cc_zone") && window.jQuery) window.jQuery(document.body).trigger("update_checkout");
      if (e.target.matches("[data-other-recipient]")) $("[data-recipient]", checkout).hidden = !e.target.checked;
    });
    toggleDelivery();
    if (window.jQuery) window.jQuery(document.body).on("updated_checkout", toggleDelivery);
  }

  /* ---------- Contact form: friendly validation before sending ---------- */
  const contact = $("[data-contact-form]");
  if (contact) {
    contact.addEventListener("submit", (e) => {
      let ok = true;
      $$(".field", contact).forEach((f) => {
        const input = $("input, textarea", f);
        const bad = input && !input.checkValidity();
        f.classList.toggle("invalid", !!bad);
        if (bad && ok) { input.focus(); ok = false; }
      });
      if (!ok) e.preventDefault();
    });
    contact.addEventListener("input", (e) => e.target.closest(".field")?.classList.remove("invalid"));
  }

  /* ---------- Wishlist page (products from the WooCommerce Store API) ---------- */
  async function renderWishlist() {
    const grid = $("[data-wish-grid]");
    if (!grid) return;
    const ids = Wish.items();
    const empty = `<div class="empty card" style="grid-column:1/-1"><h3>Lista želja je prazna</h3><p>Kliknite na srce na proizvodu da ga sačuvate za kasnije.</p><a class="btn btn-primary" href="${esc(D.shopUrl)}">Asortiman</a></div>`;
    if (!ids.length) { grid.innerHTML = empty; return; }
    try {
      const res = await fetch(`${D.store}products?include=${ids.join(",")}&per_page=100`, { credentials: "same-origin" });
      const list = await res.json();
      if (!Array.isArray(list) || !list.length) { grid.innerHTML = empty; return; }
      grid.innerHTML = list.map((p) => {
        const name = shortName(decode(p.name));
        const img = p.images?.[0]?.thumbnail || p.images?.[0]?.src || "";
        const price = Number(p.prices?.price || 0) / 10 ** Number(p.prices?.currency_minor_unit || 0);
        return `<article class="product-card v2">
          <a class="media" href="${esc(p.permalink)}"><img src="${esc(img)}" alt="${esc(name)}" loading="lazy"></a>
          <div class="body">
            <div class="name-row"><a class="title" href="${esc(p.permalink)}">${esc(name)}</a><button class="wish-inline on" type="button" data-wish="${p.id}" aria-label="Ukloni iz liste želja" aria-pressed="true">${D.icons.heart}</button></div>
            <span class="price">${money(price)}</span>
            ${p.is_purchasable && p.is_in_stock ? `<button class="btn btn-primary btn-cart" type="button" data-add="${p.id}">${D.icons.basket}Dodaj u korpu</button>` : `<a class="btn btn-outline btn-cart" href="${esc(p.permalink)}">Pogledaj</a>`}
          </div></article>`;
      }).join("");
    } catch {
      grid.innerHTML = `<div class="alert alert-err" style="grid-column:1/-1">Lista želja trenutno nije dostupna. Osvežite stranicu.</div>`;
    }
  }
  if (page === "lista-zelja") renderWishlist();
})();
