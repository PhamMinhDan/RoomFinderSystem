function filterListings(keyword) {
  const cards = document.querySelectorAll(".listing-card");
  cards.forEach((card) => {
    const text = card.textContent.toLowerCase();
    card.style.display = text.includes(keyword.toLowerCase()) ? "" : "none";
  });
}

function filterByStatus(status, btn) {
  // Update active tab
  document.querySelectorAll(".status-tab").forEach((tab) => {
    tab.classList.remove("active");
  });
  btn.classList.add("active");

  // Filter listings
  const cards = document.querySelectorAll(".listing-card");
  cards.forEach((card) => {
    if (status === "all" || card.dataset.status === status) {
      card.style.display = "";
    } else {
      card.style.display = "none";
    }
  });
}

function renewListing() {
  alert("Mở trang gia hạn tin");
}

function editListing() {
  alert("Mở trang sửa tin");
}

(function () {
  const now = new Date();
  const y = now.getFullYear();
  const m = now.getMonth();
  const today = now.getDate();
  const firstDay = new Date(y, m, 1).getDay();
  const daysInMonth = new Date(y, m + 1, 0).getDate();
  const days = ["CN", "T2", "T3", "T4", "T5", "T6", "T7"];

  let html = '<div class="calendar-grid">';
  days.forEach((d) => {
    html += `<div class="calendar-day header">${d}</div>`;
  });

  const start = (firstDay + 6) % 7; // Monday-first
  for (let i = 0; i < start; i++) html += '<div class="calendar-day"></div>';
  for (let d = 1; d <= daysInMonth; d++) {
    html += `<div class="calendar-day${d === today ? " today" : ""}">${d}</div>`;
  }
  html += "</div>";
  document.getElementById("calendarWidget").innerHTML = html;
})();

/**
 * landlord-dashboard.js  ──  Hoàn chỉnh
 */
let allRooms = [];
let currentFilter = "active";

document.addEventListener("DOMContentLoaded", loadLandlordRooms);

async function loadLandlordRooms() {
  try {
    const res = await fetch("/api/landlord/rooms");
    if (res.status === 401) {
      showEmpty("Vui lòng đăng nhập");
      return;
    }
    const data = await res.json();
    allRooms = data.data || [];
    updateStats(allRooms);
    renderAll(allRooms, currentFilter);
  } catch (e) {
    showEmpty("Không thể tải dữ liệu. Vui lòng thử lại.");
  }
}

function updateStats(rooms) {
  const cnt = {
    active: 0,
    pending: 0,
    expired: 0,
    rejected: 0,
    total: rooms.length,
  };
  rooms.forEach((r) => {
    cnt[r.display_status] = (cnt[r.display_status] || 0) + 1;
  });
  setText("stat-total", cnt.total);
  setText("stat-active", cnt.active);
  setText("stat-pending", cnt.pending);
  setText("stat-expired", cnt.expired);
  setText("count-active", cnt.active);
  setText("count-expired", cnt.expired);
  setText("count-rejected", cnt.rejected);
  setText("count-pending", cnt.pending);
}
function setText(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
}

function renderAll(rooms, filter) {
  const section = document.getElementById("listingsSection");
  if (!section) return;
  const filtered =
    filter === "all" ? rooms : rooms.filter((r) => r.display_status === filter);
  if (filtered.length === 0) {
    const labels = {
      active: "đang hiển thị",
      pending: "chờ duyệt",
      expired: "hết hạn",
      rejected: "bị từ chối",
    };
    section.innerHTML = `<div class="empty-listings"><div style="font-size:3rem;margin-bottom:1rem;">🏠</div><h3>Không có tin ${labels[filter] || ""} nào</h3><p>${filter === "active" ? "Hãy đăng tin đầu tiên!" : "Không có tin nào ở trạng thái này."}</p><a href="/post-room" class="btn-post-new"><i class="fas fa-plus"></i> Đăng tin mới</a></div>`;
    return;
  }
  section.innerHTML = "";
  filtered.forEach((room) => section.appendChild(buildCard(room)));
}

function buildCard(room) {
  const info = statusInfo(room.display_status),
    days = daysLeft(room.display_until),
    price = fmtPrice(room.price_per_month),
    date = fmtDate(room.created_at),
    img =
      room.primary_image ||
      "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&h=100&fit=crop";
  const card = document.createElement("div");
  card.className = "listing-card";
  card.dataset.status = room.display_status;
  card.innerHTML = `
        <input type="checkbox" class="listing-checkbox">
        <img src="${esc(img)}" alt="${esc(room.title)}" class="listing-image"
             onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&h=100&fit=crop'">
        <div class="listing-main">
            <div class="listing-title">${esc(room.title)}</div>
            <div class="listing-location"><i class="fas fa-map-marker-alt" style="color:var(--primary);font-size:11px;"></i> ${esc([room.district_name, room.city_name].filter(Boolean).join(", "))}</div>
            <div class="listing-price">${price} đ/tháng</div>
            <div class="listing-meta">
                <div class="listing-meta-item"><i class="fas fa-calendar"></i> Đăng: ${date}</div>
                ${days !== null ? `<div class="listing-meta-item"><i class="fas fa-clock"></i> ${days > 0 ? "Còn " + days + " ngày" : "Hết hạn hôm nay"}</div>` : ""}
                ${room.view_count > 0 ? `<div class="listing-meta-item"><i class="fas fa-eye"></i> ${room.view_count} lượt xem</div>` : ""}
            </div>
        </div>
        <div class="listing-actions">
            <div class="listing-badge" style="background:${info.bg};color:${info.color};">${info.label}</div>
            <div class="listing-buttons">
                ${room.display_status === "active" || room.display_status === "expired" ? `<button class="btn-small btn-renew" onclick="renewListing(${room.room_id})">Gia hạn</button>` : ""}
                ${room.display_status !== "rejected" ? `<button class="btn-small btn-edit" onclick="editListing(${room.room_id})">Sửa tin</button>` : ""}
                <button class="btn-small btn-more" onclick="viewRoom(${room.room_id})" title="Xem"><i class="fas fa-eye"></i></button>
            </div>
        </div>`;
  return card;
}

function filterByStatus(status, btn) {
  if (!btn) return;
  document
    .querySelectorAll(".status-tab")
    .forEach((t) => t.classList.remove("active"));
  btn.classList.add("active");
  currentFilter = status;
  renderAll(allRooms, status);
}

function renewListing(id) {
  window.location.href = `/landlord/listings/${id}/renew`;
}
function editListing(id) {
  window.location.href = `/landlord/listings/${id}/edit`;
}
function viewRoom(id) {
  window.open(`/room/${id}`, "_blank");
}

function statusInfo(s) {
  return (
    {
      active: { label: " Đang hiển thị", bg: "#dcfce7", color: "#166534" },
      pending: { label: " Chờ duyệt", bg: "#fef9c3", color: "#854d0e" },
      expired: { label: " Hết hạn", bg: "#f3f4f6", color: "#374151" },
      rejected: { label: " Bị từ chối", bg: "#fee2e2", color: "#991b1b" },
    }[s] || { label: s, bg: "#e5e7eb", color: "#374151" }
  );
}
function daysLeft(d) {
  if (!d) return null;
  return Math.ceil((new Date(d) - new Date()) / 864e5);
}
function fmtPrice(n) {
  return Number(n).toLocaleString("vi-VN");
}
function fmtDate(s) {
  if (!s) return "";
  const d = new Date(s);
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
}
function esc(s) {
  return String(s || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
function showEmpty(msg) {
  const s = document.getElementById("listingsSection");
  if (s) s.innerHTML = `<div class="empty-listings"><p>${msg}</p></div>`;
}

const css = document.createElement("style");
css.textContent = `.empty-listings{background:#fff;border-radius:12px;padding:3rem;text-align:center;border:1px solid var(--gray-200);}.empty-listings h3{font-size:1rem;font-weight:700;color:var(--gray-900);margin-bottom:.5rem;}.empty-listings p{font-size:.875rem;color:var(--gray-500);margin-bottom:1.25rem;}.btn-post-new{display:inline-flex;align-items:center;gap:.5rem;background:var(--primary);color:#fff;padding:.75rem 1.5rem;border-radius:10px;font-weight:700;text-decoration:none;font-size:.875rem;}`;
document.head.appendChild(css);
