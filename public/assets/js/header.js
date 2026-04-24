// ── Mobile Hamburger Menu ─────────────────────────────────
function toggleMobileMenu() {
  const drawer = document.getElementById("mobileDrawer");
  const overlay = document.getElementById("mobileOverlay");
  const btn = document.getElementById("hamburgerBtn");
  if (!drawer) return;

  const isOpen = drawer.classList.toggle("show");
  overlay?.classList.toggle("show", isOpen);
  btn?.classList.toggle("open", isOpen);
  document.body.style.overflow = isOpen ? "hidden" : "";
}

function closeMobileMenu() {
  const drawer = document.getElementById("mobileDrawer");
  const overlay = document.getElementById("mobileOverlay");
  const btn = document.getElementById("hamburgerBtn");
  drawer?.classList.remove("show");
  overlay?.classList.remove("show");
  btn?.classList.remove("open");
  document.body.style.overflow = "";
}

// Close drawer on Escape key
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") closeMobileMenu();
});

// ── Dropdown ─────────────────────────────────────────────
function toggleDropdown() {
  const menu = document.getElementById("dropdownMenu");
  const chevron = document.getElementById("dropdownChevron");
  if (!menu) return;

  const isOpen = menu.classList.toggle("show");
  if (chevron) {
    chevron.style.transform = isOpen ? "rotate(180deg)" : "rotate(0deg)";
  }
}

document.addEventListener("click", function (e) {
  const dropdown = document.getElementById("userDropdown");
  if (dropdown && !dropdown.contains(e.target)) {
    document.getElementById("dropdownMenu")?.classList.remove("show");

    const chevron = document.getElementById("dropdownChevron");
    if (chevron) chevron.style.transform = "rotate(0deg)";
  }
});

function handleChatClick() {
  if (!window.APP_CONFIG?.isLoggedIn) {
    if (typeof openLoginModal === "function") {
      openLoginModal();
    } else {
      window.location.href =
        "/?auth_error=" +
        encodeURIComponent("Vui lòng đăng nhập để sử dụng chức năng liên hệ");
    }
    return;
  }
  window.location.href = "/chat";
}

// ── Xử lý nút "Đăng tin" ─────────────────────────────────
async function handlePostRoom() {
  // ❗ check login từ biến global
  if (!window.APP_CONFIG?.isLoggedIn) {
    if (typeof openLoginModal === "function") {
      openLoginModal();
    } else {
      window.location.href =
        "/?auth_error=" + encodeURIComponent("Vui lòng đăng nhập để đăng tin");
    }
    return;
  }

  try {
    const res = await fetch("/api/check-post-eligibility");
    const data = await res.json();

    if (data.eligible) {
      window.location.href = "/post-room";
      return;
    }

    if (data.reason === "not_verified") {
      window.location.href = "/verify-identity";
      return;
    }

    if (data.reason === "identity_pending") {
      showHeaderToast("⏳ Hồ sơ xác thực đang chờ duyệt.", "warning");
      setTimeout(() => (window.location.href = "/verify-identity"), 500);
      return;
    }

    if (data.reason === "identity_rejected") {
      showHeaderToast("❌ Xác thực bị từ chối.", "error");
      setTimeout(() => (window.location.href = "/verify-identity"), 500);
      return;
    }

    window.location.href = "/verify-identity";
  } catch (e) {
    window.location.href = "/post-room";
  }
}

// ── Toast ─────────────────────────────────────────────
function showHeaderToast(msg, type = "info") {
  document.querySelector(".header-toast")?.remove();

  const bg =
    {
      success: "#10b981",
      error: "#ef4444",
      warning: "#f59e0b",
      info: "#1f2937",
    }[type] || "#1f2937";

  const t = document.createElement("div");
  t.className = "header-toast";

  t.style.cssText = `
        position:fixed;
        top:80px;
        left:50%;
        transform:translateX(-50%);
        background:${bg};
        color:#fff;
        padding:.875rem 1.5rem;
        border-radius:.75rem;
        font-size:.875rem;
        font-weight:600;
        z-index:9999;
        display:flex;
        align-items:center;
        gap:.5rem;
        max-width:90vw;
        box-shadow:0 8px 24px rgba(0,0,0,.15);
        text-align:center;
    `;

  t.innerHTML = msg;
  document.body.appendChild(t);

  setTimeout(() => {
    t.style.opacity = "0";
    t.style.transition = "opacity .4s";
    setTimeout(() => t.remove(), 400);
  }, 4000);
}

document.addEventListener("DOMContentLoaded", () => {
  const notifBtn = document.getElementById("notifBtn");
  const notifDropdown = document.getElementById("notifDropdown");

  if (notifBtn && notifDropdown) {
    // 1. Click chuông để bật/tắt
    notifBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isShowing = notifDropdown.classList.toggle("show");

      // Nếu mở ra thì mới tải dữ liệu
      if (isShowing) {
        loadNotifications();
      }
    });

    // 2. Click ra ngoài để đóng dropdown
    document.addEventListener("click", (e) => {
      if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
        notifDropdown.classList.remove("show");
      }
    });
  }
});

// Hàm tải thông báo từ API
async function loadNotifications() {
  const list = document.getElementById("notifList");
  if (!list) return;

  try {
    const res = await fetch("/api/notifications");
    const data = await res.json();

    if (!data.items || data.items.length === 0) {
      list.innerHTML =
        '<div style="padding:20px; text-align:center; color:#94a3b8;">Không có thông báo mới</div>';
      return;
    }

    // Render danh sách (có thể điều chỉnh nội dung tùy theo cấu trúc JSON của bạn)
    list.innerHTML = data.items
      .map(
        (n) => `
      <div class="notif-item ${n.is_read ? "" : "unread"}" onclick="location.href='${n.redirect_url || "#"}'">
          <div style="padding: 12px 16px; border-bottom: 1px solid #f8fafc; cursor: pointer;">
              <div style="font-weight: 600; font-size: 13px;">${n.title}</div>
              <div style="font-size: 12px; color: #64748b;">${n.content}</div>
          </div>
      </div>
    `,
      )
      .join("");
  } catch (err) {
    list.innerHTML =
      '<div style="padding:20px; text-align:center; color: #ef4444;">Lỗi tải dữ liệu</div>';
  }
}
