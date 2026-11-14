/* SampahRute — interaksi frontend (tanpa build step).
 * Semua fitur aktif lewat atribut data-* di Blade, jadi halaman yang
 * tidak memakai fitur tertentu tidak terpengaruh.
 */
document.addEventListener('DOMContentLoaded', () => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
  const escape = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const STATUS = {
    menunggu: { label: 'Belum diangkut', color: '#e2514b' },
    diproses: { label: 'Sedang diangkut', color: '#f1ad24' },
    selesai: { label: 'Sudah diangkut', color: '#159b56' },
    libur: { label: 'Tidak terjadwal hari ini', color: '#9aa8a0' },
  };

  /* ---------- Navigasi ---------- */
  $('[data-menu-toggle]')?.addEventListener('click', () => $('[data-nav]')?.classList.toggle('open'));
  $$('[data-sidebar-toggle]').forEach(btn => btn.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-open');
    $('#sidebar')?.classList.toggle('open');
  }));

  /* ---------- Toast ---------- */
  $$('.toast-item').forEach((toast, i) => {
    const close = () => { toast.classList.add('leaving'); setTimeout(() => toast.remove(), 250); };
    $('[data-toast-close]', toast)?.addEventListener('click', close);
    if (!toast.classList.contains('toast-error')) setTimeout(close, 6000 + i * 800);
  });

  /* ---------- Dropdown notifikasi ---------- */
  $$('[data-dropdown]').forEach(dd => {
    const btn = $('[data-dropdown-toggle]', dd);
    const menu = $('[data-dropdown-menu]', dd);
    const set = open => { menu.hidden = !open; btn.setAttribute('aria-expanded', String(open)); };
    btn.addEventListener('click', e => { e.stopPropagation(); set(menu.hidden); });
    document.addEventListener('click', e => { if (!dd.contains(e.target)) set(false); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') set(false); });
  });

  /* ---------- Konfirmasi sederhana ---------- */
  $$('form[data-confirm]').forEach(form => form.addEventListener('submit', e => {
    if (!window.confirm(form.dataset.confirm)) e.preventDefault();
  }));

  /* ---------- Upload foto dengan pratinjau ---------- */
  function resetDropzone(zone) {
    const img = $('img', zone);
    const text = $('[data-dropzone-text]', zone);
    if (img) { img.hidden = true; img.removeAttribute('src'); }
    if (text && text.dataset.original) text.textContent = text.dataset.original;
    zone.classList.remove('has-file');
  }
  $$('[data-dropzone]').forEach(zone => {
    const input = $('input[type=file]', zone);
    const img = $('img', zone);
    const text = $('[data-dropzone-text]', zone);
    if (text) text.dataset.original = text.textContent;
    const show = file => {
      if (!file) return resetDropzone(zone);
      if (file.size > 3 * 1024 * 1024) {
        text.textContent = `⚠ ${file.name} lebih dari 3 MB. Pilih foto lain.`;
        input.value = '';
        return;
      }
      if (file.type.startsWith('image/')) {
        if (img.dataset.url) URL.revokeObjectURL(img.dataset.url);
        img.dataset.url = URL.createObjectURL(file);
        img.src = img.dataset.url;
        img.hidden = false;
      }
      text.textContent = `✓ ${file.name} · klik untuk mengganti`;
      zone.classList.add('has-file');
    };
    input.addEventListener('change', () => show(input.files?.[0]));
    ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('drag'); }));
    zone.addEventListener('drop', e => {
      const file = e.dataTransfer?.files?.[0];
      if (!file) return;
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      show(file);
    });
  });

  /* ---------- Modal & drawer (elemen <dialog>) ---------- */
  const nowTime = () => new Date().toTimeString().slice(0, 5);

  function fillForm(dialog, data) {
    const form = $('form', dialog);
    if (form) form.reset();
    $$('[data-dropzone]', dialog).forEach(resetDropzone);
    Object.entries(data || {}).forEach(([key, value]) => {
      if (key.startsWith('_')) {
        $$(`[data-fill-text="${key}"]`, dialog).forEach(el => { el.textContent = value; });
        return;
      }
      $$(`[name="${key}"], [name="${key}[]"]`, dialog).forEach(field => {
        if (field.type === 'checkbox' || field.type === 'radio') {
          const values = Array.isArray(value) ? value.map(String) : [String(value)];
          field.checked = values.includes(field.value);
        } else {
          field.value = value;
        }
      });
    });
  }

  function openDialog(id, data) {
    const dialog = document.getElementById(id);
    if (!dialog || typeof dialog.showModal !== 'function') return;
    if (data !== undefined) fillForm(dialog, data);
    $$('[data-default-now]', dialog).forEach(i => { if (!i.value) i.value = nowTime(); });
    dialog.showModal();
    setTimeout(() => $$('[data-map-picker]', dialog).forEach(initPicker), 60);
  }

  $$('[data-modal-open]').forEach(btn => btn.addEventListener('click', () => {
    let data;
    if (btn.dataset.fill) { try { data = JSON.parse(btn.dataset.fill); } catch { data = {}; } }
    openDialog(btn.dataset.modalOpen, data);
  }));

  $$('dialog').forEach(dialog => {
    $$('[data-modal-close]', dialog).forEach(b => b.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
  });

  // Buka detail laporan langsung dari tautan seperti /admin/laporan#SR-001
  if (/^#SR-\d+$/.test(location.hash)) {
    const code = location.hash.slice(1);
    document.getElementById('row-' + code)?.classList.add('row-highlight');
    openDialog('detail-' + code);
  }

  /* ---------- Filter tabel / daftar ---------- */
  $$('[data-filter-scope]').forEach(scope => {
    const rows = $$('[data-row]', scope);
    const search = $('[data-filter-search]', scope);
    const statusButtons = $$('[data-filter-status]', scope);
    const fields = $$('[data-filter-field]', scope);
    const maxes = $$('[data-filter-max]', scope);
    const empty = $('[data-empty]', scope);
    let status = new URLSearchParams(location.search).get('status') || 'all';

    function apply() {
      const term = (search?.value || '').trim().toLowerCase();
      let visible = 0;
      rows.forEach(row => {
        let ok = status === 'all' || row.dataset.status === status;
        if (ok && term) ok = (row.dataset.search || row.textContent.toLowerCase()).includes(term);
        fields.forEach(f => { if (ok && f.value) ok = row.dataset[f.dataset.filterField] === f.value; });
        maxes.forEach(f => { if (ok && f.value !== '') ok = Number(row.dataset[f.dataset.filterMax]) <= Number(f.value); });
        row.hidden = !ok;
        if (ok) visible++;
      });
      statusButtons.forEach(b => b.classList.toggle('active', b.dataset.filterStatus === status));
      if (empty) empty.hidden = visible > 0 || rows.length === 0;
    }

    search?.addEventListener('input', apply);
    [...fields, ...maxes].forEach(f => f.addEventListener('change', apply));
    statusButtons.forEach(b => b.addEventListener('click', () => { status = b.dataset.filterStatus; apply(); }));
    apply();
  });

  /* ---------- Unduh CSV dari tabel (baris yang terlihat saja) ---------- */
  $$('[data-export-csv]').forEach(btn => btn.addEventListener('click', () => {
    const table = document.getElementById(btn.dataset.exportCsv);
    if (!table) return;
    const headers = $$('thead th', table);
    const keep = headers.map(th => !th.querySelector('.visually-hidden'));
    const clean = cell => '"' + cell.innerText.replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"';
    const lines = [headers.filter((_, i) => keep[i]).map(clean).join(',')];
    $$('tbody tr', table).filter(tr => !tr.hidden).forEach(tr => {
      lines.push([...tr.cells].filter((_, i) => keep[i]).map(clean).join(','));
    });
    const blob = new Blob(['\ufeff' + lines.join('\n')], { type: 'text/csv;charset=utf-8' });
    const link = Object.assign(document.createElement('a'), { href: URL.createObjectURL(blob), download: btn.dataset.filename || 'data.csv' });
    link.click();
    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
  }));

  /* ---------- Tab ---------- */
  function activateTab(name) {
    const tabs = $$('[data-tab]');
    if (!tabs.some(t => t.dataset.tab === name)) return;
    tabs.forEach(t => { t.classList.toggle('active', t.dataset.tab === name); t.setAttribute('aria-selected', String(t.dataset.tab === name)); });
    $$('[data-tab-panel]').forEach(p => { p.hidden = p.dataset.tabPanel !== name; });
  }
  $$('[data-tab]').forEach(t => t.addEventListener('click', e => { e.preventDefault(); history.replaceState(null, '', '#' + t.dataset.tab); activateTab(t.dataset.tab); }));
  if (location.hash) activateTab(location.hash.slice(1));

  $$('[data-day-tab]').forEach(btn => btn.addEventListener('click', () => {
    $$('[data-day-tab]').forEach(b => { b.classList.toggle('active', b === btn); b.setAttribute('aria-selected', String(b === btn)); });
    $$('[data-day-panel]').forEach(p => { p.hidden = p.dataset.dayPanel !== btn.dataset.dayTab; });
  }));

  /* ---------- Form laporan ---------- */
  $$('[data-counter]').forEach(area => {
    const out = $(`[data-counter-for="${area.id}"]`);
    const update = () => {
      if (!out) return;
      out.textContent = `${area.value.length}/${area.maxLength}`;
      out.classList.toggle('warn', area.value.length > 0 && area.value.length < (area.minLength || 0));
    };
    area.addEventListener('input', update);
    update();
  });

  const titleInput = $('#title');
  let lastSuggestion = titleInput?.value || '';
  $$('[data-title-suggest]').forEach(radio => radio.addEventListener('change', () => {
    if (!titleInput) return;
    if (titleInput.value.trim() === '' || titleInput.value === lastSuggestion) {
      titleInput.value = radio.dataset.titleSuggest;
      lastSuggestion = radio.dataset.titleSuggest;
    }
  }));

  $$('form[data-validate]').forEach(form => form.addEventListener('submit', e => {
    if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); }
  }));

  /* ---------- Login: isi akun demo ---------- */
  $$('[data-demo-user]').forEach(btn => btn.addEventListener('click', () => {
    const user = $('#email');
    const pass = $('#password');
    if (!user || !pass) return;
    user.value = btn.dataset.demoUser;
    pass.value = btn.dataset.demoPass;
    btn.closest('.login-card')?.querySelector('form')?.requestSubmit();
  }));

  /* ---------- Peta (Leaflet) ---------- */
  if (typeof L === 'undefined') {
    $$('.leaflet-map').forEach(el => { el.classList.add('map-offline'); el.textContent = 'Peta butuh koneksi internet untuk dimuat.'; });
    return;
  }

  const CENTER = [-7.5655, 110.8216];
  const tiles = () => L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 });
  const dotIcon = (color, label = '', size = 22) => L.divIcon({
    className: '',
    html: `<div class="map-marker" style="background:${color};width:${size}px;height:${size}px">${label}</div>`,
    iconSize: [size, size], iconAnchor: [size / 2, size / 2], popupAnchor: [0, -size / 2],
  });
  const popupHtml = (p, reportUrl) => {
    const st = STATUS[p.status] || STATUS.menunggu;
    let html = `<div class="map-popup"><strong>${escape(p.name)}</strong><span>${escape(p.type || '')} · ${escape(p.district)}</span>`
      + `<em style="color:${st.color}">● ${st.label}</em>`
      + `<dl><dt>Jadwal</dt><dd>${escape(p.days || '')}, ${escape(p.window || '')}</dd>`
      + `<dt>Berikutnya</dt><dd>${escape(p.date)}</dd>`
      + (p.fill !== undefined ? `<dt>Perkiraan isi</dt><dd>${escape(p.fill)}%</dd>` : '')
      + (p.officer ? `<dt>Petugas</dt><dd>${escape(p.officer)}</dd>` : '') + '</dl>';
    if (reportUrl) html += `<a class="popup-link" href="${escape(reportUrl)}?lokasi=${encodeURIComponent(p.id)}">Laporkan masalah di sini</a>`;
    return html + '</div>';
  };

  $$('.leaflet-map').forEach(element => {
    if (element.hasAttribute('data-map-picker')) return; // dibuat saat modal dibuka
    let points;
    try { points = JSON.parse(element.dataset.points || '[]'); } catch { return; }
    const mode = element.dataset.mode;
    const map = L.map(element, { scrollWheelZoom: !['preview', 'single'].includes(mode) }).setView(CENTER, mode === 'preview' ? 12 : 13);
    tiles().addTo(map);
    const markers = new Map();

    if (mode === 'route') {
      let route = [];
      try { route = JSON.parse(element.dataset.route || '[]'); } catch {}
      const line = route.map(r => [r.lat, r.lng]);
      if (line.length > 1) L.polyline(line, { color: '#0b7541', weight: 4, opacity: .7, dashArray: '8 8' }).addTo(map);
      route.forEach(r => {
        const st = STATUS[r.status] || STATUS.menunggu;
        L.marker([r.lat, r.lng], { icon: dotIcon(st.color, r.n, 28) }).addTo(map)
          .bindPopup(`<div class="map-popup"><strong>${r.n}. ${escape(r.name)}</strong><span>${escape(r.window)}</span><em style="color:${st.color}">● ${st.label}</em></div>`);
      });
      if (line.length) map.fitBounds(line, { padding: [30, 30] });
      setTimeout(() => map.invalidateSize(), 250);
      return;
    }

    const reportUrl = element.dataset.reportUrl || '';
    points.forEach(p => {
      const st = STATUS[p.status] || STATUS.menunggu;
      const marker = L.marker([Number(p.lat), Number(p.lng)], { icon: dotIcon(st.color, '', mode === 'single' ? 28 : 22), title: p.name }).addTo(map);
      marker.bindPopup(popupHtml(p, mode === 'full' ? reportUrl : ''));
      markers.set(p.id, { marker, p });
    });

    if (mode === 'single' && points[0]) {
      map.setView([points[0].lat, points[0].lng], 15);
    } else if (mode !== 'preview' && points.length) {
      map.fitBounds(points.map(p => [p.lat, p.lng]), { padding: [30, 30] });
    }

    if (mode === 'full') {
      const search = $('#location-search');
      const list = $$('.map-list-item');
      const count = $('[data-visible-count]');
      const empty = $('.map-list [data-empty]');
      let status = 'all';
      const match = (st, text) => (status === 'all' || st === status) && text.toLowerCase().includes((search?.value || '').trim().toLowerCase());
      function refresh() {
        let n = 0;
        markers.forEach(({ marker, p }) => {
          const visible = match(p.status, `${p.name} ${p.district}`);
          if (visible && !map.hasLayer(marker)) marker.addTo(map);
          if (!visible && map.hasLayer(marker)) map.removeLayer(marker);
        });
        list.forEach(el => {
          el.hidden = !match(el.dataset.pointStatus, `${el.dataset.pointName} ${el.dataset.pointDistrict}`);
          if (!el.hidden) n++;
        });
        if (count) count.textContent = n;
        if (empty) empty.hidden = n > 0;
      }
      const focus = id => {
        const entry = markers.get(id);
        if (!entry) return;
        list.forEach(el => el.classList.toggle('active', el.dataset.pointId === id));
        map.setView(entry.marker.getLatLng(), 16);
        entry.marker.openPopup();
      };
      search?.addEventListener('input', refresh);
      $$('[data-filter]').forEach(b => b.addEventListener('click', () => {
        status = b.dataset.filter;
        $$('[data-filter]').forEach(x => x.classList.toggle('active', x === b));
        refresh();
      }));
      list.forEach(el => el.addEventListener('click', () => {
        focus(el.dataset.pointId);
        if (window.innerWidth < 760) element.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }));
      if (/^#LOK-\d+$/.test(location.hash)) setTimeout(() => focus(location.hash.slice(1)), 300);
    }

    if (mode === 'select') {
      const select = document.getElementById(element.dataset.selectInput);
      const highlight = id => {
        markers.forEach(({ marker, p }) => {
          const st = STATUS[p.status] || STATUS.menunggu;
          marker.setIcon(dotIcon(st.color, p.id === id ? '✓' : '', p.id === id ? 30 : 20));
          marker.setZIndexOffset(p.id === id ? 1000 : 0);
        });
        const entry = markers.get(id);
        if (entry) { map.setView(entry.marker.getLatLng(), 15); entry.marker.openPopup(); }
      };
      markers.forEach(({ marker, p }) => marker.on('click', () => {
        if (!select) return;
        select.value = p.id;
        highlight(p.id);
      }));
      select?.addEventListener('change', () => highlight(select.value));
      if (select?.value) setTimeout(() => highlight(select.value), 300);
    }

    setTimeout(() => map.invalidateSize(), 250);
  });

  /* Peta pemilih titik di form lokasi (admin) */
  function initPicker(el) {
    const latInput = document.getElementById(el.dataset.latInput);
    const lngInput = document.getElementById(el.dataset.lngInput);
    if (!el._map) {
      el._map = L.map(el).setView(CENTER, 13);
      tiles().addTo(el._map);
      el._map.on('click', e => place(e.latlng));
    }
    const map = el._map;
    function write(latlng) {
      latInput.value = latlng.lat.toFixed(5);
      lngInput.value = latlng.lng.toFixed(5);
    }
    function place(latlng) {
      if (!el._marker) {
        el._marker = L.marker(latlng, { draggable: true, icon: dotIcon('#0b7541', '', 26) }).addTo(map);
        el._marker.on('dragend', () => write(el._marker.getLatLng()));
      } else {
        el._marker.setLatLng(latlng);
      }
      write(latlng);
    }
    map.invalidateSize();
    if (latInput.value && lngInput.value) {
      const ll = L.latLng(Number(latInput.value), Number(lngInput.value));
      place(ll);
      map.setView(ll, 15);
    } else {
      if (el._marker) { map.removeLayer(el._marker); el._marker = null; }
      map.setView(CENTER, 13);
    }
  }
});
