document.addEventListener("DOMContentLoaded", () => {
  initSidebar();
  initNavigation();
  initUserDropdown();
  initBarChart();
  initApprovalCheckboxes();
  initTabBtns();
  initMobileMenu();

  // Load dashboard widgets + cập nhật badge sidebar — chỉ 1 lần fetch mỗi API
  loadDashboardPendingRooms();
  loadDashboardIdentityList();
});

/* ══════════════════════════════════════════════════════════
   HELPER DÙNG CHUNG
══════════════════════════════════════════════════════════ */

async function safeFetch(url) {
  try {
    const res = await fetch(url, { credentials: "include" });
    if (!res.ok) return null;
    const text = await res.text();
    if (!text || !text.trim()) return null;
    return JSON.parse(text);
  } catch {
    return null;
  }
}

/* ══════════════════════════════════════════════════════════
   DASHBOARD WIDGETS (chạy khi load trang)
══════════════════════════════════════════════════════════ */

async function loadDashboardPendingRooms() {
  const list = document.getElementById("dashPendingList");
  if (!list) return;

  const data = await safeFetch("/api/admin/rooms/pending");
  const rooms = data?.data || [];

  // Cập nhật stat card + sidebar badge từ cùng 1 lần fetch
  const countEl = document.getElementById("dashPendingCount");
  if (countEl) countEl.textContent = rooms.length || "0";
  updateNavBadge("nav-badge-pending", rooms.length);

  const preview = rooms.slice(0, 5);

  if (!preview.length) {
    list.innerHTML =
      '<div style="padding:20px;text-align:center;color:var(--gray-400)">Không có tin chờ duyệt</div>';
    return;
  }

  list.innerHTML = "";
  preview.forEach((room) => {
    const img =
      room.primary_image ||
      "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=90&fit=crop";
    const price = Number(room.price_per_month).toLocaleString("vi-VN");
    const row = document.createElement("div");
    row.className = "approval-row";
    row.id = `dash-room-${room.room_id}`;
    row.innerHTML = `
      <input type="checkbox" class="approval-cb">
      <img src="${escHtml(img)}" class="approval-img"
           onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=90&fit=crop'">
      <div class="approval-info">
        <div class="approval-title">${escHtml(room.title)}</div>
        <div class="approval-meta-row">
          <span><i class="fas fa-map-marker-alt"></i> ${escHtml([room.district_name, room.city_name].filter(Boolean).join(", "))}</span>
          <span><i class="fas fa-calendar"></i> ${formatDate(room.created_at)}</span>
        </div>
        <div class="approval-price">${price} đ/tháng</div>
      </div>
      <div class="approval-actions-col">
        <span class="status-badge pending">Chờ duyệt</span>
        <div class="approval-btns">
          <button class="btn-approve-sm" onclick="approveListing(this)"><i class="fas fa-check"></i> Duyệt</button>
          <button class="btn-reject-sm"  onclick="rejectListing(this)"><i class="fas fa-times"></i> Từ chối</button>
        </div>
      </div>`;
    list.appendChild(row);
  });
  initApprovalCheckboxes();
}

async function loadDashboardIdentityList() {
  const list = document.getElementById("dashIdentityList");
  if (!list) return;

  const data = await safeFetch("/api/admin/identity/list");
  console.log("Pending identities:", data);
  const items = data?.data || [];

  // Cập nhật stat card + sidebar badge từ cùng 1 lần fetch
  const countEl = document.getElementById("dashIdentityCount");
  if (countEl) countEl.textContent = items.length || "0";
  updateNavBadge("nav-badge-identity", items.length);

  const preview = items.slice(0, 3);

  if (!preview.length) {
    list.innerHTML =
      '<div style="padding:20px;text-align:center;color:var(--gray-400)">Không có yêu cầu xác thực</div>';
    return;
  }

  list.innerHTML = "";
  preview.forEach((item) => {
    const initial = item.full_name
      ? item.full_name.charAt(0).toUpperCase()
      : "U";
    const docLabel =
      { cccd: "CCCD/CMND", passport: "Hộ chiếu", driver_license: "GPLX" }[
        item.document_type
      ] || item.document_type;
    const row = document.createElement("div");
    row.className = "identity-dash-row";
    row.innerHTML = `
      <div class="identity-dash-avatar">
        ${
          item.avatar_url
            ? `<img src="${escHtml(item.avatar_url)}" referrerpolicy="no-referrer"
                   onerror="this.outerHTML='<span>${escHtml(initial)}</span>'">`
            : `<span>${escHtml(initial)}</span>`
        }
      </div>
      <div class="identity-dash-info">
        <div class="identity-name">${escHtml(item.full_name)}</div>
        <div class="identity-meta">${escHtml(item.email)} · <strong>${escHtml(docLabel)}</strong> · ${formatDate(item.created_at)}</div>
      </div>
      <div class="identity-dash-actions">
        <span class="status-badge pending">Chờ duyệt</span>
        <button class="btn-link" style="font-size:12px" onclick="navigateTo('verify-users')">
          <i class="fas fa-arrow-right"></i> Xem
        </button>
      </div>`;
    list.appendChild(row);
  });
}

/* ══════════════════════════════════════════════════════════
   SIDEBAR COLLAPSE
══════════════════════════════════════════════════════════ */
function initSidebar() {
  const sidebar = document.getElementById("sidebar");
  const btn = document.getElementById("collapseBtn");
  if (!btn || !sidebar) return;

  sidebar.classList.remove("collapsed", "mobile-open");
  btn.addEventListener("click", () => sidebar.classList.toggle("collapsed"));

  window.addEventListener("resize", () => {
    if (window.innerWidth < 900) {
      sidebar.classList.remove("collapsed");
    } else {
      sidebar.classList.remove("mobile-open");
      document.getElementById("sidebarOverlay")?.classList.remove("visible");
    }
  });
}

/* ══════════════════════════════════════════════════════════
   SPA NAVIGATION
══════════════════════════════════════════════════════════ */
function initNavigation() {
  document.querySelectorAll(".nav-link[data-page]").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      navigateTo(link.dataset.page);
      document.getElementById("sidebar")?.classList.remove("mobile-open");
      closeMobileOverlay();
    });
  });

  const hash = window.location.hash.replace("#", "") || "dashboard";
  navigateTo(hash);
}

function navigateTo(pageId) {
  document.querySelectorAll(".page").forEach((p) => p.classList.add("hidden"));
  document
    .querySelectorAll(".nav-link[data-page]")
    .forEach((l) => l.classList.remove("active"));

  document.getElementById("page-" + pageId)?.classList.remove("hidden");
  document
    .querySelector(`.nav-link[data-page="${pageId}"]`)
    ?.classList.add("active");

  window.location.hash = pageId;
  window.scrollTo(0, 0);

  // Mỗi lần navigate vào trang API đều reload để lấy data mới nhất
  if (pageId === "pending") setTimeout(loadPendingRooms, 0);
  if (pageId === "verify-users") setTimeout(loadIdentityList, 0);
}

/* ══════════════════════════════════════════════════════════
   USER DROPDOWN
══════════════════════════════════════════════════════════ */
function initUserDropdown() {
  document.addEventListener("click", (e) => {
    const user = document.getElementById("topbarUser");
    const menu = document.getElementById("userDropdownMenu");
    if (!user || !menu) return;
    if (!user.contains(e.target)) {
      menu.classList.remove("open");
      const chevron = document.getElementById("topbarChevron");
      if (chevron) chevron.style.transform = "";
    }
  });
}

function toggleUserMenu() {
  const menu = document.getElementById("userDropdownMenu");
  const chevron = document.getElementById("topbarChevron");
  if (!menu) return;
  const open = menu.classList.toggle("open");
  if (chevron) chevron.style.transform = open ? "rotate(180deg)" : "";
}

/* ══════════════════════════════════════════════════════════
   BAR CHART
══════════════════════════════════════════════════════════ */
function initBarChart() {
  const container = document.getElementById("barChart");
  if (!container) return;

  const data = [
    { label: "T2", value: 45 },
    { label: "T3", value: 72 },
    { label: "T4", value: 88 },
    { label: "T5", value: 61 },
    { label: "T6", value: 94 },
    { label: "T7", value: 58 },
    { label: "CN", value: 37 },
  ];
  const max = Math.max(...data.map((d) => d.value));

  data.forEach((d) => {
    const heightPct = Math.round((d.value / max) * 100);
    const item = document.createElement("div");
    item.className = "bar-item";
    item.innerHTML = `
      <div class="bar-value-label" style="font-size:10px;color:var(--gray-500);font-weight:600;margin-bottom:4px">${d.value}</div>
      <div class="bar-fill" style="height:0%;width:100%" data-height="${heightPct}%" title="${d.label}: ${d.value} tin"></div>
      <div class="bar-label">${d.label}</div>`;
    container.appendChild(item);
  });

  requestAnimationFrame(() => {
    setTimeout(() => {
      document.querySelectorAll(".bar-fill").forEach((bar) => {
        bar.style.transition = "height .6s cubic-bezier(.4,0,.2,1)";
        bar.style.height = bar.dataset.height;
      });
    }, 100);
  });
}

/* ══════════════════════════════════════════════════════════
   TAB BUTTONS
══════════════════════════════════════════════════════════ */
function initTabBtns() {
  document.querySelectorAll(".card-hdr-tabs").forEach((group) => {
    group.querySelectorAll(".tab-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        group
          .querySelectorAll(".tab-btn")
          .forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
      });
    });
  });
}

/* ══════════════════════════════════════════════════════════
   APPROVAL CHECKBOXES
══════════════════════════════════════════════════════════ */
function initApprovalCheckboxes() {
  document.querySelectorAll(".approval-cb").forEach((cb) => {
    cb.addEventListener("change", function () {
      const row = this.closest(".approval-row");
      if (row) row.style.background = this.checked ? "#eff6ff" : "";
    });
  });
}

/* ══════════════════════════════════════════════════════════
   MOBILE MENU
══════════════════════════════════════════════════════════ */
function initMobileMenu() {
  const btn = document.getElementById("mobileMenuBtn");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  if (!btn || !sidebar) return;

  btn.addEventListener("click", () => {
    sidebar.classList.toggle("mobile-open");
    overlay?.classList.toggle("visible");
  });

  document.addEventListener("click", (e) => {
    if (
      sidebar.classList.contains("mobile-open") &&
      !sidebar.contains(e.target) &&
      !btn.contains(e.target)
    ) {
      sidebar.classList.remove("mobile-open");
      closeMobileOverlay();
    }
  });
}

function closeMobileOverlay() {
  document.getElementById("sidebarOverlay")?.classList.remove("visible");
}

/* ══════════════════════════════════════════════════════════
   API: PENDING ROOMS (page-pending)
══════════════════════════════════════════════════════════ */
async function loadPendingRooms() {
  const list = document.getElementById("pendingApiList");
  if (!list) return;

  list.innerHTML = loadingHtml();

  const data = await safeFetch("/api/admin/rooms/pending");
  const rooms = data?.data || [];

  const sub = document.getElementById("pendingSubtitle");
  if (sub) sub.textContent = `${rooms.length} tin đang chờ xét duyệt`;
  updateNavBadge("nav-badge-pending", rooms.length);

  if (!rooms.length) {
    list.innerHTML = emptyStateHtml(
      "Không có tin nào chờ duyệt",
      "Tất cả đã được xử lý!",
    );
    return;
  }

  list.innerHTML = "";
  rooms.forEach((room) => list.appendChild(buildRoomCard(room)));
  initApprovalCheckboxes();
}

function buildRoomCard(room) {
  const card = document.createElement("div");
  card.className = "approval-row api-card";
  card.id = `room-row-${room.room_id}`;

  const img =
    room.primary_image ||
    "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=90&fit=crop";
  const price = Number(room.price_per_month).toLocaleString("vi-VN");

  card.innerHTML = `
    <input type="checkbox" class="approval-cb">
    <img src="${escHtml(img)}" class="approval-img" alt="${escHtml(room.title)}"
         onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=90&fit=crop'"
         onclick="openImgModal('${escHtml(img)}')" style="cursor:pointer">
    <div class="approval-info">
      <div class="approval-title">${escHtml(room.title)}</div>
      <div class="approval-meta-row">
        <span><i class="fas fa-map-marker-alt"></i> ${escHtml([room.district_name, room.city_name].filter(Boolean).join(", "))}</span>
        <span><i class="fas fa-calendar"></i> ${formatDate(room.created_at)}</span>
        <span><i class="fas fa-user"></i> ${escHtml(room.landlord_name || "")}</span>
        ${room.area_size ? `<span><i class="fas fa-expand-arrows-alt"></i> ${room.area_size}m²</span>` : ""}
      </div>
      <div class="approval-price">${price} đ/tháng</div>
      <div class="api-reject-wrap" id="room-reject-${room.room_id}" style="display:none;margin-top:8px">
        <input type="text" class="reject-reason-input" id="room-reason-${room.room_id}" placeholder="Lý do từ chối (bắt buộc)...">
      </div>
    </div>
    <div class="approval-actions-col">
      <span class="status-badge pending">Chờ duyệt</span>
      <div class="approval-btns">
        <button class="btn-approve-sm" onclick="approveRoom(${room.room_id}, this)">
          <i class="fas fa-check"></i> Duyệt
        </button>
        <button class="btn-reject-sm" onclick="toggleRejectRoom(${room.room_id}, this)">
          <i class="fas fa-times"></i> Từ chối
        </button>
      </div>
      <div id="room-confirm-reject-${room.room_id}" style="display:none">
        <button class="btn-reject-sm" style="width:100%" onclick="confirmRejectRoom(${room.room_id})">
          <i class="fas fa-paper-plane"></i> Gửi từ chối
        </button>
      </div>
    </div>`;
  return card;
}

async function approveRoom(roomId, btn) {
  if (!confirm("Duyệt bài đăng này? Phòng sẽ hiển thị trong 15 ngày.")) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

  try {
    const res = await fetch("/api/admin/rooms/approve", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ room_id: roomId }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || data.message);

    showToast(data.message || "Đã duyệt bài đăng!", "success");
    removeApiCard(
      `room-row-${roomId}`,
      "pendingApiList",
      "nav-badge-pending",
      "pendingSubtitle",
    );
  } catch (e) {
    showToast(e.message, "error");
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Duyệt';
  }
}

function toggleRejectRoom(roomId, btn) {
  const wrap = document.getElementById(`room-reject-${roomId}`);
  const confirmBox = document.getElementById(`room-confirm-reject-${roomId}`);
  if (!wrap) return;
  const open = wrap.style.display === "none";
  wrap.style.display = open ? "" : "none";
  confirmBox.style.display = open ? "" : "none";
  btn.innerHTML = open
    ? '<i class="fas fa-times"></i> Huỷ'
    : '<i class="fas fa-times"></i> Từ chối';
}

async function confirmRejectRoom(roomId) {
  const reason = document
    .getElementById(`room-reason-${roomId}`)
    ?.value?.trim();
  if (!reason) {
    showToast("Vui lòng nhập lý do từ chối", "warn");
    return;
  }
  if (!confirm("Xác nhận từ chối bài đăng này?")) return;

  try {
    const res = await fetch("/api/admin/rooms/reject", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ room_id: roomId, reason }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || data.message);

    showToast("Đã từ chối bài đăng", "success");
    removeApiCard(
      `room-row-${roomId}`,
      "pendingApiList",
      "nav-badge-pending",
      "pendingSubtitle",
    );
  } catch (e) {
    showToast(e.message, "error");
  }
}

/* ══════════════════════════════════════════════════════════
   API: IDENTITY VERIFICATION (page-verify-users)
══════════════════════════════════════════════════════════ */
async function loadIdentityList() {
  const sub = document.getElementById("identitySubtitle");
  const list = document.getElementById("identityApiList");

  if (sub) sub.textContent = "Đang tải dữ liệu...";
  if (list) list.innerHTML = loadingHtml();
  if (!list) return;

  const data = await safeFetch("/api/admin/identity/list");
  const items = data?.data || [];

  if (sub) sub.textContent = `${items.length} người dùng đang chờ xác thực`;
  updateNavBadge("nav-badge-identity", items.length);

  if (!items.length) {
    list.innerHTML = emptyStateHtml(
      "Không có yêu cầu xác thực nào",
      "Tất cả đã được xử lý!",
    );
    return;
  }

  list.innerHTML = "";
  items.forEach((item) => list.appendChild(buildIdentityCard(item)));
}

function buildIdentityCard(item) {
  const card = document.createElement("div");
  card.className = "identity-card";
  card.id = `identity-row-${item.verification_id}`;

  const docLabel =
    {
      cccd: "CCCD / CMND",
      passport: "Hộ chiếu",
      driver_license: "Giấy phép lái xe",
    }[item.document_type] || item.document_type;

  card.innerHTML = `
    <div class="identity-card-hdr">
      <div class="identity-user">
        <div class="identity-avatar">${renderAvatarHtml(item.avatar_url, item.full_name)}</div>
        <div>
          <div class="identity-name">${escHtml(item.full_name)}</div>
          <div class="identity-meta">${escHtml(item.email)} · Gửi: ${formatDate(item.created_at)}</div>
          <div class="identity-meta">
            <i class="fas fa-id-card" style="color:var(--primary)"></i> ${escHtml(docLabel)}
            &nbsp;·&nbsp;
            <i class="fas fa-phone" style="color:var(--primary)"></i> ${escHtml(item.phone_number)}
          </div>
        </div>
      </div>
      <span class="status-badge pending">Chờ duyệt</span>
    </div>
    <div class="identity-images">
      <div class="identity-img-wrap" onclick="openImgModal('${escHtml(item.front_image_url)}')">
        <img src="${escHtml(item.front_image_url)}" alt="Mặt trước"
             onerror="this.src='https://via.placeholder.com/200x140?text=Loi+anh'">
        <span>Mặt trước</span>
      </div>
      <div class="identity-img-wrap" onclick="openImgModal('${escHtml(item.back_image_url)}')">
        <img src="${escHtml(item.back_image_url)}" alt="Mặt sau"
             onerror="this.src='https://via.placeholder.com/200x140?text=Loi+anh'">
        <span>Mặt sau</span>
      </div>
      <div class="identity-img-wrap" onclick="openImgModal('${escHtml(item.selfie_image_url)}')">
        <img src="${escHtml(item.selfie_image_url)}" alt="Khuôn mặt"
             onerror="this.src='https://via.placeholder.com/200x140?text=Loi+anh'">
        <span>Khuôn mặt</span>
      </div>
    </div>
    <div class="identity-actions">
      <button class="btn-approve-sm" style="padding:8px 20px"
              onclick="approveIdentity(${item.verification_id}, this)">
        <i class="fas fa-check-circle"></i> Duyệt xác thực
      </button>
      <div class="identity-reject-group">
        <input type="text" class="reject-reason-input"
               id="identity-reason-${item.verification_id}"
               placeholder="Lý do từ chối (bắt buộc)...">
        <button class="btn-reject-sm" style="padding:8px 16px;white-space:nowrap"
                onclick="rejectIdentity(${item.verification_id})">
          <i class="fas fa-times-circle"></i> Từ chối
        </button>
      </div>
    </div>`;
  return card;
}

async function approveIdentity(id, btn) {
  if (!confirm("Xác nhận duyệt yêu cầu xác thực này?")) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

  try {
    const res = await fetch("/api/admin/identity/approve", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ verification_id: id }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || data.message);

    showToast(data.message || "Đã duyệt xác thực!", "success");
    removeApiCard(
      `identity-row-${id}`,
      "identityApiList",
      "nav-badge-identity",
      "identitySubtitle",
    );
  } catch (e) {
    showToast(e.message, "error");
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check-circle"></i> Duyệt xác thực';
  }
}

async function rejectIdentity(id) {
  const reason = document
    .getElementById(`identity-reason-${id}`)
    ?.value?.trim();
  if (!reason) {
    showToast("Vui lòng nhập lý do từ chối", "warn");
    return;
  }
  if (!confirm("Xác nhận từ chối yêu cầu này?")) return;

  try {
    const res = await fetch("/api/admin/identity/reject", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ verification_id: id, reason }),
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || data.message);

    showToast("Đã từ chối yêu cầu xác thực", "success");
    removeApiCard(
      `identity-row-${id}`,
      "identityApiList",
      "nav-badge-identity",
      "identitySubtitle",
    );
  } catch (e) {
    showToast(e.message, "error");
  }
}

/* ══════════════════════════════════════════════════════════
   DASHBOARD QUICK-APPROVE (static list trên dashboard)
══════════════════════════════════════════════════════════ */
function approveListing(btn) {
  const row = btn.closest(".approval-row");
  const badge = row?.querySelector(".status-badge");
  if (badge) {
    badge.className = "status-badge approved";
    badge.textContent = "Đã duyệt";
  }
  const btns = row?.querySelector(".approval-btns");
  if (btns)
    btns.innerHTML =
      '<span style="font-size:12px;color:var(--green);font-weight:600"><i class="fas fa-check"></i> Đã duyệt</span>';
  showToast("Đã duyệt tin đăng!", "success");
}

function rejectListing(btn) {
  const row = btn.closest(".approval-row");
  const badge = row?.querySelector(".status-badge");
  if (badge) {
    badge.className = "status-badge rejected";
    badge.textContent = "Từ chối";
  }
  const btns = row?.querySelector(".approval-btns");
  if (btns)
    btns.innerHTML =
      '<span style="font-size:12px;color:var(--red);font-weight:600"><i class="fas fa-times"></i> Từ chối</span>';
  showToast("Đã từ chối tin đăng.", "error");
}

function approveSelected() {
  const checked = document.querySelectorAll(".approval-cb:checked");
  if (!checked.length) {
    showToast("Vui lòng chọn ít nhất một tin!", "warn");
    return;
  }
  checked.forEach((cb) => {
    const btn = cb.closest(".approval-row")?.querySelector(".btn-approve-sm");
    if (btn) approveListing(btn);
  });
  showToast(`Đã duyệt ${checked.length} tin!`, "success");
}

function rejectSelected() {
  const checked = document.querySelectorAll(".approval-cb:checked");
  if (!checked.length) {
    showToast("Vui lòng chọn ít nhất một tin!", "warn");
    return;
  }
  checked.forEach((cb) => {
    const btn = cb.closest(".approval-row")?.querySelector(".btn-reject-sm");
    if (btn) rejectListing(btn);
  });
}

/* ══════════════════════════════════════════════════════════
   IMAGE MODAL
══════════════════════════════════════════════════════════ */
function openImgModal(url) {
  if (!url || url === "undefined") return;
  const modal = document.getElementById("imgModal");
  const img = document.getElementById("imgModalImg");
  if (!modal || !img) return;
  img.src = url;
  modal.style.display = "flex";
  document.body.style.overflow = "hidden";
}

function closeImgModal() {
  const modal = document.getElementById("imgModal");
  if (modal) modal.style.display = "none";
  document.body.style.overflow = "";
}

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeImgModal();
});

/* ══════════════════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════════════════ */
function updateNavBadge(id, count) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = count || "";
  el.style.display = count ? "" : "none";
}

function removeApiCard(cardId, listId, badgeId, subtitleId) {
  const card = document.getElementById(cardId);
  if (!card) return;
  card.style.transition = "opacity .4s, transform .4s";
  card.style.opacity = "0";
  card.style.transform = "translateX(1rem)";

  setTimeout(() => {
    card.remove();
    const list = document.getElementById(listId);
    if (!list) return;

    const remaining = list.querySelectorAll(
      '[id^="room-row-"], [id^="identity-row-"], .api-card, .identity-card',
    ).length;
    if (remaining === 0)
      list.innerHTML = emptyStateHtml(
        "Không còn mục nào",
        "Tất cả đã được xử lý!",
      );

    const badge = document.getElementById(badgeId);
    if (badge) {
      const val = Math.max(0, (parseInt(badge.textContent) || 1) - 1);
      badge.textContent = val || "";
      badge.style.display = val ? "" : "none";
    }

    const sub = document.getElementById(subtitleId);
    if (sub) {
      const next = Math.max(0, (parseInt(sub.textContent) || 1) - 1);
      sub.textContent = sub.textContent.replace(/^\d+/, next);
    }
  }, 400);
}

function loadingHtml() {
  return '<div class="api-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
}

function emptyStateHtml(title, sub = "") {
  return `<div class="empty-state">
    <p>${escHtml(title)}</p>
    ${sub ? `<span>${escHtml(sub)}</span>` : ""}
  </div>`;
}

function renderAvatarHtml(url, name) {
  const initial = name ? name.charAt(0).toUpperCase() : "U";
  return url
    ? `<img src="${escHtml(url)}" referrerpolicy="no-referrer" onerror="this.outerHTML='<span>${escHtml(initial)}</span>'">`
    : `<span>${escHtml(initial)}</span>`;
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

/* ══════════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════════ */
function showToast(msg, type = "success") {
  document.querySelector(".admin-toast")?.remove();

  const colors = {
    success: { bg: "#d1fae5", color: "#065f46", icon: "fa-check-circle" },
    error: { bg: "#fee2e2", color: "#991b1b", icon: "fa-times-circle" },
    warn: { bg: "#fef3c7", color: "#92400e", icon: "fa-exclamation-circle" },
  };
  const c = colors[type] || colors.success;

  const toast = document.createElement("div");
  toast.className = "admin-toast";
  toast.style.cssText = `
    position:fixed;bottom:24px;right:24px;z-index:9999;
    background:${c.bg};color:${c.color};
    padding:12px 18px;border-radius:12px;
    font-size:13px;font-weight:600;
    display:flex;align-items:center;gap:8px;
    box-shadow:0 4px 20px rgba(0,0,0,.12);
    animation:toastIn .3s ease;
    font-family:'Be Vietnam Pro',sans-serif;
    max-width:320px;`;
  toast.innerHTML = `<i class="fas ${c.icon}"></i> ${msg}`;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = "toastOut .3s ease forwards";
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

const _toastStyle = document.createElement("style");
_toastStyle.textContent = `
  @keyframes toastIn  { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
  @keyframes toastOut { from{opacity:1} to{opacity:0;transform:translateY(12px)} }
`;
document.head.appendChild(_toastStyle);
