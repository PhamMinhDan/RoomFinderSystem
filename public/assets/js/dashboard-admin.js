/**
 * admin-dashboard.js
 * FE admin: duyệt xác thực danh tính & duyệt bài đăng phòng
 */

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  loadIdentityList();
  loadRoomsList();
});

// ── Section switch ────────────────────────────────────────────────────────
function showSection(name) {
  document
    .querySelectorAll(".admin-section")
    .forEach((s) => (s.style.display = "none"));
  document
    .querySelectorAll(".admin-nav-item")
    .forEach((b) => b.classList.remove("active"));

  document.getElementById(
    `section${name.charAt(0).toUpperCase() + name.slice(1)}`,
  ).style.display = "";
  event.currentTarget.classList.add("active");
}

// ── Identity List ─────────────────────────────────────────────────────────
async function loadIdentityList() {
  const list = document.getElementById("identityList");
  list.innerHTML =
    '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';

  try {
    const res = await fetch("/api/admin/identity/list");
    if (res.status === 403) {
      list.innerHTML = emptyState("🚫", "Không có quyền truy cập");
      return;
    }
    const data = await res.json();
    const items = data.data || [];

    document.getElementById("identityBadge").textContent = items.length || "";

    if (items.length === 0) {
      list.innerHTML = emptyState(
        "",
        "Không có yêu cầu xác thực nào",
        "Tất cả đã được xử lý!",
      );
      return;
    }

    list.innerHTML = "";
    items.forEach((item) => list.appendChild(buildIdentityCard(item)));
  } catch (e) {
    list.innerHTML = emptyState("❌", "Lỗi tải dữ liệu", e.message);
  }
}

function buildIdentityCard(item) {
  const card = document.createElement("div");
  card.className = "admin-card";
  card.id = `identity-card-${item.verification_id}`;

  const docTypeLabel =
    {
      cccd: "CCCD / CMND",
      passport: "Hộ chiếu",
      driver_license: "Giấy phép lái xe",
    }[item.document_type] || item.document_type;

  card.innerHTML = `
    <div class="admin-card-header">
      <div class="admin-card-user">
        <div class="admin-avatar-container">
          ${renderAdminAvatar(item.avatar_url, item.full_name)}
        </div>
        <div>
          <div class="admin-user-name">${escHtml(item.full_name)}</div>
          <div class="admin-user-meta">
            ${escHtml(item.email)} · Gửi lúc: ${formatDate(item.created_at)}
          </div>
        </div>
      </div>
      <span class="status-pill status-pending">⏳ Chờ duyệt</span>
    </div>

    <div class="id-info-row">
      <span><i class="fas fa-id-card" style="color:var(--primary);"></i> <strong>${escHtml(docTypeLabel)}</strong></span>
      <span><i class="fas fa-phone" style="color:var(--primary);"></i> <strong>${escHtml(item.phone_number)}</strong></span>
    </div>

    <div class="id-images">
      <div class="id-img-wrap" onclick="openImg('${escHtml(item.front_image_url)}')">
        <img src="${escHtml(item.front_image_url)}" alt="Mặt trước"
             onerror="this.src='https://via.placeholder.com/200x150?text=Lỗi+ảnh'">
        <div class="id-img-label">Mặt trước</div>
      </div>
      <div class="id-img-wrap" onclick="openImg('${escHtml(item.back_image_url)}')">
        <img src="${escHtml(item.back_image_url)}" alt="Mặt sau"
             onerror="this.src='https://via.placeholder.com/200x150?text=Lỗi+ảnh'">
        <div class="id-img-label">Mặt sau</div>
      </div>
      <div class="id-img-wrap" onclick="openImg('${escHtml(item.selfie_image_url)}')">
        <img src="${escHtml(item.selfie_image_url)}" alt="Khuôn mặt"
             onerror="this.src='https://via.placeholder.com/200x150?text=Lỗi+ảnh'">
        <div class="id-img-label">Khuôn mặt</div>
      </div>
    </div>

    <div class="admin-card-actions">
      <button class="btn-approve" onclick="approveIdentity(${item.verification_id})">
        <i class="fas fa-check-circle"></i> Duyệt xác thực
      </button>
      <div class="reject-reason-wrap">
        <input type="text" class="reject-reason-input"
               id="identity-reason-${item.verification_id}"
               placeholder="Lý do từ chối (bắt buộc)...">
        <button class="btn-reject" onclick="rejectIdentity(${item.verification_id})">
          <i class="fas fa-times-circle"></i> Từ chối
        </button>
      </div>
    </div>
  `;
  return card;
}

async function approveIdentity(id) {
  if (!confirm("Xác nhận duyệt yêu cầu xác thực này?")) return;
  try {
    const res = await fetch("/api/admin/identity/approve", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ verification_id: id }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error);

    showToast("" + data.message, "success");
    removeCard(`identity-card-${id}`);
    updateBadge("identityBadge", -1);
  } catch (e) {
    showToast(" " + e.message, "error");
  }
}

async function rejectIdentity(id) {
  const reason = document
    .getElementById(`identity-reason-${id}`)
    ?.value?.trim();
  if (!reason) {
    showToast(" Vui lòng nhập lý do từ chối", "warning");
    return;
  }
  if (!confirm("Xác nhận từ chối yêu cầu này?")) return;

  try {
    const res = await fetch("/api/admin/identity/reject", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ verification_id: id, reason }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error);

    showToast("" + data.message, "success");
    removeCard(`identity-card-${id}`);
    updateBadge("identityBadge", -1);
  } catch (e) {
    showToast(" " + e.message, "error");
  }
}

// ── Rooms List ────────────────────────────────────────────────────────────
async function loadRoomsList() {
  const list = document.getElementById("roomsList");
  list.innerHTML =
    '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';

  try {
    const res = await fetch("/api/admin/rooms/pending");
    if (res.status === 403) {
      list.innerHTML = emptyState("🚫", "Không có quyền truy cập");
      return;
    }
    const data = await res.json();
    const rooms = data.data || [];

    document.getElementById("roomsBadge").textContent = rooms.length || "";

    if (rooms.length === 0) {
      list.innerHTML = emptyState(
        "",
        "Không có bài đăng nào chờ duyệt",
        "Tất cả đã được xử lý!",
      );
      return;
    }

    list.innerHTML = "";
    rooms.forEach((room) => list.appendChild(buildRoomCard(room)));
  } catch (e) {
    list.innerHTML = emptyState("", "Lỗi tải dữ liệu", e.message);
  }
}

function buildRoomCard(room) {
  const card = document.createElement("div");
  card.className = "admin-card";
  card.id = `room-card-${room.room_id}`;

  const img =
    room.primary_image ||
    "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=90&fit=crop";
  const price = Number(room.price_per_month).toLocaleString("vi-VN");

  card.innerHTML = `
    <div class="admin-card-header">
      <div class="admin-card-user">
        <div class="admin-avatar-container">
          ${renderAdminAvatar(room.landlord_avatar, room.landlord_name)}
        </div>
        <div>
          <div class="admin-user-name">${escHtml(room.landlord_name)}</div>
          <div class="admin-user-meta">
            ${escHtml(room.landlord_email)} · Đăng: ${formatDate(room.created_at)}
          </div>
        </div>
      </div>
      <span class="status-pill status-pending">⏳ Chờ duyệt</span>
    </div>

    <div class="room-admin-wrap">
      <img src="${escHtml(img)}" class="room-admin-img" alt="${escHtml(room.title)}"
           onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=90&fit=crop'"
           onclick="openImg('${escHtml(img)}')" style="cursor:pointer;">
      <div class="room-admin-info">
        <div class="room-admin-title">${escHtml(room.title)}</div>
        <div class="room-admin-price">${price} đ/tháng</div>
        <div class="room-admin-meta">
          <i class="fas fa-map-marker-alt" style="color:var(--primary);"></i>
          ${escHtml([room.district_name, room.city_name].filter(Boolean).join(", "))}
        </div>
        <div class="room-admin-meta">
          <i class="fas fa-expand-arrows-alt" style="color:var(--gray-500);"></i>
          ${room.area_size || "?"}m²
          · <a href="/room/${room.room_id}" target="_blank" style="color:var(--primary);font-weight:600;">
              Xem trước <i class="fas fa-external-link-alt" style="font-size:10px;"></i>
            </a>
        </div>
      </div>
    </div>

    <div class="admin-card-actions">
      <button class="btn-approve" onclick="approveRoom(${room.room_id})">
        <i class="fas fa-check-circle"></i> Duyệt (+15 ngày)
      </button>
      <div class="reject-reason-wrap">
        <input type="text" class="reject-reason-input"
               id="room-reason-${room.room_id}"
               placeholder="Lý do từ chối (bắt buộc)...">
        <button class="btn-reject" onclick="rejectRoom(${room.room_id})">
          <i class="fas fa-times-circle"></i> Từ chối
        </button>
      </div>
    </div>
  `;
  return card;
}

async function approveRoom(roomId) {
  if (!confirm("Duyệt bài đăng này? Phòng sẽ hiển thị trong 15 ngày.")) return;
  try {
    const res = await fetch("/api/admin/rooms/approve", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ room_id: roomId }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error);

    showToast("✅ " + data.message, "success");
    removeCard(`room-card-${roomId}`);
    updateBadge("roomsBadge", -1);
  } catch (e) {
    showToast("❌ " + e.message, "error");
  }
}

async function rejectRoom(roomId) {
  const reason = document
    .getElementById(`room-reason-${roomId}`)
    ?.value?.trim();
  if (!reason) {
    showToast("⚠️ Vui lòng nhập lý do từ chối", "warning");
    return;
  }
  if (!confirm("Xác nhận từ chối bài đăng này?")) return;

  try {
    const res = await fetch("/api/admin/rooms/reject", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ room_id: roomId, reason }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error);

    showToast("ℹ️ Đã từ chối bài đăng", "success");
    removeCard(`room-card-${roomId}`);
    updateBadge("roomsBadge", -1);
  } catch (e) {
    showToast("❌ " + e.message, "error");
  }
}

// ── Image modal ────────────────────────────────────────────────────────────
function openImg(url) {
  if (!url || typeof url !== "string" || url === "undefined") return;

  const modalImg = document.getElementById("imgModalImg");
  const modal = document.getElementById("imgModal");

  if (modalImg && modal) {
    modalImg.src = url;
    modal.style.display = "flex";
  }
}
function closeImgModal() {
  document.getElementById("imgModal").style.display = "none";
}

// ── Helpers ────────────────────────────────────────────────────────────────
function removeCard(cardId) {
  const card = document.getElementById(cardId);
  if (!card) return;
  card.style.transition = "opacity .4s, transform .4s";
  card.style.opacity = "0";
  card.style.transform = "translateX(1rem)";
  setTimeout(() => {
    card.remove();
    // Kiểm tra nếu list trống
    checkEmptyList(card.parentElement);
  }, 400);
}

function checkEmptyList(list) {
  if (!list) return;
  const cards = list.querySelectorAll(".admin-card");
  if (cards.length === 0) {
    list.innerHTML = emptyState(
      "✅",
      "Không còn mục nào",
      "Tất cả đã được xử lý!",
    );
  }
}

function updateBadge(badgeId, delta) {
  const badge = document.getElementById(badgeId);
  if (!badge) return;
  const val = Math.max(0, (parseInt(badge.textContent) || 0) + delta);
  badge.textContent = val || "";
}

function emptyState(icon, title, sub = "") {
  return `
    <div class="empty-state">
      <div class="icon">${icon}</div>
      <h3>${escHtml(title)}</h3>
      ${sub ? `<p>${escHtml(sub)}</p>` : ""}
    </div>
  `;
}

function showToast(msg, type = "info", duration = 4000) {
  const toast = document.getElementById("adminToast");
  if (!toast) return;
  toast.className = `admin-toast ${type}`;
  toast.innerHTML = msg;
  toast.style.display = "flex";
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.style.display = "none";
  }, duration);
}

function formatDate(str) {
  if (!str) return "";
  const d = new Date(str);
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()} ${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
}

function escHtml(str) {
  return String(str || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function renderAdminAvatar(avatarUrl, fullName) {
  if (avatarUrl) {
    return `<img src="${escHtml(avatarUrl)}" class="admin-avatar-img" referrerpolicy="no-referrer" onerror="this.parentElement.innerHTML='<div class=\"admin-avatar-placeholder\">${escHtml(fullName.charAt(0).toUpperCase())}</div>'">`;
  }
  return `<div class="admin-avatar-placeholder">${escHtml(fullName ? fullName.charAt(0).toUpperCase() : "U")}</div>`;
}
