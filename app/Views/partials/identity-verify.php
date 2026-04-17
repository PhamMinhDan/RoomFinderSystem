<?php
/*
 * ĐÃ CHUẨN HÓA HEADER & FOOTER - Sử dụng file chung
 * Header: public/assets/components/header.php (được include từ file này)
 * Footer: public/assets/components/footer.php (được include từ file này)
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Xác thực danh tính</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/identity-verify.css">
</head>
<body>
  <?php include __DIR__ . '/../../../public/assets/components/header.php'; ?>

<div class="verify-page">

  <!-- ── Top nav bar ──────────────────────────────────────────── -->
  <div class="verify-topbar">
    <span class="topbar-title">Xác thực danh tính</span>

    <div class="stepper">
      <div class="step-item">
        <div class="step-num active" id="step1Num">1</div>
        <span class="step-label active-label" id="step1Label">Xác thực danh tính</span>
      </div>
      <div class="step-line"></div>
      <div class="step-item">
        <div class="step-num" id="step2Num">2</div>
        <span class="step-label" id="step2Label">Thông tin phòng trọ</span>
      </div>
    </div>
  </div>

  <!-- ── Content ──────────────────────────────────────────────── -->
  <div class="content">

    <!-- ══ Đã verified ══ -->
    <div class="verified-notice hidden" id="verifiedNotice">
      <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
      </svg>
      <span>Tài khoản đã được xác minh danh tính. Bạn có thể đăng tin ngay.</span>
    </div>

    <!-- ══ Đang chờ duyệt (pending) ══ -->
    <div class="verify-card hidden" id="pendingCard">
      <div class="pending-card">
        <div class="pending-icon">
          <svg class="spin" width="32" height="32" fill="none" viewBox="0 0 24 24" style="color:#fbbf24">
            <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
        </div>
        <div>
          <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:8px;">Đang chờ xác thực</h3>
          <p style="font-size:0.875rem;color:#64748b;line-height:1.6;max-width:360px;">
            Hồ sơ của bạn đã được gửi thành công và đang chờ đội ngũ kiểm duyệt phê duyệt. Yêu cầu của bạn sẽ được xử lý trong vòng
            <strong style="color:#d97706;">24h</strong>.
          </p>
          <p style="font-size:0.72rem;color:#94a3b8;margin-top:6px;">
            Chúng tôi sẽ thông báo qua email và thông báo trên hệ thống khi có kết quả.
          </p>
        </div>
        <div class="pending-steps">
          <div class="pstep">
            <div class="pstep-dot green">
              <svg width="12" height="12" fill="white" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            </div>
            <span>Hồ sơ đã gửi thành công</span>
          </div>
          <div class="pstep active">
            <div class="pstep-dot amber"><div class="inner"></div></div>
            <span>Đang kiểm duyệt hồ sơ...</span>
          </div>
          <div class="pstep inactive">
            <div class="pstep-dot gray"><div class="inner" style="background:#94a3b8;"></div></div>
            <span>Nhận kết quả &amp; đăng tin</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ Bị từ chối ══ -->
    <div class="rejected-notice hidden" id="rejectedNotice">
      <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <div>
        <p class="rj-title">Yêu cầu xác thực bị từ chối</p>
        <p class="rj-reason" id="rejectReasonText" style="display:none;"></p>
        <p class="rj-hint">Vui lòng kiểm tra lại hồ sơ và gửi lại yêu cầu bên dưới.</p>
      </div>
    </div>

    <!-- ══ Form xác thực (none / rejected) ══ -->
    <div id="verifyForm">

      <!-- Card 1: Thông tin cá nhân -->
      <div class="verify-card">
        <div class="card-header"><h2>Xác thực thông tin cá nhân</h2></div>
        <div class="card-body">

          <!-- SĐT -->
          <div>
            <label class="field-label" for="phoneNumber">SĐT <span class="req">*</span></label>
            <input
              class="field-input"
              type="tel"
              id="phoneNumber"
              placeholder="Nhập số điện thoại của bạn"
            />
            <span class="field-error" id="phoneError">Số điện thoại không hợp lệ</span>
          </div>

          <!-- Loại giấy tờ -->
          <div>
            <label class="field-label">Loại giấy tờ <span class="req">*</span></label>
            <div class="radio-group">
              <label class="radio-option selected" id="optCCCD" onclick="selectDoc('CCCD')">
                <input type="radio" name="docType" value="CCCD" checked />
                <div class="radio-dot"></div>
                <span>Căn cước công dân / Chứng minh nhân dân</span>
              </label>
              <label class="radio-option" id="optPASSPORT" onclick="selectDoc('PASSPORT')">
                <input type="radio" name="docType" value="PASSPORT" />
                <div class="radio-dot"></div>
                <span>Hộ chiếu</span>
              </label>
            </div>
          </div>

          <!-- Upload ảnh giấy tờ -->
          <div>
            <label class="field-label">Ảnh giấy tờ <span class="req">*</span></label>
            <div class="upload-row">
              <!-- Front -->
              <div class="upload-box" id="frontBox" onclick="document.getElementById('frontInput').click()">
                <input type="file" accept="image/*" id="frontInput" onchange="handleFile(event,'front')" />
                <img class="upload-preview" id="frontPreview" src="" alt="Mặt trước" />
                <div class="upload-placeholder">
                  <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M13.5 12h.008v.008H13.5V12zm0 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM3.75 3h16.5a.75.75 0 01.75.75v16.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 20.25V3.75A.75.75 0 013.75 3z"/>
                  </svg>
                  <span>Ảnh mặt trước</span>
                </div>
              </div>
              <!-- Back -->
              <div class="upload-box" id="backBox" onclick="document.getElementById('backInput').click()">
                <input type="file" accept="image/*" id="backInput" onchange="handleFile(event,'back')" />
                <img class="upload-preview" id="backPreview" src="" alt="Mặt sau" />
                <div class="upload-placeholder">
                  <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M13.5 12h.008v.008H13.5V12zm0 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM3.75 3h16.5a.75.75 0 01.75.75v16.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 20.25V3.75A.75.75 0 013.75 3z"/>
                  </svg>
                  <span>Ảnh mặt sau</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Tips -->
          <div class="tips-box">
            <p>Hướng dẫn tải lên</p>
            <ul>
              <li><span>•</span> Đảm bảo nhìn thấy rõ 4 góc của giấy tờ</li>
              <li><span>•</span> Tránh bị lóa hoặc phản chiếu trên bề mặt thẻ</li>
              <li><span>•</span> Văn bản phải rõ ràng và dễ đọc</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Card 2: Xác thực khuôn mặt -->
      <div class="verify-card" style="margin-top:20px;">
        <div class="card-header"><h2>Xác thực khuôn mặt</h2></div>
        <div class="card-body">
          <div class="face-row">
            <!-- Camera box -->
            <div class="camera-box" id="cameraBox">
              <video id="cameraVideo" autoplay playsinline style="display:none;width:100%;height:100%;object-fit:cover;"></video>
              <img id="selfiePreview" src="" alt="Selfie" style="display:none;width:100%;height:100%;object-fit:cover;" />
              <div class="camera-placeholder" id="cameraPlaceholder" onclick="startCamera()">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                </svg>
                <span>Camera</span>
              </div>
              <button class="camera-shutter" id="shutterBtn" onclick="takeSelfie()" style="display:none;">
                <span class="shutter-inner"></span>
              </button>
            </div>

            <!-- Instructions -->
            <div class="face-instructions">
              <div class="face-instruction">
                <div class="inst-num">1</div>
                <span>Đảm bảo khuôn mặt bạn ở khu vực có ánh sáng tốt</span>
              </div>
              <div class="face-instruction">
                <div class="inst-num">2</div>
                <span>Tháo bỏ kính mắt hoặc mũ</span>
              </div>
              <div class="face-instruction">
                <div class="inst-num">3</div>
                <span>Giữ nét mặt trung lập</span>
              </div>
              <p class="privacy-note">
                Thông tin sinh trắc học của bạn được mã hóa và chỉ được sử dụng cho mục đích xác minh danh tính. Chúng tôi không lưu trữ dữ liệu khuôn mặt thô của bạn.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Error message -->
      <div class="msg-error" id="errorMsg">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span id="errorText"></span>
      </div>

      <!-- Submit button -->
      <div class="pb-4" style="margin-top:4px;">
        <button class="continue-btn" id="submitBtn" onclick="handleSubmit()">
          Tiếp tục
        </button>
      </div>
    </div>

    <!-- ══ Button tiếp tục khi đã verified ══ -->
    <div class="pb-4 hidden" id="verifiedActionBtn">
      <button class="continue-btn" onclick="goPostRoom()">
        Tiếp tục đăng tin
      </button>
    </div>

  </div><!-- /content -->
</div><!-- /verify-page -->

<script>
  // ── State ───────────────────────────────────────────────────────
  let docType = 'CCCD';
  let frontFile = null;
  let backFile  = null;
  let selfieDataUrl = null;
  let mediaStream = null;
  let submitLoading = false;

  // ── Simulated status (change for testing: 'none' | 'pending' | 'rejected' | 'approved') ──
  const SIMULATED_STATUS = 'none';
  const REJECT_REASON    = '';
  const API_URL          = ''; // set your API base URL here, e.g. 'https://api.example.com'

  // ── Init ────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    checkVerificationStatus();
  });

  function checkVerificationStatus() {
    if (!API_URL) {
      // Demo mode: use SIMULATED_STATUS
      applyStatus(SIMULATED_STATUS, REJECT_REASON);
      return;
    }
    fetch(`${API_URL}/identity-verification/me`, { credentials: 'include' })
      .then(r => r.json())
      .then(res => {
        const data = res?.data;
        if (!data) { applyStatus('none'); return; }
        applyStatus(data.status, data.rejectReason || '');
      })
      .catch(() => applyStatus('none'));
  }

  function applyStatus(status, rejectReason) {
    const verifiedNotice     = document.getElementById('verifiedNotice');
    const pendingCard        = document.getElementById('pendingCard');
    const rejectedNotice     = document.getElementById('rejectedNotice');
    const verifyForm         = document.getElementById('verifyForm');
    const verifiedActionBtn  = document.getElementById('verifiedActionBtn');
    const submitBtn          = document.getElementById('submitBtn');

    if (status === 'approved') {
      verifiedNotice.classList.remove('hidden');
      pendingCard.classList.add('hidden');
      rejectedNotice.classList.add('hidden');
      verifyForm.classList.add('hidden');
      verifiedActionBtn.classList.remove('hidden');
      // Auto redirect after 2 seconds
      setTimeout(() => goPostRoom(), 1500);
    } else if (status === 'pending') {
      verifiedNotice.classList.add('hidden');
      pendingCard.classList.remove('hidden');
      rejectedNotice.classList.add('hidden');
      verifyForm.classList.add('hidden');
      verifiedActionBtn.classList.add('hidden');
    } else if (status === 'rejected') {
      rejectedNotice.classList.remove('hidden');
      if (rejectReason) {
        const el = document.getElementById('rejectReasonText');
        el.textContent = 'Lý do: ' + rejectReason;
        el.style.display = 'block';
      }
      submitBtn.textContent = 'Gửi lại yêu cầu';
      verifyForm.classList.remove('hidden');
      pendingCard.classList.add('hidden');
      verifiedNotice.classList.add('hidden');
      verifiedActionBtn.classList.add('hidden');
    } else {
      // none
      verifyForm.classList.remove('hidden');
      verifiedNotice.classList.add('hidden');
      pendingCard.classList.add('hidden');
      rejectedNotice.classList.add('hidden');
      verifiedActionBtn.classList.add('hidden');
    }
  }

  // ── Document type radio ──────────────────────────────────────────
  function selectDoc(type) {
    docType = type;
    document.getElementById('optCCCD').classList.toggle('selected', type === 'CCCD');
    document.getElementById('optPASSPORT').classList.toggle('selected', type === 'PASSPORT');
  }

  // ── File upload ──────────────────────────────────────────────────
  function handleFile(event, side) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
      if (side === 'front') {
        frontFile = file;
        document.getElementById('frontPreview').src = e.target.result;
        document.getElementById('frontBox').classList.add('has-image');
      } else {
        backFile = file;
        document.getElementById('backPreview').src = e.target.result;
        document.getElementById('backBox').classList.add('has-image');
      }
    };
    reader.readAsDataURL(file);
  }

  // ── Camera ───────────────────────────────────────────────────────
  async function startCamera() {
    try {
      mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
      const video = document.getElementById('cameraVideo');
      video.srcObject = mediaStream;
      video.style.display = 'block';
      document.getElementById('cameraPlaceholder').style.display = 'none';
      document.getElementById('shutterBtn').style.display = 'flex';
    } catch {
      showError('Không thể truy cập camera. Vui lòng cấp quyền.');
    }
  }

  function takeSelfie() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    selfieDataUrl = canvas.toDataURL('image/jpeg');

    const img = document.getElementById('selfiePreview');
    img.src = selfieDataUrl;
    img.style.display = 'block';
    video.style.display = 'none';
    document.getElementById('shutterBtn').style.display = 'none';
    stopCamera();
  }

  function stopCamera() {
    mediaStream?.getTracks().forEach(t => t.stop());
    mediaStream = null;
  }

  // ── Validation ───────────────────────────────────────────────────
  function validatePhone(val) {
    return /^(0|\+84)[0-9]{9,10}$/.test(val.trim());
  }

  function showError(msg) {
    const el = document.getElementById('errorMsg');
    document.getElementById('errorText').textContent = msg;
    el.classList.add('visible');
  }
  function hideError() {
    document.getElementById('errorMsg').classList.remove('visible');
  }

  // ── Submit ───────────────────────────────────────────────────────
  async function handleSubmit() {
    if (submitLoading) return;

    const phone = document.getElementById('phoneNumber').value;
    const phoneInput = document.getElementById('phoneNumber');
    const phoneError = document.getElementById('phoneError');
    hideError();

    let valid = true;
    if (!validatePhone(phone)) {
      phoneInput.classList.add('error');
      phoneError.classList.add('visible');
      valid = false;
    } else {
      phoneInput.classList.remove('error');
      phoneError.classList.remove('visible');
    }

    if (!frontFile || !backFile) {
      showError('Vui lòng tải ảnh mặt trước và mặt sau giấy tờ');
      return;
    }
    if (!valid) return;

    if (!API_URL) {
      // Demo: simulate success → mark verified and show pending
      localStorage.setItem('identity_verified', JSON.stringify({
        verified: true,
        phone: phone,
        docType: docType,
        timestamp: new Date().toISOString()
      }));
      applyStatus('pending');
      return;
    }

    submitLoading = true;
    setButtonLoading(true);

    try {
      const frontUrl  = await uploadFile(frontFile,  `verify/front_${uid()}`);
      const backUrl   = await uploadFile(backFile,   `verify/back_${uid()}`);
      let selfieUrl = '';
      if (selfieDataUrl) selfieUrl = await uploadBase64(selfieDataUrl, `verify/selfie_${uid()}`);

      const payload = {
        phoneNumber: phone,
        documentType: docType,
        frontImageUrl: frontUrl,
        backImageUrl: backUrl,
        selfieImageUrl: selfieUrl || undefined,
      };

      const res = await fetch(`${API_URL}/identity-verification`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(payload),
      });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err?.message || 'Gửi xác minh thất bại');
      }

      localStorage.setItem('identity_verified', JSON.stringify({
        verified: true,
        phone: phone,
        docType: docType,
        timestamp: new Date().toISOString()
      }));

      applyStatus('pending');
    } catch (err) {
      showError(err.message || 'Gửi xác minh thất bại. Vui lòng thử lại.');
    } finally {
      submitLoading = false;
      setButtonLoading(false);
    }
  }

  function setButtonLoading(loading) {
    const btn = document.getElementById('submitBtn');
    if (loading) {
      btn.disabled = true;
      btn.innerHTML = `
        <svg class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
          <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        Đang xử lý...`;
    } else {
      btn.disabled = false;
      btn.textContent = 'Tiếp tục';
    }
  }

  async function uploadFile(file, secureId) {
    const form = new FormData();
    form.append('file', file);
    form.append('secureId', secureId);
    const res = await fetch(`${API_URL}/upload`, { method: 'POST', credentials: 'include', body: form });
    if (!res.ok) throw new Error('Upload thất bại');
    const data = await res.json();
    return data.url;
  }

  async function uploadBase64(base64, secureId) {
    const blob = await fetch(base64).then(r => r.blob());
    const file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
    return uploadFile(file, secureId);
  }

  function uid() {
    return Math.random().toString(36).slice(2) + Date.now().toString(36);
  }

  function goPostRoom() {
    // Redirect to post-room with verified flag
    // The ?verified=1 parameter tells post-room that the user has completed identity verification
    window.location.href = '/post-room?verified=1';
  }
</script>

<?php include __DIR__ . '/../../../public/assets/components/footer.php'; ?>

</body>
</html>
