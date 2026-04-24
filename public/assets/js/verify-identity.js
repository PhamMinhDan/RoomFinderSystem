const uploadState = {
  front: { url: "", uploading: false },
  back: { url: "", uploading: false },
  selfie: { url: "", uploading: false },
};
let cameraStream = null;
let _stateLocked = false;

// ── Init ─────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  checkIdentityStatus();

  document.querySelectorAll('input[name="document_type"]').forEach((radio) => {
    radio.addEventListener("change", () => {
      document
        .querySelectorAll(".doc-type-card")
        .forEach((c) => c.classList.remove("selected"));
      radio.nextElementSibling.classList.add("selected");
      validateForm();
    });
  });

  document
    .getElementById("phoneNumber")
    ?.addEventListener("input", validateForm);
});

// ── Check status từ server ────────────────────────────────────────────────
async function checkIdentityStatus() {
  if (_stateLocked) return;
  try {
    const res = await fetch("/api/identity/status");
    const data = await res.json();
    if (_stateLocked) return;
    const s = data.data;
    if (!s) {
      showForm();
      return;
    }
    if (s.status === "pending") {
      showPending();
      return;
    }
    if (s.status === "approved") {
      showApproved();
      return;
    }
    if (s.status === "rejected") {
      showRejected(s.reject_reason || "Không có lý do cụ thể");
      return;
    }
    showForm();
  } catch (e) {
    if (!_stateLocked) showForm();
  }
}

// ── State helpers ─────────────────────────────────────────────────────────
function showForm() {
  setVisible("stateForm");
}
function showPending() {
  setVisible("statePending");
}
function showApproved() {
  setVisible("stateApproved");
}
function showRejected(reason) {
  const el = document.getElementById("rejectedReasonJs");
  if (el) {
    el.textContent = "Lý do: " + reason;
    el.style.display = "";
  }
  setVisible("stateRejected");
}

function setVisible(id) {
  ["stateForm", "statePending", "stateApproved", "stateRejected"].forEach(
    (s) => {
      const el = document.getElementById(s);
      if (el) el.classList.toggle("hidden", s !== id);
    },
  );
}

// ── Upload ảnh giấy tờ ────────────────────────────────────────────────────
function triggerUpload(inputId, side) {
  if (uploadState[side].uploading) return;
  document.getElementById(inputId).click();
}

async function handleDocUpload(event, side) {
  const file = event.target.files[0];
  if (!file) return;

  const previewEl = document.getElementById(side + "Preview");
  const placeholderEl = document.getElementById(side + "Placeholder");
  const imgEl = document.getElementById(side + "Img");
  const statusEl = document.getElementById(side + "Status");
  const urlInput = document.getElementById(side + "Url");
  const errorEl = document.getElementById(side + "Error");

  // Local preview
  imgEl.src = URL.createObjectURL(file);
  previewEl.style.display = "";
  placeholderEl.style.display = "none";
  statusEl.className = "upload-status uploading";
  statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tải lên...';
  statusEl.style.display = "";

  uploadState[side].uploading = true;
  uploadState[side].url = "";
  urlInput.value = "";
  validateForm();

  try {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("secureId", `identity_${side}_${Date.now()}`);

    const res = await fetch("/api/upload", { method: "POST", body: formData });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || "Upload thất bại");

    uploadState[side].url = data.url;
    urlInput.value = data.url;
    statusEl.className = "upload-status done";
    statusEl.innerHTML =
      '<i class="fas fa-circle-check"></i> Tải lên thành công';
    errorEl.textContent = "";
    setTimeout(() => {
      statusEl.style.display = "none";
    }, 2500);
  } catch (err) {
    statusEl.style.display = "none";
    previewEl.style.display = "none";
    placeholderEl.style.display = "";
    errorEl.textContent = err.message;
    showToast(err.message, "error");
  } finally {
    uploadState[side].uploading = false;
    validateForm();
  }
}

// ── Camera selfie ──────────────────────────────────────────────────────────
async function startCamera() {
  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: "user" },
    });
    const video = document.getElementById("cameraVideo");
    video.srcObject = cameraStream;
    document.getElementById("cameraWrap").style.display = "";
    document.getElementById("selfiePreview").style.display = "none";
    document.getElementById("selfiePlaceholder").style.display = "none";
    document.getElementById("btnCamera").style.display = "none";
    document.getElementById("btnCapture").style.display = "";
    document.getElementById("btnRetake").style.display = "none";
  } catch (e) {
    showToast("Không thể truy cập camera. Vui lòng cấp quyền.", "error");
  }
}

async function capturePhoto() {
  const video = document.getElementById("cameraVideo");
  const canvas = document.getElementById("cameraCanvas");
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext("2d").drawImage(video, 0, 0);

  if (cameraStream) cameraStream.getTracks().forEach((t) => t.stop());

  canvas.toBlob(
    async (blob) => {
      const selfieFile = new File([blob], `selfie_${Date.now()}.jpg`, {
        type: "image/jpeg",
      });
      const selfiePreview = document.getElementById("selfiePreview");
      const selfieImg = document.getElementById("selfieImg");
      const selfieStatus = document.getElementById("selfieStatus");
      const selfieUrl = document.getElementById("selfieUrl");
      const selfieError = document.getElementById("selfieError");

      selfieImg.src = URL.createObjectURL(blob);
      selfiePreview.style.display = "";
      document.getElementById("cameraWrap").style.display = "none";
      document.getElementById("btnCapture").style.display = "none";
      document.getElementById("btnRetake").style.display = "";
      selfieStatus.style.display = "";
      selfieStatus.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Đang tải lên...';

      uploadState.selfie.uploading = true;
      validateForm();

      try {
        const formData = new FormData();
        formData.append("file", selfieFile);
        formData.append("secureId", `selfie_${Date.now()}`);
        const res = await fetch("/api/upload", {
          method: "POST",
          body: formData,
        });
        const data = await res.json();
        if (!res.ok || data.error)
          throw new Error(data.error || "Upload thất bại");

        uploadState.selfie.url = data.url;
        selfieUrl.value = data.url;
        selfieStatus.innerHTML =
          '<i class="fas fa-circle-check"></i> Tải lên thành công';
        selfieError.textContent = "";
        setTimeout(() => {
          selfieStatus.style.display = "none";
        }, 2500);
      } catch (err) {
        selfieStatus.innerHTML = `<i class="fas fa-circle-xmark"></i> ${err.message}`;
        selfieError.textContent = err.message;
      } finally {
        uploadState.selfie.uploading = false;
        validateForm();
      }
    },
    "image/jpeg",
    0.85,
  );
}

function retakePhoto() {
  document.getElementById("selfiePreview").style.display = "none";
  document.getElementById("cameraWrap").style.display = "none";
  document.getElementById("selfiePlaceholder").style.display = "";
  document.getElementById("btnCamera").style.display = "";
  document.getElementById("btnCapture").style.display = "none";
  document.getElementById("btnRetake").style.display = "none";
  document.getElementById("selfieUrl").value = "";
  uploadState.selfie.url = "";
  validateForm();
}

// ── Validate form ─────────────────────────────────────────────────────────
function validateForm() {
  const phone = document.getElementById("phoneNumber")?.value?.trim();
  const docType = document.querySelector('input[name="document_type"]:checked');
  const frontOk = !!uploadState.front.url;
  const backOk = !!uploadState.back.url;
  const selfieOk = !!uploadState.selfie.url;
  const anyLoading = Object.values(uploadState).some((s) => s.uploading);

  document.getElementById("btnSubmit").disabled = !(
    phone &&
    docType &&
    frontOk &&
    backOk &&
    selfieOk &&
    !anyLoading
  );
}

// ── Submit ────────────────────────────────────────────────────────────────
async function submitVerify(event) {
  event.preventDefault();

  const phone = document.getElementById("phoneNumber").value.trim();
  const docType = document.querySelector(
    'input[name="document_type"]:checked',
  )?.value;
  const frontUrl = document.getElementById("frontUrl").value;
  const backUrl = document.getElementById("backUrl").value;
  const selfieUrl = document.getElementById("selfieUrl").value;

  if (!phone || !docType || !frontUrl || !backUrl || !selfieUrl) {
    showToast("Vui lòng điền đầy đủ tất cả thông tin bắt buộc", "warning");
    return;
  }

  const btn = document.getElementById("btnSubmit");
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';

  try {
    const body = new URLSearchParams({
      phone_number: phone,
      document_type: docType,
      front_image_url: frontUrl,
      back_image_url: backUrl,
      selfie_image_url: selfieUrl,
    });
    const res = await fetch("/api/identity/submit", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body,
    });
    const data = await res.json();
    if (!res.ok || data.error)
      throw new Error(data.error || "Gửi yêu cầu thất bại");

    _stateLocked = true;
    showPending();
    showToast("Đã gửi yêu cầu xác thực thành công!", "success");
  } catch (err) {
    showToast(err.message, "error");
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-shield-halved"></i> Gửi yêu cầu xác thực';
  }
}

// ── Unlock phone (nếu user muốn thay số đã có sẵn) ───────────────────────
function unlockPhone(e) {
  e.preventDefault();
  const input = document.getElementById("phoneNumber");
  input.removeAttribute("readonly");
  input.classList.remove("readonly");
  input.focus();
  input.closest(".vfield-group").querySelector(".vfield-hint")?.remove();
  validateForm();
}

// ── Toast ─────────────────────────────────────────────────────────────────
function showToast(msg, type = "info", duration = 4000) {
  document.querySelector(".toast")?.remove();
  const toast = document.createElement("div");
  toast.className = `toast ${type}`;
  const icons = {
    success: "circle-check",
    error: "circle-xmark",
    warning: "triangle-exclamation",
    info: "circle-info",
  };
  toast.innerHTML = `<i class="fas fa-${icons[type] || "circle-info"}"></i> ${msg}`;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transition = "opacity .4s";
    setTimeout(() => toast.remove(), 400);
  }, duration);
}
