<?php
?>
<div class="modal-overlay" id="login-modal" onclick="if(event.target===this) closeLoginModal()">
    <div class="modal-box">

        <button class="modal-close" onclick="closeLoginModal()" aria-label="Đóng">✕</button>

        <!-- Logo -->
        <div class="modal-logo">
            <div class="logo-icon">🏠</div>
            RoomFinder.vn
        </div>

        <h2 class="modal-title">Chào mừng trở lại!</h2>
        <p class="modal-sub">Đăng nhập để tìm phòng và quản lý tin đăng của bạn</p>

        <!-- Social login -->
        <div class="social-login">
            <button class="social-btn" onclick="location.href='/api/auth/google'">
                <svg width="18" height="18" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M19.6 10.23c0-.82-.14-1.42-.35-2.05H10v3.72h5.5c-.15.96-.74 2.31-2.04 3.22v2.45h3.16c1.89-1.73 2.98-4.3 2.98-7.34z" fill="#4285F4"/>
                    <path d="M10 19.74c2.55 0 4.7-.85 6.27-2.3l-3.16-2.45c-.81.53-1.86 1.1-3.11 1.1-2.38 0-4.41-1.6-5.13-3.74H1.6v2.54C3.18 18.38 6.32 19.74 10 19.74z" fill="#34A853"/>
                    <path d="M4.87 11.95c-.19-.53-.3-1.1-.3-1.69s.11-1.16.29-1.69V5.03H1.62C.8 6.63.35 8.27.35 10c0 1.73.45 3.37 1.27 4.97l3.16-2.54z" fill="#FBBC04"/>
                    <path d="M10 3.88c1.44 0 2.63.48 3.54 1.4l2.64-2.64C14.66.82 12.52 0 10 0 6.32 0 3.18 1.36 1.62 3.58l3.25 2.54c.72-2.14 2.75-3.74 5.13-3.74z" fill="#EA4335"/>
                </svg>
                Tiếp tục với Google
            </button>

            <button class="social-btn" onclick="alert('Đăng nhập với Facebook')">
                <svg width="18" height="18" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M20 10c0-5.523-4.477-10-10-10S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.879V12.89h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.989C16.343 19.128 20 14.991 20 10z" fill="#1877F2"/>
                </svg>
                Tiếp tục với Facebook
            </button>
        </div>

        <!-- Divider -->
        <div class="divider"><span>hoặc đăng nhập bằng số điện thoại</span></div>

        <!-- Phone form -->
        <div>
            <label class="form-label" for="login-phone">Số điện thoại</label>
            <input
                type="tel"
                id="login-phone"
                class="form-input"
                placeholder="Nhập số điện thoại của bạn"
            >
            <button class="btn-submit" onclick="handleLogin()">Tiếp tục →</button>
        </div>

        <p class="modal-footer-text">
            Chưa có tài khoản? <a href="#">Đăng ký ngay</a>
        </p>

    </div>
</div>

<script>
    function openLoginModal() {
        document.getElementById('login-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeLoginModal() {
        document.getElementById('login-modal').classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleLogin() {
        const phone = document.getElementById('login-phone').value.trim();
        if (!phone) {
            alert('Vui lòng nhập số điện thoại');
            return;
        }
        alert('Gửi OTP đến: ' + phone);
        closeLoginModal();
    }

    // Đóng modal khi nhấn Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLoginModal();
    });
</script>