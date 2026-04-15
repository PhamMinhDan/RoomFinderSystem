<?php
/**
 * Login Modal Component
 * Form đăng nhập theo design RoomFinder
 */
?>
<!-- Login Modal -->
<div class="modal" id="login-modal">
    <div class="modal-content login-modal-content">
        <button class="modal-close" onclick="closeModal('login-modal')">✕</button>
        
        <div class="login-form-wrapper">
            <h2 class="login-title">Đăng nhập vào RoomFinder</h2>
            <p class="login-subtitle">Chỉ mất 3 giây để bắt đầu tìm phòng phù hợp</p>

            <!-- Social Login Buttons -->
            <button class="social-btn google-btn" onclick="alert('Đăng nhập với Google')">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.6 10.23c0-.82-.14-1.42-.35-2.05H10v3.72h5.5c-.15.96-.74 2.31-2.04 3.22v2.45h3.16c1.89-1.73 2.98-4.3 2.98-7.34z" fill="#4285F4"/>
                    <path d="M10 19.74c2.55 0 4.7-.85 6.27-2.3l-3.16-2.45c-.81.53-1.86 1.1-3.11 1.1-2.38 0-4.41-1.6-5.13-3.74H1.6v2.54C3.18 18.38 6.32 19.74 10 19.74z" fill="#34A853"/>
                    <path d="M4.87 11.95c-.19-.53-.3-1.1-.3-1.69s.11-1.16.29-1.69V5.03H1.62C.8 6.63.35 8.27.35 10c0 1.73.45 3.37 1.27 4.97l3.16-2.54c-.02-.18-.05-.36-.05-.54z" fill="#FBBC04"/>
                    <path d="M10 3.88c1.44 0 2.63.48 3.54 1.4l2.64-2.64C14.66.82 12.52 0 10 0 6.32 0 3.18 1.36 1.62 3.58l3.25 2.54c.72-2.14 2.75-3.74 5.13-3.74z" fill="#EA4335"/>
                </svg>
                Tiếp tục với Google
            </button>

            <button class="social-btn facebook-btn" onclick="alert('Đăng nhập với Facebook')">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 10c0-5.523-4.477-10-10-10S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.879V12.89h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.989C16.343 19.128 20 14.991 20 10z" fill="#1877F2"/>
                </svg>
                Tiếp tục với Facebook
            </button>

            <!-- Divider -->
            <div class="login-divider">
                <span>hoặc</span>
            </div>

            <!-- Phone Number Form -->
            <form onsubmit="handleLoginSubmit(event)">
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" class="login-input" placeholder="Nhập số điện thoại của bạn" required>
                </div>
                <button type="submit" class="btn-login-submit">Tiếp tục</button>
            </form>

            <!-- Footer Text -->
            <p class="login-footer-text">
                Bạn chưa có tài khoản? <a href="#" onclick="switchModal('register-modal', 'login-modal')">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</div>

<style>
    .login-modal-content {
        width: 100%;
        max-width: 450px !important;
        padding: 40px !important;
    }

    .login-form-wrapper {
        position: relative;
    }

    .login-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--gray-900);
        text-align: center;
        margin-bottom: 8px;
    }

    .login-subtitle {
        text-align: center;
        color: var(--gray-500);
        font-size: 14px;
        margin-bottom: 28px;
    }

    .social-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 12px 16px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        background: var(--white);
        color: var(--gray-700);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        margin-bottom: 12px;
        transition: var(--transition);
    }

    .social-btn:hover {
        background: var(--gray-50);
        border-color: var(--gray-400);
    }

    .google-btn:hover {
        background: #f8f8f8;
    }

    .facebook-btn:hover {
        background: #f0f2f5;
    }

    .login-divider {
        text-align: center;
        margin: 24px 0;
        position: relative;
    }

    .login-divider span {
        background: var(--white);
        padding: 0 12px;
        color: var(--gray-500);
        font-size: 14px;
    }

    .login-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--gray-300);
        z-index: -1;
    }

    .login-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
    }

    .login-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-login-submit {
        width: 100%;
        padding: 14px;
        background: #1abc9c;
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        margin-top: 16px;
        transition: var(--transition);
    }

    .btn-login-submit:hover {
        background: #16a085;
    }

    .login-footer-text {
        text-align: center;
        font-size: 14px;
        color: var(--gray-600);
        margin-top: 20px;
    }

    .login-footer-text a {
        color: var(--primary-color);
        font-weight: 600;
    }

    @media (max-width: 480px) {
        .login-modal-content {
            padding: 30px !important;
        }

        .login-title {
            font-size: 20px;
        }
    }
</style>

<script>
    function handleLoginSubmit(event) {
        event.preventDefault();
        const phone = event.target.querySelector('.login-input').value;
        console.log('Phone entered:', phone);
        alert('Số điện thoại: ' + phone + '\nSẽ gửi OTP để xác minh');
        closeModal('login-modal');
    }
</script>
