document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => document.querySelector('[data-nav]')?.classList.toggle('open'));
  document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => document.querySelector('#sidebar')?.classList.toggle('open'));
  const input = document.getElementById('photo');
  input?.addEventListener('change', () => {
    const preview = document.getElementById('photo-preview');
    const file = input.files?.[0];
    if (!preview) return;
    if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
    if (file && file.type.startsWith('image/')) {
      const src = URL.createObjectURL(file); preview.dataset.objectUrl = src; preview.src = src; preview.hidden = false;
    } else preview.hidden = true;
  });

  // Leaflet and map tiles require a working internet connection.
  if (typeof L === 'undefined') return;
  document.querySelectorAll('.leaflet-map').forEach(element => {
    let points;
    try { points = JSON.parse(element.dataset.points || '[]'); } catch { return; }
    const mode = element.dataset.mode;
    const map = L.map(element, {scrollWheelZoom:mode !== 'preview'}).setView([-7.5755, 110.8243], mode === 'preview' ? 12 : 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'&copy; OpenStreetMap contributors', maxZoom:19}).addTo(map);
    const markers = new Map();
    const escape = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    points.forEach(p => {
      const color = {selesai:'#159b56',diproses:'#f1ad24',menunggu:'#e2514b'}[p.status] || '#16894c';
      const icon = L.divIcon({className:'',html:`<div style="border:3px solid white;background:${color};width:23px;height:23px;border-radius:50%;box-shadow:0 2px 9px #0005"></div>`,iconSize:[23,23],iconAnchor:[11,11]});
      const marker = L.marker([Number(p.lat),Number(p.lng)],{icon}).addTo(map);
      marker.bindPopup(`<strong>${escape(p.name)}</strong><br>${escape(p.district)}<br>Status: ${escape(p.status)}<br>Jadwal: ${escape(p.date)}`);
      markers.set(p.name,{marker,p});
    });
    if (mode === 'full') {
      const search = document.getElementById('location-search');
      const list = [...document.querySelectorAll('.map-list-item')];
      let status = 'all';
      function refresh() {
        const term = (search?.value || '').toLowerCase();
        markers.forEach(({marker,p}) => {
          const visible = (status==='all'||p.status===status) && (`${p.name} ${p.district}`.toLowerCase().includes(term));
          if (visible && !map.hasLayer(marker)) marker.addTo(map);
          if (!visible && map.hasLayer(marker)) map.removeLayer(marker);
        });
        list.forEach(el => {el.hidden = !((status==='all'||el.dataset.pointStatus===status)&&(`${el.dataset.pointName} ${el.dataset.pointDistrict}`.toLowerCase().includes(term)));});
      }
      search?.addEventListener('input', refresh);
      document.querySelectorAll('[data-filter]').forEach(b=>b.addEventListener('click',()=>{
        status=b.dataset.filter;document.querySelectorAll('[data-filter]').forEach(x=>x.classList.toggle('active',x===b));refresh();
      }));
      list.forEach(el=>el.addEventListener('click',()=>{const entry=markers.get(el.dataset.pointName);if(entry){map.setView(entry.marker.getLatLng(),15);entry.marker.openPopup();}}));
    }
    setTimeout(()=>map.invalidateSize(),250);
  });
});
