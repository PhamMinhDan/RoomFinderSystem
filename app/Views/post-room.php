<?php
// session_start();

// $demoMode = isset($_GET['demo']) || isset($_GET['verified']) || getenv('DEMO_MODE') === 'true';
// $isVerified = isset($_SESSION['verified']) && $_SESSION['verified'] === true;
// $verificationStatus = $_SESSION['verification_status'] ?? 'none';

// // If not verified, redirect to identity verification (unless in demo mode or explicitly marked verified)
// if (!$demoMode && (!$isVerified || $verificationStatus !== 'approved')) {
//     header('Location: /verify-identity');
//     exit;
// }

// // Mark as verified in session for post-room page
// $_SESSION['verified'] = true;
// $_SESSION['verification_status'] = 'approved';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Tin Phòng Trọ - RoomFinder.vn</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/post-room.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../public/assets/components/header.php'; ?>

    <div class="post-page">
        <div class="post-topbar">
            <div class="topbar-title">Đăng tin</div>
            <div class="stepper">
                <div class="step-group">
                    <div class="step-num done">1</div>
                    <span class="step-label">Xác thực danh tính</span>
                </div>
                <div class="step-line"></div>
                <div class="step-group">
                    <div class="step-num active">2</div>
                    <span class="step-label active">Thông tin phòng trọ</span>
                </div>
            </div>
        </div>

        <form class="post-form-wrapper" id="postRoomForm">
            <!-- Location -->
            <div class="post-card">
                <div class="card-header">
                    <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <h2>Vị trí trọ</h2>
                </div>
                <div class="card-body">
                    <div class="field-group">
                        <label class="field-label">Địa chỉ <span class="required">*</span></label>
                        <div class="address-display placeholder" id="addressDisplay" onclick="openLocationModal()" style="cursor: pointer;">
                            <i class="fas fa-map-pin" style="color: var(--gray-400);"></i>
                            <span style="color: var(--gray-400);">Chọn địa chỉ</span>
                            <i class="fas fa-chevron-right" style="margin-left: auto; color: var(--gray-400);"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="post-card">
                <div class="card-header">
                    <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                    </svg>
                    <h2>Nội dung tin đăng</h2>
                </div>
                <div class="card-body">
                    <!-- Images -->
                    <div class="upload-section">
                        <div class="upload-header">
                            <label class="upload-label">Ảnh phòng <span class="required">*</span></label>
                            <span class="upload-max">Tối đa 10 ảnh</span>
                        </div>
                        <div class="upload-zone" id="imageUploadZone" ondrop="handleImageDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" onclick="document.getElementById('imageInput').click()" style="cursor: pointer;">
                            <input type="file" id="imageInput" accept="image/*" multiple style="display: none;" onchange="handleImageSelect(event)">
                            <div id="imageGrid" class="image-grid" style="display: none;"></div>
                            <div id="imageUploadPlaceholder">
                                <div class="upload-icon"><i class="fas fa-plus"></i></div>
                                <div class="upload-text">Thêm ảnh phòng</div>
                            </div>
                        </div>
                    </div>

                    <!-- Video -->
                    <div class="upload-section">
                        <label class="upload-label">Video giới thiệu</label>
                        <div class="upload-zone" id="videoUploadZone" ondrop="handleVideoDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" onclick="document.getElementById('videoInput').click()" style="cursor: pointer;">
                            <input type="file" id="videoInput" accept="video/*" style="display: none;" onchange="handleVideoSelect(event)">
                            <div id="videoPreviewContainer" style="display: none;">
                                <video id="videoPreview" controls style="width: 100%; max-height: 14rem; border-radius: 0.5rem;"></video>
                            </div>
                            <div id="videoUploadPlaceholder">
                                <div class="upload-icon"><i class="fas fa-play"></i></div>
                                <div class="upload-text">Thêm video phòng</div>
                                <div class="upload-hint">Tối đa 20MB</div>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="field-group">
                        <input type="text" id="title" placeholder="Tiêu đề tin đăng *" class="field-input" maxlength="100">
                    </div>

                    <!-- Price & Deposit -->
                    <div class="form-row">
                        <div class="field-group">
                            <label class="field-label" style="font-size: 0.75rem; color: var(--gray-500);">Giá thuê <span class="required">*</span></label>
                            <div class="price-input-wrap">
                                <input type="text" id="priceInput" inputmode="numeric" placeholder="Nhập giá" class="field-input">
                                <span class="currency-badge">đ</span>
                            </div>
                            <div class="price-preview" id="pricePreview" style="display: none;"></div>
                        </div>
                        <div class="field-group">
                            <label class="field-label" style="font-size: 0.75rem; color: var(--gray-500);">Tiền cọc</label>
                            <div class="price-input-wrap">
                                <input type="text" id="depositInput" inputmode="numeric" placeholder="Cọc (nếu có)" class="field-input">
                                <span class="currency-badge">đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="field-group">
                        <textarea id="description" placeholder="Mô tả phòng..." class="field-textarea"></textarea>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="post-card">
                <div class="card-header">
                    <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2>Đặc điểm & tiện ích <span class="required">*</span></h2>
                </div>
                <div class="card-body">
                    <!-- Area & Room Type -->
                    <div class="form-row">
                        <div class="field-group">
                            <input type="number" id="areaSize" placeholder="Diện tích (m²) *" min="1" class="field-input">
                        </div>
                        <div class="field-group">
                            <select id="roomType" class="field-input">
                                <option value="">Loại phòng *</option>
                                <option value="motel">Phòng trọ</option>
                                <option value="mini">Căn hộ mini</option>
                                <option value="apt">Chung cư</option>
                            </select>
                        </div>
                    </div>

                    <!-- Capacity & Furnish -->
                    <div class="form-row">
                        <div class="field-group">
                            <select id="capacity" class="field-input">
                                <option value="">Sức chứa *</option>
                                <option value="1">1 người</option>
                                <option value="2">2 người</option>
                                <option value="3">3 người</option>
                                <option value="4">4 người</option>
                                <option value="5">5+ người</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <select id="furnishLevel" class="field-input">
                                <option value="">Nội thất</option>
                                <option value="no">Chưa nội thất</option>
                                <option value="semi">Nội thất cơ bản</option>
                                <option value="full">Đầy đủ</option>
                            </select>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div>
                        <p class="field-label">Chọn tiện ích <span class="required">*</span></p>
                        <div class="amenities-grid" id="amenitiesGrid"></div>
                    </div>

                    <!-- Custom Amenities -->
                    <button type="button" class="add-amenity-btn" onclick="toggleCustom()">+ Thêm tiện ích</button>
                    <div id="customSection" style="display: none; margin-top: 1rem;">
                        <div class="custom-amenity-row">
                            <input type="text" id="customInput" placeholder="Tiện ích..." class="field-input">
                            <button type="button" class="add-btn" onclick="addCustom()">Thêm</button>
                        </div>
                        <div id="customList" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;"></div>
                    </div>
                </div>
            </div>

            <div class="info-box">Bài đăng hiển thị trong <span class="info-highlight">15 ngày</span> kể từ khi được duyệt.</div>

            <div class="submit-section">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-check"></i> Đăng tin
                </button>
            </div>
        </form>
    </div>

    <!-- Location Modal -->
    <div class="modal-overlay" id="locationModal">
        <div class="location-modal">
            <div class="modal-header">
                <button type="button" class="modal-close" onclick="closeLocationModal()"><i class="fas fa-times"></i></button>
                <h3 class="modal-title">Chọn địa chỉ</h3>
                <div></div>
            </div>
            <div class="modal-body">
                <div class="field-group">
                    <div class="loc-field">
                        <div class="loc-input" onclick="toggleDropdown('city')">
                            <span id="cityDisplay" style="color: var(--gray-400);">Tỉnh, thành phố *</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="loc-dropdown" id="cityDropdown" style="display: none;">
                            <input type="text" class="loc-search" id="citySearch" placeholder="🔍 Tìm..." onkeyup="filterLocations('city')">
                            <div class="loc-list" id="cityList"></div>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <div class="loc-field">
                        <div class="loc-input" id="districtInput" onclick="toggleDropdown('district')" style="opacity: 0.5;">
                            <span id="districtDisplay" style="color: var(--gray-400);">Quận, huyện *</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="loc-dropdown" id="districtDropdown" style="display: none;">
                            <input type="text" class="loc-search" id="districtSearch" placeholder="🔍 Tìm..." onkeyup="filterLocations('district')">
                            <div class="loc-list" id="districtList"></div>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <div class="loc-field">
                        <div class="loc-input" id="wardInput" onclick="toggleDropdown('ward')" style="opacity: 0.5;">
                            <span id="wardDisplay" style="color: var(--gray-400);">Phường, xã *</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="loc-dropdown" id="wardDropdown" style="display: none;">
                            <input type="text" class="loc-search" id="wardSearch" placeholder="🔍 Tìm..." onkeyup="filterLocations('ward')">
                            <div class="loc-list" id="wardList"></div>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <input type="text" id="streetName" placeholder="Tên đường *" class="field-input">
                </div>
                <div class="field-group">
                    <input type="text" id="houseNumber" placeholder="Số nhà" class="field-input">
                </div>
                <button type="button" class="confirm-btn" onclick="confirmLocation()">Xong</button>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../public/assets/components/footer.php'; ?>

    <script>
        const locationData = {
            cities: [
                { id: 'hcm', name: 'TP. Hồ Chí Minh', districts: [
                    { id: 'q1', name: 'Quận 1', wards: [{ id: 'bd', name: 'P. Bến Nghé' }, { id: 'db', name: 'P. Đa Kao' }]},
                    { id: 'q2', name: 'Quận 2', wards: [{ id: 'at', name: 'P. An Lợi Đông' }]}
                ]},
                { id: 'hanoi', name: 'Hà Nội', districts: [
                    { id: 'hbd', name: 'Quận Hoàn Bình', wards: [{ id: 'qk', name: 'P. Quán Khánh' }]}
                ]}
            ]
        };

        const amenitiesData = [
            { id: 'wifi', name: 'WiFi' },
            { id: 'ac', name: 'Điều Hòa' },
            { id: 'tv', name: 'TV' },
            { id: 'bed', name: 'Giường' },
            { id: 'desk', name: 'Bàn Làm Việc' },
            { id: 'fridge', name: 'Tủ Lạnh' },
            { id: 'water', name: 'Bình Nóng Lạnh' },
            { id: 'bathroom', name: 'Phòng Tắm Riêng' },
            { id: 'kitchen', name: 'Bếp Riêng' },
            { id: 'washer', name: 'Máy Giặt' },
            { id: 'balcony', name: 'Ban Công' },
            { id: 'parking', name: 'Chỗ Đỗ Xe' }
        ];

        let selected = { city: null, district: null, ward: null, street: '', house: '' };
        let imageFiles = [];
        let selectedAmenities = [];
        let customAmenities = [];
        
        const API_URL = ''; // set your API base URL here, e.g. 'https://api.example.com'

        // Images
        function handleImageDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
            processImages(Array.from(e.dataTransfer.files));
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
        }

        function handleImageSelect(e) {
            processImages(Array.from(e.target.files));
        }

        function processImages(files) {
            imageFiles = imageFiles.concat(files.filter(f => f.type.startsWith('image/'))).slice(0, 10);
            renderImages();
        }

        function renderImages() {
            const grid = document.getElementById('imageGrid');
            const placeholder = document.getElementById('imageUploadPlaceholder');
            grid.innerHTML = '';

            imageFiles.forEach((f, i) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const item = document.createElement('div');
                    item.className = 'image-item';
                    item.innerHTML = `<img src="${e.target.result}"><button type="button" class="image-remove" onclick="imageFiles.splice(${i}, 1); renderImages();">×</button>`;
                    grid.appendChild(item);
                };
                reader.readAsDataURL(f);
            });

            if (imageFiles.length > 0) {
                grid.style.display = 'grid';
                placeholder.style.display = 'none';
                if (imageFiles.length < 10) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'add-image-btn';
                    btn.textContent = '+';
                    btn.onclick = () => document.getElementById('imageInput').click();
                    grid.appendChild(btn);
                }
            } else {
                grid.style.display = 'none';
                placeholder.style.display = 'block';
            }
        }

        // Video
        function handleVideoDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) handleVideoSelect({ target: { files: e.dataTransfer.files }});
        }

        function handleVideoSelect(e) {
            const f = e.target.files[0];
            if (f && f.type.startsWith('video/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('videoPreview').src = e.target.result;
                    document.getElementById('videoPreviewContainer').style.display = 'block';
                    document.getElementById('videoUploadPlaceholder').style.display = 'none';
                };
                reader.readAsDataURL(f);
            }
        }

        // Price
        ['priceInput', 'depositInput'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', function(e) {
                const val = e.target.value.replace(/\D/g, '');
                const previewId = id === 'priceInput' ? 'pricePreview' : 'depositPreview';
                if (val) {
                    document.getElementById(previewId).textContent = new Intl.NumberFormat('vi-VN').format(val) + (id === 'priceInput' ? ' đ/tháng' : ' đ');
                    document.getElementById(previewId).style.display = 'block';
                }
            });
        });

        // Amenities
        function renderAmenities() {
            const grid = document.getElementById('amenitiesGrid');
            grid.innerHTML = '';
            amenitiesData.forEach(a => {
                const label = document.createElement('label');
                label.className = 'amenity-item' + (selectedAmenities.includes(a.id) ? ' selected' : '');
                label.innerHTML = `
                    <input type="checkbox" ${selectedAmenities.includes(a.id) ? 'checked' : ''}>
                    <div class="amenity-check"></div>
                    <span class="amenity-label">${a.name}</span>
                `;
                label.addEventListener('change', (e) => {
                    selectedAmenities = e.target.checked ? [...selectedAmenities, a.id] : selectedAmenities.filter(x => x !== a.id);
                    renderAmenities();
                });
                grid.appendChild(label);
            });
        }

        function toggleCustom() {
            document.getElementById('customSection').style.display = document.getElementById('customSection').style.display === 'none' ? 'block' : 'none';
        }

        function addCustom() {
            const val = document.getElementById('customInput').value.trim();
            if (val) {
                customAmenities.push(val);
                document.getElementById('customInput').value = '';
                renderCustom();
            }
        }

        function renderCustom() {
            const list = document.getElementById('customList');
            list.innerHTML = '';
            customAmenities.forEach((c, i) => {
                const tag = document.createElement('span');
                tag.className = 'custom-tag';
                tag.innerHTML = `${c} <button type="button" onclick="customAmenities.splice(${i}, 1); renderCustom();">×</button>`;
                list.appendChild(tag);
            });
        }

        // Location
        function openLocationModal() {
            document.getElementById('locationModal').classList.add('open');
            renderCities();
        }

        function closeLocationModal() {
            document.getElementById('locationModal').classList.remove('open');
        }

        function toggleDropdown(type) {
            if (type === 'city' || (type === 'district' && selected.city) || (type === 'ward' && selected.district)) {
                const dd = document.getElementById(`${type}Dropdown`);
                dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
            }
        }

        function renderCities() {
            const list = document.getElementById('cityList');
            list.innerHTML = '';
            locationData.cities.forEach(c => {
                const opt = document.createElement('div');
                opt.className = 'loc-option' + (selected.city?.id === c.id ? ' selected' : '');
                opt.textContent = c.name;
                opt.onclick = () => {
                    selected.city = c;
                    selected.district = null;
                    selected.ward = null;
                    document.getElementById('cityDisplay').textContent = c.name;
                    document.getElementById('cityDropdown').style.display = 'none';
                    document.getElementById('districtInput').style.opacity = '1';
                    document.getElementById('districtDisplay').textContent = 'Quận, huyện *';
                    renderDistricts();
                };
                list.appendChild(opt);
            });
        }

        function renderDistricts() {
            const list = document.getElementById('districtList');
            list.innerHTML = '';
            if (!selected.city) return;
            selected.city.districts.forEach(d => {
                const opt = document.createElement('div');
                opt.className = 'loc-option' + (selected.district?.id === d.id ? ' selected' : '');
                opt.textContent = d.name;
                opt.onclick = () => {
                    selected.district = d;
                    selected.ward = null;
                    document.getElementById('districtDisplay').textContent = d.name;
                    document.getElementById('districtDropdown').style.display = 'none';
                    document.getElementById('wardInput').style.opacity = '1';
                    document.getElementById('wardDisplay').textContent = 'Phường, xã *';
                    renderWards();
                };
                list.appendChild(opt);
            });
        }

        function renderWards() {
            const list = document.getElementById('wardList');
            list.innerHTML = '';
            if (!selected.district) return;
            selected.district.wards.forEach(w => {
                const opt = document.createElement('div');
                opt.className = 'loc-option' + (selected.ward?.id === w.id ? ' selected' : '');
                opt.textContent = w.name;
                opt.onclick = () => {
                    selected.ward = w;
                    document.getElementById('wardDisplay').textContent = w.name;
                    document.getElementById('wardDropdown').style.display = 'none';
                };
                list.appendChild(opt);
            });
        }

        function filterLocations(type) {
            // TODO: Search filter
        }

        // Upload functions
        function uid() {
            return Math.random().toString(36).slice(2) + Date.now().toString(36);
        }

        async function uploadFile(file, secureId) {
            if (!API_URL) {
                // Demo mode: return a fake URL
                return URL.createObjectURL(file);
            }
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
            const file = new File([blob], 'preview.jpg', { type: 'image/jpeg' });
            return uploadFile(file, secureId);
        }

        function confirmLocation() {
            if (!selected.city || !selected.district || !selected.ward || !document.getElementById('streetName').value.trim()) {
                alert('Vui lòng chọn đủ thông tin');
                return;
            }
            selected.street = document.getElementById('streetName').value;
            selected.house = document.getElementById('houseNumber').value;

            const addr = `${selected.house ? selected.house + ', ' : ''}${selected.street}, ${selected.ward.name}, ${selected.district.name}, ${selected.city.name}`;
            document.getElementById('addressDisplay').innerHTML = `
                <i class="fas fa-map-pin" style="color: var(--primary-color);"></i>
                <span style="color: var(--gray-800);">${addr}</span>
                <i class="fas fa-chevron-right" style="margin-left: auto; color: var(--gray-400);"></i>
            `;
            document.getElementById('addressDisplay').classList.remove('placeholder');
            closeLocationModal();
        }

        // Form submit
        document.getElementById('postRoomForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const errors = [];
            if (!selected.city) errors.push('Chọn địa chỉ');
            if (imageFiles.length === 0) errors.push('Chọn ít nhất 1 ảnh');
            if (!document.getElementById('title').value) errors.push('Nhập tiêu đề');
            if (!document.getElementById('priceInput').value) errors.push('Nhập giá');
            if (!document.getElementById('areaSize').value) errors.push('Nhập diện tích');
            if (!document.getElementById('roomType').value) errors.push('Chọn loại phòng');
            if (!document.getElementById('capacity').value) errors.push('Chọn sức chứa');
            if (selectedAmenities.length + customAmenities.length === 0) errors.push('Chọn ít nhất 1 tiện ích');

            if (errors.length > 0) {
                alert(errors.join('\n'));
                return;
            }

            // Upload images and video
            try {
                const submitBtn = document.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24" style="margin-right:8px;"><circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Đang tải lên...';

                // Upload images
                const imageUrls = [];
                for (let i = 0; i < imageFiles.length; i++) {
                    const url = await uploadFile(imageFiles[i], `room/image_${uid()}`);
                    imageUrls.push(url);
                }

                // Upload video if exists
                let videoUrl = '';
                const videoInput = document.getElementById('videoInput');
                if (videoInput.files.length > 0) {
                    videoUrl = await uploadFile(videoInput.files[0], `room/video_${uid()}`);
                }

                // Prepare data to send to server
                const formData = {
                    address: selected,
                    title: document.getElementById('title').value,
                    price: document.getElementById('priceInput').value.replace(/\D/g, ''),
                    deposit: document.getElementById('depositInput').value.replace(/\D/g, ''),
                    description: document.getElementById('description').value,
                    areaSize: document.getElementById('areaSize').value,
                    roomType: document.getElementById('roomType').value,
                    capacity: document.getElementById('capacity').value,
                    furnishLevel: document.getElementById('furnishLevel').value,
                    amenities: selectedAmenities.concat(customAmenities),
                    imageUrls: imageUrls,
                    videoUrl: videoUrl
                };

                console.log('Form data ready to send:', formData);

                // If you have an API endpoint, send data here
                if (API_URL) {
                    const res = await fetch(`${API_URL}/post-room`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify(formData)
                    });
                    if (!res.ok) throw new Error('Đăng tin thất bại');
                    alert('Đăng tin thành công!');
                    window.location.href = '/';
                } else {
                    // Demo mode
                    alert('Đăng tin thành công!\n\nImages: ' + imageUrls.length + ' files\nVideo: ' + (videoUrl ? 'Yes' : 'No'));
                    console.log(formData);
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            } catch (err) {
                alert('Lỗi: ' + (err.message || 'Đã xảy ra lỗi khi đăng tin'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        renderAmenities();
    </script>
</body>
</html>
