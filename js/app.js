/* Cveta Cvetić — shared site logic: data, cart, wishlist, layout and page modules. */
(() => {
  "use strict";

  const SHOP = {
    name: "Cvećara Cveta Cvetić",
    phone: "064/41-24-337",
    phoneHref: "tel:+381644124337",
    viber: "viber://chat?number=%2B381644124337",
    email: "tomatasovac@gmail.com",
    address: "Ilije Birčanina 39, Novi Sad 21000",
    mapsUrl: "https://maps.app.goo.gl/?q=Cve%C4%87ara+Cveta+Cveti%C4%87+Novi+Sad",
    mapsEmbed: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2808.4277049796883!2d19.8155159!3d45.25936430000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x475b11f704189c1b%3A0x8716eb7e8d463c3e!2sCve%C4%87ara%20Cveta%20Cveti%C4%87%20-%20Novi%20Sad!5e0!3m2!1sen!2srs!4v1749474277320!5m2!1sen!2srs",
    facebook: "https://www.facebook.com/flowershop.cvetacvetic/",
    instagram: "https://www.instagram.com/cveta_cvetic_/",
    pib: "113733973",
    mb: "65201909",
    owner: "JELENA TASOVAC",
  };
  const PER_PAGE = 12;
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const money = (n) => new Intl.NumberFormat("sr-RS").format(n) + " RSD";
  const params = new URLSearchParams(location.search);

  /* ---------- Icons ---------- */
  const ICONS = {
    cart: '<path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M6 6 5 3H2"/>',
    heart: '<path d="M12 20s-7-4.4-9.2-8.6C1.2 8.2 3.1 4.5 6.8 4.5c2.1 0 3.6 1.2 5.2 3 1.6-1.8 3.1-3 5.2-3 3.7 0 5.6 3.7 4 6.9C19 15.6 12 20 12 20z"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
    close: '<path d="M6 6l12 12M18 6 6 18"/>',
    phone: '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
    mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    pin: '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
    arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    flower: '<circle cx="12" cy="12" r="2.5"/><path d="M12 9.5C10 6 10 3.5 12 3c2 .5 2 3 0 6.5zM14.5 12c3.5-2 6-2 6.5 0-.5 2-3 2-6.5 0zM12 14.5c2 3.5 2 6 0 6.5-2-.5-2-3 0-6.5zM9.5 12C6 14 3.5 14 3 12c.5-2 3-2 6.5 0z"/>',
    truck: '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
    shield: '<path d="M12 3 5 6v5c0 4.5 3 8.4 7 10 4-1.6 7-5.5 7-10V6z"/><path d="m9 12 2 2 4-4"/>',
    gift: '<rect x="3" y="8" width="18" height="5" rx="1"/><path d="M5 13v8h14v-8M12 8v13M12 8S10.5 3 8 4.5 9 8 12 8zM12 8s1.5-5 4-3.5S15 8 12 8z"/>',
    trash: '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    check: '<path d="m5 12 5 5 9-10"/>',
    fb: '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v7h4v-7h3l1-4h-4V8z"/>',
    ig: '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1"/>',
    chat: '<path d="M4 5h16v11H9l-5 4z"/>',
    basket: '<path d="M3 10h18l-2 10H5z"/><path d="M8 10l4-6 4 6M9 14v3M12 14v3M15 14v3"/>',
    external: '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
    chevron: '<path d="m9 6 6 6-6 6"/>',
    sparkle: '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
  };
  const icon = (name, cls = "icon") =>
    `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] || ""}</svg>`;

  /* ---------- Storage helpers (never fatal) ---------- */
  const store = {
    get(key, fallback) {
      try { const v = JSON.parse(localStorage.getItem(key)); return v ?? fallback; } catch { return fallback; }
    },
    set(key, value) {
      try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* private mode */ }
    },
  };

  /* ---------- Data ---------- */
  let DATA = null;
  async function loadData() {
    if (DATA) return DATA;
    const [products, categories, posts] = await Promise.all(
      ["products", "categories", "posts"].map((n) => fetch(`data/${n}.json`).then((r) => r.json()))
    );
    const bySlug = new Map(products.map((p) => [p.slug, p]));
    DATA = { products, categories, posts, bySlug };
    return DATA;
  }
  const catName = (slug) => DATA?.categories.find((c) => c.slug === slug)?.name || slug;
  const productUrl = (p) => `proizvod.html?p=${encodeURIComponent(p.slug)}`;
  // Card titles use the quoted product name when there is one: Buket sa medom „Nežna pažnja“ – … → Nežna pažnja
const shortName = (name) => (name.match(/„([^“”"]+)[“”"]/)?.[1] || name.split(" – ")[0]).replace(/[„“”"]/g, "").trim();
  const subName = (name) => name.split(" – ").slice(1).join(" – ");

  /* ---------- Cart ---------- */
  const Cart = {
    items() { return store.get("cc_cart", []).filter((i) => i && i.slug && i.qty > 0); },
    save(items) { store.set("cc_cart", items); updateBadges(); },
    add(slug, qty = 1) {
      const items = this.items();
      const found = items.find((i) => i.slug === slug);
      if (found) found.qty = Math.min(99, found.qty + qty); else items.push({ slug, qty });
      this.save(items);
    },
    setQty(slug, qty) {
      const items = this.items().map((i) => (i.slug === slug ? { ...i, qty: Math.max(1, Math.min(99, qty)) } : i));
      this.save(items);
    },
    remove(slug) { this.save(this.items().filter((i) => i.slug !== slug)); },
    clear() { this.save([]); },
    detailed() {
      return this.items().map((i) => ({ ...i, product: DATA?.bySlug.get(i.slug) })).filter((i) => i.product);
    },
    count() { return this.items().reduce((s, i) => s + i.qty, 0); },
    total() { return this.detailed().reduce((s, i) => s + i.qty * i.product.price, 0); },
  };

  const Wish = {
    items() { return store.get("cc_wish", []); },
    has(slug) { return this.items().includes(slug); },
    toggle(slug) {
      const items = this.items();
      const next = items.includes(slug) ? items.filter((s) => s !== slug) : [...items, slug];
      store.set("cc_wish", next); updateBadges();
      return next.includes(slug);
    },
  };

  function updateBadges() {
    $$("[data-cart-count]").forEach((el) => { const n = Cart.count(); el.textContent = n || ""; el.dataset.n = n; });
        $$("[data-wish-count]").forEach((el) => { const n = Wish.items().length; el.textContent = n || ""; el.dataset.n = n; });
  }

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

  /* ---------- Logo ---------- */
  // Same structure as the original logo (round emblem + two-line wordmark + tagline), but the wordmark
  // is live text, so it stays sharp at any size; the leaf over Ć is drawn as SVG like in the original.
  const LEAF = '<svg class="leaf" viewBox="0 0 20 26" aria-hidden="true"><path d="M3 24C3 13 8 5 18 2c-1 9-6 15-15 22z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M4 23c3-6 7-11 12-17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
  const logoHTML = () => `<img class="emblem" src="img/brand/emblem.png" alt="" width="512" height="512">
    <span class="wordmark"><span class="wm-line">CVETA</span><span class="wm-line">CVETI<span class="wm-c">C${LEAF}</span></span><span class="wm-tag">Flower &amp; Gift shop</span></span>`;

  /* ---------- Layout ---------- */
  const NAV = [
    ["index.html", "Početna"],
    ["o-nama.html", "O nama"],
    ["asortiman.html", "Asortiman", true],
    ["aktuelnosti.html", "Aktuelnosti"],
    ["cesta-pitanja.html", "Česta pitanja"],
    ["kontakt.html", "Kontakt"],
  ];

  function renderLayout(page) {
    const here = location.pathname.split("/").pop() || "index.html";
    const activeFor = { product: "asortiman.html", post: "aktuelnosti.html" }[page] || here;
    const subCats = DATA.categories.map((c) => `<a href="asortiman.html?kat=${c.slug}">${esc(c.name)}</a>`).join("");
    const navItems = NAV.map(([href, label, sub]) => {
      const a = `<a href="${href}" class="${href === activeFor ? "active" : ""}">${label}</a>`;
      return sub ? `<li class="has-sub">${a}<div class="sub">${subCats}<a href="asortiman.html">Svi proizvodi</a></div></li>` : `<li>${a}</li>`;
    }).join("");

    const header = document.createElement("div");
    header.innerHTML = `
      <header class="header"><div class="container">
        <a class="logo" href="index.html" aria-label="Cveta Cvetić – Flower & Gift shop, početna">${logoHTML()}</a>
        <nav class="nav" aria-label="Glavni meni"><ul>${navItems}</ul></nav>
        <div class="header-actions">
          <a class="icon-btn" href="lista-zelja.html" aria-label="Lista želja" title="Lista želja">${icon("heart")}<span class="count" data-wish-count></span></a>
          <a class="icon-btn" href="korpa.html" aria-label="Korpa" title="Korpa">${icon("cart")}<span class="count" data-cart-count></span></a>
          <button class="icon-btn menu-toggle" data-menu-open aria-label="Otvori meni">${icon("menu")}</button>
        </div>
      </div></header>`;
    const drawerWrap = document.createElement("div");
    drawerWrap.innerHTML = `
      <div class="drawer" data-drawer aria-hidden="true">
        <div class="drawer-backdrop" data-menu-close></div>
        <div class="drawer-panel" role="dialog" aria-label="Meni">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span class="logo logo-sm">${logoHTML()}</span>
            <button class="icon-btn" data-menu-close aria-label="Zatvori meni" style="width:48px;height:48px">${icon("close")}</button>
          </div>
          <form action="asortiman.html" role="search" style="margin-top:1rem"><input class="input" type="search" name="q" placeholder="Pretraga proizvoda…" aria-label="Pretraga proizvoda"></form>
          <ul>
            ${NAV.map(([href, label, sub]) => `<li><a href="${href}">${label}</a></li>${sub ? `<li class="sub-list">${DATA.categories.map((c) => `<a href="asortiman.html?kat=${c.slug}">${esc(c.name)}</a>`).join("")}</li>` : ""}`).join("")}
            <li><a href="lista-zelja.html">Lista želja</a></li>
            <li><a href="korpa.html">Korpa</a></li>
          </ul>
          <a class="btn btn-primary btn-block" href="${SHOP.phoneHref}">${icon("phone")}${SHOP.phone}</a>
        </div>
      </div>`;

    // Everything visible lives inside one rounded frame, as in the mockups.
    const frame = document.createElement("div");
    frame.className = "frame";
    const main = $("main");
    main.before(frame);
    frame.append(...header.children, main);
    document.body.append(...drawerWrap.children);

    const footer = document.createElement("footer");
    footer.className = "footer";
    footer.innerHTML = `
      <img class="deco l" src="img/design/footer-left.jpg" alt="" aria-hidden="true">
      <img class="deco r" src="img/design/footer-right.jpg" alt="" aria-hidden="true">
      <div class="container inner">
        <div class="socials">
          <a href="${SHOP.facebook}" target="_blank" rel="noopener" aria-label="Facebook">${icon("fb")}</a>
          <span class="sep" aria-hidden="true"></span>
          <a href="${SHOP.instagram}" target="_blank" rel="noopener" aria-label="Instagram">${icon("ig")}</a>
        </div>
        <nav class="footer-links" aria-label="Informacije">
          <a href="o-nama.html">O nama</a><a href="asortiman.html">Asortiman</a><a href="dostava.html">Naručivanje, plaćanje i dostava</a>
          <a href="cesta-pitanja.html">Česta pitanja</a><a href="kontakt.html">Kontakt</a><a href="lista-zelja.html">Lista želja</a>
          <a href="uslovi.html">Uslovi korišćenja i prodaje</a><a href="privatnost.html">Pravila privatnosti</a><a href="mapa-sajta.html">Mapa sajta</a>
        </nav>
        <div class="footer-meta">PIB: ${SHOP.pib} &nbsp;·&nbsp; Matični broj: ${SHOP.mb}</div>
        <div class="footer-small">© ${new Date().getFullYear()} Cvećara Cveta Cvetić – ${SHOP.address} – <a href="${SHOP.phoneHref}">${SHOP.phone}</a></div>
      </div>`;
    frame.append(footer);

    const drawer = $("[data-drawer]");
    const setDrawer = (open) => { drawer.classList.toggle("open", open); drawer.setAttribute("aria-hidden", String(!open)); document.body.style.overflow = open ? "hidden" : ""; };
    $("[data-menu-open]").addEventListener("click", () => setDrawer(true));
    $$("[data-menu-close]").forEach((b) => b.addEventListener("click", () => setDrawer(false)));
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") setDrawer(false); });
    const hdr = $(".header");
    const onScroll = () => hdr.classList.toggle("scrolled", scrollY > 40);
    addEventListener("scroll", onScroll, { passive: true }); onScroll();

    cookieBanner();
    updateBadges();
  }

  function cookieBanner() {
    if (store.get("cc_cookie", null)) return;
    const el = document.createElement("div");
    el.className = "cookie";
    el.innerHTML = `<p>Koristimo samo neophodne kolačiće (korpa, lista želja) kako bi sajt radio kako treba. <a href="privatnost.html">Saznajte više</a>.</p><button class="btn btn-primary btn-sm">U redu</button>`;
    $("button", el).addEventListener("click", () => { store.set("cc_cookie", 1); el.remove(); });
    document.body.append(el);
  }

  /* ---------- Product card ---------- */
  // Home uses the mockup-01 card; shop, related and wishlist use the mockup-04 card (opts.v2).
  function productCard(p, opts = {}) {
    const wished = Wish.has(p.slug);
    const badge = opts.badge ? `<span class="tag-badge ${opts.badge.cls || ""}">${esc(opts.badge.text)}</span>` : "";
    const heart = (cls) => `<button class="${cls} ${wished ? "on" : ""}" data-wish="${esc(p.slug)}" aria-label="Dodaj u listu želja" aria-pressed="${wished}">${icon("heart")}</button>`;
    const media = `<a class="media" href="${productUrl(p)}">${badge}<img src="${esc(p.image)}" alt="${esc(shortName(p.name))}" loading="lazy" width="400" height="500"></a>`;
    if (opts.v2) {
      return `
      <article class="product-card v2">
        ${media}
        <div class="body">
          <div class="name-row"><a class="title" href="${productUrl(p)}">${esc(shortName(p.name))}</a>${heart("wish-inline")}</div>
          <span class="price">${money(p.price)}</span>
          <button class="btn btn-primary btn-cart" data-add="${esc(p.slug)}">${icon("basket")}Dodaj u korpu</button>
        </div>
      </article>`;
    }
    return `
      <article class="product-card">
        ${heart("wish")}
        ${media}
        <div class="body">
          <a class="title" href="${productUrl(p)}">${esc(shortName(p.name))}</a>
          <span class="price">${money(p.price)}</span>
          <button class="btn btn-primary btn-cart" data-add="${esc(p.slug)}">${icon("cart")}Dodaj u korpu</button>
        </div>
      </article>`;
  }

  function bindProductActions(root = document) {
    root.addEventListener("click", (e) => {
      const add = e.target.closest("[data-add]");
      if (add) {
        const p = DATA.bySlug.get(add.dataset.add);
        Cart.add(p.slug, 1);
        toast(`${icon("check")}<span>„${esc(shortName(p.name))}“ je dodat u korpu.</span><a href="korpa.html">Pogledaj korpu</a>`);
        return;
      }
      const w = e.target.closest("[data-wish]");
      if (w) {
        const on = Wish.toggle(w.dataset.wish);
        w.classList.toggle("on", on); w.setAttribute("aria-pressed", on);
        toast(on ? `${icon("heart")}<span>Dodato u listu želja.</span><a href="lista-zelja.html">Lista želja</a>` : `<span>Uklonjeno iz liste želja.</span>`);
        if (document.body.dataset.page === "lista-zelja" && !on) pages["lista-zelja"]();
      }
    });
  }

  /* ---------- Pages ---------- */
  const firstOf = (cat) => DATA.products.find((p) => p.cats.includes(cat));
  const pick = (names) => names.map((n) => DATA.products.find((p) => p.name.includes(n))).filter(Boolean);
  // "Najpopularnije": featured bouquets first, then flowers, then gift sets and extras.
  const popRank = (p) => {
    const f = FEATURED.findIndex((n) => p.name.includes(n));
    if (f >= 0) return f;
    return p.cats.some((c) => ["poklon-set", "dodaci"].includes(c)) ? 200 : 100;
  };
  const FEATURED = ["Puder roze san", "Ružičasti san", "Lavanda elegancija", "Ljubičasti osmeh", "Luksuzna orhideja", "Pastelna elegancija", "Ljubičasta elegancija", "Nežna pažnja"];
  // Category strip on the shop page (mockup 04). Empty categories show a "call us" message.
  const SHOP_CATS = [
    { slug: "buketi", label: "Buketi", img: "img/design/cat-buketi.jpg" },
    { slug: "mini-buketi", label: "Mini buketi", img: "img/design/cat-mini-buketi.jpg" },
    { slug: "cvetni-aranzmani", label: "Cvetni aranžmani", img: "img/design/cat-cvetni-aranzmani.jpg" },
    { slug: "vecna-ruza", label: "Večna ruža", img: "img/design/cat-vecna-ruza.jpg" },
    { slug: "poklon-set", label: "Poklon set", img: "img/design/cat-poklon-set.jpg" },
    { slug: "saksijsko-cvece", label: "Saksijsko cveće", img: "img/design/cat-saksijsko-cvece.jpg" },
    { slug: "helijum-baloni", label: "Helijum baloni", img: "img/design/cat-helijum-baloni.jpg" },
    { slug: "kad-je-tesko", label: "Kad je teško", img: "img/design/cat-kad-je-tesko.jpg" },
    { slug: "bidermajeri", label: "Bidermajeri" },
    { slug: "dodaci", label: "Dodaci" },
    { slug: "specijalne-ponude", label: "Specijalne ponude", img: "img/design/special-ponude.jpg" },
  ];

  const pages = {
    home() {
      $("[data-featured]").innerHTML = pick(FEATURED).map((p) => productCard(p)).join("");
    },

    shop() {
      const state = {
        kat: params.get("kat") || "",
        q: params.get("q") || "",
        sort: params.get("sort") || "pop",
        page: Number(params.get("str")) || 1,
      };
      // "Novo" marks the two most recently added products; "Sa medom" any product with a teddy bear.
      const newest = new Set([...DATA.products].sort((a, b) => b.date.localeCompare(a.date)).slice(0, 2).map((p) => p.slug));
      const badgeFor = (p) =>
        /\bmed(a|om|e)?\b|meda/i.test(p.name) ? { text: "Sa medom", cls: "right" } :
        newest.has(p.slug) ? { text: "Novo", cls: "pink" } : null;

      $("[data-cats]").innerHTML = SHOP_CATS.map((c) =>
        `<button class="cat-card" type="button" data-kat="${c.slug}"><img src="${c.img || firstOf(c.slug)?.image || "img/design/cat-buketi.jpg"}" alt="" loading="lazy"><span>${esc(c.label)}</span></button>`).join("");
      const track = $("[data-cats]");
      $$("[data-cat-scroll]").forEach((b) => b.addEventListener("click", () => track.scrollBy({ left: Number(b.dataset.catScroll) * 480, behavior: "smooth" })));

      function syncControls() {
        $$("[data-kat]").forEach((b) => b.classList.toggle("active", b.dataset.kat === state.kat));
        $("[data-sort]").value = state.sort;
        $("[data-q]").value = state.q;
        const label = SHOP_CATS.find((c) => c.slug === state.kat)?.label || catName(state.kat);
        $("[data-title]").textContent = state.kat ? label : "Asortiman";
        document.title = `${state.kat ? label : "Asortiman"} – Cveta Cvetić – Novi Sad`;
      }

      function writeUrl() {
        const u = new URLSearchParams();
        if (state.kat) u.set("kat", state.kat);
        if (state.q) u.set("q", state.q);
        if (state.sort !== "pop") u.set("sort", state.sort);
        if (state.page > 1) u.set("str", state.page);
        history.replaceState(null, "", `${location.pathname}${u.toString() ? "?" + u : ""}`);
      }

      function filtered() {
        const q = state.q.trim().toLowerCase();
        const list = DATA.products.filter((p) =>
          (!state.kat || p.cats.includes(state.kat)) &&
          (!q || (p.name + " " + p.short + " " + p.tags.join(" ")).toLowerCase().includes(q)));
        const sorters = {
          pop: (a, b) => popRank(a) - popRank(b),
          "cena-rastuce": (a, b) => a.price - b.price,
          "cena-opadajuce": (a, b) => b.price - a.price,
          najnovije: (a, b) => b.date.localeCompare(a.date),
          naziv: (a, b) => shortName(a.name).localeCompare(shortName(b.name), "sr"),
        };
        return [...list].sort(sorters[state.sort] || sorters.pop);
      }

      function render() {
        syncControls(); writeUrl();
        const list = filtered();
        const totalPages = Math.max(1, Math.ceil(list.length / PER_PAGE));
        state.page = Math.min(state.page, totalPages);
        const from = (state.page - 1) * PER_PAGE;
        const slice = list.slice(from, from + PER_PAGE);
        $("[data-count]").textContent = list.length ? `Prikaz ${from + 1}–${from + slice.length} od ${list.length} rezultata` : "Nema rezultata";
        $("[data-grid]").innerHTML = slice.length ? slice.map((p) => productCard(p, { v2: true, badge: badgeFor(p) })).join("") :
          `<div class="empty" style="grid-column:1/-1"><h3>Ovu ponudu pripremamo po porudžbini</h3><p>Pozovite nas na <a href="${SHOP.phoneHref}">${SHOP.phone}</a> – napravićemo poklon po Vašoj želji.</p><button class="btn btn-outline" data-reset>Prikaži sve proizvode</button></div>`;
        $("[data-pages]").innerHTML = totalPages > 1 ? Array.from({ length: totalPages }, (_, i) => `<button class="${i + 1 === state.page ? "active" : ""}" data-goto="${i + 1}" aria-label="Strana ${i + 1}">${i + 1}</button>`).join("") : "";
      }

      document.addEventListener("click", (e) => {
        const k = e.target.closest("[data-kat]");
        if (k) { state.kat = state.kat === k.dataset.kat ? "" : k.dataset.kat; state.page = 1; render(); }
        const pg = e.target.closest("[data-goto]");
        if (pg) { state.page = Number(pg.dataset.goto); render(); $("[data-count]").scrollIntoView({ behavior: "smooth", block: "center" }); }
        if (e.target.closest("[data-reset]")) { Object.assign(state, { kat: "", q: "", page: 1, sort: "pop" }); render(); }
      });
      $("[data-sort]").addEventListener("change", (e) => { state.sort = e.target.value; state.page = 1; render(); });
      $("[data-q-form]").addEventListener("submit", (e) => { e.preventDefault(); state.q = $("[data-q]").value.trim(); state.page = 1; render(); });
      render();
      $(".cat-card.active")?.scrollIntoView({ block: "nearest", inline: "center" });
    },

    product() {
      const p = DATA.bySlug.get(params.get("p"));
      const root = $("[data-product]");
      if (!p) {
        root.innerHTML = `<div class="empty card"><h3>Proizvod nije pronađen</h3><p>Možda je uklonjen iz ponude.</p><a class="btn btn-primary" href="asortiman.html">Pogledajte asortiman</a></div>`;
        return;
      }
      document.title = `${shortName(p.name)} – Cveta Cvetić – Novi Sad`;
      $('meta[name="description"]')?.setAttribute("content", p.short.slice(0, 155));
      const mainCat = p.cats[0];
      $("[data-crumbs]").innerHTML = `<a href="index.html">Početna</a><span>/</span><a href="asortiman.html">Asortiman</a>${mainCat ? `<span>/</span><a href="asortiman.html?kat=${mainCat}">${esc(catName(mainCat))}</a>` : ""}<span>/</span>${esc(shortName(p.name))}`;
      const wished = Wish.has(p.slug);
      root.innerHTML = `
        <div class="product-page">
          <div class="product-gallery"><img src="${esc(p.image)}" alt="${esc(p.name)}" width="400" height="500"></div>
          <div class="product-info">
            ${mainCat ? `<a class="eyebrow" href="asortiman.html?kat=${mainCat}">${esc(catName(mainCat))}</a>` : ""}
            <h1>${esc(p.name)}</h1>
            <span class="price">${money(p.price)}</span>
            <div class="desc">${p.description || `<p>${esc(p.short)}</p>`}</div>
            <div class="buy-row">
              <div class="qty"><button type="button" data-q="-1" aria-label="Manje">−</button><input type="number" min="1" max="99" value="1" aria-label="Količina" data-qty><button type="button" data-q="1" aria-label="Više">+</button></div>
              <button class="btn btn-primary" data-buy>${icon("cart")}Dodaj u korpu</button>
              <button class="icon-btn wish-inline ${wished ? "on" : ""}" data-wish="${esc(p.slug)}" aria-label="Lista želja" aria-pressed="${wished}" style="color:var(--pink-500)">${icon("heart")}</button>
            </div>
            <div class="card perks">
              <div>${icon("truck")}Besplatna dostava na teritoriji Novog Sada</div>
              <div>${icon("gift")}Poruka ili čestitka uz buket – samo je upišite pri naručivanju</div>
              <div>${icon("phone")}Porudžbina i telefonom: <a href="${SHOP.phoneHref}">${SHOP.phone}</a></div>
            </div>
            <ul class="meta-list">
              <li><strong>Kategorije:</strong> ${p.cats.map((c) => `<a href="asortiman.html?kat=${c}">${esc(catName(c))}</a>`).join(", ") || "—"}</li>
              ${p.tags.length ? `<li><strong>Oznake:</strong><div class="chips" style="margin-top:.4rem">${p.tags.map((t) => `<span class="chip">${esc(t)}</span>`).join("")}</div></li>` : ""}
            </ul>
          </div>
        </div>`;
      const qtyEl = $("[data-qty]", root);
      root.addEventListener("click", (e) => {
        const step = e.target.closest("[data-q]");
        if (step) qtyEl.value = Math.max(1, Math.min(99, (Number(qtyEl.value) || 1) + Number(step.dataset.q)));
        if (e.target.closest("[data-buy]")) {
          Cart.add(p.slug, Math.max(1, Number(qtyEl.value) || 1));
          toast(`${icon("check")}<span>Dodato u korpu.</span><a href="korpa.html">Završi kupovinu</a>`);
        }
      });
      const related = DATA.products.filter((x) => x.slug !== p.slug && x.cats.some((c) => p.cats.includes(c))).slice(0, 4);
      $("[data-related]").innerHTML = related.map((x) => productCard(x, { v2: true })).join("");
      $("[data-related-wrap]").hidden = !related.length;
    },

    cart() {
      const root = $("[data-cart]");
      function render() {
        const items = Cart.detailed();
        if (!items.length) {
          root.innerHTML = `<div class="empty card"><h3>Vaša korpa je prazna</h3><p>Izaberite buket koji govori više od reči.</p><a class="btn btn-primary" href="asortiman.html">Pogledajte asortiman</a></div>`;
          return;
        }
        const total = Cart.total();
        root.innerHTML = `
          <div class="cart-layout">
            <div class="card cart-items">
              ${items.map(({ product: p, qty }) => `
                <div class="cart-row" data-slug="${esc(p.slug)}">
                  <a href="${productUrl(p)}"><img src="${esc(p.image)}" alt=""></a>
                  <div class="info"><a class="name" href="${productUrl(p)}">${esc(shortName(p.name))}</a><div class="unit">${money(p.price)} / kom</div></div>
                  <div class="qty"><button type="button" data-step="-1" aria-label="Manje">−</button><input type="number" min="1" max="99" value="${qty}" aria-label="Količina" data-set><button type="button" data-step="1" aria-label="Više">+</button></div>
                  <div class="line-total">${money(p.price * qty)}</div>
                  <button class="remove" data-remove aria-label="Ukloni">${icon("trash")}</button>
                </div>`).join("")}
            </div>
            <aside class="card summary">
              <h3>Pregled porudžbine</h3>
              <div class="sum-row"><span>Međuzbir</span><strong>${money(total)}</strong></div>
              <div class="sum-row"><span>Dostava (Novi Sad)</span><strong style="color:var(--mint-ink)">Besplatno</strong></div>
              <div class="sum-row total"><span>Ukupno</span><span>${money(total)}</span></div>
              <a class="btn btn-primary btn-block" href="porucivanje.html" style="margin-top:1rem">Nastavi na naručivanje ${icon("arrow")}</a>
              <a class="btn btn-outline btn-block" href="asortiman.html" style="margin-top:.6rem">Nastavi kupovinu</a>
              <p class="form-note" style="margin-top:1rem">Cene su sa uračunatim PDV-om. Plaćanje pouzećem, uplatom na račun ili karticom u radnji.</p>
            </aside>
          </div>`;
      }
      root.addEventListener("click", (e) => {
        const row = e.target.closest("[data-slug]"); if (!row) return;
        const slug = row.dataset.slug;
        const step = e.target.closest("[data-step]");
        if (step) { const it = Cart.items().find((i) => i.slug === slug); Cart.setQty(slug, it.qty + Number(step.dataset.step)); render(); }
        if (e.target.closest("[data-remove]")) { Cart.remove(slug); render(); }
      });
      root.addEventListener("change", (e) => {
        if (!e.target.matches("[data-set]")) return;
        Cart.setQty(e.target.closest("[data-slug]").dataset.slug, Number(e.target.value) || 1); render();
      });
      render();
    },

    checkout() {
      const root = $("[data-checkout]");
      const items = Cart.detailed();
      if (!items.length) {
        root.innerHTML = `<div class="empty card"><h3>Korpa je prazna</h3><p>Dodajte proizvode pre naručivanja.</p><a class="btn btn-primary" href="asortiman.html">Asortiman</a></div>`;
        return;
      }
      const total = Cart.total();
      $("[data-summary]").innerHTML = `
        <h3>Vaša porudžbina</h3>
        <ul class="mini-items">${items.map(({ product: p, qty }) => `<li><img src="${esc(p.image)}" alt=""><span class="n">${esc(shortName(p.name))} <small>× ${qty}</small></span><strong>${money(p.price * qty)}</strong></li>`).join("")}</ul>
        <div class="sum-row"><span>Dostava</span><strong data-ship>Besplatno</strong></div>
        <div class="sum-row total"><span>Ukupno</span><span>${money(total)}</span></div>`;
      const form = $("[data-checkout-form]");
      const today = new Date(); today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
      form.date.min = today.toISOString().slice(0, 10);
      form.date.value = form.date.min;
      const toggleDelivery = () => {
        const pickup = form.delivery.value === "preuzimanje";
        $$("[data-delivery-only]").forEach((el) => (el.hidden = pickup));
        form.address.required = !pickup;
        $("[data-ship]").textContent = pickup ? "Preuzimanje u radnji" : "Besplatno";
        $("[data-time-label]").textContent = pickup ? "Vreme preuzimanja" : "Vreme dostave";
      };
      form.addEventListener("change", (e) => { if (e.target.name === "delivery") toggleDelivery(); });
      $("[data-other-recipient]").addEventListener("change", (e) => ($("[data-recipient]").hidden = !e.target.checked));
      toggleDelivery();
      handleForm(form, "api/order", (data) => ({
        ...data,
        items: Cart.items(),
      }), (res) => {
        Cart.clear();
        location.href = `hvala.html?n=${encodeURIComponent(res.orderNumber)}`;
      });
    },

    thanks() {
      const n = params.get("n");
      $("[data-order-no]").textContent = n ? `#${n}` : "";
    },

    wishlist() {
      const list = Wish.items().map((s) => DATA.bySlug.get(s)).filter(Boolean);
      $("[data-wish-grid]").innerHTML = list.length ? list.map((p) => productCard(p, { v2: true })).join("") :
        `<div class="empty card" style="grid-column:1/-1"><h3>Lista želja je prazna</h3><p>Kliknite na srce na proizvodu da ga sačuvate za kasnije.</p><a class="btn btn-primary" href="asortiman.html">Asortiman</a></div>`;
    },

    contact() {
      // Order follows the grid: two tall photos, three small (row 1), one tall on the right, three small (row 2).
      const shots = pick(["Puder roze san", "Ružičasti san", "Lavanda elegancija", "Nežna pažnja", "Ljubičasta elegancija", "Luksuzna orhideja", "Ljubičasti osmeh", "Romantična harmonija", "Pastelna elegancija"]);
      const tall = new Set([0, 1, 5]);
      $("[data-collage]").innerHTML = shots.map((p, i) => `<a class="${tall.has(i) ? "tall" : ""}" href="${productUrl(p)}" aria-label="${esc(shortName(p.name))}"><img src="${esc(p.image)}" alt="" loading="lazy"></a>`).join("");
      handleForm($("[data-contact-form]"), "api/contact", (d) => d, () => {
        $("[data-contact-form]").reset();
        $("[data-contact-result]").innerHTML = `<div class="alert alert-ok">Hvala! Vaša poruka je poslata. Javićemo Vam se u najkraćem roku.</div>`;
      });
    },

    blog() {
      $("[data-posts]").innerHTML = DATA.posts.map(postCard).join("");
    },

    post() {
      const post = DATA.posts.find((x) => x.slug === params.get("c"));
      const root = $("[data-post]");
      if (!post) { root.innerHTML = `<div class="empty card"><h3>Članak nije pronađen</h3><a class="btn btn-primary" href="aktuelnosti.html">Svi članci</a></div>`; return; }
      document.title = `${post.title} – Cveta Cvetić`;
      $("[data-post-title]").textContent = post.title;
      $("[data-crumb]").textContent = post.title;
      root.innerHTML = `<article class="card prose"><time>${fmtDate(post.date)}</time>${post.image ? `<img src="${esc(post.image)}" alt="">` : ""}${post.content}
        <p style="margin-top:2rem"><a class="btn btn-primary" href="asortiman.html">Pogledajte asortiman</a></p></article>`;
      $("[data-more]").innerHTML = DATA.posts.filter((x) => x.slug !== post.slug).slice(0, 3).map(postCard).join("");
    },

    sitemap() {
      $("[data-sitemap-cats]").innerHTML = DATA.categories.map((c) => `<li><a href="asortiman.html?kat=${c.slug}">${esc(c.name)}</a> (${c.count})</li>`).join("");
      $("[data-sitemap-products]").innerHTML = DATA.products.map((p) => `<li><a href="${productUrl(p)}">${esc(p.name)}</a></li>`).join("");
      $("[data-sitemap-posts]").innerHTML = DATA.posts.map((p) => `<li><a href="clanak.html?c=${p.slug}">${esc(p.title)}</a></li>`).join("");
    },
  };
  pages["lista-zelja"] = pages.wishlist;

  // FAQ accordion: opening one question closes the others (animation is pure CSS).
  pages.faq = () => {
    const items = $$(".faq-item");
    items.forEach((item) => {
      const btn = $(".faq-q", item);
      btn.addEventListener("click", () => {
        const opening = !item.classList.contains("is-open");
        items.forEach((x) => { x.classList.remove("is-open"); $(".faq-q", x).setAttribute("aria-expanded", "false"); });
        if (opening) { item.classList.add("is-open"); btn.setAttribute("aria-expanded", "true"); }
      });
    });
  };

  const fmtDate = (d) => new Date(d).toLocaleDateString("sr-Latn-RS", { day: "numeric", month: "long", year: "numeric" });
  function postCard(p) {
    return `<a class="post-card" href="clanak.html?c=${encodeURIComponent(p.slug)}">
      ${p.image ? `<img src="${esc(p.image)}" alt="" loading="lazy">` : ""}
      <div class="body"><time>${fmtDate(p.date)}</time><h3>${esc(p.title)}</h3><p>${esc(p.excerpt)}</p></div></a>`;
  }

  /* ---------- Forms ---------- */
  function validate(form) {
    let ok = true;
    $$(".field", form).forEach((f) => {
      const input = $("input, select, textarea", f);
      if (!input || input.closest("[hidden]")) { f.classList.remove("invalid"); return; }
      const bad = !input.checkValidity();
      f.classList.toggle("invalid", bad);
      if (bad && ok) { input.focus(); ok = false; }
    });
    return ok;
  }

  function handleForm(form, url, shape, onOk) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const result = $("[data-form-result]", form) || $("[data-contact-result]");
      if (result) result.innerHTML = "";
      if (!validate(form)) return;
      const btn = $('button[type="submit"]', form);
      btn.disabled = true;
      const data = Object.fromEntries(new FormData(form).entries());
      try {
        const res = await fetch(url, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(shape(data)) });
        // Static hosting (e.g. GitHub Pages) has no server for orders and messages.
        if ([404, 405, 501].includes(res.status)) throw new Error("DEMO");
        const body = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(body.error || "Greška pri slanju.");
        onOk(body);
      } catch (err) {
        if (result && err.message === "DEMO") {
          result.innerHTML = `<div class="alert alert-err">Ovo je probna verzija sajta – porudžbine i poruke još ne primamo preko sajta. Pozovite nas na <a href="${SHOP.phoneHref}">${SHOP.phone}</a>.</div>`;
        } else if (result) result.innerHTML = `<div class="alert alert-err">${esc(err.message)} Pokušajte ponovo ili nas pozovite na <a href="${SHOP.phoneHref}">${SHOP.phone}</a>.</div>`;
      } finally {
        btn.disabled = false;
      }
    });
    form.addEventListener("input", (e) => e.target.closest(".field")?.classList.remove("invalid"));
  }

  /* ---------- Boot ---------- */
  async function boot() {
    const page = document.body.dataset.page;
    try {
      await loadData();
    } catch {
      document.body.insertAdjacentHTML("afterbegin", `<div class="alert alert-err" style="margin:1rem">Podaci nisu učitani. Pokrenite sajt preko servera (npm start).</div>`);
      return;
    }
    renderLayout(page);
    bindProductActions();
    pages[page]?.();
    $$("[data-shop]").forEach((el) => { el.textContent = SHOP[el.dataset.shop]; });
    $$("[data-shop-href]").forEach((el) => {
      const k = el.dataset.shopHref;
      el.href = k === "phone" ? SHOP.phoneHref : k === "email" ? `mailto:${SHOP.email}` : SHOP[k];
    });
        addEventListener("storage", updateBadges);
  }
  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", boot) : boot();
  window.CC = { icon, SHOP };
})();
