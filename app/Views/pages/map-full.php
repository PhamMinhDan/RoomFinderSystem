<?php

use Core\SessionManager;
SessionManager::start();

$mapboxToken = $_ENV['MAPBOX_TOKEN'] ?? '';

$toLat   = isset($_GET['toLat'])   ? (float)$_GET['toLat']   : null;
$toLng   = isset($_GET['toLng'])   ? (float)$_GET['toLng']   : null;
$toName  = isset($_GET['toName'])  ? trim($_GET['toName'])   : 'Phòng trọ';
$fromLat = isset($_GET['fromLat']) ? (float)$_GET['fromLat'] : null;
$fromLng = isset($_GET['fromLng']) ? (float)$_GET['fromLng'] : null;
$fromName = isset($_GET['fromName']) ? trim($_GET['fromName']) : '';

if (!$fromName) {
    // Không dùng tên người dùng – để trống để user tự nhập hoặc dùng GPS
    $fromName = '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bản đồ chỉ đường – RoomFinder</title>
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css">
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.1/mapbox-gl-geocoder.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --primary:      #2b3cf7;
    --primary-dark: #1a2ad4;
    --accent:       #f05136;
    --gray-50:      #f9fafb;
    --gray-100:     #f3f4f6;
    --gray-200:     #e5e7eb;
    --gray-400:     #9ca3af;
    --gray-500:     #6b7280;
    --gray-700:     #374151;
    --gray-800:     #1f2937;
    --gray-900:     #111827;
    --white:        #fff;
    --shadow:       0 4px 20px rgba(0,0,0,.12);
  }

  html, body { height: 100%; font-family: 'Segoe UI', system-ui, sans-serif; }

  #map-full { position: fixed; inset: 0; }

  /* ── Sidebar ── */
  #sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    width: 360px;
    background: var(--white);
    box-shadow: var(--shadow);
    z-index: 10;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .sb-header {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--gray-200);
    background: var(--primary);
    color: #fff;
    flex-shrink: 0;
  }

  .sb-header-top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
  }

  .sb-back {
    background: rgba(255,255,255,.2);
    border: none; color: #fff;
    width: 34px; height: 34px;
    border-radius: 50%;
    cursor: pointer; font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background .2s;
  }
  .sb-back:hover { background: rgba(255,255,255,.35); }

  .sb-title { font-size: 15px; font-weight: 800; }
  .sb-sub   { font-size: 11.5px; opacity: .8; margin-top: 2px; }

  .sb-body { padding: 18px; overflow-y: auto; flex: 1; }

  /* Geocoder input groups */
  .route-inputs { display: flex; flex-direction: column; gap: 0; margin-bottom: 16px; }

  .route-point {
    display: flex;
    align-items: stretch;
    gap: 0;
    position: relative;
  }

  .route-dot-wrap {
    width: 36px;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 14px;
    flex-shrink: 0;
  }

  .route-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 2px solid var(--primary);
    background: var(--white);
  }
  .route-dot.dest { background: var(--accent); border-color: var(--accent); }

  .route-line-seg {
    width: 2px;
    flex: 1;
    background: repeating-linear-gradient(
      to bottom, var(--gray-400) 0 4px, transparent 4px 8px
    );
    margin: 3px 0;
    min-height: 18px;
  }

  .route-field {
    flex: 1;
    margin-bottom: 6px;
  }

  .route-field label {
    font-size: 10px;
    font-weight: 700;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: .5px;
    display: block;
    margin-bottom: 4px;
  }

  .route-field input {
    width: 100%;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 13px;
    font-family: inherit;
    color: var(--gray-800);
    outline: none;
    transition: border-color .2s;
    background: var(--gray-50);
  }
  .route-field input:focus { border-color: var(--primary); background: #fff; }

  .btn-route-go {
    width: 100%;
    padding: 12px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .2s;
    margin-bottom: 16px;
  }
  .btn-route-go:hover { background: var(--primary-dark); }
  .btn-route-go:disabled { opacity: .55; cursor: not-allowed; }

  /* Travel modes */
  .mode-bar {
    display: flex; gap: 6px; margin-bottom: 18px;
    border-bottom: 1px solid var(--gray-200);
    padding-bottom: 14px;
  }
  .mode-btn {
    flex: 1; padding: 8px 4px;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px; font-size: 11px; font-weight: 600;
    color: var(--gray-500); cursor: pointer; font-family: inherit;
    background: var(--white); transition: all .18s;
    display: flex; flex-direction: column; align-items: center; gap: 3px;
  }
  .mode-btn i { font-size: 15px; }
  .mode-btn.active {
    background: var(--primary); color: #fff; border-color: var(--primary);
  }
  .mode-btn:hover:not(.active) { border-color: var(--primary); color: var(--primary); }

  /* Route result card */
  #routeResult {
    background: var(--gray-50);
    border-radius: 12px;
    padding: 16px;
    border: 1px solid var(--gray-200);
    display: none;
  }
  .result-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
  .result-icon { font-size: 20px; color: var(--primary); width: 28px; text-align: center; }
  .result-label { font-size: 11px; color: var(--gray-500); }
  .result-value { font-size: 16px; font-weight: 800; color: var(--gray-800); }

  .steps-list { margin-top: 12px; border-top: 1px solid var(--gray-200); padding-top: 12px; }
  .step-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 7px 0; border-bottom: 1px solid var(--gray-100); font-size: 12.5px;
    color: var(--gray-700);
  }
  .step-item:last-child { border-bottom: none; }
  .step-item i { color: var(--gray-400); margin-top: 2px; font-size: 11px; width: 14px; }

  /* Toast */
  #toast {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
    background: var(--gray-900); color: #fff;
    padding: 10px 20px; border-radius: 24px;
    font-size: 13px; z-index: 999;
    display: none; gap: 8px; align-items: center;
  }

  /* Geo button floating */
  #btnGeo {
    position: fixed; bottom: 100px; right: 16px; z-index: 10;
    background: #fff; border: none;
    width: 40px; height: 40px; border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,.18);
    cursor: pointer; font-size: 16px; color: var(--gray-700);
    display: flex; align-items: center; justify-content: center;
    transition: color .2s;
  }
  #btnGeo:hover { color: var(--primary); }

  /* Loading overlay */
  #loadingOverlay {
    position: fixed; inset: 0; background: rgba(255,255,255,.7);
    z-index: 20; display: none;
    align-items: center; justify-content: center;
    font-size: 14px; font-weight: 600; color: var(--gray-700);
    gap: 10px;
  }

  /* Responsive: mobile sidebar becomes bottom sheet */
  @media (max-width: 640px) {
    #sidebar {
      width: 100%; top: auto; bottom: 0; height: 50vh;
      border-radius: 18px 18px 0 0;
      box-shadow: 0 -4px 24px rgba(0,0,0,.15);
    }
    #map-full { bottom: 50vh; }
    #btnGeo { bottom: calc(50vh + 16px); }
  }
</style>
</head>
<body>

<div id="loadingOverlay"><i class="fas fa-spinner fa-spin"></i> Đang tải bản đồ…</div>

<!-- Bản đồ -->
<div id="map-full"></div>

<!-- Nút lấy vị trí hiện tại -->
<button id="btnGeo" title="Vị trí của tôi" onclick="useCurrentLocation()">
  <i class="fas fa-crosshairs"></i>
</button>

<!-- Sidebar chỉ đường -->
<div id="sidebar">
  <div class="sb-header">
    <div class="sb-header-top">
      <button class="sb-back" onclick="history.back()" title="Quay lại">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div>
        <div class="sb-title"><i class="fas fa-route"></i> Chỉ đường</div>
        <div class="sb-sub">Nhập điểm đi &amp; đến, chọn phương tiện</div>
      </div>
    </div>
  </div>

  <div class="sb-body">

    <!-- From / To -->
    <div class="route-inputs">
      <!-- Điểm đi -->
      <div class="route-point">
        <div class="route-dot-wrap">
          <div class="route-dot"></div>
          <div class="route-line-seg"></div>
        </div>
        <div class="route-field">
          <label>Điểm đi</label>
          <input type="text" id="inputFrom"
                 placeholder="Nhập địa chỉ hoặc chọn vị trí…"
                 value="<?= htmlspecialchars($fromName) ?>">
        </div>
      </div>
      <!-- Điểm đến -->
      <div class="route-point">
        <div class="route-dot-wrap">
          <div class="route-dot dest"></div>
        </div>
        <div class="route-field">
          <label>Điểm đến</label>
          <input type="text" id="inputTo"
                 placeholder="Nhập địa chỉ điểm đến…"
                 value="<?= htmlspecialchars($toName) ?>">
        </div>
      </div>
    </div>

    <!-- Phương tiện -->
    <div class="mode-bar">
      <button class="mode-btn active" data-mode="driving"   onclick="setMode(this)">
        <i class="fas fa-car"></i> Ô tô
      </button>
      <button class="mode-btn" data-mode="cycling"   onclick="setMode(this)">
        <i class="fas fa-bicycle"></i> Xe đạp
      </button>
      <button class="mode-btn" data-mode="walking"   onclick="setMode(this)">
        <i class="fas fa-person-walking"></i> Đi bộ
      </button>
    </div>

    <!-- Nút tìm đường -->
    <button class="btn-route-go" id="btnGo" onclick="fetchRoute()">
      <i class="fas fa-map-signs"></i> Tìm đường
    </button>

    <!-- Kết quả đường đi -->
    <div id="routeResult">
      <div class="result-row">
        <div class="result-icon"><i class="fas fa-road"></i></div>
        <div><div class="result-label">Khoảng cách</div><div class="result-value" id="resDistance">–</div></div>
      </div>
      <div class="result-row">
        <div class="result-icon"><i class="fas fa-clock"></i></div>
        <div><div class="result-label">Thời gian ước tính</div><div class="result-value" id="resDuration">–</div></div>
      </div>
      <div class="steps-list" id="stepsList"></div>
    </div>

  </div><!-- /sb-body -->
</div><!-- /sidebar -->

<div id="toast"></div>

<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
<script>
// ── Config từ PHP ────────────────────────────────────────────────────────
const MAPBOX_TOKEN = <?= json_encode($mapboxToken) ?>;
const INIT_TO = {
  lat: <?= $toLat   !== null ? $toLat   : 'null' ?>,
  lng: <?= $toLng   !== null ? $toLng   : 'null' ?>,
  name: <?= json_encode($toName) ?>,
};
const INIT_FROM = {
  lat: <?= $fromLat !== null ? $fromLat : 'null' ?>,
  lng: <?= $fromLng !== null ? $fromLng : 'null' ?>,
};

// ── State ────────────────────────────────────────────────────────────────
let map, fromCoords = null, toCoords = null;
let fromMarker = null, toMarker = null;
let travelMode = 'driving';

// ── Init ─────────────────────────────────────────────────────────────────
mapboxgl.accessToken = MAPBOX_TOKEN;

document.getElementById('loadingOverlay').style.display = 'flex';

map = new mapboxgl.Map({
  container: 'map-full',
  style:     'mapbox://styles/mapbox/streets-v12',
  center:    (INIT_TO.lat && INIT_TO.lng) ? [INIT_TO.lng, INIT_TO.lat] : [105.85, 21.02],
  zoom:      INIT_TO.lat ? 14 : 12,
  language:  'vi',
});

map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');
map.addControl(new mapboxgl.ScaleControl({ maxWidth: 100, unit: 'metric' }), 'bottom-right');

map.on('load', () => {
  document.getElementById('loadingOverlay').style.display = 'none';

  // Đặt marker phòng trọ (to)
  if (INIT_TO.lat && INIT_TO.lng) {
    toCoords = [INIT_TO.lng, INIT_TO.lat];
    _placeToMarker(toCoords);
  }

  // Đặt marker điểm đi (from) nếu có từ URL
  if (INIT_FROM.lat && INIT_FROM.lng) {
    fromCoords = [INIT_FROM.lng, INIT_FROM.lat];
    _placeFromMarker(fromCoords);

    // Fit bounds ngay
    if (toCoords) {
      const b = new mapboxgl.LngLatBounds(fromCoords, toCoords);
      map.fitBounds(b, { padding: 80, maxZoom: 15 });
    }

    // Tự động tìm đường
    fetchRoute();
  }
});

// Click trên bản đồ → đặt điểm đi
map.on('click', (e) => {
  fromCoords = [e.lngLat.lng, e.lngLat.lat];
  _placeFromMarker(fromCoords);
  document.getElementById('inputFrom').value = `${e.lngLat.lat.toFixed(5)}, ${e.lngLat.lng.toFixed(5)}`;
});

// ── Markers ───────────────────────────────────────────────────────────────
function _placeFromMarker(coords) {
  if (fromMarker) fromMarker.setLngLat(coords);
  else {
    const el = document.createElement('div');
    el.className = 'map-user-marker-full';
    el.style.cssText = `
      width:36px;height:36px;border-radius:50%;
      background:#2b3cf7;border:3px solid #fff;
      display:flex;align-items:center;justify-content:center;
      box-shadow:0 3px 12px rgba(0,0,0,.25);cursor:pointer;`;
    el.innerHTML = '<i class="fas fa-user" style="color:#fff;font-size:14px"></i>';
    fromMarker = new mapboxgl.Marker(el).setLngLat(coords).addTo(map);
  }
}

function _placeToMarker(coords) {
  if (toMarker) toMarker.setLngLat(coords);
  else {
    const el = document.createElement('div');
    el.style.cssText = `
      width:42px;height:42px;
      background:#f05136;border-radius:50% 50% 50% 0;
      transform:rotate(-45deg);border:3px solid #fff;
      display:flex;align-items:center;justify-content:center;
      box-shadow:0 3px 14px rgba(0,0,0,.28);cursor:pointer;`;
    el.innerHTML = '<i class="fas fa-home" style="color:#fff;font-size:16px;transform:rotate(45deg)"></i>';
    toMarker = new mapboxgl.Marker(el)
      .setLngLat(coords)
      .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(
        `<div style="font-weight:700;font-size:13px">${INIT_TO.name}</div>`
      ))
      .addTo(map);
    toMarker.getPopup(); // init popup
  }
}

// ── Travel mode ───────────────────────────────────────────────────────────
function setMode(btn) {
  document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  travelMode = btn.dataset.mode;
  if (fromCoords && toCoords) fetchRoute();
}

// ── Geolocation ───────────────────────────────────────────────────────────
function useCurrentLocation() {
  if (!navigator.geolocation) { _toast('Trình duyệt không hỗ trợ GPS'); return; }
  _toast('Đang xác định vị trí…');
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      fromCoords = [pos.coords.longitude, pos.coords.latitude];
      _placeFromMarker(fromCoords);
      document.getElementById('inputFrom').value = `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
      map.flyTo({ center: fromCoords, zoom: 14 });
      if (toCoords) fetchRoute();
    },
    () => _toast('Không lấy được vị trí GPS', 'error')
  );
}

// ── Geocode input (Mapbox geocoding API) ──────────────────────────────────
async function geocodeInput(query) {
  if (!query) return null;
  // Nếu là lat,lng trực tiếp
  const latLng = query.match(/^(-?\d+\.?\d*),\s*(-?\d+\.?\d*)$/);
  if (latLng) return [parseFloat(latLng[2]), parseFloat(latLng[1])];

  const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${MAPBOX_TOKEN}&language=vi&country=vn&limit=1`;
  const res  = await fetch(url);
  const data = await res.json();
  return data.features?.[0]?.center || null;
}

// ── Fetch route ───────────────────────────────────────────────────────────
async function fetchRoute() {
  const btn = document.getElementById('btnGo');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tìm…';

  try {
    // Geocode điểm đi nếu chưa có toạ độ chính xác
    if (!fromCoords) {
      const q = document.getElementById('inputFrom').value.trim();
      if (!q) { _toast('Vui lòng nhập điểm đi', 'warn'); return; }
      fromCoords = await geocodeInput(q);
      if (!fromCoords) { _toast('Không tìm thấy địa chỉ điểm đi', 'error'); return; }
      _placeFromMarker(fromCoords);
    }

    // Geocode điểm đến nếu chưa có
    if (!toCoords) {
      const q = document.getElementById('inputTo').value.trim();
      if (!q) { _toast('Vui lòng nhập điểm đến', 'warn'); return; }
      toCoords = await geocodeInput(q);
      if (!toCoords) { _toast('Không tìm thấy địa chỉ điểm đến', 'error'); return; }
      _placeToMarker(toCoords);
    }

    // Fit bounds
    const bounds = new mapboxgl.LngLatBounds(fromCoords, toCoords);
    map.fitBounds(bounds, { padding: 80, maxZoom: 15 });

    // Gọi Directions API
    const url =
      `https://api.mapbox.com/directions/v5/mapbox/${travelMode}/` +
      `${fromCoords[0]},${fromCoords[1]};${toCoords[0]},${toCoords[1]}` +
      `?steps=true&geometries=geojson&overview=full&language=vi&access_token=${MAPBOX_TOKEN}`;
    const res  = await fetch(url);
    const data = await res.json();
    const route = data.routes?.[0];
    if (!route) { _toast('Không tìm được đường đi', 'error'); return; }

    // Vẽ route
    _drawRouteLine(route.geometry);

    // Kết quả
    const km   = (route.distance / 1000).toFixed(1);
    const mins = Math.round(route.duration / 60);
    const dur  = mins >= 60
      ? `${Math.floor(mins/60)} giờ ${mins%60} phút`
      : `${mins} phút`;

    document.getElementById('resDistance').textContent = `${km} km`;
    document.getElementById('resDuration').textContent = dur;

    // Các bước
    const steps   = route.legs?.[0]?.steps ?? [];
    const stepHtml = steps.map(s => `
      <div class="step-item">
        <i class="fas fa-arrow-right"></i>
        <span>${s.maneuver?.instruction ?? s.name ?? ''}</span>
      </div>`).join('');
    document.getElementById('stepsList').innerHTML = stepHtml;
    document.getElementById('routeResult').style.display = 'block';

  } catch (e) {
    console.error(e);
    _toast('Có lỗi xảy ra khi tìm đường', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-map-signs"></i> Tìm đường';
  }
}

function _drawRouteLine(geometry) {
  if (map.getSource('route-full')) {
    map.getSource('route-full').setData(geometry);
  } else {
    map.addSource('route-full', { type: 'geojson', data: geometry });
    // Casing trắng
    map.addLayer({
      id: 'route-full-casing', type: 'line', source: 'route-full',
      layout: { 'line-join': 'round', 'line-cap': 'round' },
      paint:  { 'line-color': '#fff', 'line-width': 8, 'line-opacity': .7 },
    });
    // Đường màu primary
    map.addLayer({
      id: 'route-full-line', type: 'line', source: 'route-full',
      layout: { 'line-join': 'round', 'line-cap': 'round' },
      paint:  { 'line-color': '#2b3cf7', 'line-width': 5, 'line-opacity': .9 },
    }, 'route-full-casing');
  }
}

// ── Toast ─────────────────────────────────────────────────────────────────
function _toast(msg, type = 'info') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.display = 'flex';
  t.style.background = type === 'error' ? '#dc2626' : type === 'warn' ? '#f59e0b' : '#1f2937';
  clearTimeout(t._to);
  t._to = setTimeout(() => { t.style.display = 'none'; }, 3200);
}
</script>
</body>
</html>