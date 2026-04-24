/**
 * favourites.js – module yêu thích dùng chung
 * Tích hợp: search page, room-detail page, saved-rooms page
 */

window.FavModule = (() => {
  "use strict";

  let _favedIds = new Set();
  let _loaded = false;

  /* ── Load danh sách ids đã lưu ─────────────────────────────────────── */
  async function init() {
    try {
      const res = await fetch("/api/favourites/ids");
      const data = await res.json();
      if (data.success && Array.isArray(data.ids)) {
        _favedIds = new Set(data.ids.map(Number));
        _loaded = true;
      }
    } catch (_) {
      // guest – _favedIds giữ nguyên empty
    }
    return _favedIds;
  }

  function isFaved(roomId) {
    return _favedIds.has(Number(roomId));
  }

  /* ── Toggle gọi API ─────────────────────────────────────────────────── */
  async function toggle(roomId, buttonEl) {
    const id = Number(roomId);

    // Optimistic UI
    const nowFaved = !_favedIds.has(id);
    _applyState(buttonEl, nowFaved);

    try {
      const res = await fetch("/api/favourites/toggle", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ room_id: id }),
      });
      const data = await res.json();

      if (!data.success) {
        _applyState(buttonEl, !nowFaved);
        if (res.status === 401) {
          _showToast("Vui lòng đăng nhập để lưu phòng yêu thích!", "warn");
          _openLoginIfAvailable();
        } else {
          _showToast(data.message || "Có lỗi xảy ra.", "error");
        }
        return;
      }

      if (data.faved) {
        _favedIds.add(id);
        _showToast("Đã lưu phòng yêu thích! ❤️", "success");
      } else {
        _favedIds.delete(id);
        _showToast("Đã bỏ lưu phòng.", "info");
      }

      _syncAllButtons(id, data.faved);
    } catch (_) {
      _applyState(buttonEl, !nowFaved);
      _showToast("Mất kết nối, vui lòng thử lại.", "error");
    }
  }

  /* ── Apply trạng thái tim lên button ───────────────────────────────── */
  function _applyState(btn, faved) {
    if (!btn) return;
    btn.dataset.faved = faved ? "1" : "0";

    const icon = btn.querySelector("i") || btn;

    if (faved) {
      icon.className = "fas fa-heart";
      icon.style.color = "var(--accent, #ff5a3d)";
      btn.classList.add("faved");
      btn.classList.add("active");
    } else {
      icon.className = "far fa-heart";
      icon.style.color = "";
      btn.classList.remove("faved");
    }
  }

  function _syncAllButtons(roomId, faved) {
    document
      .querySelectorAll(`[data-fav-room="${roomId}"]`)
      .forEach((btn) => _applyState(btn, faved));
  }

  function applyInitialState(btn, roomId) {
    _applyState(btn, isFaved(roomId));
  }

  /* ── Toast notification ─────────────────────────────────────────────── */
  function _showToast(msg, type = "success") {
    let container = document.getElementById("fav-toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "fav-toast-container";
      container.style.cssText = `
        position:fixed;bottom:24px;right:24px;z-index:9999;
        display:flex;flex-direction:column;gap:10px;pointer-events:none;
      `;
      document.body.appendChild(container);
    }

    const colors = {
      success: { bg: "#10b981", icon: "fa-check-circle" },
      error: { bg: "#ef4444", icon: "fa-times-circle" },
      warn: { bg: "#f59e0b", icon: "fa-exclamation-circle" },
      info: { bg: "#6b7280", icon: "fa-info-circle" },
    };
    const { bg, icon } = colors[type] || colors.success;

    const toast = document.createElement("div");
    toast.style.cssText = `
      display:flex;align-items:center;gap:10px;
      background:${bg};color:#fff;
      padding:12px 18px;border-radius:10px;
      font-size:13px;font-weight:600;font-family:inherit;
      box-shadow:0 4px 16px rgba(0,0,0,.18);
      pointer-events:auto;
      transform:translateX(120%);transition:transform .3s cubic-bezier(.34,1.56,.64,1);
    `;
    toast.innerHTML = `<i class="fas ${icon}" style="font-size:15px;"></i><span>${msg}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => {
      toast.style.transform = "translateX(0)";
    });
    setTimeout(() => {
      toast.style.transform = "translateX(120%)";
      setTimeout(() => toast.remove(), 350);
    }, 3000);
  }

  function _openLoginIfAvailable() {
    if (typeof openLoginModal === "function") openLoginModal();
  }

  return { init, toggle, isFaved, applyInitialState };
})();
