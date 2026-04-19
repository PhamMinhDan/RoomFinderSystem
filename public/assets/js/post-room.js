const MAPBOX_TOKEN =
  "pk.eyJ1IjoibHVvbmcyMyIsImEiOiJjbW1raDNueWcxZGJ3MnFwemg1aTI2cXF1In0.P4Zv9Up4zZaXXn7wG3ue4g";
let mapInstance = null;
let mapMarker = null;

// ── State ─────────────────────────────────────────────────────────────────
const postState = {
  images: [],
  videoUrl: "",
  videoUploading: false,
  address: null,
  selectedAmenities: new Set(),
  customAmenities: [],
};

const AMENITY_PRESETS = [
  { id: 1, name: "Wi-Fi", icon: "fa-wifi" },
  { id: 2, name: "Điều hòa", icon: "fa-snowflake" },
  { id: 3, name: "Nước nóng", icon: "fa-fire" },
  { id: 4, name: "Bãi đỗ xe", icon: "fa-square-parking" },
  { id: 5, name: "Ban công", icon: "fa-sun" },
  { id: 6, name: "An ninh 24/7", icon: "fa-shield-halved" },
  { id: 7, name: "Tủ lạnh", icon: "fa-temperature-low" },
  { id: 8, name: "Máy giặt", icon: "fa-shirt" },
  { id: 9, name: "Thú cưng OK", icon: "fa-paw" },
  { id: 10, name: "Nhà bếp", icon: "fa-utensils" },
];

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", async () => {
  await checkEligibility();
  renderAmenities();

  document
    .getElementById("postRoomForm")
    ?.addEventListener("submit", submitRoom);
  document
    .getElementById("priceInput")
    ?.addEventListener("input", () =>
      updatePricePreview("priceInput", "pricePreview"),
    );
  document
    .getElementById("depositInput")
    ?.addEventListener("input", () =>
      updatePricePreview("depositInput", "depositPreview"),
    );
});

// ── Kiểm tra quyền đăng tin ───────────────────────────────────────────────
async function checkEligibility() {
  try {
    const res = await fetch("/api/check-post-eligibility");
    const data = await res.json();
    if (res.status === 401) {
      showToast("Vui lòng đăng nhập để đăng tin", "warning");
      return;
    }
    if (!data.eligible) {
      if (data.reason === "identity_pending") {
        showEligibilityBanner("pending");
        disableForm();
      } else if (data.reason === "identity_rejected") {
        showEligibilityBanner("rejected", data.reject_reason);
        disableForm();
      } else {
        window.location.href = "/verify-identity";
      }
    } else {
      markStepDone();
    }
  } catch (e) {}
}

function markStepDone() {
  const step1 = document.getElementById("step1Num");
  if (step1) {
    step1.classList.remove("active");
    step1.classList.add("done");
  }
}

function disableForm() {
  document
    .getElementById("postRoomForm")
    ?.querySelectorAll('input, select, textarea, button[type="submit"]')
    .forEach((el) => (el.disabled = true));
}

function showEligibilityBanner(type, reason = "") {
  document.getElementById("eligibilityBanner")?.remove();
  const banner = document.createElement("div");
  banner.id = "eligibilityBanner";
  banner.className = `eligibility-banner ${type}`;
  banner.innerHTML =
    type === "pending"
      ? `<i class="fas fa-clock"></i><div><strong>Đang chờ xác thực danh tính</strong><p>Yêu cầu xác thực của bạn đang được xem xét.</p></div>`
      : `<i class="fas fa-circle-xmark"></i><div><strong>Xác thực bị từ chối</strong><p>${reason ? "Lý do: " + reason + ". " : ""}Vui lòng <a href="/verify-identity">gửi lại yêu cầu</a>.</p></div>`;
  (
    document.querySelector(".post-form-wrapper") ||
    document.querySelector(".post-page")
  )?.prepend(banner);
}

// ── Amenities ─────────────────────────────────────────────────────────────
function renderAmenities() {
  const grid = document.getElementById("amenitiesGrid");
  if (!grid) return;
  grid.innerHTML = "";
  AMENITY_PRESETS.forEach((am) => {
    const item = document.createElement("div");
    item.className = "amenity-item";
    item.dataset.id = am.id;
    item.innerHTML = `
      <i class="fas ${am.icon} amenity-icon"></i>
      <span class="amenity-label">${am.name}</span>
      <div class="amenity-check"><i class="fas fa-check"></i></div>
    `;
    item.addEventListener("click", () => toggleAmenity(item, am.id));
    grid.appendChild(item);
  });
}

function toggleAmenity(el, id) {
  el.classList.toggle("selected");
  if (postState.selectedAmenities.has(id)) {
    postState.selectedAmenities.delete(id);
  } else {
    postState.selectedAmenities.add(id);
  }
}

function toggleCustom() {
  const sec = document.getElementById("customSection");
  if (sec) sec.style.display = sec.style.display === "none" ? "block" : "none";
}

function addCustom() {
  const input = document.getElementById("customInput");
  const val = input?.value?.trim();
  if (!val) return;
  postState.customAmenities.push(val);
  input.value = "";
  const list = document.getElementById("customList");
  const tag = document.createElement("div");
  tag.className = "custom-tag";
  tag.innerHTML = `${val} <button type="button" onclick="removeCustom(this,'${val.replace(/'/g, "\\'")}')">×</button>`;
  list?.appendChild(tag);
}

function removeCustom(btn, val) {
  postState.customAmenities = postState.customAmenities.filter(
    (v) => v !== val,
  );
  btn.parentElement.remove();
}

// ── Image Upload ──────────────────────────────────────────────────────────
function handleImageSelect(event) {
  Array.from(event.target.files).forEach((f) => uploadImage(f));
  event.target.value = "";
}

function handleImageDrop(event) {
  event.preventDefault();
  document.getElementById("imageUploadZone")?.classList.remove("dragover");
  Array.from(event.dataTransfer.files)
    .filter((f) => f.type.startsWith("image/"))
    .forEach((f) => uploadImage(f));
}

function handleDragOver(event) {
  event.preventDefault();
  event.currentTarget.classList.add("dragover");
}
function handleDragLeave(event) {
  event.currentTarget.classList.remove("dragover");
}

async function uploadImage(file) {
  if (postState.images.length >= 10) {
    showToast("Tối đa 10 ảnh", "warning");
    return;
  }
  const localUrl = URL.createObjectURL(file);
  const idx = postState.images.length;
  postState.images.push({ url: "", localUrl, uploading: true });
  renderImageGrid();
  try {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("secureId", `room_img_${Date.now()}_${idx}`);
    const res = await fetch("/api/upload", { method: "POST", body: formData });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || "Upload thất bại");
    postState.images[idx].url = data.url;
    postState.images[idx].uploading = false;
  } catch (err) {
    postState.images.splice(idx, 1);
    showToast(err.message, "error");
  }
  renderImageGrid();
}

function renderImageGrid() {
  const grid = document.getElementById("imageGrid");
  const placeholder = document.getElementById("imageUploadPlaceholder");
  if (!grid) return;
  grid.innerHTML = "";
  if (postState.images.length === 0) {
    grid.style.display = "none";
    if (placeholder) placeholder.style.display = "";
    return;
  }
  grid.style.display = "";
  if (placeholder) placeholder.style.display = "none";
  postState.images.forEach((img, i) => {
    const item = document.createElement("div");
    item.className = "image-item";
    if (img.uploading) {
      item.innerHTML = `<img src="${img.localUrl}" alt="img"><div class="img-uploading"><i class="fas fa-spinner fa-spin"></i></div>`;
    } else {
      item.innerHTML = `
        <img src="${img.localUrl}" alt="img">
        ${i === 0 ? '<span class="img-badge-main">Chính</span>' : ""}
        <button type="button" class="image-remove" onclick="removeImage(${i})"><i class="fas fa-xmark"></i></button>
      `;
    }
    grid.appendChild(item);
  });
  if (postState.images.length < 10) {
    const addBtn = document.createElement("div");
    addBtn.className = "add-image-btn";
    addBtn.innerHTML = '<i class="fas fa-plus"></i>';
    addBtn.onclick = () => document.getElementById("imageInput")?.click();
    grid.appendChild(addBtn);
  }
}

function removeImage(idx) {
  postState.images.splice(idx, 1);
  renderImageGrid();
}

// ── Video Upload ──────────────────────────────────────────────────────────
async function handleVideoSelect(event) {
  const file = event.target.files[0];
  if (!file) return;
  const prevContainer = document.getElementById("videoPreviewContainer");
  const placeholder = document.getElementById("videoPlaceholder");
  if (placeholder)
    placeholder.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i><span>Đang tải video lên...</span>';
  postState.videoUploading = true;
  try {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("secureId", `room_video_${Date.now()}`);
    const res = await fetch("/api/upload", { method: "POST", body: formData });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || "Upload thất bại");
    postState.videoUrl = data.url;
    if (placeholder) placeholder.style.display = "none";
    if (prevContainer) {
      prevContainer.innerHTML = `<video src="${data.url}" controls style="width:100%;border-radius:.5rem;"></video>`;
      prevContainer.style.display = "";
    }
    showToast("Tải video thành công", "success");
  } catch (err) {
    if (placeholder)
      placeholder.innerHTML =
        '<i class="fas fa-photo-video"></i><span>Thêm video</span>';
    showToast(err.message, "error");
  } finally {
    postState.videoUploading = false;
    event.target.value = "";
  }
}

function handleVideoDrop(event) {
  event.preventDefault();
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith("video/")) {
    handleVideoSelect({ target: { files: [file], value: "" } });
  }
}

// ── Price preview ─────────────────────────────────────────────────────────
function updatePricePreview(inputId, previewId) {
  const raw = document.getElementById(inputId)?.value?.replace(/\D/g, "") || "";
  const val = parseInt(raw);
  const preview = document.getElementById(previewId);
  if (preview) {
    preview.textContent =
      val > 0 ? new Intl.NumberFormat("vi-VN").format(val) + " đ/tháng" : "";
  }
}

function formatPriceInput(el, previewId) {
  const raw = el.value.replace(/\D/g, "");
  el.value = raw ? new Intl.NumberFormat("vi-VN").format(parseInt(raw)) : "";
  const preview = document.getElementById(previewId);
  if (preview) {
    const val = parseInt(raw);
    preview.textContent =
      val > 0 ? new Intl.NumberFormat("vi-VN").format(val) + " đ/tháng" : "";
  }
}

// ── Location modal ────────────────────────────────────────────────────────
const locationData = { cities: [], districts: [], wards: [] };
const selected = { city: null, district: null, ward: null };

function openLocationModal() {
  document.getElementById("locationModal").classList.add("open");
  if (locationData.cities.length === 0) loadCities();
}

function closeLocationModal() {
  document.getElementById("locationModal").classList.remove("open");
  ["cityDropdown", "districtDropdown", "wardDropdown"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.style.display = "none";
  });
}

async function loadCities() {
  try {
    const res = await fetch("https://provinces.open-api.vn/api/");
    const data = await res.json();
    locationData.cities = data;
    renderList(
      "cityList",
      data.map((c) => ({ code: c.code, name: c.name })),
      "city",
    );
  } catch (e) {
    showToast("Không thể tải danh sách tỉnh thành", "error");
  }
}

function renderList(listId, items, type) {
  const list = document.getElementById(listId);
  if (!list) return;
  list.innerHTML = items
    .map(
      (item) => `
    <div class="loc-option ${selected[type]?.code === item.code ? "selected" : ""}"
         onclick="selectLocation('${type}', ${JSON.stringify(item).replace(/"/g, "&quot;")})">
      ${item.name}
    </div>
  `,
    )
    .join("");
}

async function selectLocation(type, item) {
  selected[type] = item;
  const displayEl = document.getElementById(type + "Display");
  if (displayEl) {
    displayEl.textContent = item.name;
    displayEl.style.color = "";
  }
  const dropdownEl = document.getElementById(type + "Dropdown");
  if (dropdownEl) dropdownEl.style.display = "none";

  if (type === "city") {
    selected.district = null;
    selected.ward = null;
    ["districtDisplay", "wardDisplay"].forEach((id) => {
      const el = document.getElementById(id);
      if (el) {
        el.textContent = id.includes("district")
          ? "Quận, huyện *"
          : "Phường, xã *";
        el.style.color = "var(--gray-400)";
      }
    });
    const distInput = document.getElementById("districtInput");
    if (distInput) {
      distInput.style.opacity = "1";
      distInput.style.pointerEvents = "";
    }
    const wardInput = document.getElementById("wardInput");
    if (wardInput) {
      wardInput.style.opacity = "0.45";
      wardInput.style.pointerEvents = "none";
    }
    try {
      const res = await fetch(
        `https://provinces.open-api.vn/api/p/${item.code}?depth=2`,
      );
      const data = await res.json();
      locationData.districts = data.districts;
      renderList(
        "districtList",
        data.districts.map((d) => ({ code: d.code, name: d.name })),
        "district",
      );
    } catch (e) {}
  }

  if (type === "district") {
    selected.ward = null;
    const wardDisplay = document.getElementById("wardDisplay");
    if (wardDisplay) {
      wardDisplay.textContent = "Phường, xã *";
      wardDisplay.style.color = "var(--gray-400)";
    }
    const wardInput = document.getElementById("wardInput");
    if (wardInput) {
      wardInput.style.opacity = "1";
      wardInput.style.pointerEvents = "";
    }
    try {
      const res = await fetch(
        `https://provinces.open-api.vn/api/d/${item.code}?depth=2`,
      );
      const data = await res.json();
      locationData.wards = data.wards;
      renderList(
        "wardList",
        data.wards.map((w) => ({ code: w.code, name: w.name })),
        "ward",
      );
    } catch (e) {}
  }
}

function toggleDropdown(type) {
  const dropdown = document.getElementById(type + "Dropdown");
  if (!dropdown) return;
  const isOpen = dropdown.style.display !== "none";
  ["cityDropdown", "districtDropdown", "wardDropdown"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.style.display = "none";
  });
  if (!isOpen) {
    dropdown.style.display = "block";
    // auto-focus search
    const searchInput = document.getElementById(type + "Search");
    if (searchInput) setTimeout(() => searchInput.focus(), 50);
  }
}

function filterLocations(type) {
  const search =
    document.getElementById(type + "Search")?.value?.toLowerCase() || "";
  const items =
    type === "city"
      ? locationData.cities
      : type === "district"
        ? locationData.districts
        : locationData.wards;
  const filtered = items.filter((i) => i.name.toLowerCase().includes(search));
  renderList(
    type + "List",
    filtered.map((i) => ({ code: i.code, name: i.name })),
    type,
  );
}

async function confirmLocation() {
  if (!selected.city || !selected.district || !selected.ward) {
    showToast(
      "Vui lòng chọn đầy đủ tỉnh/thành, quận/huyện, phường/xã",
      "warning",
    );
    return;
  }
  const street = document.getElementById("streetName")?.value?.trim();
  if (!street) {
    showToast("Vui lòng nhập tên đường", "warning");
    return;
  }

  const house = document.getElementById("houseNumber")?.value?.trim();
  const full = [
    house,
    street,
    selected.ward.name,
    selected.district.name,
    selected.city.name,
  ]
    .filter(Boolean)
    .join(", ");

  // ── FIX: cập nhật postState.address NGAY, không phụ thuộc geocode ──
  postState.address = {
    city_name: selected.city.name,
    district_name: selected.district.name,
    ward_name: selected.ward.name,
    street_address: [house, street].filter(Boolean).join(" "),
    lat: null,
    lng: null,
  };

  // Hiện địa chỉ lên input display ngay
  const textEl = document.getElementById("addressText");
  const display = document.getElementById("addressDisplay");
  if (textEl) textEl.textContent = full;
  if (display) {
    display.classList.remove("placeholder");
    display.classList.add("has-value");
  }

  closeLocationModal();

  // Geocode async (không block confirm)
  geocodeAddress(full)
    .then((coords) => {
      if (!coords) return;
      postState.address.lat = coords.lat;
      postState.address.lng = coords.lng;

      const mapSection = document.getElementById("mapSection");
      if (mapSection) {
        mapSection.style.display = "block";
        initMap(coords.lng, coords.lat);
      }
    })
    .catch(() => {});
}

// ── Submit Room ────────────────────────────────────────────────────────────
async function submitRoom(event) {
  event.preventDefault();
  const errors = [];
  const title = document.getElementById("title")?.value?.trim();
  const priceRaw = document
    .getElementById("priceInput")
    ?.value?.replace(/\D/g, "");
  const price = priceRaw ? parseInt(priceRaw) : 0;
  const area = document.getElementById("areaSize")?.value?.trim();
  const roomType = document.getElementById("roomType")?.value;
  const capacity = document.getElementById("capacity")?.value;
  const desc = document.getElementById("description")?.value?.trim();
  const depRaw = document
    .getElementById("depositInput")
    ?.value?.replace(/\D/g, "");
  const deposit = depRaw ? parseInt(depRaw) : null;

  if (!title) errors.push("Tiêu đề");
  if (!price) errors.push("Giá thuê");
  if (!area) errors.push("Diện tích");
  if (!roomType) errors.push("Loại phòng");
  if (!capacity) errors.push("Sức chứa");
  if (!desc) errors.push("Mô tả");
  if (postState.images.filter((i) => i.url).length === 0)
    errors.push("Ít nhất 1 ảnh");
  if (!postState.address) errors.push("Địa chỉ");

  if (errors.length) {
    showToast("Vui lòng điền: " + errors.join(", "), "warning");
    return;
  }
  if (postState.images.some((i) => i.uploading)) {
    showToast("Đang tải ảnh, vui lòng đợi...", "warning");
    return;
  }

  const btn = document.querySelector(".submit-btn");
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng tin...';
  }

  const payload = {
    title,
    description: desc,
    price_per_month: price,
    deposit_amount: deposit,
    area_size: parseFloat(area),
    capacity: parseInt(capacity),
    room_type: roomType,
    furnish_level: document.getElementById("furnishLevel")?.value || null,
    available_from: document.getElementById("availableFrom")?.value || null,
    images: postState.images.filter((i) => i.url).map((i) => i.url),
    video_url: postState.videoUrl || null,
    amenity_ids: Array.from(postState.selectedAmenities),
    custom_amenities: postState.customAmenities,
    address: postState.address,
  };

  try {
    const res = await fetch("/api/rooms", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok || data.error)
      throw new Error(data.error || "Đăng tin thất bại");
    showSuccessModal(data.message);
  } catch (err) {
    showToast(err.message, "error");
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi tin đăng';
    }
  }
}

function showSuccessModal(message) {
  const modal = document.createElement("div");
  modal.style.cssText =
    "position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:1rem;";
  modal.innerHTML = `
    <div style="background:#fff;border-radius:1.5rem;padding:2.5rem 2rem;max-width:420px;width:100%;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,.15);">
      <div style="font-size:3.5rem;margin-bottom:1rem;">🎉</div>
      <h2 style="font-size:1.3rem;font-weight:800;color:#111827;margin-bottom:.5rem;">Đăng tin thành công!</h2>
      <p style="color:#6b7280;font-size:.875rem;line-height:1.7;margin-bottom:1rem;">${message || "Bài đăng đang chờ kiểm duyệt."}</p>
      <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:.875rem;font-size:.8rem;color:#92400e;margin-bottom:1.5rem;display:flex;gap:.5rem;align-items:flex-start;text-align:left;">
        <i class="fas fa-clock" style="margin-top:2px;"></i>
        <span>Bài đăng đang chờ quản trị viên phê duyệt. Bạn sẽ nhận thông báo trong vòng 1–24 giờ.</span>
      </div>
      <a href="/landlord/dashboard" style="display:inline-flex;align-items:center;gap:.5rem;background:#3b82f6;color:#fff;padding:.75rem 1.5rem;border-radius:10px;font-weight:700;text-decoration:none;font-size:.875rem;">
        <i class="fas fa-gauge-high"></i> Xem bảng điều khiển
      </a>
    </div>
  `;
  document.body.appendChild(modal);
}

// ── Toast ─────────────────────────────────────────────────────────────────
function showToast(msg, type = "info", duration = 4000) {
  document.querySelector(".pr-toast")?.remove();
  const colors = {
    success: "#10b981",
    error: "#ef4444",
    warning: "#f59e0b",
    info: "#1f2937",
  };
  const icons = {
    success: "circle-check",
    error: "circle-xmark",
    warning: "triangle-exclamation",
    info: "circle-info",
  };
  const toast = document.createElement("div");
  toast.className = "pr-toast";
  toast.style.cssText = `position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);
    background:${colors[type] || "#1f2937"};color:#fff;padding:.75rem 1.25rem;border-radius:.75rem;
    font-size:.875rem;font-weight:600;z-index:9999;display:flex;align-items:center;gap:.5rem;
    min-width:240px;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:slideUp .3s ease;white-space:nowrap;`;
  toast.innerHTML = `<i class="fas fa-${icons[type] || "circle-info"}"></i> ${msg}`;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transition = "opacity .4s";
    setTimeout(() => toast.remove(), 400);
  }, duration);
}

async function geocodeAddress(address) {
  try {
    const res = await fetch(
      `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${MAPBOX_TOKEN}&country=vn&limit=1`,
    );
    const data = await res.json();
    if (data.features?.length) {
      const [lng, lat] = data.features[0].center;
      return { lat, lng };
    }
  } catch (e) {}
  return null;
}

function initMap(lng, lat) {
  mapboxgl.accessToken = MAPBOX_TOKEN;
  if (!mapInstance) {
    mapInstance = new mapboxgl.Map({
      container: "mapContainer",
      style: "mapbox://styles/mapbox/streets-v12",
      center: [lng, lat],
      zoom: 15,
    });
    mapMarker = new mapboxgl.Marker({ draggable: true })
      .setLngLat([lng, lat])
      .addTo(mapInstance);
    mapMarker.on("dragend", () => {
      const pos = mapMarker.getLngLat();
      if (postState.address) {
        postState.address.lat = pos.lat;
        postState.address.lng = pos.lng;
      }
    });
  } else {
    mapInstance.setCenter([lng, lat]);
    mapMarker.setLngLat([lng, lat]);
  }
}
