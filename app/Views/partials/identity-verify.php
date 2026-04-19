<?php
use Core\SessionManager;
SessionManager::start();

$title      = "Xác thực danh tính – Bước 1";
$css        = ['verify-identity.css'];
$js         = ['verify-identity.js'];
$showFooter = false;

$currentUser = SessionManager::getUser();
if (!$currentUser) {
    header('Location: /?auth_error=' . urlencode('Vui lòng đăng nhập để tiếp tục'));
    exit;
}

$identityStatus = null;
$isVerified     = (bool)($currentUser['identity_verified'] ?? false);

try {
    $svc            = new \Services\IdentityVerificationService();
    $identityStatus = $svc->getStatus($currentUser['user_id']);
} catch (\Exception $e) { /* DB chưa ready */ }

if ($isVerified || ($identityStatus && $identityStatus['status'] === 'approved')) {
    $state = 'approved';
} elseif ($identityStatus && $identityStatus['status'] === 'pending') {
    $state = 'pending';
} elseif ($identityStatus && $identityStatus['status'] === 'rejected') {
    $state = 'rejected';
} else {
    $state = 'form';
}
$rejectReason = htmlspecialchars($identityStatus['reject_reason'] ?? '');

ob_start();
?>

<div class="verify-page">

    <!-- TOPBAR STEPPER -->
    <div class="post-topbar">
        <div class="topbar-title">Đăng tin</div>
        <div class="stepper">
            <div class="step-group">
                <div class="step-num <?= $state === 'approved' ? 'done' : 'active' ?>">
                    <?= $state === 'approved' ? '✓' : '1' ?>
                </div>
                <span class="step-label <?= $state !== 'approved' ? 'active' : '' ?>">Xác thực danh tính</span>
            </div>
            <div class="step-line"></div>
            <div class="step-group">
                <div class="step-num <?= $state === 'approved' ? 'active' : '' ?>">2</div>
                <span class="step-label <?= $state === 'approved' ? 'active' : '' ?>">Thông tin phòng trọ</span>
            </div>
        </div>
    </div>

    <div class="verify-wrapper">

        <!-- STATE: PENDING -->
        <div id="statePending" class="state-card <?= $state === 'pending' ? '' : 'hidden' ?>">
            <div class="state-icon pending-icon">⏳</div>
            <h2>Đang chờ phê duyệt</h2>
            <p>Yêu cầu xác thực đã được gửi thành công.<br>
               Quản trị viên sẽ xem xét trong vòng <strong>24</strong> giờ.</p>
            <div class="pending-notice">
                <i class="fas fa-info-circle"></i>
                Bạn sẽ nhận được thông báo khi hồ sơ được duyệt.
            </div>
            <div class="state-actions">
                <a href="/" class="state-btn outline"><i class="fas fa-home"></i> Trang chủ</a>
                <button class="state-btn primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Kiểm tra lại
                </button>
            </div>
        </div>

        <!-- STATE: REJECTED -->
        <div id="stateRejected" class="state-card <?= $state === 'rejected' ? '' : 'hidden' ?>">
            <div class="state-icon">❌</div>
            <h2>Xác thực bị từ chối</h2>
            <?php if ($rejectReason): ?>
            <div class="rejected-reason">
                <i class="fas fa-exclamation-triangle"></i>
                Lý do: <?= $rejectReason ?>
            </div>
            <?php endif; ?>
            <!-- JS có thể set reason ở đây -->
            <p id="rejectedReasonJs" class="rejected-reason" style="display:none;"></p>
            <p>Vui lòng kiểm tra lại và gửi lại yêu cầu với thông tin chính xác.</p>
            <div class="state-actions">
                <button class="state-btn primary" onclick="showForm()">
                    <i class="fas fa-redo"></i> Gửi lại yêu cầu
                </button>
            </div>
        </div>

        <!-- STATE: APPROVED -->
        <div id="stateApproved" class="state-card <?= $state === 'approved' ? '' : 'hidden' ?>">
            <div class="state-icon approved-icon">✅</div>
            <h2>Danh tính đã được xác thực!</h2>
            <p>Hồ sơ của bạn đã được phê duyệt. Bạn có thể đăng tin ngay.</p>
            <div class="state-actions">
                <a href="/post-room" class="state-btn primary">
                    <i class="fas fa-plus-circle"></i> Đăng tin ngay
                </a>
            </div>
        </div>

        <!-- STATE: FORM -->
        <div id="stateForm" class="verify-form-wrap <?= $state === 'form' || $state === 'rejected' ? '' : 'hidden' ?>"
             style="<?= $state === 'rejected' ? 'display:none;' : '' ?>">

            <div class="verify-header">
                <div class="verify-icon">🛡️</div>
                <h1>Xác thực danh tính</h1>
                <p>Để đảm bảo an toàn cho cộng đồng, chúng tôi cần xác thực trước khi bạn đăng tin.</p>
            </div>

            <form id="verifyForm" class="verify-form" onsubmit="submitVerify(event)" novalidate>

                <!-- 1. SĐT -->
                <div class="vfield-group">
                    <label class="vfield-label"><i class="fas fa-phone"></i> Số điện thoại <span class="req">*</span></label>
                    <input type="tel" id="phoneNumber" class="vfield-input"
                           placeholder="VD: 0901 234 567" autocomplete="tel">
                    <span class="vfield-error" id="phoneError"></span>
                </div>

                <!-- 2. Loại giấy tờ -->
                <div class="vfield-group">
                    <label class="vfield-label"><i class="fas fa-id-card"></i> Loại giấy tờ <span class="req">*</span></label>
                    <div class="doc-type-grid">
                        <label class="doc-type-option">
                            <input type="radio" name="document_type" value="cccd">
                            <div class="doc-type-card"><span class="doc-emoji">🪪</span><span>CCCD / CMND</span></div>
                        </label>
                        <label class="doc-type-option">
                            <input type="radio" name="document_type" value="passport">
                            <div class="doc-type-card"><span class="doc-emoji">📘</span><span>Hộ chiếu</span></div>
                        </label>
                        <label class="doc-type-option">
                            <input type="radio" name="document_type" value="driver_license">
                            <div class="doc-type-card"><span class="doc-emoji">🚗</span><span>Bằng lái xe</span></div>
                        </label>
                    </div>
                    <span class="vfield-error" id="docTypeError"></span>
                </div>

                <!-- 3. Ảnh mặt trước -->
                <div class="vfield-group">
                    <label class="vfield-label"><i class="fas fa-camera"></i> Ảnh mặt trước <span class="req">*</span></label>
                    <div class="upload-box" id="frontBox" onclick="triggerUpload('frontInput','front')">
                        <input type="file" id="frontInput" accept="image/*" style="display:none" onchange="handleDocUpload(event,'front')">
                        <div class="upload-placeholder" id="frontPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Tải ảnh mặt trước</span>
                            <small>JPG, PNG – tối đa 10MB</small>
                        </div>
                        <div class="upload-preview" id="frontPreview" style="display:none;">
                            <img id="frontImg" src="" alt="Mặt trước">
                            <div class="upload-overlay"><i class="fas fa-redo"></i> Chọn lại</div>
                            <div class="upload-status" id="frontStatus"></div>
                        </div>
                    </div>
                    <span class="vfield-error" id="frontError"></span>
                    <input type="hidden" id="frontUrl">
                </div>

                <!-- 4. Ảnh mặt sau -->
                <div class="vfield-group">
                    <label class="vfield-label"><i class="fas fa-images"></i> Ảnh mặt sau <span class="req">*</span></label>
                    <div class="upload-box" id="backBox" onclick="triggerUpload('backInput','back')">
                        <input type="file" id="backInput" accept="image/*" style="display:none" onchange="handleDocUpload(event,'back')">
                        <div class="upload-placeholder" id="backPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Tải ảnh mặt sau</span>
                            <small>JPG, PNG – tối đa 10MB</small>
                        </div>
                        <div class="upload-preview" id="backPreview" style="display:none;">
                            <img id="backImg" src="" alt="Mặt sau">
                            <div class="upload-overlay"><i class="fas fa-redo"></i> Chọn lại</div>
                            <div class="upload-status" id="backStatus"></div>
                        </div>
                    </div>
                    <span class="vfield-error" id="backError"></span>
                    <input type="hidden" id="backUrl">
                </div>

                <!-- 5. Selfie camera -->
                <div class="vfield-group">
                    <label class="vfield-label"><i class="fas fa-face-smile"></i> Ảnh xác thực khuôn mặt <span class="req">*</span></label>
                    <div class="selfie-section">

                        <div class="selfie-placeholder" id="selfiePlaceholder">
                            <i class="fas fa-user-circle"></i>
                            <p>Nhấn "Bật camera" để chụp ảnh xác thực</p>
                        </div>

                        <div class="camera-wrap" id="cameraWrap" style="display:none;">
                            <video id="cameraVideo" autoplay playsinline muted></video>
                            <div class="face-guide">
                                <div class="face-guide-label">Nhìn thẳng vào đây</div>
                            </div>
                            <canvas id="cameraCanvas" style="display:none;"></canvas>
                        </div>

                        <div class="selfie-preview" id="selfiePreview" style="display:none;">
                            <img id="selfieImg" src="" alt="Selfie">
                            <div class="selfie-status" id="selfieStatus"></div>
                        </div>

                        <div class="camera-controls">
                            <button type="button" class="btn-camera" id="btnCamera" onclick="startCamera()">
                                <i class="fas fa-video"></i> Bật camera
                            </button>
                            <button type="button" class="btn-capture" id="btnCapture" onclick="capturePhoto()" style="display:none;">
                                <i class="fas fa-circle"></i>
                            </button>
                            <button type="button" class="btn-retake" id="btnRetake" onclick="retakePhoto()" style="display:none;">
                                <i class="fas fa-redo"></i> Chụp lại
                            </button>
                        </div>
                        <p class="selfie-hint">
                            <i class="fas fa-lightbulb"></i>
                            Nhìn thẳng vào camera, ánh sáng đủ, khuôn mặt rõ trong vòng tròn
                        </p>
                    </div>
                    <span class="vfield-error" id="selfieError"></span>
                    <input type="hidden" id="selfieUrl">
                </div>

                <!-- Submit -->
                <div class="verify-submit">
                    <button type="submit" class="btn-submit-verify" id="btnSubmit" disabled>
                        <i class="fas fa-shield-check"></i>
                        Gửi yêu cầu xác thực
                    </button>
                    <p class="submit-note">
                        <i class="fas fa-lock"></i>
                        Thông tin được mã hóa AES-256 và bảo mật tuyệt đối
                    </p>
                </div>

            </form>
        </div><!-- /stateForm -->

    </div><!-- /verify-wrapper -->
</div><!-- /verify-page -->


<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';