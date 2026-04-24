"use strict";

/**
 * chat-init.js  (fixed)
 *
 * Thay đổi so với bản cũ:
 * - KHÔNG còn gọi API /room-preview khi click "Nhắn tin".
 *   Data (room_id, landlord_id, title, price, image) đã có sẵn ở data-* attributes
 *   trên button → redirect ngay lập tức, không có độ trễ.
 * - Fallback: nếu button không có data-* thì mới gọi API (backward-compat).
 */

let _chatBtnLock = false;

/**
 * Gọi từ onclick của button "Nhắn tin" / "Nhắn tin cho chủ nhà".
 *
 * Cách dùng nhanh (không cần API):
 *   <button onclick="openChatForRoom(123, this)"
 *           data-landlord-id="uuid-..."
 *           data-title="Phòng trọ đẹp"
 *           data-price="3000000"
 *           data-image="/img/room.jpg">
 *     Nhắn tin ngay
 *   </button>
 *
 * Nếu button có đủ data-* → redirect ngay (0ms).
 * Nếu thiếu          → gọi API /room-preview như cũ.
 */
async function openChatForRoom(roomId, btn) {
  if (!roomId || _chatBtnLock) return;

  // ── Thử redirect ngay nếu button có đủ data ──────────────────────────────
  if (btn) {
    const landlordId = btn.dataset.landlordId || "";
    // Nếu landlordId có sẵn → không cần API, redirect thẳng
    if (landlordId) {
      const title = btn.dataset.title || "";
      const price = btn.dataset.price || "0";
      const image = btn.dataset.image || "";

      const url =
        "/chat" +
        "?room_id=" +
        encodeURIComponent(roomId) +
        "&landlord_id=" +
        encodeURIComponent(landlordId) +
        "&title=" +
        encodeURIComponent(title) +
        "&price=" +
        encodeURIComponent(price) +
        "&image=" +
        encodeURIComponent(image);

      window.location.href = url;
      return;
    }
  }

  // ── Fallback: gọi API (khi button không mang data-*) ─────────────────────
  const origHtml = btn ? btn.innerHTML : null;

  _chatBtnLock = true;
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  }

  try {
    const res = await fetch("/api/chat/room-preview", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ room_id: parseInt(roomId, 10) }),
    });

    if (res.status === 401) {
      if (typeof openLoginModal === "function") {
        openLoginModal();
      } else {
        window.location.href =
          "/?login=1&next=" + encodeURIComponent("/chat?room_id=" + roomId);
      }
      return;
    }

    const data = await res.json();

    if (data.redirect_url) {
      window.location.href = data.redirect_url;
    } else if (data.error) {
      _showChatInitError(data.error);
    }
  } catch {
    _showChatInitError("Lỗi kết nối. Vui lòng thử lại.");
  } finally {
    _chatBtnLock = false;
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = origHtml ?? btn.innerHTML;
    }
  }
}

function _showChatInitError(msg) {
  if (typeof chatToast === "function") {
    chatToast(msg);
    return;
  }
  const toast = document.createElement("div");
  toast.style.cssText = [
    "position:fixed",
    "bottom:24px",
    "left:50%",
    "transform:translateX(-50%)",
    "background:#1a1b1e",
    "color:#fff",
    "padding:10px 20px",
    "border-radius:10px",
    "font-size:13px",
    "z-index:9999",
    "font-family:'Be Vietnam Pro',system-ui,sans-serif",
  ].join(";");
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
