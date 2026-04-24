/* ════════════════════════════════════════════════════════════════════════
   save-rooms.js  –  Trang Tin đã lưu
   Fetch danh sách từ API, render card, lọc, sắp xếp, huỷ lưu
   ════════════════════════════════════════════════════════════════════════ */

let _allRooms = [];
let _currentFilter = "all";
let _sortVisible = false;

/* ── Khởi động ─────────────────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", async () => {
  if (!window.SAVED_USER_LOGGED_IN) return; // chưa đăng nhập → PHP đã hiện thông báo

  await _loadSavedRooms();

  // Search box
  document.getElementById("saved-search")?.addEventListener("input", (e) => {
    _renderFiltered(e.target.value.trim().toLowerCase());
  });
});

/* ── Fetch API ──────────────────────────────────────────────────────────── */
async function _loadSavedRooms() {
  try {
    const res = await fetch("/api/favourites");
    const data = await res.json();

    _hideSkeleton();

    if (data.success && Array.isArray(data.data)) {
      _allRooms = data.data;
      _updateCounts();
      _showUI();
      _renderFiltered();
    } else {
      _showEmpty();
    }
  } catch (e) {
    console.error("Render error:", e);
    _showError();
  }
}

/* ── Render ─────────────────────────────────────────────────────────────── */
function _renderFiltered(search = "") {
  const sort = document.getElementById("saved-sort-select")?.value || "newest";

  let rooms = _allRooms.slice();

  // Filter theo status
  if (_currentFilter !== "all") {
    rooms = rooms.filter((r) => {
      const tag = _roomFilterTag(r);
      return tag === _currentFilter;
    });
  }

  // Search
  if (search) {
    rooms = rooms.filter((r) => {
      const loc = [r.ward_name, r.district_name, r.city_name]
        .filter(Boolean)
        .join(" ");
      return (
        r.title.toLowerCase().includes(search) ||
        loc.toLowerCase().includes(search)
      );
    });
  }

  // Sort
  rooms = _sortRooms(rooms, sort);

  // Stats text
  const totalEl = document.getElementById("saved-total-text");
  if (totalEl) {
    totalEl.textContent =
      rooms.length > 0
        ? `Hiển thị ${rooms.length} phòng đã lưu`
        : "Không có phòng nào";
  }

  const container = document.getElementById("saved-list-container");
  if (!container) return;

  if (rooms.length === 0) {
    container.innerHTML = `
      <div style="grid-column:1/-1;">
        <div class="saved-empty-inline">
          <i class="far fa-heart"></i>
          <p>${search ? "Không tìm thấy phòng phù hợp." : "Không có phòng nào trong mục này."}</p>
        </div>
      </div>`;
    return;
  }

  container.innerHTML = rooms.map((r) => _cardHTML(r)).join("");

  // Gắn event sau khi render
  container.querySelectorAll(".saved-card-heart").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      _handleUnfav(btn);
    });
  });
}

/* ── Card HTML ──────────────────────────────────────────────────────────── */
function _cardHTML(r) {
  const price = Number(r.price_per_month).toLocaleString("vi-VN");
  const img =
    r.primary_image ||
    "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=270&fit=crop";
  const loc = [r.ward_name, r.district_name, r.city_name]
    .filter(Boolean)
    .join(", ");
  const tag = _roomFilterTag(r);
  const badgeColor =
    { approved: "#10b981", expiring: "#f59e0b", expired: "#ef4444" }[tag] ||
    "#10b981";
  const badgeLabel =
    {
      approved: "Đang hiển thị",
      expiring: "Sắp hết hạn",
      expired: "Đã hết hạn",
    }[tag] || "";
  const savedDate = r.saved_at ? _formatDate(r.saved_at) : "";
  const rating = r.average_rating ? Number(r.average_rating).toFixed(1) : null;

  return `
    <div class="saved-room-card" data-room-id="${r.room_id}" data-filter="${_roomFilterTag(r)}">
      <div class="saved-card-image-wrapper">
        <img class="saved-card-image"
             src="${_esc(img)}"
             alt="${_esc(r.title)}"
             onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=270&fit=crop'"
             loading="lazy">

        <!-- Badge status -->
        ${badgeLabel ? `<span class="saved-card-status" style="background:${badgeColor};">${badgeLabel}</span>` : ""}

        <!-- Nút bỏ lưu (tim đỏ) -->
        <button class="saved-card-heart" data-room-id="${r.room_id}" title="Bỏ lưu">
          <i class="fas fa-heart"></i>
        </button>

        <!-- Rating overlay -->
        ${
          rating
            ? `
        <div class="saved-card-rating-overlay">
          <i class="fas fa-star"></i> ${rating}
        </div>`
            : ""
        }
      </div>

      <div class="saved-card-content">
        <div class="saved-card-title">${_esc(r.title)}</div>
        <div class="saved-card-location">
          <i class="fas fa-map-marker-alt"></i>
          <span>${_esc(loc)}</span>
        </div>
        <div class="saved-card-price">${price} <span>đ/tháng</span></div>

        <div class="saved-card-meta">
          ${r.area_size ? `<div class="saved-card-meta-item"><i class="fas fa-ruler-combined"></i>${r.area_size}m²</div>` : ""}
          ${r.capacity ? `<div class="saved-card-meta-item"><i class="fas fa-users"></i>${r.capacity} người</div>` : ""}
          ${savedDate ? `<div class="saved-card-meta-item saved-card-meta-date"><i class="fas fa-bookmark"></i>Lưu ${savedDate}</div>` : ""}
        </div>

        <div class="saved-card-actions">
          <a class="saved-card-btn btn-primary" href="/room/${r.room_id}">
            <i class="fas fa-eye"></i> Xem phòng
          </a>
          <button class="saved-card-btn btn-secondary saved-unfav-btn" data-room-id="${r.room_id}">
            <i class="fas fa-heart-broken"></i> Bỏ lưu
          </button>
        </div>
      </div>
    </div>`;
}

/* ── Huỷ lưu yêu thích ─────────────────────────────────────────────────── */
async function _handleUnfav(btn) {
  const roomId = Number(btn.dataset.roomId);
  if (!roomId) return;

  const card = btn.closest(".saved-room-card");

  // Confirm nhẹ bằng animation thay vì confirm()
  card.style.transition = "opacity .2s, transform .2s";
  card.style.opacity = "0.5";
  card.style.transform = "scale(0.97)";

  try {
    const res = await fetch("/api/favourites/toggle", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ room_id: roomId }),
    });
    const data = await res.json();

    if (data.success && !data.faved) {
      // Xóa khỏi danh sách local
      _allRooms = _allRooms.filter((r) => r.room_id !== roomId);
      _updateCounts();

      // Animate out rồi remove
      card.style.opacity = "0";
      card.style.transform = "scale(0.9)";
      setTimeout(() => {
        card.remove();
        const search =
          document.getElementById("saved-search")?.value.trim().toLowerCase() ||
          "";
        _renderFiltered(search);
        if (_allRooms.length === 0) _showEmpty();
      }, 250);

      _showToast("Đã bỏ lưu phòng.", "info");
    } else {
      card.style.opacity = "1";
      card.style.transform = "";
      _showToast(data.message || "Có lỗi xảy ra.", "error");
    }
  } catch (_) {
    card.style.opacity = "1";
    card.style.transform = "";
    _showToast("Mất kết nối, vui lòng thử lại.", "error");
  }
}

// Gắn event cho nút "Bỏ lưu" trong action bar (delegate)
document.addEventListener("click", (e) => {
  const btn = e.target.closest(".saved-unfav-btn");
  if (!btn) return;
  e.preventDefault();
  _handleUnfav(btn);
});

/* ── Filter tabs ────────────────────────────────────────────────────────── */
function filterSavedRooms(filterType, element) {
  document
    .querySelectorAll(".saved-filter-btn")
    .forEach((btn) => btn.classList.remove("active"));
  element.classList.add("active");
  _currentFilter = filterType;
  const search =
    document.getElementById("saved-search")?.value.trim().toLowerCase() || "";
  _renderFiltered(search);
}

/* ── Sort ───────────────────────────────────────────────────────────────── */
function toggleSort() {
  const sel = document.getElementById("saved-sort-select");
  if (!sel) return;
  _sortVisible = !_sortVisible;
  sel.style.display = _sortVisible ? "inline-block" : "none";
}

function renderSaved() {
  const search =
    document.getElementById("saved-search")?.value.trim().toLowerCase() || "";
  _renderFiltered(search);
}

function _sortRooms(rooms, sort) {
  return rooms.slice().sort((a, b) => {
    if (sort === "newest") return new Date(b.saved_at) - new Date(a.saved_at);
    if (sort === "oldest") return new Date(a.saved_at) - new Date(b.saved_at);
    if (sort === "price_asc") return a.price_per_month - b.price_per_month;
    if (sort === "price_desc") return b.price_per_month - a.price_per_month;
    return 0;
  });
}

/* ── Đếm theo filter ─────────────────────────────────────────────────────── */
function _updateCounts() {
  const counts = {
    all: _allRooms.length,
    approved: 0,
    expiring: 0,
    expired: 0,
  };

  _allRooms.forEach((r) => {
    const t = _roomFilterTag(r);
    if (counts[t] !== undefined) counts[t]++;
  });

  // Cập nhật lên giao diện
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };

  set("cnt-all", counts.all);
  set("cnt-approved", counts.approved);
  set("cnt-expiring", counts.expiring);
  set("cnt-expired", counts.expired);
}

function _roomFilterTag(r) {
  if (!r.expires_at) return "approved";

  try {
    const exp = new Date(r.expires_at.replace(/-/g, "/"));
    if (isNaN(exp.getTime())) return "approved";

    const now = new Date();
    if (exp < now) return "expired";

    const diff = (exp - now) / (1000 * 3600 * 24);
    if (diff <= 7) return "expiring";
    return "approved";
  } catch (e) {
    return "approved";
  }
}

/* ── UI state helpers ─────────────────────────────────────────────────────── */
function _hideSkeleton() {
  const sk = document.getElementById("saved-skeleton");
  if (sk) sk.style.display = "none";
}

function _showUI() {
  const filters = document.getElementById("saved-filters");
  const container = document.getElementById("saved-list-container");
  const stats = document.getElementById("saved-stats");
  if (filters) filters.style.display = "flex";
  if (container) container.style.display = "grid";
  if (stats) stats.style.display = "flex";
}

function _showEmpty() {
  const el = document.getElementById("saved-empty");
  const container = document.getElementById("saved-list-container");
  if (el) el.style.display = "flex";
  if (container) container.style.display = "none";
  const filters = document.getElementById("saved-filters");
  if (filters) filters.style.display = "none";
  const stats = document.getElementById("saved-stats");
  if (stats) stats.style.display = "none";
}

function _showError() {
  const container = document.getElementById("saved-list-container");
  if (container) {
    container.style.display = "block";
    container.innerHTML = `
      <div style="text-align:center;padding:60px 20px;color:#6b7280;">
        <i class="fas fa-exclamation-circle" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:12px;"></i>
        Không thể tải danh sách. <a href="" style="color:var(--primary);">Thử lại</a>
      </div>`;
  }
}

/* ── Helpers ─────────────────────────────────────────────────────────────── */
function _formatDate(dateStr) {
  const d = new Date(dateStr);
  if (isNaN(d)) return "";
  const now = new Date();
  const diff = Math.floor((now - d) / (1000 * 3600 * 24));
  if (diff === 0) return "hôm nay";
  if (diff === 1) return "hôm qua";
  if (diff < 7) return `${diff} ngày trước`;
  return d.toLocaleDateString("vi-VN");
}

function _esc(str) {
  return String(str || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function _showToast(msg, type = "success") {
  let container = document.getElementById("fav-toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "fav-toast-container";
    container.style.cssText =
      "position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;";
    document.body.appendChild(container);
  }
  const colors = {
    success: "#10b981",
    error: "#ef4444",
    warn: "#f59e0b",
    info: "#6b7280",
  };
  const icons = {
    success: "fa-check-circle",
    error: "fa-times-circle",
    warn: "fa-exclamation-circle",
    info: "fa-info-circle",
  };
  const toast = document.createElement("div");
  toast.style.cssText = `
    display:flex;align-items:center;gap:10px;
    background:${colors[type] || colors.success};color:#fff;
    padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;
    box-shadow:0 4px 16px rgba(0,0,0,.18);pointer-events:auto;
    transform:translateX(120%);transition:transform .3s cubic-bezier(.34,1.56,.64,1);`;
  toast.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i><span>${msg}</span>`;
  container.appendChild(toast);
  requestAnimationFrame(() => {
    toast.style.transform = "translateX(0)";
  });
  setTimeout(() => {
    toast.style.transform = "translateX(120%)";
    setTimeout(() => toast.remove(), 350);
  }, 3000);
}
