/* ════════════════════════════════════════════════════════════════════════
   room-detail.js  –  Chi tiết phòng
   Tích hợp FavModule để lưu/huỷ lưu yêu thích
   ════════════════════════════════════════════════════════════════════════ */

/* ── Khởi động ─────────────────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", async () => {
  // Init FavModule
  await FavModule.init();

  const favBtn = document.getElementById("fav-btn");
  const favBtnCard = document.getElementById("fav-btn-sidebar");
  const roomId = window.ROOM_DETAIL_ID;

  if (roomId) {
    if (favBtn) _applyFavDetail(favBtn, FavModule.isFaved(roomId));
    if (favBtnCard) _applyFavDetail(favBtnCard, FavModule.isFaved(roomId));
  }
});

function _applyFavDetail(btn, faved) {
  if (!btn) return;

  const icon = btn.querySelector("i");
  const span = btn.querySelector("span");

  if (faved) {
    btn.classList.add("faved");
    if (icon) {
      icon.className = "fas fa-heart";
      icon.style.color = "var(--accent)";
    }
    if (span) span.textContent = "Đã lưu";
  } else {
    btn.classList.remove("faved");
    if (icon) {
      icon.className = "far fa-heart";
      icon.style.color = "";
    }

    if (span) {
      span.textContent = window.IS_LOGGED_IN
        ? "Lưu yêu thích"
        : "Đăng nhập để lưu";
    }
  }
  btn.dataset.faved = faved ? "1" : "0";
}

/* ── Toggle yêu thích (gọi từ HTML) ──────────────────────────────────── */
async function toggleFav(btn) {
  const roomId = window.ROOM_DETAIL_ID;
  if (!roomId) return;

  // Toggle tất cả button fav trên trang này
  const favBtn = document.getElementById("fav-btn");
  const favBtnCard = document.getElementById("fav-btn-sidebar");

  // Dùng FavModule toggle
  await FavModule.toggle(roomId, btn);

  // Sync icon fav-icon-card (nút sidebar text "Lưu yêu thích")
  const nowFaved = FavModule.isFaved(roomId);
  if (favBtn) _applyFavDetail(favBtn, nowFaved);
  if (favBtnCard) _applyFavDetail(favBtnCard, nowFaved);

  // Cập nhật text nút sidebar
  const favSidebar = document.getElementById("fav-btn-sidebar");
  if (favSidebar) {
    const icon = favSidebar.querySelector("i");
    const span = favSidebar.querySelector("span");
    if (icon) icon.className = nowFaved ? "fas fa-heart" : "far fa-heart";
    if (span) span.textContent = nowFaved ? "Đã lưu" : "Lưu yêu thích";
  }
}

/* ── Đổi ảnh gallery ────────────────────────────────────────────────── */
function changeImg(el) {
  document.getElementById("main-img").src = el.src;
  document
    .querySelectorAll(".thumb")
    .forEach((t) => t.classList.remove("active"));
  el.classList.add("active");

  // Lightbox effect
  document.getElementById("main-img").style.opacity = "0";
  setTimeout(() => {
    document.getElementById("main-img").style.opacity = "1";
  }, 50);
}

/* ── Hiện / ẩn số điện thoại ─────────────────────────────────────────── */
function showPhone() {
  const reveal = document.getElementById("phone-reveal");
  const btn = document.getElementById("phone-btn");
  const isHidden =
    reveal.style.display === "none" || reveal.style.display === "";
  reveal.style.display = isHidden ? "block" : "none";
  btn.style.display = isHidden ? "none" : "flex";
}

/* ── Chuyển tab ──────────────────────────────────────────────────────── */
function showTab(name, el) {
  ["desc", "map", "reviews", "similar"].forEach((t) => {
    document.getElementById("tab-" + t).style.display =
      t === name ? "block" : "none";
  });
  document
    .querySelectorAll(".tab-btn")
    .forEach((b) => b.classList.remove("active"));
  el.classList.add("active");

  if (name === "map") initMapbox();
  if (name === "similar") loadSimilar();
}

/* ── Rating stars ─────────────────────────────────────────────────────── */
let rating = 0;
function setRating(n) {
  rating = n;
  document.querySelectorAll("#star-row .star-btn").forEach((s, i) => {
    s.style.color = i < n ? "var(--gold)" : "var(--gray-300)";
  });
}

/* ── Load phòng tương tự ─────────────────────────────────────────────── */
async function loadSimilar() {
  const grid = document.getElementById("similarGrid");
  const roomId = grid.dataset.roomId;
  const city = grid.dataset.roomCity;

  if (!grid || grid.dataset.loaded === "1") return;

  try {
    const res = await fetch(
      `/api/rooms/public?city=${encodeURIComponent(city)}&limit=4`,
    );
    const data = await res.json();
    const rooms = (data.data || [])
      .filter((r) => r.room_id != roomId)
      .slice(0, 3);

    if (rooms.length === 0) {
      grid.innerHTML = `<p style="color:#9ca3af;font-size:.875rem;grid-column:1/-1;">Không có phòng tương tự.</p>`;
      return;
    }

    grid.innerHTML = rooms
      .map(
        (r) => `
      <div class="sim-card" onclick="window.location.href='/room/${r.room_id}'">
        <img src="${r.primary_image || "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=160&fit=crop"}"
             alt="${r.title}"
             onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=160&fit=crop'">
        <div class="sim-body">
          <div class="sim-title">${r.title}</div>
          <div class="sim-price">${Number(r.price_per_month).toLocaleString("vi-VN")} đ/tháng</div>
          <div class="sim-loc"><i class="fas fa-location-dot" style="color:var(--primary);font-size:10px"></i> ${r.district_name || r.city_name || ""}</div>
        </div>
      </div>`,
      )
      .join("");

    grid.dataset.loaded = "1";
  } catch (e) {
    console.error("Lỗi tải phòng tương tự:", e);
  }
}

/* ── Share ────────────────────────────────────────────────────────────── */
function shareRoom() {
  if (navigator.share) {
    navigator.share({ title: document.title, url: window.location.href });
  } else {
    navigator.clipboard.writeText(window.location.href);
    alert("Đã sao chép link vào clipboard");
  }
}

/* ── Report ───────────────────────────────────────────────────────────── */
function reportRoom(roomId) {
  const reason = prompt("Lý do báo cáo:");
  if (!reason) return;
  fetch("/api/rooms/" + roomId + "/report", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ reason }),
  }).then(() => alert("Đã gửi báo cáo. Cảm ơn bạn!"));
}

function submitReview(roomId) {
  alert("Chức năng đánh giá sẽ được cập nhật sớm!");
}

/* ════════════════════════════════════════════════════════════════════════
   MAPBOX
   ════════════════════════════════════════════════════════════════════════ */
let _mapInstance = null;
let _mapReady = false;
let _mapGLLoaded = false;
let _userMarker = null;

function initMapbox() {
  if (!window.ROOM_MAP || !window.MAPBOX_TOKEN) return;
  if (_mapInstance) return;
  if (_mapGLLoaded) {
    _buildMap();
    return;
  }

  if (!document.querySelector('link[href*="mapbox-gl"]')) {
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = "https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css";
    document.head.appendChild(link);
  }

  const script = document.createElement("script");
  script.src = "https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js";
  script.onload = () => {
    _mapGLLoaded = true;
    _buildMap();
  };
  document.head.appendChild(script);
}

async function _geocodeAddress(address) {
  const url =
    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json` +
    `?access_token=${window.MAPBOX_TOKEN}&language=vi&country=vn&limit=1`;
  const res = await fetch(url);
  const data = await res.json();
  return data.features?.[0]?.center ?? null;
}

function _showSkeleton(show) {
  const sk = document.getElementById("mapSkeleton");
  const map = document.getElementById("mapbox-map");
  if (sk) sk.style.display = show ? "flex" : "none";
  if (map) map.style.display = show ? "none" : "block";
}

async function _buildMap() {
  const room = window.ROOM_MAP;
  mapboxgl.accessToken = window.MAPBOX_TOKEN;

  let roomLat = room.lat;
  let roomLng = room.lng;

  if (!roomLat || !roomLng) {
    _showSkeleton(true);
    const txt = document.getElementById("mapModeText");
    if (txt) txt.textContent = "Đang tìm vị trí phòng…";
    try {
      const coords = await _geocodeAddress(room.address);
      if (coords) {
        roomLng = coords[0];
        roomLat = coords[1];
        window.ROOM_MAP.lat = roomLat;
        window.ROOM_MAP.lng = roomLng;
        const a = document.getElementById("btnOpenFullMap");
        if (a) {
          const u = new URL(a.href, location.origin);
          u.searchParams.set("toLat", roomLat);
          u.searchParams.set("toLng", roomLng);
          a.href = u.toString();
        }
      } else {
        _showSkeleton(false);
        document.getElementById("mapbox-map").innerHTML = `
          <div style="height:340px;display:flex;flex-direction:column;align-items:center;
                      justify-content:center;gap:10px;color:#9ca3af;font-size:14px;
                      border:2px dashed #e5e7eb;border-radius:10px;">
            <i class="fas fa-map-marked-alt" style="font-size:32px;color:#d1d5db;"></i>
            <span>Không xác định được vị trí phòng</span>
            <small style="font-size:12px;">${room.address}</small>
          </div>`;
        if (txt) txt.textContent = "Không tìm được vị trí";
        return;
      }
    } catch (e) {
      _showSkeleton(false);
      console.error(e);
      return;
    }
  }

  _showSkeleton(false);

  _mapInstance = new mapboxgl.Map({
    container: "mapbox-map",
    style: "mapbox://styles/mapbox/streets-v12",
    center: [roomLng, roomLat],
    zoom: 15,
    language: "vi",
  });

  _mapInstance.addControl(new mapboxgl.NavigationControl(), "top-right");
  _mapInstance.addControl(new mapboxgl.FullscreenControl(), "top-right");

  const roomEl = document.createElement("div");
  roomEl.className = "map-room-marker";
  roomEl.innerHTML = '<i class="fas fa-home"></i>';
  new mapboxgl.Marker(roomEl)
    .setLngLat([roomLng, roomLat])
    .setPopup(
      new mapboxgl.Popup({ offset: 25 }).setHTML(
        `<div class="mpop-title">${room.title}</div><div class="mpop-addr">${room.address}</div>`,
      ),
    )
    .addTo(_mapInstance);

  _mapInstance.on("load", () => {
    _mapReady = true;
    if (!room.isOwner) {
      if (room.userLat && room.userLng) {
        _drawRoute(
          room.userLat,
          room.userLng,
          room.userFromName || "Vị trí của bạn",
        );
      } else {
        _requestGeo();
      }
    } else {
      const txt = document.getElementById("mapModeText");
      if (txt) txt.textContent = "Vị trí phòng của bạn";
    }
  });
}

function _requestGeo() {
  const txt = document.getElementById("mapModeText");
  if (txt) txt.textContent = "Đang xin quyền vị trí…";
  if (!navigator.geolocation) {
    if (txt) txt.textContent = "Trình duyệt không hỗ trợ GPS.";
    return;
  }
  navigator.geolocation.getCurrentPosition(
    (pos) =>
      _drawRoute(pos.coords.latitude, pos.coords.longitude, "Vị trí hiện tại"),
    () => {
      if (txt)
        txt.textContent =
          'Chưa lấy được vị trí – nhấn "Dùng vị trí hiện tại" để thử lại.';
    },
    { timeout: 10000, maximumAge: 60000 },
  );
}

function retryGeoLocation() {
  const txt = document.getElementById("mapModeText");
  if (txt) txt.textContent = "Đang lấy vị trí GPS…";
  if (!navigator.geolocation) {
    if (txt) txt.textContent = "Trình duyệt không hỗ trợ GPS.";
    return;
  }
  navigator.geolocation.getCurrentPosition(
    (pos) =>
      _drawRoute(pos.coords.latitude, pos.coords.longitude, "Vị trí hiện tại"),
    () => {
      if (txt) txt.textContent = "Không lấy được vị trí GPS.";
    },
    { timeout: 10000, maximumAge: 0 },
  );
}

async function _drawRoute(fromLat, fromLng, fromLabel) {
  const room = window.ROOM_MAP;
  const txt = document.getElementById("mapModeText");
  if (txt) txt.textContent = `Đường từ "${fromLabel}" đến phòng`;

  if (_userMarker) {
    _userMarker.setLngLat([fromLng, fromLat]);
  } else {
    const userEl = document.createElement("div");
    userEl.className = "map-user-marker";
    userEl.innerHTML = '<i class="fas fa-user"></i>';
    _userMarker = new mapboxgl.Marker(userEl)
      .setLngLat([fromLng, fromLat])
      .setPopup(
        new mapboxgl.Popup({ offset: 25 }).setHTML(
          `<div class="mpop-title">${fromLabel}</div>`,
        ),
      )
      .addTo(_mapInstance);
  }

  const bounds = new mapboxgl.LngLatBounds();
  bounds.extend([fromLng, fromLat]);
  bounds.extend([room.lng, room.lat]);
  _mapInstance.fitBounds(bounds, { padding: 70, maxZoom: 15 });

  try {
    const url =
      `https://api.mapbox.com/directions/v5/mapbox/driving/${fromLng},${fromLat};${room.lng},${room.lat}` +
      `?steps=false&geometries=geojson&overview=full&access_token=${window.MAPBOX_TOKEN}`;
    const res = await fetch(url);
    const data = await res.json();
    const route = data.routes?.[0];
    if (!route) return;

    if (_mapInstance.getSource("route")) {
      _mapInstance.getSource("route").setData(route.geometry);
    } else {
      _mapInstance.addSource("route", {
        type: "geojson",
        data: route.geometry,
      });
      _mapInstance.addLayer(
        {
          id: "route-casing",
          type: "line",
          source: "route",
          layout: { "line-join": "round", "line-cap": "round" },
          paint: { "line-color": "#fff", "line-width": 7, "line-opacity": 0.6 },
        },
        "road-label",
      );
      _mapInstance.addLayer(
        {
          id: "route",
          type: "line",
          source: "route",
          layout: { "line-join": "round", "line-cap": "round" },
          paint: {
            "line-color": "#2b3cf7",
            "line-width": 4.5,
            "line-opacity": 0.9,
          },
        },
        "route-casing",
      );
    }

    const km = (route.distance / 1000).toFixed(1);
    const mins = Math.round(route.duration / 60);
    const dur =
      mins >= 60
        ? `${Math.floor(mins / 60)} giờ ${mins % 60} phút`
        : `${mins} phút`;
    document.getElementById("routeDistance").textContent = `${km} km`;
    document.getElementById("routeDuration").textContent = dur;
    document.getElementById("routeInfo").style.display = "flex";
    _updateFullMapLink(fromLat, fromLng, fromLabel);
  } catch (e) {
    console.error("Directions error:", e);
  }
}

function _updateFullMapLink(fromLat, fromLng, fromLabel) {
  const room = window.ROOM_MAP;
  const btn = document.getElementById("btnOpenFullMap");
  if (!btn) return;
  let url = `/map?toLat=${room.lat}&toLng=${room.lng}&toName=${encodeURIComponent(room.address)}`;
  if (fromLat != null && fromLng != null) {
    url += `&fromLat=${fromLat}&fromLng=${fromLng}`;
    if (fromLabel) url += `&fromName=${encodeURIComponent(fromLabel)}`;
  }
  btn.href = url;
}
