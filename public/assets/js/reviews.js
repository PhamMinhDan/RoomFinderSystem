/**
 * reviews.js — Hệ thống đánh giá phòng trọ
 * Tích hợp: chọn sao, upload ảnh qua /api/upload, gửi đánh giá, phân trang
 */

(function () {
  "use strict";

  /* ── State ─────────────────────────────────────────────────────────────── */
  const State = {
    roomId: 0,
    currentPage: 1,
    perPage: 5,
    totalPages: 1,
    totalReviews: 0,
    distribution: {},
    isLoading: false,
    isSubmitting: false,

    // form
    selectedRating: 0,
    uploadedImages: [], // [{url, previewUrl, name}]
    uploadingCount: 0,
  };

  /* ── Init ─────────────────────────────────────────────────────────────── */
  function init() {
    const wrap = document.getElementById("reviews-root");
    if (!wrap) return;
    State.roomId = parseInt(wrap.dataset.roomId || "0", 10);
    if (!State.roomId) return;

    renderShell(wrap);
    loadReviews(1);
  }

  /* ── Shell HTML ────────────────────────────────────────────────────────── */
  function renderShell(wrap) {
    wrap.innerHTML = `
      <div class="rv-container">
        <!-- Rating Summary -->
        <div class="rv-summary" id="rv-summary">
          <div class="rv-summary-skeleton">
            <div class="rv-sk rv-sk-big"></div>
            <div class="rv-sk-bars">
              ${[5, 4, 3, 2, 1].map(() => `<div class="rv-sk rv-sk-bar"></div>`).join("")}
            </div>
          </div>
        </div>

        <!-- Write Review -->
        <div class="rv-write-box" id="rv-write-box">
          <div class="rv-write-header">
            <i class="fas fa-pen-nib"></i>
            <span>Viết đánh giá của bạn</span>
          </div>
          <div id="rv-form-area"></div>
        </div>

        <!-- Review List -->
        <div class="rv-list-header">
          <span id="rv-list-title">Tất cả đánh giá</span>
        </div>
        <div id="rv-list" class="rv-list">
          <div class="rv-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
        </div>
        <div id="rv-pagination" class="rv-pagination"></div>
      </div>
    `;

    renderForm();
  }

  /* ── Form ──────────────────────────────────────────────────────────────── */
  function renderForm() {
    const isLoggedIn = window.REVIEW_USER && window.REVIEW_USER.user_id;
    const area = document.getElementById("rv-form-area");
    if (!area) return;

    if (!isLoggedIn) {
      area.innerHTML = `
        <div class="rv-login-prompt">
          <i class="fas fa-lock"></i>
          <span>Vui lòng <a href="#" onclick="openLoginModal();return false;">đăng nhập</a> để viết đánh giá</span>
        </div>`;
      return;
    }

    area.innerHTML = `
      <div class="rv-form">
        <!-- Stars -->
        <div class="rv-star-row" id="rv-star-row">
          ${[1, 2, 3, 4, 5]
            .map(
              (n) => `
            <button class="rv-star-btn" data-n="${n}"
                    onmouseenter="rvHoverStar(${n})"
                    onmouseleave="rvHoverStar(0)"
                    onclick="rvSetStar(${n})">
              <i class="fas fa-star"></i>
            </button>`,
            )
            .join("")}
          <span class="rv-star-label" id="rv-star-label">Chọn số sao</span>
        </div>

        <!-- Comment -->
        <textarea id="rv-comment" class="rv-textarea"
                  placeholder="Chia sẻ trải nghiệm của bạn về phòng này..." rows="3"
                  maxlength="1000"></textarea>
        <div class="rv-char-count"><span id="rv-char-cur">0</span>/1000</div>

        <!-- Image Upload -->
        <div class="rv-upload-area">
          <input type="file" id="rv-file-input" accept="image/*" multiple style="display:none">
          <button class="rv-upload-btn" onclick="document.getElementById('rv-file-input').click()">
            <i class="fas fa-camera"></i>
            <span>Thêm ảnh (tùy chọn)</span>
          </button>
          <div id="rv-img-preview" class="rv-img-preview"></div>
        </div>

        <!-- Submit -->
        <div class="rv-form-footer">
          <span class="rv-required-note">* Yêu cầu: chọn số sao + nội dung hoặc ảnh</span>
          <button class="rv-submit-btn" id="rv-submit-btn" onclick="rvSubmit()">
            <i class="fas fa-paper-plane"></i> Gửi đánh giá
          </button>
        </div>
      </div>
    `;

    // Textarea char count
    const ta = document.getElementById("rv-comment");
    ta &&
      ta.addEventListener("input", () => {
        const cur = document.getElementById("rv-char-cur");
        if (cur) cur.textContent = ta.value.length;
      });

    // File input
    const fi = document.getElementById("rv-file-input");
    fi && fi.addEventListener("change", rvHandleFiles);
  }

  /* ── Star Labels ───────────────────────────────────────────────────────── */
  const STAR_LABELS = [
    "",
    "Tệ",
    "Không tốt",
    "Bình thường",
    "Tốt",
    "Tuyệt vời",
  ];

  window.rvHoverStar = function (n) {
    const target = n || State.selectedRating;
    document.querySelectorAll(".rv-star-btn").forEach((btn, i) => {
      btn.classList.toggle("rv-star-active", i < target);
      btn.classList.toggle("rv-star-hover", n > 0 && i < n);
    });
    const lbl = document.getElementById("rv-star-label");
    if (lbl)
      lbl.textContent = n
        ? STAR_LABELS[n]
        : State.selectedRating
          ? STAR_LABELS[State.selectedRating]
          : "Chọn số sao";
  };

  window.rvSetStar = function (n) {
    State.selectedRating = n;
    document.querySelectorAll(".rv-star-btn").forEach((btn, i) => {
      btn.classList.toggle("rv-star-active", i < n);
      btn.classList.remove("rv-star-hover");
    });
    const lbl = document.getElementById("rv-star-label");
    if (lbl) {
      lbl.textContent = STAR_LABELS[n];
      lbl.className = "rv-star-label rv-star-chosen";
    }
  };

  /* ── Image Upload ──────────────────────────────────────────────────────── */
  async function rvHandleFiles(e) {
    const files = Array.from(e.target.files || []);
    e.target.value = "";
    if (!files.length) return;

    const remaining = 5 - State.uploadedImages.length;
    const toUpload = files.slice(0, remaining);

    for (const file of toUpload) {
      if (!file.type.startsWith("image/")) continue;
      if (file.size > 10 * 1024 * 1024) {
        showToast("Ảnh " + file.name + " vượt quá 10MB", "error");
        continue;
      }
      await uploadSingleImage(file);
    }
  }

  async function uploadSingleImage(file) {
    const tempId =
      "tmp_" + Date.now() + "_" + Math.random().toString(36).slice(2);
    const previewUrl = URL.createObjectURL(file);

    // Add pending thumbnail
    State.uploadedImages.push({
      tempId,
      url: null,
      previewUrl,
      name: file.name,
    });
    State.uploadingCount++;
    renderImagePreviews();

    const formData = new FormData();
    formData.append("file", file);
    formData.append("secureId", "review_" + State.roomId + "_" + tempId);

    try {
      const res = await fetch("/api/upload", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();
      if (data.error) throw new Error(data.error);

      const idx = State.uploadedImages.findIndex((x) => x.tempId === tempId);
      if (idx !== -1) State.uploadedImages[idx].url = data.url;
    } catch (err) {
      showToast("Upload thất bại: " + err.message, "error");
      State.uploadedImages = State.uploadedImages.filter(
        (x) => x.tempId !== tempId,
      );
    } finally {
      State.uploadingCount--;
      renderImagePreviews();
    }
  }

  function renderImagePreviews() {
    const wrap = document.getElementById("rv-img-preview");
    if (!wrap) return;

    wrap.innerHTML = State.uploadedImages
      .map(
        (img) => `
      <div class="rv-img-thumb ${!img.url ? "rv-img-uploading" : ""}" data-id="${img.tempId}">
        <img src="${img.previewUrl}" alt="${img.name}">
        ${
          !img.url
            ? `<div class="rv-img-overlay"><i class="fas fa-spinner fa-spin"></i></div>`
            : `<button class="rv-img-remove" onclick="rvRemoveImage('${img.tempId}')"><i class="fas fa-times"></i></button>`
        }
      </div>
    `,
      )
      .join("");

    // Upload button visibility
    const btn = document.querySelector(".rv-upload-btn");
    if (btn)
      btn.style.display = State.uploadedImages.length >= 5 ? "none" : "flex";
  }

  window.rvRemoveImage = function (tempId) {
    const img = State.uploadedImages.find((x) => x.tempId === tempId);
    if (img) URL.revokeObjectURL(img.previewUrl);
    State.uploadedImages = State.uploadedImages.filter(
      (x) => x.tempId !== tempId,
    );
    renderImagePreviews();
  };

  /* ── Submit ────────────────────────────────────────────────────────────── */
  window.rvSubmit = async function () {
    if (State.isSubmitting) return;

    const comment = (document.getElementById("rv-comment")?.value || "").trim();
    const rating = State.selectedRating;
    const images = State.uploadedImages.filter((x) => x.url).map((x) => x.url);

    if (!rating) return showToast("Vui lòng chọn số sao đánh giá", "warn");
    if (!comment && images.length === 0)
      return showToast("Vui lòng nhập nội dung hoặc thêm ảnh", "warn");
    if (State.uploadingCount > 0)
      return showToast("Đang tải ảnh lên, vui lòng chờ...", "warn");

    State.isSubmitting = true;
    const btn = document.getElementById("rv-submit-btn");
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    }

    try {
      const res = await fetch(`/api/rooms/${State.roomId}/reviews`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ rating, comment, images }),
      });
      const data = await res.json();

      if (!res.ok) throw new Error(data.error || "Lỗi không xác định");

      showToast("Gửi đánh giá thành công! 🎉", "success");

      // Reset form
      State.selectedRating = 0;
      State.uploadedImages = [];
      document
        .querySelectorAll(".rv-star-btn")
        .forEach((b) => b.classList.remove("rv-star-active"));
      const lbl = document.getElementById("rv-star-label");
      if (lbl) {
        lbl.textContent = "Chọn số sao";
        lbl.className = "rv-star-label";
      }
      const ta = document.getElementById("rv-comment");
      if (ta) {
        ta.value = "";
        document.getElementById("rv-char-cur").textContent = "0";
      }
      renderImagePreviews();

      // Ẩn form write
      const wb = document.getElementById("rv-write-box");
      if (wb) {
        wb.innerHTML = `<div class="rv-thanks"><i class="fas fa-check-circle"></i> Cảm ơn bạn đã đánh giá!</div>`;
      }

      // Reload list từ đầu
      await loadReviews(1);
    } catch (err) {
      showToast(err.message, "error");
    } finally {
      State.isSubmitting = false;
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi đánh giá';
      }
    }
  };

  /* ── Load Reviews ──────────────────────────────────────────────────────── */
  async function loadReviews(page) {
    if (State.isLoading) return;
    State.isLoading = true;
    State.currentPage = page;

    const list = document.getElementById("rv-list");
    const pag = document.getElementById("rv-pagination");

    if (list)
      list.innerHTML = `<div class="rv-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>`;
    if (pag) pag.innerHTML = "";

    try {
      const res = await fetch(
        `/api/rooms/${State.roomId}/reviews?page=${page}&per_page=${State.perPage}`,
      );
      const data = await res.json();

      State.totalPages = data.last_page || 1;
      State.totalReviews = data.total || 0;
      State.distribution = data.distribution || {};

      renderSummary(data);
      renderList(data.reviews || []);
      renderPagination();

      const title = document.getElementById("rv-list-title");
      if (title) title.textContent = `Tất cả đánh giá (${State.totalReviews})`;
    } catch (err) {
      if (list)
        list.innerHTML = `<div class="rv-error"><i class="fas fa-exclamation-triangle"></i> Không thể tải đánh giá</div>`;
    } finally {
      State.isLoading = false;
    }
  }

  /* ── Render Summary ────────────────────────────────────────────────────── */
  function renderSummary(data) {
    const wrap = document.getElementById("rv-summary");
    if (!wrap) return;

    const total = data.total || 0;
    const dist = data.distribution || {};
    const avg =
      total > 0
        ? (
            [5, 4, 3, 2, 1].reduce((s, k) => s + k * (dist[k] || 0), 0) / total
          ).toFixed(1)
        : null;

    if (!total) {
      wrap.innerHTML = `<div class="rv-no-summary">Chưa có đánh giá nào</div>`;
      return;
    }

    wrap.innerHTML = `
      <div class="rv-summary-inner">
        <div class="rv-avg-block">
          <div class="rv-avg-num">${avg}</div>
          <div class="rv-avg-stars">${renderStarsFull(parseFloat(avg))}</div>
          <div class="rv-avg-count">${total} đánh giá</div>
        </div>
        <div class="rv-dist-bars">
          ${[5, 4, 3, 2, 1]
            .map((k) => {
              const cnt = dist[k] || 0;
              const pct = total > 0 ? Math.round((cnt / total) * 100) : 0;
              return `
              <div class="rv-dist-row">
                <span class="rv-dist-label">${k} <i class="fas fa-star rv-dist-star"></i></span>
                <div class="rv-dist-bar-wrap">
                  <div class="rv-dist-bar" style="width:${pct}%" data-pct="${pct}"></div>
                </div>
                <span class="rv-dist-cnt">${cnt}</span>
              </div>`;
            })
            .join("")}
        </div>
      </div>
    `;

    // Animate bars
    requestAnimationFrame(() => {
      wrap.querySelectorAll(".rv-dist-bar").forEach((b) => {
        b.style.width = "0";
        setTimeout(() => {
          b.style.width = b.dataset.pct + "%";
        }, 50);
      });
    });
  }

  /* ── Render List ───────────────────────────────────────────────────────── */
  function renderList(reviews) {
    const list = document.getElementById("rv-list");
    if (!list) return;

    if (!reviews.length) {
      list.innerHTML = `
        <div class="rv-empty">
          <i class="fas fa-comment-slash"></i>
          <p>Chưa có đánh giá nào.<br>Hãy là người đầu tiên!</p>
        </div>`;
      return;
    }

    list.innerHTML = reviews.map((rv) => renderReviewCard(rv)).join("");
  }

  function renderReviewCard(rv) {
    const date = new Date(rv.created_at).toLocaleDateString("vi-VN");
    const initials = (rv.reviewer_name || "U").charAt(0).toUpperCase();
    const images = rv.images || [];

    return `
      <div class="rv-card">
        <div class="rv-card-avatar">
          ${
            rv.reviewer_avatar
              ? `<img src="${esc(rv.reviewer_avatar)}" referrerpolicy="no-referrer" alt="${esc(rv.reviewer_name)}">`
              : `<span>${initials}</span>`
          }
        </div>
        <div class="rv-card-body">
          <div class="rv-card-top">
            <span class="rv-card-name">${esc(rv.reviewer_name || "Người dùng")}</span>
            <span class="rv-card-date">${date}</span>
          </div>
          <div class="rv-card-stars">${renderStarsFull(rv.rating)}
            <span class="rv-card-rating-num">${parseFloat(rv.rating).toFixed(1)}</span>
          </div>
          ${rv.comment ? `<p class="rv-card-comment">${esc(rv.comment)}</p>` : ""}
          ${
            images.length
              ? `
            <div class="rv-card-imgs">
              ${images
                .map(
                  (url) => `
                <img src="${esc(url)}" alt="review image" onclick="rvLightbox('${esc(url)}')" loading="lazy">
              `,
                )
                .join("")}
            </div>`
              : ""
          }
        </div>
      </div>`;
  }

  function renderStarsFull(rating) {
    const full = Math.floor(rating);
    const half = rating - full >= 0.5;
    const empty = 5 - full - (half ? 1 : 0);
    return (
      '<span class="rv-stars-display">' +
      '<i class="fas fa-star"></i>'.repeat(full) +
      (half ? '<i class="fas fa-star-half-alt"></i>' : "") +
      '<i class="far fa-star"></i>'.repeat(empty) +
      "</span>"
    );
  }

  /* ── Pagination ────────────────────────────────────────────────────────── */
  function renderPagination() {
    const pag = document.getElementById("rv-pagination");
    if (!pag || State.totalPages <= 1) {
      if (pag) pag.innerHTML = "";
      return;
    }

    const pages = [];
    const cur = State.currentPage;
    const last = State.totalPages;

    if (cur > 1)
      pages.push({
        label: '<i class="fas fa-chevron-left"></i>',
        page: cur - 1,
      });

    for (let i = 1; i <= last; i++) {
      if (i === 1 || i === last || Math.abs(i - cur) <= 1) {
        pages.push({ label: i, page: i, active: i === cur });
      } else if (Math.abs(i - cur) === 2) {
        pages.push({ label: "…", page: null });
      }
    }

    if (cur < last)
      pages.push({
        label: '<i class="fas fa-chevron-right"></i>',
        page: cur + 1,
      });

    pag.innerHTML = pages
      .map((p) =>
        p.page === null
          ? `<span class="rv-pag-ellipsis">…</span>`
          : `<button class="rv-pag-btn ${p.active ? "rv-pag-active" : ""}"
                onclick="rvGoPage(${p.page})">${p.label}</button>`,
      )
      .join("");
  }

  window.rvGoPage = function (page) {
    loadReviews(page);
    // Scroll to reviews tab
    const root = document.getElementById("reviews-root");
    if (root) root.scrollIntoView({ behavior: "smooth", block: "start" });
  };

  /* ── Lightbox ──────────────────────────────────────────────────────────── */
  window.rvLightbox = function (url) {
    const overlay = document.createElement("div");
    overlay.className = "rv-lightbox";
    overlay.innerHTML = `
      <div class="rv-lightbox-inner" onclick="event.stopPropagation()">
        <img src="${url}" alt="preview">
        <button class="rv-lightbox-close" onclick="this.closest('.rv-lightbox').remove()">
          <i class="fas fa-times"></i>
        </button>
      </div>`;
    overlay.onclick = () => overlay.remove();
    document.body.appendChild(overlay);
  };

  /* ── Toast ─────────────────────────────────────────────────────────────── */
  function showToast(msg, type = "info") {
    let wrap = document.getElementById("rv-toast-wrap");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = "rv-toast-wrap";
      wrap.style.cssText =
        "position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:8px;";
      document.body.appendChild(wrap);
    }
    const t = document.createElement("div");
    t.className = `rv-toast rv-toast-${type}`;
    const icons = {
      success: "check-circle",
      error: "exclamation-circle",
      warn: "exclamation-triangle",
      info: "info-circle",
    };
    t.innerHTML = `<i class="fas fa-${icons[type] || "info-circle"}"></i> ${msg}`;
    wrap.appendChild(t);
    requestAnimationFrame(() => t.classList.add("rv-toast-in"));
    setTimeout(() => {
      t.classList.remove("rv-toast-in");
      t.classList.add("rv-toast-out");
      setTimeout(() => t.remove(), 300);
    }, 3500);
  }

  /* ── Utils ─────────────────────────────────────────────────────────────── */
  function esc(str) {
    return String(str || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  /* ── Boot ──────────────────────────────────────────────────────────────── */
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
