"use strict";

// ── State ──────────────────────────────────────────────────────────────────
let _convId = CHAT_ACTIVE_CONV_ID;
let _pendingRoom = CHAT_PENDING_ROOM; // {room_id, landlord_id, title, price, image} | null
let _oldestMsgId = null;
let _latestMsgId = 0;
let _hasMore = true;
let _loading = false;
let _sending = false;
let _sseSource = null;
let _pendingFiles = [];

// ── Init ───────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  if (_convId) {
    loadMessages(_convId, true); // startSSE gọi bên trong sau khi load xong
  } else if (_pendingRoom) {
    document.getElementById("msgSkeleton")?.remove();
    renderEmptyChat();
    setTimeout(() => document.getElementById("msgInput")?.focus(), 80);
  }

  const updateBackBtn = () => {
    const back = document.querySelector(".mob-back-btn");
    if (back) back.style.display = window.innerWidth <= 768 ? "flex" : "none";
  };
  updateBackBtn();
  window.addEventListener("resize", updateBackBtn);

  document.getElementById("msgInput")?.addEventListener("paste", handlePaste);

  window.addEventListener("popstate", (e) => {
    const convId = e.state?.convId ?? null;
    if (convId && convId !== _convId) {
      const item = document.querySelector(
        `.conv-item[data-conv-id="${convId}"]`,
      );
      if (item) selectConv(item, convId);
    } else if (!convId) {
      showEmptyState();
    }
  });
});

// ── Conversation selection ─────────────────────────────────────────────────
async function selectConv(el, convId) {
  if (_convId === convId) {
    if (window.innerWidth <= 768) hideSidebar();
    return;
  }

  // Highlight ngay lập tức
  document.querySelectorAll(".conv-item").forEach((i) => {
    i.classList.remove("active");
    if (i.dataset.convId == convId) i.querySelector(".conv-badge")?.remove();
  });
  el.classList.add("active");

  history.pushState({ convId }, "", "/chat/" + convId);

  _convId = convId;
  _pendingRoom = null;
  _oldestMsgId = null;
  _latestMsgId = 0;
  _hasMore = true;
  _pendingFiles = [];
  renderFilePreview();

  _sseSource?.close();
  _sseSource = null;

  // Render shell ngay với avatar + tên từ sidebar item
  _renderConvShell(el);

  await loadMessages(convId, true);
}

function showEmptyState() {
  _convId = null;
  _sseSource?.close();
  _sseSource = null;
  document
    .querySelectorAll(".conv-item")
    .forEach((i) => i.classList.remove("active"));
  history.pushState({}, "", "/chat");
  const main = document.getElementById("chatMain");
  if (!main) return;
  main.innerHTML = `
    <div class="chat-empty-state">
      <div class="big-icon">💬</div>
      <h3>Chọn cuộc hội thoại</h3>
      <p>Chọn một cuộc hội thoại bên trái,<br>hoặc tìm phòng và bắt đầu nhắn tin.</p>
      <a href="/search" style="margin-top:8px;display:inline-flex;align-items:center;gap:8px;
         background:#5b63f5;color:#fff;padding:11px 24px;border-radius:12px;
         font-weight:700;font-size:14px;text-decoration:none;">
        <i class="fas fa-search"></i> Tìm phòng trọ
      </a>
    </div>`;
}

/**
 * Render shell chat-main ngay lập tức dựa vào data trong sidebar item.
 * Đọc avatar + tên từ .conv-avatar-img và .conv-name trong item được click.
 */
function _renderConvShell(convItem) {
  const name =
    convItem.querySelector(".conv-name")?.textContent?.trim() || "Người dùng";
  const avatarEl = convItem.querySelector(".conv-avatar-img");
  const isImg = avatarEl?.tagName === "IMG";
  const avatarHtml = isImg
    ? `<img class="ch-avatar-img" src="${esc(avatarEl.src)}" referrerpolicy="no-referrer" alt="">`
    : `<div class="ch-avatar-img">${esc(avatarEl?.textContent?.trim() || name.charAt(0).toUpperCase())}</div>`;

  const isMob = window.innerWidth <= 768;
  const main = document.getElementById("chatMain");
  if (!main) return;

  main.innerHTML = `
    <div class="chat-header" id="chatHeader">
      <div class="ch-info">
        <button class="ch-btn mob-back-btn" onclick="showSidebar()" title="Quay lại"
                style="display:${isMob ? "flex" : "none"}">
          <i class="fas fa-arrow-left"></i>
        </button>
        <div class="ch-avatar">${avatarHtml}</div>
        <div>
          <div class="ch-name">${esc(name)}</div>
          <div class="ch-status" id="headerStatus">Đang hoạt động</div>
        </div>
      </div>
      <div class="ch-actions">
        <button class="ch-btn" onclick="chatToast('Chức năng đang phát triển')">
          <i class="fas fa-search"></i>
        </button>
        <button class="ch-btn" onclick="chatToast('Chức năng đang phát triển')">
          <i class="fas fa-info-circle"></i>
        </button>
      </div>
    </div>

    <div class="chat-messages" id="chatMessages">
      <div class="load-more-wrap" id="loadMoreWrap" style="display:none;">
        <button class="load-more-btn" onclick="loadMoreMessages()">
          <i class="fas fa-chevron-up" style="margin-right:6px;"></i> Tải thêm tin nhắn cũ
        </button>
      </div>
      <div id="msgSkeleton" style="display:flex;flex-direction:column;gap:16px;padding:10px 0;">
        ${[160, 220, 130, 195, 100]
          .map(
            (w, i) => `
          <div style="display:flex;gap:10px;align-items:flex-end;${i % 2 === 0 ? "" : "flex-direction:row-reverse;"}">
            <div style="width:28px;height:28px;border-radius:50%;background:#e5e7eb;flex-shrink:0;"></div>
            <div style="height:42px;background:#e5e7eb;border-radius:14px;width:${w}px;animation:pulse 1.5s infinite;"></div>
          </div>`,
          )
          .join("")}
      </div>
    </div>

    <div class="typing-indicator" id="typingIndicator" style="padding:0 24px 6px;display:none;"></div>

    <div class="chat-input-area">
      <div class="file-preview-strip" id="filePreviewStrip"></div>
      <div class="chat-input-row">
        <div class="ci-left-btns">
          <label class="ci-icon-btn" title="Gửi file">
            <i class="fas fa-paperclip"></i>
            <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.zip,.txt,image/*"
                   style="display:none;" onchange="handleFileSelect(this)">
          </label>
          <label class="ci-icon-btn" title="Gửi ảnh">
            <i class="fas fa-image"></i>
            <input type="file" id="imageInput" multiple accept="image/*"
                   style="display:none;" onchange="handleFileSelect(this)">
          </label>
        </div>
        <div class="ci-textarea-wrap">
          <textarea class="ci-textarea" id="msgInput" placeholder="Nhắn tin..." rows="1"
                    onkeydown="handleMsgKeydown(event)" oninput="autoResize(this)"></textarea>
        </div>
        <button class="ci-send-btn" id="sendBtn" onclick="sendMessage()" title="Gửi (Enter)">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    </div>`;

  document.getElementById("msgInput")?.addEventListener("paste", handlePaste);
  if (isMob)
    document.getElementById("chatSidebar")?.classList.remove("mob-open");
  setTimeout(() => document.getElementById("msgInput")?.focus(), 50);
}

// ── Load messages ──────────────────────────────────────────────────────────
async function loadMessages(convId, initial = false) {
  if (_loading) return;
  _loading = true;

  try {
    const params = new URLSearchParams({ limit: 40 });
    if (!initial && _oldestMsgId) params.set("before_id", _oldestMsgId);

    const res = await fetch(`/api/chat/${convId}/messages?${params}`);
    if (!res.ok) throw new Error("HTTP " + res.status);
    const data = await res.json();
    const msgs = data.data || [];

    if (initial) document.getElementById("msgSkeleton")?.remove();

    const lmw = document.getElementById("loadMoreWrap");

    if (msgs.length === 0) {
      _hasMore = false;
      if (lmw) lmw.style.display = "none";
      if (initial) {
        renderEmptyChat();
        startSSE(convId, 0);
      }
      return;
    }

    _hasMore = msgs.length >= 40;
    if (lmw) lmw.style.display = _hasMore ? "block" : "none";

    if (initial) {
      renderMessages(msgs, true);
      scrollToBottom(true);
      _latestMsgId = msgs[msgs.length - 1]?.message_id ?? 0;
      _oldestMsgId = msgs[0].message_id;
      startSSE(convId, _latestMsgId);
    } else {
      const container = document.getElementById("chatMessages");
      const scrollBottom = container.scrollHeight - container.scrollTop;
      renderMessages(msgs, false);
      container.scrollTop = container.scrollHeight - scrollBottom;
      if (msgs.length > 0) _oldestMsgId = msgs[0].message_id;
    }

    // fire-and-forget markRead
    fetch(`/api/chat/${convId}/read`, { method: "POST" });
  } catch (e) {
    console.error("loadMessages error:", e);
    if (initial) document.getElementById("msgSkeleton")?.remove();
    chatToast("Không thể tải tin nhắn. Thử lại sau.");
  } finally {
    _loading = false;
  }
}

function loadMoreMessages() {
  if (_hasMore && !_loading) loadMessages(_convId, false);
}

function renderEmptyChat() {
  const container = document.getElementById("chatMessages");
  if (!container) return;
  container.innerHTML = `
    <div style="flex:1;display:flex;flex-direction:column;align-items:center;
                justify-content:center;gap:10px;color:#9ca3af;padding:3rem 0;">
      <i class="fas fa-comment-dots" style="font-size:40px;opacity:.3;"></i>
      <p style="font-size:13.5px;">Hãy bắt đầu cuộc trò chuyện!</p>
    </div>`;
}

// ── Render messages ────────────────────────────────────────────────────────
function renderMessages(msgs, append = true) {
  const container = document.getElementById("chatMessages");
  if (!container) return;

  let lastDateStr = "";
  const fragment = document.createDocumentFragment();

  msgs.forEach((msg) => {
    const dateStr = formatDateSep(msg.created_at);
    if (dateStr !== lastDateStr) {
      lastDateStr = dateStr;
      const sep = document.createElement("div");
      sep.className = "msg-date-sep";
      sep.innerHTML = `<span>${esc(dateStr)}</span>`;
      fragment.appendChild(sep);
    }
    fragment.appendChild(buildMsgEl(msg));
    _latestMsgId = Math.max(_latestMsgId, msg.message_id);
  });

  if (append) {
    const lmw = document.getElementById("loadMoreWrap");
    container.appendChild(fragment);
    if (lmw) container.insertBefore(lmw, container.firstChild);
  } else {
    const ref = container.children[1] || null;
    [...fragment.childNodes]
      .reverse()
      .forEach((n) => container.insertBefore(n, ref));
  }
}

function buildMsgEl(msg) {
  const isMine = msg.sender_id === CHAT_ME.user_id;
  const group = document.createElement("div");
  group.className = `msg-group ${isMine ? "own" : "other"}`;
  group.dataset.msgId = msg.message_id;

  let bubbleHtml = "";
  if (msg.recalled) {
    bubbleHtml = `<div class="msg-bubble recalled"><i class="fas fa-ban" style="margin-right:5px;"></i>Tin nhắn đã bị thu hồi</div>`;
  } else if (
    msg.msg_type === "system" &&
    msg.system_data?.type === "room_card"
  ) {
    bubbleHtml = buildRoomCard(msg.system_data.room);
  } else if (msg.msg_type === "image") {
    bubbleHtml = buildImgBubble(msg.attachments || []);
  } else if (msg.msg_type === "file") {
    bubbleHtml = buildFileBubble(msg.attachments || []);
  } else {
    bubbleHtml = `<div class="msg-bubble">${linkify(esc(msg.content || ""))}${buildCtxBtn(isMine, msg.message_id)}</div>`;
  }

  const timeStr = formatTime(msg.created_at);
  const readTick = isMine
    ? `<span class="msg-read-tick">${msg.is_read ? '<i class="fas fa-check-double"></i>' : '<i class="fas fa-check"></i>'}</span>`
    : "";
  const avatarHtml = !isMine
    ? `<div class="msg-mini-avatar">${getInitial(msg.sender_name)}</div>`
    : "";

  group.innerHTML = `
    <div class="msg-sender-row ${isMine ? "own" : ""}">
      ${!isMine ? avatarHtml : ""}
      <div style="display:flex;flex-direction:column;gap:2px;align-items:${isMine ? "flex-end" : "flex-start"};">
        ${bubbleHtml}
        <div class="msg-time">${readTick}${esc(timeStr)}</div>
      </div>
      ${isMine ? avatarHtml : ""}
    </div>`;
  return group;
}

function buildRoomCard(room) {
  if (!room) return "";
  const price = Number(room.price || 0).toLocaleString("vi-VN");
  const img = room.image
    ? `<img src="${esc(room.image)}" onerror="this.style.display='none'">`
    : `<div style="width:52px;height:52px;border-radius:10px;background:#c7d2fe;display:flex;align-items:center;justify-content:center;">
         <i class="fas fa-home" style="color:#6366f1;font-size:18px;"></i></div>`;
  return `
    <a class="msg-room-card" href="/room/${esc(room.room_id)}" target="_blank" onclick="event.stopPropagation()">
      ${img}
      <div class="mrc-info">
        <div class="mrc-label">🏠 Phòng trọ</div>
        <div class="mrc-title">${esc(room.title || "")}</div>
        <div class="mrc-price">${price} đ/tháng</div>
        <div class="mrc-loc"><i class="fas fa-location-dot" style="margin-right:3px;"></i>${esc(room.district || room.city || "")}</div>
      </div>
      <i class="fas fa-chevron-right" style="font-size:11px;color:#9ca3af;margin-left:auto;flex-shrink:0;"></i>
    </a>`;
}

function buildImgBubble(attachments) {
  const n = Math.min(attachments.length, 6);
  const cols = n === 1 ? "cols-1" : n === 2 ? "cols-2" : "cols-3";
  const imgs = attachments
    .slice(0, n)
    .map((a) => {
      const url = esc(a.url || a.file_url || "");
      return `<img src="${url}" loading="lazy" onclick="openLightbox('${url}')" onerror="this.parentElement.style.display='none'">`;
    })
    .join("");
  return `<div class="msg-img-grid ${cols}">${imgs}</div>`;
}

function buildFileBubble(attachments) {
  return attachments
    .map((a) => {
      const icon = fileIcon(a.type || a.file_type || "");
      const size = formatBytes(a.size || a.file_size || 0);
      const url = esc(a.url || a.file_url || "#");
      return `
      <a class="msg-file-card" href="${url}" download target="_blank">
        <div class="msg-file-icon"><i class="${icon}"></i></div>
        <div class="msg-file-info">
          <div class="msg-file-name">${esc(a.name || a.file_name || "file")}</div>
          <div class="msg-file-size">${size}</div>
        </div>
        <i class="fas fa-download" style="margin-left:auto;font-size:13px;opacity:.6;"></i>
      </a>`;
    })
    .join("");
}

function buildCtxBtn(isMine, msgId) {
  if (!isMine) return "";
  return `<button class="msg-ctx-btn" onclick="recallMsg(${msgId},event)" title="Thu hồi">
    <i class="fas fa-undo" style="margin-right:4px;"></i>Thu hồi</button>`;
}

// ── Send message ───────────────────────────────────────────────────────────
async function sendMessage() {
  if (_sending) return;

  const input = document.getElementById("msgInput");
  const text = (input?.value || "").trim();

  if (_pendingFiles.length > 0) {
    await sendFiles();
    return;
  }
  if (!text) return;

  // Nếu chưa có convId → tạo conversation trước
  if (!_convId) {
    if (!_pendingRoom?.room_id) {
      chatToast("Không xác định được cuộc hội thoại.");
      return;
    }
    _sending = true;
    try {
      const res = await fetch("/api/chat/open-room", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ room_id: _pendingRoom.room_id }),
      });
      const data = await res.json();
      if (!data.conversation_id) {
        chatToast(data.error || "Không thể tạo cuộc hội thoại.");
        return;
      }
      _convId = data.conversation_id;

      // Upgrade sidebar item với tên + avatar chủ nhà (API trả về)
      _upgradePendingConvItem(
        _convId,
        _pendingRoom,
        data.landlord_name || null,
        data.landlord_avatar || null,
      );

      _pendingRoom = null;
      history.replaceState({ convId: _convId }, "", "/chat/" + _convId);
      startSSE(_convId, 0);
    } catch {
      chatToast("Lỗi kết nối. Thử lại sau.");
      return;
    } finally {
      _sending = false;
    }
  }

  input.value = "";
  autoResize(input);

  const tempId = "tmp_" + Date.now();
  appendOptimistic(tempId, text);
  scrollToBottom();

  _sending = true;
  try {
    const res = await fetch(`/api/chat/${_convId}/messages`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ text }),
    });
    const data = await res.json();
    const tmpEl = document.querySelector(`[data-temp-id="${tempId}"]`);
    if (data.data && tmpEl) {
      tmpEl.replaceWith(buildMsgEl(data.data));
      _latestMsgId = Math.max(_latestMsgId, data.data.message_id ?? 0);
      updateSidebarPreview(_convId, data.data);
    } else if (tmpEl) {
      tmpEl.style.opacity = ".4";
      chatToast(data.error || "Gửi thất bại.");
    }
  } catch {
    chatToast("Gửi thất bại. Vui lòng thử lại.");
    const tmpEl = document.querySelector(`[data-temp-id="${tempId}"]`);
    if (tmpEl) {
      tmpEl.remove();
      input.value = text;
    }
  } finally {
    _sending = false;
  }
}

/**
 * Nâng conv item ảo (pending) thành item thật sau khi tạo conv.
 * Ưu tiên: avatar chủ nhà > ảnh phòng > chữ cái đầu tên.
 */
function _upgradePendingConvItem(convId, room, landlordName, landlordAvatar) {
  const list = document.getElementById("convList");
  const pendingEl = document.getElementById("pendingConvItem");

  const displayName = landlordName || room.title || "Chủ nhà";

  let avatarHtml;
  if (landlordAvatar) {
    avatarHtml = `<img class="conv-avatar-img" src="${esc(landlordAvatar)}"
                       referrerpolicy="no-referrer" alt="${esc(displayName)}">`;
  } else if (room.image) {
    const initial = esc(displayName.charAt(0).toUpperCase());
    avatarHtml = `<img class="conv-avatar-img" src="${esc(room.image)}"
                       referrerpolicy="no-referrer" alt=""
                       onerror="this.outerHTML='<div class=\\'conv-avatar-img\\'>${initial}</div>'">`;
  } else {
    avatarHtml = `<div class="conv-avatar-img">${esc(displayName.charAt(0).toUpperCase())}</div>`;
  }

  const innerHtml = `
    <div class="conv-avatar">${avatarHtml}</div>
    <div class="conv-body">
      <div class="conv-name">${esc(displayName)}</div>
      <div class="conv-preview">Đang gửi...</div>
    </div>
    <div class="conv-meta">
      <div class="conv-time">vừa xong</div>
    </div>`;

  if (pendingEl) {
    pendingEl.id = "";
    pendingEl.dataset.convId = convId;
    delete pendingEl.dataset.pending;
    pendingEl.onclick = function () {
      selectConv(this, convId);
    };
    pendingEl.innerHTML = innerHtml;
  } else {
    const el = document.createElement("div");
    el.className = "conv-item active";
    el.dataset.convId = convId;
    el.innerHTML = innerHtml;
    el.onclick = function () {
      selectConv(this, convId);
    };
    list?.insertBefore(el, list.firstChild);
  }

  list?.querySelector(".chs-empty")?.remove();
}

function appendOptimistic(tempId, text) {
  const container = document.getElementById("chatMessages");
  const group = document.createElement("div");
  group.className = "msg-group own";
  group.dataset.tempId = tempId;
  group.innerHTML = `
    <div class="msg-sender-row own">
      <div style="display:flex;flex-direction:column;gap:2px;align-items:flex-end;">
        <div class="msg-bubble" style="opacity:.8;">${linkify(esc(text))}</div>
        <div class="msg-time"><i class="fas fa-clock" style="font-size:10px;color:#d1d5db;"></i></div>
      </div>
    </div>`;
  container.appendChild(group);
}

// ── File handling ──────────────────────────────────────────────────────────
function handleFileSelect(input) {
  if (!input.files?.length) return;
  Array.from(input.files).slice(0, 5).forEach(addPendingFile);
  input.value = "";
}

function addPendingFile(file) {
  if (_pendingFiles.length >= 5) {
    chatToast("Tối đa 5 file mỗi lần gửi");
    return;
  }
  const isImage = file.type.startsWith("image/");
  const entry = { file, type: isImage ? "image" : "file", dataUrl: null };
  _pendingFiles.push(entry);
  if (isImage) {
    const reader = new FileReader();
    reader.onload = (e) => {
      entry.dataUrl = e.target.result;
      renderFilePreview();
    };
    reader.readAsDataURL(file);
  } else {
    renderFilePreview();
  }
}

function renderFilePreview() {
  const strip = document.getElementById("filePreviewStrip");
  if (!strip) return;
  strip.className = `file-preview-strip ${_pendingFiles.length > 0 ? "has-files" : ""}`;
  strip.innerHTML = "";
  _pendingFiles.forEach((entry, i) => {
    const item = document.createElement("div");
    item.className = "fp-item";
    let thumbHtml = "";
    if (entry.type === "image" && entry.dataUrl) {
      thumbHtml = `<div class="fp-thumb"><img src="${esc(entry.dataUrl)}" alt=""></div>`;
    } else {
      const icon = fileIcon(entry.file.name.split(".").pop() || "");
      const ext = entry.file.name.split(".").pop().toUpperCase();
      thumbHtml = `<div class="fp-thumb file-type"><i class="${icon}"></i><span>${esc(ext)}</span></div>`;
    }
    item.innerHTML = `${thumbHtml}<button class="fp-remove" onclick="removeFile(${i})" title="Xóa">×</button>`;
    strip.appendChild(item);
  });
}

function removeFile(idx) {
  _pendingFiles.splice(idx, 1);
  renderFilePreview();
}

async function sendFiles() {
  if (!_pendingFiles.length) return;

  if (!_convId) {
    if (!_pendingRoom?.room_id) {
      chatToast("Không xác định được cuộc hội thoại.");
      return;
    }
    try {
      const res = await fetch("/api/chat/open-room", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ room_id: _pendingRoom.room_id }),
      });
      const data = await res.json();
      if (!data.conversation_id) {
        chatToast(data.error || "Không thể tạo cuộc hội thoại.");
        return;
      }
      _convId = data.conversation_id;
      _upgradePendingConvItem(
        _convId,
        _pendingRoom,
        data.landlord_name || null,
        data.landlord_avatar || null,
      );
      _pendingRoom = null;
      history.replaceState({ convId: _convId }, "", "/chat/" + _convId);
      startSSE(_convId, 0);
    } catch {
      chatToast("Lỗi kết nối. Thử lại sau.");
      return;
    }
  }

  const toSend = [..._pendingFiles];
  _pendingFiles = [];
  renderFilePreview();

  const formData = new FormData();
  toSend.forEach((e) => formData.append("files[]", e.file));

  const tempId = "tmp_" + Date.now();
  const container = document.getElementById("chatMessages");
  const tmpEl = document.createElement("div");
  tmpEl.className = "msg-group own";
  tmpEl.dataset.tempId = tempId;
  const isAllImgs = toSend.every((e) => e.type === "image");
  tmpEl.innerHTML = `
    <div class="msg-sender-row own">
      <div class="msg-bubble" style="opacity:.6;display:flex;gap:6px;align-items:center;">
        <i class="fas fa-spinner fa-spin"></i>
        Đang gửi ${toSend.length} ${isAllImgs ? "ảnh" : "file"}...
      </div>
    </div>`;
  container.appendChild(tmpEl);
  scrollToBottom();

  try {
    const res = await fetch(`/api/chat/${_convId}/upload`, {
      method: "POST",
      body: formData,
    });
    const data = await res.json();
    const tmp = document.querySelector(`[data-temp-id="${tempId}"]`);
    if (data.data) {
      tmp?.replaceWith(buildMsgEl(data.data));
      _latestMsgId = Math.max(_latestMsgId, data.data.message_id ?? 0);
      updateSidebarPreview(_convId, data.data);
    } else {
      tmp?.remove();
      chatToast(data.error || "Gửi file thất bại");
    }
  } catch {
    document.querySelector(`[data-temp-id="${tempId}"]`)?.remove();
    chatToast("Gửi file thất bại. Thử lại sau.");
  }
}

function handlePaste(e) {
  const items = e.clipboardData?.items;
  if (!items) return;
  for (const item of items) {
    if (item.type.startsWith("image/")) {
      e.preventDefault();
      const file = item.getAsFile();
      if (file) addPendingFile(file);
    }
  }
}

// ── Recall ─────────────────────────────────────────────────────────────────
async function recallMsg(msgId, event) {
  event.stopPropagation();
  if (!confirm("Thu hồi tin nhắn này?")) return;
  try {
    const res = await fetch(`/api/chat/${_convId}/recall/${msgId}`, {
      method: "POST",
    });
    const data = await res.json();
    if (data.success) {
      const bubble = document.querySelector(
        `.msg-group[data-msg-id="${msgId}"] .msg-bubble`,
      );
      if (bubble) {
        bubble.className = "msg-bubble recalled";
        bubble.innerHTML =
          '<i class="fas fa-ban" style="margin-right:5px;"></i>Tin nhắn đã bị thu hồi';
      }
    } else {
      chatToast("Không thể thu hồi tin nhắn này");
    }
  } catch {
    chatToast("Lỗi mạng. Thử lại sau.");
  }
}

// ── SSE ────────────────────────────────────────────────────────────────────
function startSSE(convId, afterId) {
  _sseSource?.close();
  _sseSource = null;
  try {
    _sseSource = new EventSource(`/api/chat/${convId}/sse?after_id=${afterId}`);
  } catch {
    startPolling(convId, afterId);
    return;
  }
  _sseSource.addEventListener("message", (e) => {
    const msg = JSON.parse(e.data);
    if (msg.sender_id === CHAT_ME.user_id) return;
    appendIncomingMsg(msg);
  });
  _sseSource.addEventListener("reconnect", () => {
    _sseSource.close();
    setTimeout(() => startSSE(convId, _latestMsgId), 500);
  });
  _sseSource.onerror = () => {
    _sseSource.close();
    setTimeout(() => startSSE(convId, _latestMsgId), 3000);
  };
}

function appendIncomingMsg(msg) {
  const container = document.getElementById("chatMessages");
  if (!container) return;
  if (document.querySelector(`.msg-group[data-msg-id="${msg.message_id}"]`))
    return;
  container.appendChild(buildMsgEl(msg));
  _latestMsgId = Math.max(_latestMsgId, msg.message_id);
  scrollToBottom();
  updateSidebarPreview(_convId, msg);
  fetch(`/api/chat/${_convId}/read`, { method: "POST" });
}

function startPolling(convId, afterId) {
  let cursor = afterId;
  setInterval(async () => {
    try {
      const res = await fetch(
        `/api/chat/${convId}/messages?after_id=${cursor}&limit=20`,
      );
      const data = await res.json();
      (data.data || []).forEach((msg) => {
        if (msg.sender_id === CHAT_ME.user_id) return;
        appendIncomingMsg(msg);
        cursor = Math.max(cursor, msg.message_id);
      });
    } catch {}
  }, 3000);
}

// ── Sidebar helpers ────────────────────────────────────────────────────────
function updateSidebarPreview(convId, msg) {
  const item = document.querySelector(`.conv-item[data-conv-id="${convId}"]`);
  if (!item) return;
  const preview = item.querySelector(".conv-preview");
  const time = item.querySelector(".conv-time");
  if (preview) {
    let text = msg.content ? msg.content.substring(0, 50) : "";
    if (msg.msg_type === "image") text = "🖼 Đã gửi ảnh";
    if (msg.msg_type === "file") text = "📎 Đã gửi file";
    preview.textContent = text;
    preview.style.color = "";
  }
  if (time) {
    time.textContent = formatTime(msg.created_at);
    time.style.color = "";
  }

  // Đưa lên đầu list
  const list = document.getElementById("convList");
  if (list && item.parentNode === list)
    list.insertBefore(item, list.firstChild);
}

function filterConvs(q) {
  const lower = q.toLowerCase();
  document.querySelectorAll(".conv-item").forEach((item) => {
    const name = (
      item.querySelector(".conv-name")?.textContent || ""
    ).toLowerCase();
    const prev = (
      item.querySelector(".conv-preview")?.textContent || ""
    ).toLowerCase();
    item.style.display =
      !q || name.includes(lower) || prev.includes(lower) ? "" : "none";
  });
}

// ── UI helpers ─────────────────────────────────────────────────────────────
function handleMsgKeydown(e) {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
}
function autoResize(el) {
  el.style.height = "auto";
  el.style.height = Math.min(el.scrollHeight, 140) + "px";
}
function scrollToBottom(instant = false) {
  const c = document.getElementById("chatMessages");
  if (!c) return;
  if (instant) {
    c.scrollTop = c.scrollHeight;
    return;
  }
  requestAnimationFrame(() =>
    c.scrollTo({ top: c.scrollHeight, behavior: "smooth" }),
  );
}
function showSidebar() {
  document.getElementById("chatSidebar")?.classList.add("mob-open");
}
function hideSidebar() {
  document.getElementById("chatSidebar")?.classList.remove("mob-open");
}

function openLightbox(src) {
  const lb = document.getElementById("chatLightbox");
  const img = document.getElementById("lightboxImg");
  if (!lb || !img) return;
  img.src = src;
  lb.classList.add("open");
  document.body.style.overflow = "hidden";
}
function closeLightbox() {
  document.getElementById("chatLightbox")?.classList.remove("open");
  document.body.style.overflow = "";
}
function chatToast(msg, duration = 2800) {
  const el = document.getElementById("chatToast");
  if (!el) return;
  el.textContent = msg;
  el.classList.add("show");
  setTimeout(() => el.classList.remove("show"), duration);
}

// ── Format helpers ─────────────────────────────────────────────────────────
function formatTime(dt) {
  if (!dt) return "";
  const d = new Date(dt.replace(" ", "T"));
  return (
    d.getHours().toString().padStart(2, "0") +
    ":" +
    d.getMinutes().toString().padStart(2, "0")
  );
}
function formatDateSep(dt) {
  if (!dt) return "";
  const d = new Date(dt.replace(" ", "T"));
  const diffDays = Math.floor((Date.now() - d.getTime()) / 86400000);
  if (diffDays === 0) return "Hôm nay";
  if (diffDays === 1) return "Hôm qua";
  const days = [
    "Chủ nhật",
    "Thứ hai",
    "Thứ ba",
    "Thứ tư",
    "Thứ năm",
    "Thứ sáu",
    "Thứ bảy",
  ];
  if (diffDays < 7) return days[d.getDay()];
  return `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
}
function formatBytes(n) {
  if (!n) return "";
  if (n < 1024) return n + " B";
  if (n < 1048576) return (n / 1024).toFixed(1) + " KB";
  return (n / 1048576).toFixed(1) + " MB";
}
function fileIcon(ext) {
  const e = (ext || "").toLowerCase();
  if (["jpg", "jpeg", "png", "gif", "webp", "svg"].includes(e))
    return "fas fa-image";
  if (e === "pdf") return "fas fa-file-pdf";
  if (["doc", "docx"].includes(e)) return "fas fa-file-word";
  if (["xls", "xlsx"].includes(e)) return "fas fa-file-excel";
  if (["zip", "rar", "7z"].includes(e)) return "fas fa-file-archive";
  return "fas fa-file";
}
function getInitial(name) {
  return (name || "U").charAt(0).toUpperCase();
}
function esc(s) {
  return String(s ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
function linkify(text) {
  return text.replace(
    /(https?:\/\/[^\s<>"]+)/g,
    '<a href="$1" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline;opacity:.85;">$1</a>',
  );
}
