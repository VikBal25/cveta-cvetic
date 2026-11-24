/* Cveta Cvetić – shop panel (no dependencies) */
(function () {
  'use strict';
  var CP = window.CP;
  var main = document.getElementById('cp-main');
  var sheet = document.getElementById('cp-sheet');
  var toastEl = document.getElementById('cp-toast');
  var state = { cats: [], products: null, orders: null, messages: null, q: '', cat: '', show: 'all', orderFilter: 'new' };

  /* ---------- helpers ---------- */
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function money(v) { if (v === '' || v == null) return ''; return Number(v).toLocaleString('sr-RS').replace(/,/g, '.') + ' RSD'; }
  function $(sel, root) { return (root || document).querySelector(sel); }
  function $$(sel, root) { return [].slice.call((root || document).querySelectorAll(sel)); }
  var toastTimer;
  function toast(msg, isError) {
    toastEl.textContent = msg; toastEl.classList.toggle('is-error', !!isError); toastEl.hidden = false;
    clearTimeout(toastTimer); toastTimer = setTimeout(function () { toastEl.hidden = true; }, isError ? 5000 : 2600);
  }
  function api(path, opts) {
    opts = opts || {};
    var init = { method: opts.method || 'GET', credentials: 'same-origin', headers: { 'X-WP-Nonce': CP.nonce } };
    if (opts.json) { init.headers['Content-Type'] = 'application/json'; init.body = JSON.stringify(opts.json); }
    if (opts.form) { init.body = opts.form; }
    return fetch(CP.api + path, init).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok) {
          if (r.status === 401 || r.status === 403) { if (data && data.code === 'rest_cookie_invalid_nonce') location.reload(); }
          throw new Error((data && data.message) || 'Greška na serveru (' + r.status + ').');
        }
        return data;
      });
    });
  }
  function catName(id) { for (var i = 0; i < state.cats.length; i++) if (state.cats[i].id === id) return state.cats[i].name; return ''; }

  /* ---------- tabs ---------- */
  function route() {
    var tab = (location.hash || '#proizvodi').slice(1);
    if (['proizvodi', 'narudzbe', 'poruke'].indexOf(tab) < 0) tab = 'proizvodi';
    $$('.cp-tabs a').forEach(function (a) { a.classList.toggle('is-active', a.dataset.tab === tab); });
    window.scrollTo(0, 0);
    if (tab === 'proizvodi') showProducts();
    if (tab === 'narudzbe') showOrders();
    if (tab === 'poruke') showMessages();
  }
  window.addEventListener('hashchange', route);

  /* ---------- products ---------- */
  function showProducts() {
    if (!state.products) {
      main.innerHTML = '<p class="cp-loading">Učitavanje proizvoda…</p>';
      api('products').then(function (list) { state.products = list; showProducts(); }).catch(function (e) { main.innerHTML = '<p class="cp-empty">' + esc(e.message) + '</p>'; });
      return;
    }
    var opts = state.cats.map(function (c) { return '<option value="' + c.id + '"' + (String(c.id) === state.cat ? ' selected' : '') + '>' + esc(c.name) + '</option>'; }).join('');
    main.innerHTML =
      '<div class="cp-head"><h1>Proizvodi <small id="cp-count"></small></h1>' +
      '<button class="cp-btn cp-btn-primary" type="button" data-new>+ Novi proizvod</button></div>' +
      '<div class="cp-filters">' +
      '<input class="cp-input" type="search" id="cp-q" placeholder="Pretraga po nazivu…" value="' + esc(state.q) + '">' +
      '<select class="cp-input" id="cp-cat"><option value="">Sve kategorije</option>' + opts + '</select>' +
      '</div>' +
      '<div class="cp-chips" style="margin-bottom:1rem">' +
      chip('all', 'Svi') + chip('out', 'Nema na stanju') + chip('hidden', 'Skriveni') + chip('hit', 'Hit') + chip('sale', 'Na akciji') +
      '</div>' +
      '<div class="cp-grid" id="cp-grid"></div>';
    renderGrid();
    $('#cp-q').addEventListener('input', function (e) { state.q = e.target.value; renderGrid(); });
    $('#cp-cat').addEventListener('change', function (e) { state.cat = e.target.value; renderGrid(); });
    $$('.cp-chip').forEach(function (b) { b.addEventListener('click', function () { state.show = b.dataset.show; $$('.cp-chip').forEach(function (x) { x.classList.toggle('is-on', x === b); }); renderGrid(); }); });
    $('[data-new]').addEventListener('click', function () { openEditor(null); });
  }
  function chip(key, label) { return '<button type="button" class="cp-chip' + (state.show === key ? ' is-on' : '') + '" data-show="' + key + '">' + label + '</button>'; }

  function filtered() {
    var q = state.q.trim().toLowerCase(), cat = state.cat ? Number(state.cat) : 0;
    return state.products.filter(function (p) {
      if (q && p.name.toLowerCase().indexOf(q) < 0) return false;
      if (cat && p.cats.indexOf(cat) < 0) return false;
      if (state.show === 'out' && p.instock) return false;
      if (state.show === 'hidden' && p.visible) return false;
      if (state.show === 'hit' && !p.featured) return false;
      if (state.show === 'sale' && !p.sale) return false;
      return true;
    });
  }

  function cardHTML(p) {
    var tags = '';
    if (!p.visible) tags += '<span class="cp-tag hid">Skriven</span>';
    if (!p.instock) tags += '<span class="cp-tag out">Nema</span>';
    if (p.featured) tags += '<span class="cp-tag hit">★ Hit</span>';
    if (p.sale) tags += '<span class="cp-tag sale">Akcija</span>';
    var price = p.simple ? (p.sale ? '<s>' + money(p.regular) + '</s>' + money(p.sale) : money(p.regular)) : 'Više cena';
    return '<button type="button" class="cp-card' + (!p.visible || !p.instock ? ' is-off' : '') + '" data-id="' + p.id + '">' +
      '<div class="ph">' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="" loading="lazy">' : '✿') + '</div>' +
      '<div class="cp-tags">' + tags + '</div>' +
      '<div class="bd"><span class="nm">' + esc(p.short || p.name) + '</span><span class="pr">' + price + '</span></div></button>';
  }

  function renderGrid() {
    var list = filtered();
    $('#cp-count').textContent = list.length + ' od ' + state.products.length;
    var grid = $('#cp-grid');
    grid.innerHTML = list.length ? list.map(cardHTML).join('') : '<p class="cp-empty" style="grid-column:1/-1">Nema proizvoda za ovaj izbor.</p>';
    $$('.cp-card[data-id]', grid).forEach(function (c) { c.addEventListener('click', function () { openEditor(Number(c.dataset.id)); }); });
  }

  /* ---------- editor ---------- */
  var ed = null; // current editor state

  function openEditor(id) {
    ed = { id: id, data: null, image: null, gallery: [], uploading: 0, dirty: false };
    sheet.hidden = false;
    document.body.style.overflow = 'hidden';
    sheet.innerHTML = '<div class="cp-sheet-panel"><div class="cp-sheet-head"><h2>' + (id ? 'Uređivanje' : 'Novi proizvod') + '</h2><button class="cp-x" type="button" data-close aria-label="Zatvori">×</button></div><div class="cp-sheet-body"><p class="cp-loading">Učitavanje…</p></div></div>';
    $('[data-close]', sheet).addEventListener('click', closeEditor);
    if (!id) { ed.data = { name: '', regular: '', sale: '', description: '', excerpt: '', cats: state.cat ? [Number(state.cat)] : [], instock: true, featured: false, visible: true, simple: true }; renderEditor(); return; }
    api('products/' + id).then(function (d) { ed.data = d; ed.image = d.image && d.image.id ? d.image : null; ed.gallery = d.gallery || []; renderEditor(); })
      .catch(function (e) { toast(e.message, true); closeEditor(true); });
  }

  function closeEditor(force) {
    if (force !== true && ed && ed.dirty && !window.confirmClose) {
      var btn = $('[data-close]', sheet);
      if (!btn.dataset.sure) { btn.dataset.sure = '1'; toast('Izmene nisu sačuvane. Kliknite × ponovo da zatvorite bez čuvanja.', true); return; }
    }
    sheet.hidden = true; sheet.innerHTML = ''; document.body.style.overflow = ''; ed = null;
  }
  sheet.addEventListener('click', function (e) { if (e.target === sheet) closeEditor(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !sheet.hidden) closeEditor(); });

  function renderEditor() {
    var d = ed.data;
    var cats = state.cats.map(function (c) {
      return '<label><input type="checkbox" value="' + c.id + '"' + (d.cats.indexOf(c.id) > -1 ? ' checked' : '') + '> ' + esc(c.name) + '</label>';
    }).join('');
    var body =
      '<div class="cp-box"><h3>Fotografije</h3><div class="cp-photos" id="cp-photos"></div>' +
      '<p class="cp-muted" style="margin:.6rem 0 0;font-size:.85rem">Prva fotografija je glavna. Možete slikati telefonom ili izabrati iz galerije.</p></div>' +

      '<div class="cp-box">' +
      '<label class="cp-field">Naziv proizvoda<input type="text" id="f-name" value="' + esc(d.name) + '" placeholder="npr. Buket „Prolećna nežnost“ – 15 tulipana" required></label>' +
      (d.simple ?
        '<div class="cp-row">' +
        '<label class="cp-field">Cena (RSD)<input type="text" inputmode="numeric" id="f-regular" value="' + esc(d.regular) + '" placeholder="4500"></label>' +
        '<label class="cp-field">Akcijska cena<input type="text" inputmode="numeric" id="f-sale" value="' + esc(d.sale) + '" placeholder="nije obavezno"></label>' +
        '</div>' :
        '<p class="cp-muted">Ovaj proizvod ima više varijanti – cene se menjaju u <a href="' + esc(CP.admin) + '">administraciji</a>.</p>') +
      '</div>' +

      '<div class="cp-box"><h3>Kategorije</h3><div class="cp-cats" id="f-cats">' + cats + '</div></div>' +

      '<div class="cp-box">' +
      '<label class="cp-field">Kratak opis <small>prikazuje se pored cene</small><textarea id="f-excerpt" rows="3" placeholder="Jedna-dve rečenice o proizvodu">' + esc(d.excerpt) + '</textarea></label>' +
      '<div class="cp-field" style="margin-bottom:0">Opis <small>sastav, veličina, napomene</small>' +
      '<div class="cp-rich-bar"><button type="button" data-cmd="bold" title="Podebljano"><b>B</b></button><button type="button" data-cmd="insertUnorderedList" title="Lista">•</button></div>' +
      '<div class="cp-rich" id="f-desc" contenteditable="true" data-placeholder="Opišite proizvod…">' + (d.description || '') + '</div></div>' +
      '</div>' +

      '<div class="cp-box">' +
      sw('f-instock', 'Ima na stanju', 'Ako isključite, kupci vide „Nema na stanju“', d.instock) +
      sw('f-visible', 'Vidljiv na sajtu', 'Isključite da sakrijete proizvod bez brisanja', d.visible) +
      sw('f-featured', 'Hit na naslovnoj', 'Prikazuje se u bloku „Najprodavanije“', d.featured) +
      '</div>' +

      (ed.id ? '<div class="cp-box" style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:center">' +
        '<a class="cp-btn cp-btn-sm" href="' + esc(d.url) + '" target="_blank" rel="noopener">Pogledaj na sajtu ↗</a>' +
        '<button type="button" class="cp-btn cp-btn-sm cp-btn-danger" id="f-delete" style="margin-left:auto">Obriši proizvod</button></div>' : '');

    $('.cp-sheet-body', sheet).innerHTML = body;
    var foot = document.createElement('div');
    foot.className = 'cp-sheet-foot';
    foot.innerHTML = '<button type="button" class="cp-btn" data-cancel>Otkaži</button><button type="button" class="cp-btn cp-btn-primary" id="f-save">' + (ed.id ? 'Sačuvaj izmene' : 'Dodaj proizvod') + '</button>';
    $('.cp-sheet-panel', sheet).appendChild(foot);

    renderPhotos();
    var body$ = $('.cp-sheet-body', sheet);
    body$.addEventListener('input', function () { ed.dirty = true; });
    body$.addEventListener('change', function () { ed.dirty = true; });
    $$('.cp-rich-bar button', sheet).forEach(function (b) { b.addEventListener('mousedown', function (e) { e.preventDefault(); document.execCommand(b.dataset.cmd); ed.dirty = true; }); });
    $('#f-desc').addEventListener('paste', function (e) {
      e.preventDefault();
      var text = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, text);
    });
    ed.descStart = $('#f-desc').innerHTML;
    $('[data-cancel]', sheet).addEventListener('click', function () { closeEditor(); });
    $('#f-save').addEventListener('click', save);
    var del = $('#f-delete');
    if (del) del.addEventListener('click', function () {
      if (!del.classList.contains('is-sure')) { del.classList.add('is-sure'); del.textContent = 'Kliknite ponovo da obrišete'; setTimeout(function () { if (del.isConnected) { del.classList.remove('is-sure'); del.textContent = 'Obriši proizvod'; } }, 4000); return; }
      del.disabled = true;
      api('products/' + ed.id, { method: 'DELETE' }).then(function () {
        state.products = state.products.filter(function (p) { return p.id !== ed.id; });
        toast('Proizvod je obrisan (može se vratiti iz korpe za otpatke u administraciji 30 dana).');
        closeEditor(true); renderGrid();
      }).catch(function (e) { del.disabled = false; toast(e.message, true); });
    });
    if (!ed.id) $('#f-name').focus();
  }

  function sw(id, label, hint, on) {
    return '<label class="cp-switch"><span>' + label + '<small>' + hint + '</small></span><input type="checkbox" id="' + id + '"' + (on ? ' checked' : '') + '></label>';
  }

  /* photos */
  function allPhotos() { return (ed.image ? [ed.image] : []).concat(ed.gallery); }
  function renderPhotos() {
    var box = $('#cp-photos');
    if (!box) return;
    var list = allPhotos();
    box.innerHTML = list.map(function (ph, i) {
      return '<div class="cp-photo' + (ph.loading ? ' is-loading' : '') + '">' + (ph.url ? '<img src="' + esc(ph.url) + '" alt="">' : '') +
        (i === 0 ? '<span class="main">Glavna</span>' : '<button type="button" class="mk" data-main="' + i + '">Postavi kao glavnu</button>') +
        (ph.loading ? '' : '<button type="button" class="rm" data-rm="' + i + '" aria-label="Ukloni">×</button>') + '</div>';
    }).join('') +
      '<label class="cp-photo-add"><input type="file" accept="image/*" multiple><div><span>+</span>Dodaj fotografiju</div></label>';
    $$('[data-rm]', box).forEach(function (b) { b.addEventListener('click', function () { removePhoto(Number(b.dataset.rm)); }); });
    $$('[data-main]', box).forEach(function (b) { b.addEventListener('click', function () { makeMain(Number(b.dataset.main)); }); });
    $('input[type=file]', box).addEventListener('change', function (e) { [].slice.call(e.target.files).forEach(uploadPhoto); e.target.value = ''; });
  }
  function setPhotos(list) { ed.image = list[0] || null; ed.gallery = list.slice(1); ed.dirty = true; renderPhotos(); }
  function removePhoto(i) { var l = allPhotos(); l.splice(i, 1); setPhotos(l); }
  function makeMain(i) { var l = allPhotos(); var p = l.splice(i, 1)[0]; l.unshift(p); setPhotos(l); }

  function shrink(file) {
    // Phone photos are large: resize to max 1600 px JPEG before upload.
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png|webp|heic|heif)/i.test(file.type) || !window.createImageBitmap) { resolve(file); return; }
      createImageBitmap(file).then(function (bmp) {
        var max = 1600, k = Math.min(1, max / Math.max(bmp.width, bmp.height));
        if (k === 1 && file.size < 1.5e6) { resolve(file); return; }
        var c = document.createElement('canvas'); c.width = Math.round(bmp.width * k); c.height = Math.round(bmp.height * k);
        c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
        c.toBlob(function (b) { resolve(b ? new File([b], (file.name || 'foto').replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file); }, 'image/jpeg', 0.86);
      }).catch(function () { resolve(file); });
    });
  }

  function uploadPhoto(file) {
    var ph = { id: 0, url: URL.createObjectURL(file), loading: true };
    var l = allPhotos(); l.push(ph); setPhotos(l);
    ed.uploading++; updateSave();
    var me = ed;
    shrink(file).then(function (small) {
      var fd = new FormData();
      fd.append('file', small, small.name || 'foto.jpg');
      fd.append('title', ($('#f-name') && $('#f-name').value) || '');
      return api('media', { method: 'POST', form: fd });
    }).then(function (res) {
      if (ed !== me) return;
      ph.id = res.id; ph.url = res.url || ph.url; ph.loading = false; renderPhotos();
    }).catch(function (e) {
      if (ed !== me) return;
      removePhoto(allPhotos().indexOf(ph)); toast(e.message, true);
    }).then(function () { if (ed === me) { ed.uploading--; updateSave(); } });
  }
  function updateSave() { var b = $('#f-save'); if (b) { b.disabled = ed.uploading > 0; b.textContent = ed.uploading ? 'Čekam fotografije…' : (ed.id ? 'Sačuvaj izmene' : 'Dodaj proizvod'); } }

  function save() {
    var btn = $('#f-save');
    var d = ed.data;
    var payload = {
      name: $('#f-name').value.trim(),
      cats: $$('#f-cats input:checked').map(function (i) { return Number(i.value); }),
      excerpt: $('#f-excerpt').value,
      instock: $('#f-instock').checked,
      visible: $('#f-visible').checked,
      featured: $('#f-featured').checked,
      image: ed.image ? ed.image.id : 0,
      gallery: ed.gallery.map(function (g) { return g.id; }).filter(Boolean)
    };
    if (d.simple) { payload.regular = $('#f-regular').value; payload.sale = $('#f-sale').value; }
    var desc = $('#f-desc').innerHTML;
    if (!ed.id || desc !== ed.descStart) payload.description = desc;
    if (!payload.name) { toast('Upišite naziv proizvoda.', true); $('#f-name').focus(); return; }
    if (d.simple && !String(payload.regular).trim()) { toast('Upišite cenu.', true); $('#f-regular').focus(); return; }
    if (!payload.cats.length) { toast('Izaberite bar jednu kategoriju.', true); return; }
    btn.disabled = true; btn.textContent = 'Čuvam…';
    api('products' + (ed.id ? '/' + ed.id : ''), { method: 'POST', json: payload }).then(function (res) {
      var brief = { id: res.id, name: res.name, short: res.short, simple: res.simple, regular: res.regular, sale: res.sale, instock: res.instock, featured: res.featured, visible: res.visible, cats: res.cats, thumb: res.thumb, url: res.url };
      var idx = -1;
      state.products.forEach(function (p, i) { if (p.id === res.id) idx = i; });
      if (idx > -1) state.products[idx] = brief; else state.products.unshift(brief);
      toast(ed.id ? 'Sačuvano ✓' : 'Proizvod je dodat na sajt ✓');
      closeEditor(true); renderGrid();
    }).catch(function (e) { btn.disabled = false; updateSave(); toast(e.message, true); });
  }

  /* ---------- orders ---------- */
  function showOrders() {
    if (!state.orders) {
      main.innerHTML = '<p class="cp-loading">Učitavanje narudžbi…</p>';
      api('orders').then(function (l) { state.orders = l; showOrders(); }).catch(function (e) { main.innerHTML = '<p class="cp-empty">' + esc(e.message) + '</p>'; });
      return;
    }
    main.innerHTML = '<div class="cp-head"><h1>Narudžbe</h1><button class="cp-btn cp-btn-sm" type="button" data-reload>Osveži</button></div>' +
      '<div class="cp-chips" style="margin-bottom:1rem">' +
      ochip('new', 'Za obradu') + ochip('completed', 'Isporučene') + ochip('all', 'Sve') + '</div><div class="cp-orders" id="cp-orders"></div>';
    $$('.cp-chip').forEach(function (b) { b.addEventListener('click', function () { state.orderFilter = b.dataset.f; showOrders(); }); });
    $('[data-reload]').addEventListener('click', function () { state.orders = null; showOrders(); refreshBadge(); });
    var list = state.orders.filter(function (o) {
      if (state.orderFilter === 'new') return ['on-hold', 'processing', 'pending'].indexOf(o.status) > -1;
      if (state.orderFilter === 'completed') return o.status === 'completed';
      return true;
    });
    var box = $('#cp-orders');
    box.innerHTML = list.length ? list.map(orderHTML).join('') : '<p class="cp-empty">Nema narudžbi u ovoj grupi.</p>';
    $$('[data-st]', box).forEach(function (b) { b.addEventListener('click', function () { setStatus(Number(b.dataset.id), b.dataset.st, b); }); });
  }
  function ochip(k, l) { return '<button type="button" class="cp-chip' + (state.orderFilter === k ? ' is-on' : '') + '" data-f="' + k + '">' + l + '</button>'; }

  function orderHTML(o) {
    var rows = '';
    Object.keys(o.delivery || {}).forEach(function (k) { rows += '<dt>' + esc(k) + '</dt><dd>' + esc(o.delivery[k]) + '</dd>'; });
    var items = o.items.map(function (i) { return esc(i.name) + (i.qty > 1 ? ' × ' + i.qty : ''); }).join('<br>');
    var tel = (o.phone || '').replace(/[^\d+]/g, '');
    var btns = '';
    if (o.status !== 'processing' && o.status !== 'completed') btns += '<button class="cp-btn cp-btn-sm" data-st="processing" data-id="' + o.id + '">Prihvati (u pripremi)</button>';
    if (o.status !== 'completed') btns += '<button class="cp-btn cp-btn-sm cp-btn-primary" data-st="completed" data-id="' + o.id + '">Isporučeno</button>';
    if (o.status !== 'cancelled' && o.status !== 'completed') btns += '<button class="cp-btn cp-btn-sm cp-btn-danger" data-st="cancelled" data-id="' + o.id + '">Otkaži</button>';
    return '<details class="cp-order"' + (['on-hold', 'pending'].indexOf(o.status) > -1 ? ' open' : '') + '>' +
      '<summary><span class="who">#' + esc(o.number) + ' · ' + esc(o.name || 'Kupac') + '</span><span class="sum">' + esc(o.total) + '</span>' +
      '<span class="meta">' + esc(o.date) + (o.delivery && o.delivery['Datum'] ? ' · za ' + esc(o.delivery['Datum']) + (o.delivery['Vreme dostave'] ? ' ' + esc(o.delivery['Vreme dostave']) : '') : '') + '</span>' +
      '<span class="cp-st ' + esc(o.status) + '">' + esc(o.label) + '</span></summary>' +
      '<div class="cp-order-bd"><dl class="cp-dl">' +
      '<dt>Proizvodi</dt><dd>' + items + '</dd>' + rows +
      '<dt>Telefon</dt><dd>' + (tel ? '<a href="tel:' + esc(tel) + '">' + esc(o.phone) + '</a>' : '—') + '</dd>' +
      '<dt>E-mail</dt><dd>' + (o.email ? '<a href="mailto:' + esc(o.email) + '">' + esc(o.email) + '</a>' : '—') + '</dd>' +
      '<dt>Plaćanje</dt><dd>' + esc(o.payment) + '</dd>' +
      (o.note ? '<dt>Napomena</dt><dd>' + esc(o.note) + '</dd>' : '') +
      '</dl><div class="cp-actions">' + (tel ? '<a class="cp-btn cp-btn-sm" href="tel:' + esc(tel) + '">📞 Pozovi</a>' : '') + btns + '</div></div></details>';
  }

  function setStatus(id, st, btn) {
    btn.disabled = true;
    api('orders/' + id, { method: 'POST', json: { status: st } }).then(function (res) {
      state.orders.forEach(function (o) { if (o.id === id) { o.status = res.status; o.label = res.label; } });
      toast('Narudžba #' + id + ': ' + res.label);
      showOrders(); refreshBadge();
    }).catch(function (e) { btn.disabled = false; toast(e.message, true); });
  }

  function refreshBadge(n) {
    var b = document.getElementById('cp-new-orders');
    var set = function (v) { b.textContent = v; b.hidden = !v; };
    if (typeof n === 'number') { set(n); return; }
    if (state.orders) set(state.orders.filter(function (o) { return ['on-hold', 'processing', 'pending'].indexOf(o.status) > -1; }).length);
  }

  /* ---------- messages ---------- */
  function showMessages() {
    if (!state.messages) {
      main.innerHTML = '<p class="cp-loading">Učitavanje poruka…</p>';
      api('messages').then(function (l) { state.messages = l; showMessages(); }).catch(function (e) { main.innerHTML = '<p class="cp-empty">' + esc(e.message) + '</p>'; });
      return;
    }
    main.innerHTML = '<div class="cp-head"><h1>Poruke sa sajta</h1></div>' + (state.messages.length ? state.messages.map(function (m) {
      return '<article class="cp-msg"><b>' + esc(m.title) + '</b><time>' + esc(m.date) + '</time><p>' + esc(m.text) + '</p></article>';
    }).join('') : '<p class="cp-empty">Još nema poruka.</p>');
  }

  /* ---------- start ---------- */
  window.addEventListener('beforeunload', function (e) { if (ed && ed.dirty) { e.preventDefault(); e.returnValue = ''; } });
  api('bootstrap').then(function (b) {
    state.cats = b.categories || [];
    refreshBadge(b.newOrders || 0);
    route();
  }).catch(function (e) { main.innerHTML = '<p class="cp-empty">' + esc(e.message) + '</p>'; });
})();
