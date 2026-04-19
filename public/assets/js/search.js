let currentPage = 1;
const PAGE_SIZE = 12;
let totalResults = 0;
let activeType = "";

// ── Init ───────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  prefillFromUrl();
  initCitySelectSearch();

  // Tabs
  document.querySelectorAll(".tab").forEach((t) => {
    t.addEventListener("click", () => {
      document
        .querySelectorAll(".tab")
        .forEach((x) => x.classList.remove("active"));
      t.classList.add("active");
      activeType = t.dataset.type || "";
      currentPage = 1;
      loadRooms();
    });
  });

  // Sort
  document.getElementById("sort-select")?.addEventListener("change", () => {
    currentPage = 1;
    loadRooms();
  });

  // Search btn
  document.querySelector(".btn-go")?.addEventListener("click", applySearch);
  document.getElementById("kw")?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") applySearch();
  });

  loadRooms();
});

// ── Load rooms ─────────────────────────────────────────────────────────────
async function loadRooms() {
  const params = buildParams();
  const grid = document.getElementById("rooms-grid");
  const icon = document.getElementById("loadingIcon");

  if (icon) {
    icon.style.display = "";
  }
  if (grid) grid.innerHTML = skeletons(PAGE_SIZE);

  try {
    const qs = new URLSearchParams(params).toString();
    const res = await fetch("/api/rooms/public?" + qs);
    const data = await res.json();
    const rooms = data.data || [];
    totalResults = data.total || rooms.length;

    updateResultCount(totalResults);
    renderRooms(rooms);
    renderPaginationUI(totalResults, PAGE_SIZE, currentPage);
  } catch (e) {
    if (grid)
      grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:4rem;color:#ef4444;">
            <i class="fas fa-exclamation-circle" style="font-size:2rem;"></i>
            <p style="margin-top:1rem;font-size:.875rem;">Không thể tải kết quả. Vui lòng thử lại.</p>
            <button onclick="loadRooms()" style="margin-top:1rem;background:var(--primary);color:#fff;border:none;padding:.625rem 1.5rem;border-radius:.5rem;font-weight:700;cursor:pointer;font-family:inherit;">
                <i class="fas fa-redo"></i> Thử lại
            </button>
        </div>`;
  } finally {
    if (icon) icon.style.display = "none";
  }
}

function buildParams() {
  const kw = document.getElementById("kw")?.value?.trim() || "";
  const cityEl = document.getElementById("city-select");
  const city = cityEl ? cityEl.options[cityEl.selectedIndex]?.text : "";
  const sort = document.getElementById("sort-select")?.value || "newest";
  const pMin = document.getElementById("price-min")?.value || "";
  const pMax = document.getElementById("price-max")?.value || "";

  const p = {
    limit: PAGE_SIZE,
    offset: (currentPage - 1) * PAGE_SIZE,
    sort,
  };
  if (kw) p.keyword = kw;
  if (cityEl && cityEl.value) p.city = city;
  if (pMin) p.price_min = parseFloat(pMin) * 1_000_000;
  if (pMax) p.price_max = parseFloat(pMax) * 1_000_000;
  if (activeType) p.room_type = activeType;

  return p;
}

// ── Render rooms ───────────────────────────────────────────────────────────
function renderRooms(rooms) {
  const grid = document.getElementById("rooms-grid");
  if (!grid) return;

  if (rooms.length === 0) {
    grid.innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:4rem 2rem;color:#6b7280;">
                <div style="font-size:3.5rem;margin-bottom:1rem;">🔍</div>
                <h3 style="font-size:1.1rem;font-weight:800;color:#1f2937;margin-bottom:.5rem;">Không tìm thấy phòng phù hợp</h3>
                <p style="font-size:.875rem;margin-bottom:1.25rem;">Hãy thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm</p>
                <button onclick="resetFilters()" style="background:var(--primary,#2b3cf7);color:#fff;border:none;padding:.625rem 1.5rem;border-radius:8px;font-weight:700;cursor:pointer;font-size:.875rem;font-family:inherit;">
                    <i class="fas fa-times"></i> Xóa bộ lọc
                </button>
            </div>`;
    document.getElementById("pagination").style.display = "none";
    return;
  }

  grid.innerHTML = "";
  rooms.forEach((room) => grid.appendChild(buildRoomCard(room)));
}

function buildRoomCard(room) {
  const card = document.createElement("div");
  card.className = "room-card";
  card.onclick = () => {
    window.location.href = `/room/${room.room_id}`;
  };

  const img =
    room.primary_image ||
    "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=250&fit=crop";
  const price = new Intl.NumberFormat("vi-VN").format(room.price_per_month);
  const badge = room.average_rating >= 4.5 ? "featured" : "hot";
  const loc = [room.district_name, room.city_name].filter(Boolean).join(", ");

  const amenHtml = (room.amenities || [])
    .slice(0, 3)
    .map(
      (a) =>
        `<span class="amenity-tag"><i class="fas fa-check"></i>${esc(a.amenity_name)}</span>`,
    )
    .join("");

  card.innerHTML = `
        <div class="rc-img">
            <img src="${esc(img)}" alt="${esc(room.title)}" loading="lazy"
                 onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=250&fit=crop'">
            <span class="badge badge-${badge}">${badge === "featured" ? "⭐ NỔI BẬT" : "🔥 HOT"}</span>
            ${
              room.landlord_verified
                ? '<span style="position:absolute;bottom:10px;left:10px;background:rgba(16,185,129,.9);color:#fff;font-size:10px;padding:3px 8px;border-radius:4px;font-weight:700;"><i class="fas fa-shield-check"></i> eKYC</span>'
                : ""
            }
            <button class="btn-fav-card" onclick="event.stopPropagation();toggleFav(this,${room.room_id})">
                <i class="far fa-heart"></i>
            </button>
        </div>
        <div class="rc-body">
            <div class="rc-title">${esc(room.title)}</div>
            <div class="rc-price">${price} đ/tháng</div>
            <div class="rc-meta">
                ${room.area_size ? `<span><i class="fas fa-vector-square"></i> ${room.area_size}m²</span>` : ""}
                <span><i class="fas fa-users"></i> ${room.capacity || 1} người</span>
                ${room.average_rating ? `<span style="color:#f59e0b;"><i class="fas fa-star"></i> ${parseFloat(room.average_rating).toFixed(1)}</span>` : ""}
            </div>
            <div class="rc-loc"><i class="fas fa-location-dot"></i> ${esc(loc)}</div>
            ${amenHtml ? `<div class="rc-amenities">${amenHtml}</div>` : ""}
            <div class="rc-actions">
                <button class="btn-detail" onclick="event.stopPropagation();window.location.href='/room/${room.room_id}'">
                    Xem chi tiết
                </button>
                <button class="btn-chat" onclick="event.stopPropagation()" title="Nhắn tin">
                    <i class="fas fa-comment-dots"></i>
                </button>
            </div>
        </div>`;
  return card;
}

// ── Result count & loading icon ────────────────────────────────────────────
function updateResultCount(total) {
  const el = document.querySelector(".results-count");
  if (el)
    el.innerHTML = `Tìm thấy <strong>${total.toLocaleString("vi-VN")}</strong> phòng trọ phù hợp`;
}

// ── Pagination ─────────────────────────────────────────────────────────────
function renderPaginationUI(total, pageSize, current) {
  const totalPages = Math.ceil(total / pageSize);
  const wrapper = document.getElementById("pagination");
  const nums = document.getElementById("pg-nums");
  const prev = document.getElementById("pg-prev");
  const next = document.getElementById("pg-next");

  if (!wrapper || !nums) return;
  wrapper.style.display = totalPages <= 1 ? "none" : "flex";
  if (totalPages <= 1) return;

  if (prev) prev.disabled = current <= 1;
  if (next) next.disabled = current >= totalPages;

  const range = buildRange(current, totalPages);
  nums.innerHTML = "";
  range.forEach((p) => {
    if (p === "...") {
      const dot = document.createElement("span");
      dot.className = "pg-dots";
      dot.textContent = "···";
      nums.appendChild(dot);
    } else {
      const btn = document.createElement("button");
      btn.className = "pg-btn" + (p === current ? " active" : "");
      btn.textContent = p;
      btn.onclick = () => goPage(p);
      nums.appendChild(btn);
    }
  });
}

function buildRange(current, total) {
  const range = [];
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || Math.abs(i - current) <= 1) range.push(i);
    else if (range[range.length - 1] !== "...") range.push("...");
  }
  return range;
}

function goPage(page) {
  if (page < 1) return;
  currentPage = page;
  loadRooms();
  document
    .querySelector(".results-area")
    ?.scrollIntoView({ behavior: "smooth", block: "start" });
}

// ── Filter / Search ────────────────────────────────────────────────────────
function applySearch() {
  currentPage = 1;
  const kw = document.getElementById("kw")?.value?.trim() || "";
  const cityEl = document.getElementById("city-select");
  const city = cityEl ? cityEl.options[cityEl.selectedIndex]?.text : "";
  const params = new URLSearchParams(window.location.search);
  kw ? params.set("keyword", kw) : params.delete("keyword");
  city && cityEl?.value ? params.set("city", city) : params.delete("city");
  history.replaceState(null, "", "/search?" + params.toString());
  loadRooms();
}

function applyFilters() {
  currentPage = 1;
  loadRooms();
}

function resetFilters() {
  document
    .querySelectorAll('input[type="checkbox"]')
    .forEach((c) => (c.checked = false));
  document
    .querySelectorAll('input[type="radio"]')
    .forEach((r) => (r.checked = false));
  const pMin = document.getElementById("price-min");
  if (pMin) pMin.value = "";
  const pMax = document.getElementById("price-max");
  if (pMax) pMax.value = "";
  const kw = document.getElementById("kw");
  if (kw) kw.value = "";
  const city = document.getElementById("city-select");
  if (city) city.value = "";
  activeType = "";
  currentPage = 1;
  document
    .querySelectorAll(".tab")
    .forEach((t) => t.classList.remove("active"));
  document.querySelector(".tab")?.classList.add("active");
  loadRooms();
}

// ── View toggle ────────────────────────────────────────────────────────────
function setView(v) {
  const grid = document.getElementById("rooms-grid");
  const bg = document.getElementById("btn-grid");
  const bl = document.getElementById("btn-list");
  if (v === "grid") {
    grid?.classList.remove("list-view");
    bg?.classList.add("active");
    bl?.classList.remove("active");
  } else {
    grid?.classList.add("list-view");
    bl?.classList.add("active");
    bg?.classList.remove("active");
  }
}

// ── URL prefill ────────────────────────────────────────────────────────────
function prefillFromUrl() {
  const p = new URLSearchParams(window.location.search);
  const kw = p.get("keyword");
  const el = document.getElementById("kw");
  if (kw && el) el.value = kw;

  const type = p.get("type");
  if (type) {
    activeType = type;
    document.querySelectorAll(".tab").forEach((t) => {
      t.classList.toggle("active", t.dataset.type === type);
    });
  }
}

function initCitySelectSearch() {
  const el = document.getElementById("city-select");
  if (!el || typeof initCitySelect !== "function") return;
  initCitySelect(el, () => {
    currentPage = 1;
    loadRooms();
  });
}

// ── Skeleton ───────────────────────────────────────────────────────────────
function skeletons(n) {
  const s = `background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px;`;
  let html = "";
  for (let i = 0; i < n; i++) {
    html += `<div class="room-card" style="pointer-events:none;">
            <div class="rc-img"><div style="${s}height:185px;border-radius:0;"></div></div>
            <div class="rc-body">
                <div style="${s}height:14px;width:85%;margin-bottom:8px;"></div>
                <div style="${s}height:20px;width:50%;margin-bottom:8px;"></div>
                <div style="${s}height:12px;width:65%;"></div>
            </div>
        </div>`;
  }
  return html;
}

// ── Fav ────────────────────────────────────────────────────────────────────
function toggleFav(btn, roomId) {
  const faved = btn.dataset.faved === "1";
  btn.dataset.faved = faved ? "0" : "1";
  btn.innerHTML = faved
    ? '<i class="far fa-heart"></i>'
    : '<i class="fas fa-heart" style="color:#ff5a3d"></i>';
}

function esc(s) {
  return String(s || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
// ── Quick price helper ────────────────────────────────────────────────────
function setQuickPrice(min, max) {
  const pMin = document.getElementById("price-min");
  const pMax = document.getElementById("price-max");
  if (pMin) pMin.value = min;
  if (pMax) pMax.value = max;
}

// ── Pagination helper ─────────────────────────────────────────────────────
function goPage(page) {
  if (typeof currentPage === "undefined") return;
  if (page < 1) return;
  currentPage = page;
  loadRooms();
  document
    .querySelector(".results-area")
    ?.scrollIntoView({ behavior: "smooth", block: "start" });
}

// ── Render dynamic pagination ─────────────────────────────────────────────
function renderPagination(total, pageSize, current) {
  const totalPages = Math.ceil(total / pageSize);
  const nums = document.getElementById("pg-nums");
  const prev = document.getElementById("pg-prev");
  const next = document.getElementById("pg-next");
  if (!nums) return;

  prev.disabled = current <= 1;
  next.disabled = current >= totalPages;

  nums.innerHTML = "";
  const range = [];
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || Math.abs(i - current) <= 2)
      range.push(i);
    else if (range[range.length - 1] !== "...") range.push("...");
  }

  range.forEach((p) => {
    if (p === "...") {
      const dots = document.createElement("span");
      dots.className = "pg-dots";
      dots.textContent = "···";
      nums.appendChild(dots);
    } else {
      const btn = document.createElement("button");
      btn.className = "pg-btn" + (p === current ? " active" : "");
      btn.textContent = p;
      btn.onclick = () => goPage(p);
      nums.appendChild(btn);
    }
  });

  document.getElementById("pagination").style.display =
    totalPages <= 1 ? "none" : "flex";
}

// Override updateResultCount từ search.js để update icon loading và pagination
const _origUpdate =
  typeof updateResultCount === "function" ? updateResultCount : null;
document.addEventListener("DOMContentLoaded", () => {
  // Patch loadRooms để update pagination sau khi load
  const origLoadRooms = loadRooms;
});
