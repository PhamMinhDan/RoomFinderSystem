/**
 * edit-listing.js
 * Xử lý toàn bộ form chỉnh sửa tin đăng:
 *   1. Load dữ liệu phòng hiện tại từ API
 *   2. Pre-fill tất cả fields
 *   3. Xử lý ảnh (giữ cũ / thêm mới / xoá)
 *   4. Xử lý địa chỉ (Location modal)
 *   5. Xử lý tiện ích
 *   6. Submit → POST /api/landlord/rooms/{id}/edit-request
 */

"use strict";

// ─── State ──────────────────────────────────────────────────────────────────

const ROOM_ID = parseInt(
  document.getElementById("editRoomForm")?.dataset?.roomId || "0",
);

// Ảnh: mỗi phần tử là { url: string, uploading: bool }
let images = [];
const MAX_IMAGES = 10;

// Địa chỉ
let locationData = {
  city_name: "",
  district_name: "",
  ward_name: "",
  street_address: "",
  latitude: null,
  longitude: null,
};

// Tiện ích tuỳ chỉnh (custom)
let customAmenities = [];

// Province/district/ward data (giống post-room)
let allProvinces = [];
let selectedCity = null;
let selectedDistrict = null;
let selectedWard = null;

// ─── Init ────────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  if (!ROOM_ID) {
    showError("Không xác định được ID tin đăng.");
    return;
  }
  loadRoomData();

  const form = document.getElementById("editRoomForm");
  if (form) form.addEventListener("submit", handleSubmit);
});

// ─── 1. Load dữ liệu phòng ───────────────────────────────────────────────────

async function loadRoomData() {
  try {
    const res = await fetch(`/api/landlord/rooms/${ROOM_ID}/edit-data`, {
      credentials: "include",
    });
    const json = await res.json();

    if (!res.ok || !json.data) {
      showError(json.error || "Không thể tải dữ liệu tin đăng.");
      return;
    }

    const data = json.data;

    // Nếu đang có yêu cầu chờ duyệt → show notice
    if (data.has_pending_edit) {
      document.getElementById("loadingState").style.display = "none";
      document.getElementById("pendingEditNotice").style.display = "";
      return;
    }

    populateForm(data);

    document.getElementById("loadingState").style.display = "none";
    document.getElementById("editRoomForm").style.display = "";

    // Load amenities sau khi form hiện
    await loadAmenities(data.amenities || []);
  } catch (err) {
    showError("Lỗi kết nối. Vui lòng thử lại.");
    console.error(err);
  }
}

// ─── 2. Pre-fill form ─────────────────────────────────────────────────────────

function populateForm(data) {
  // --- Text fields ---
  setVal("title", data.title || "");
  setVal("description", data.description || "");
  setVal("areaSize", data.area_size || "");
  setVal("availableFrom", data.available_from || "");

  // --- Price fields (format với dấu phẩy) ---
  if (data.price_per_month) {
    const priceEl = document.getElementById("priceInput");
    if (priceEl) {
      priceEl.value = formatNumber(data.price_per_month);
      updatePricePreview("pricePreview", data.price_per_month);
    }
  }
  if (data.deposit_amount) {
    const depEl = document.getElementById("depositInput");
    if (depEl) {
      depEl.value = formatNumber(data.deposit_amount);
      updatePricePreview("depositPreview", data.deposit_amount);
    }
  }

  // --- Selects ---
  setSelect("roomType", data.room_type || "");
  setSelect("capacity", String(data.capacity || ""));
  setSelect("furnishLevel", data.furnish_level || "");

  // --- Địa chỉ ---
  locationData = {
    city_name: data.city_name || "",
    district_name: data.district_name || "",
    ward_name: data.ward_name || "",
    street_address: data.street_address || "",
    latitude: data.latitude || null,
    longitude: data.longitude || null,
  };
  renderAddressDisplay();

  // --- Ảnh ---
  images = (data.images || []).map((img) => ({
    url: img.image_url || img,
    uploading: false,
  }));
  renderImageGrid();

  // --- Status badge ---
  if (data.rejected_by_admin) {
    showStatusBadge("Tin đã bị từ chối", "#ef4444");
  } else if (!data.is_approved) {
    showStatusBadge("Chờ duyệt lần đầu", "#f59e0b");
  } else {
    showStatusBadge("Đang hiển thị công khai", "#10b981");
  }
}

// ─── 3. Images ───────────────────────────────────────────────────────────────

function renderImageGrid() {
  const grid = document.getElementById("imageGrid");
  const placeholder = document.getElementById("imageUploadPlaceholder");
  if (!grid) return;

  grid.innerHTML = "";
  grid.style.display = images.length ? "grid" : "none";
  if (placeholder) placeholder.style.display = images.length ? "none" : "";

  images.forEach((img, idx) => {
    const item = document.createElement("div");
    item.className = "image-item";
    if (img.uploading) item.classList.add("uploading");

    item.innerHTML = img.uploading
      ? `<div class="img-loading"><i class="fas fa-spinner fa-spin"></i></div>`
      : `<img src="${escHtml(img.url)}" alt="Ảnh ${idx + 1}"
              onerror="this.src='https://via.placeholder.com/150?text=Lỗi+ảnh'">
         <div class="img-overlay">
           <button type="button" class="img-del-btn" onclick="removeImage(${idx})" title="Xoá ảnh">
             <i class="fas fa-trash"></i>
           </button>
         </div>
         <div class="img-order-badge">${idx + 1}</div>`;

    grid.appendChild(item);
  });

  // Nút thêm ảnh (nếu chưa đủ max)
  if (images.length < MAX_IMAGES) {
    const addBtn = document.createElement("div");
    addBtn.className = "image-add-btn";
    addBtn.innerHTML = `<i class="fas fa-plus"></i><span>Thêm ảnh</span>`;
    addBtn.onclick = (e) => {
      e.stopPropagation();
      document.getElementById("imageInput").click();
    };
    grid.appendChild(addBtn);
  }
}

function triggerImageUpload(e) {
  if (e.target.closest(".image-add-btn") || e.target.closest(".img-del-btn"))
    return;
  if (images.length === 0) document.getElementById("imageInput").click();
}

function removeImage(idx) {
  images.splice(idx, 1);
  renderImageGrid();
}

async function handleImageSelect(e) {
  const files = Array.from(e.target.files);
  e.target.value = "";
  await uploadFiles(files);
}

function handleImageDrop(e) {
  e.preventDefault();
  e.currentTarget.classList.remove("drag-over");
  const files = Array.from(e.dataTransfer.files).filter((f) =>
    f.type.startsWith("image/"),
  );
  uploadFiles(files);
}
function handleDragOver(e) {
  e.preventDefault();
  e.currentTarget.classList.add("drag-over");
}
function handleDragLeave(e) {
  e.currentTarget.classList.remove("drag-over");
}

async function uploadFiles(files) {
  const remaining = MAX_IMAGES - images.length;
  if (remaining <= 0) return;
  const toUpload = files.slice(0, remaining);

  // Thêm placeholders
  const startIdx = images.length;
  toUpload.forEach(() => images.push({ url: "", uploading: true }));
  renderImageGrid();

  for (let i = 0; i < toUpload.length; i++) {
    const file = toUpload[i];
    const imgIndex = startIdx + i;

    try {
      const fd = new FormData();
      fd.append("file", file);
      const res = await fetch("/api/upload", {
        method: "POST",
        credentials: "include",
        body: fd,
      });
      const json = await res.json();

      if (!res.ok || !json.url)
        throw new Error(json.error || "Upload thất bại");

      images[imgIndex] = { url: json.url, uploading: false };
    } catch (err) {
      images.splice(imgIndex, 1);
      showFieldError("imageError", "Upload ảnh thất bại: " + err.message);
    }
    renderImageGrid();
  }
}

// ─── 4. Location Modal ────────────────────────────────────────────────────────

function renderAddressDisplay() {
  const el = document.getElementById("addressText");
  if (!el) return;
  const parts = [
    locationData.street_address,
    locationData.ward_name,
    locationData.district_name,
    locationData.city_name,
  ].filter(Boolean);
  if (parts.length) {
    el.textContent = parts.join(", ");
    el.style.color = "";
    const wrap = document.getElementById("addressDisplay");
    if (wrap) wrap.classList.remove("placeholder");
  } else {
    el.textContent = "Chọn địa chỉ phòng trọ...";
    el.style.color = "var(--gray-400)";
  }
}

function openLocationModal() {
  document.getElementById("locationModal").style.display = "flex";
  document.body.style.overflow = "hidden";
  if (!allProvinces.length) loadProvinces();
  else if (locationData.city_name) prefillLocationModal();
}

function closeLocationModal() {
  document.getElementById("locationModal").style.display = "none";
  document.body.style.overflow = "";
  ["cityDropdown", "districtDropdown", "wardDropdown"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.style.display = "none";
  });
}

async function loadProvinces() {
  try {
    const res = await fetch("https://provinces.open-api.vn/api/?depth=1");
    allProvinces = await res.json();
    renderCityList(allProvinces);
    if (locationData.city_name) prefillLocationModal();
  } catch {
    // Fallback nếu API lỗi
  }
}

function prefillLocationModal() {
  // Pre-select city
  const city = allProvinces.find((p) => p.name === locationData.city_name);
  if (city) {
    selectedCity = city;
    document.getElementById("cityDisplay").textContent = city.name;
    document.getElementById("cityDisplay").style.color = "";
    enableInput("districtInput");
    loadDistricts(city.code);
  }
}

async function loadDistricts(cityCode) {
  try {
    const res = await fetch(
      `https://provinces.open-api.vn/api/p/${cityCode}?depth=2`,
    );
    const data = await res.json();
    renderDistrictList(data.districts || []);
    if (locationData.district_name) prefillDistrict(data.districts || []);
  } catch {}
}

function prefillDistrict(districts) {
  const dist = districts.find((d) => d.name === locationData.district_name);
  if (dist) {
    selectedDistrict = dist;
    document.getElementById("districtDisplay").textContent = dist.name;
    document.getElementById("districtDisplay").style.color = "";
    enableInput("wardInput");
    loadWards(dist.code);
  }
}

async function loadWards(distCode) {
  try {
    const res = await fetch(
      `https://provinces.open-api.vn/api/d/${distCode}?depth=2`,
    );
    const data = await res.json();
    renderWardList(data.wards || []);
    if (locationData.ward_name) {
      const ward = (data.wards || []).find(
        (w) => w.name === locationData.ward_name,
      );
      if (ward) {
        selectedWard = ward;
        document.getElementById("wardDisplay").textContent = ward.name;
        document.getElementById("wardDisplay").style.color = "";
      }
    }
    // Pre-fill street
    if (locationData.street_address) {
      const parts = locationData.street_address.split(",").map((s) => s.trim());
      document.getElementById("streetName").value = parts[0] || "";
      document.getElementById("houseNumber").value = parts[1] || "";
    }
  } catch {}
}

function renderCityList(items) {
  const list = document.getElementById("cityList");
  if (!list) return;
  list.innerHTML = items
    .map(
      (p) =>
        `<div class="loc-item" onclick="selectCity(${p.code},'${escHtml(p.name)}')">${p.name}</div>`,
    )
    .join("");
}

function renderDistrictList(items) {
  const list = document.getElementById("districtList");
  if (!list) return;
  list.innerHTML = items
    .map(
      (d) =>
        `<div class="loc-item" onclick="selectDistrict(${d.code},'${escHtml(d.name)}')">${d.name}</div>`,
    )
    .join("");
}

function renderWardList(items) {
  const list = document.getElementById("wardList");
  if (!list) return;
  list.innerHTML = items
    .map(
      (w) =>
        `<div class="loc-item" onclick="selectWard(${w.code},'${escHtml(w.name)}')">${w.name}</div>`,
    )
    .join("");
}

async function selectCity(code, name) {
  selectedCity = { code, name };
  selectedDistrict = null;
  selectedWard = null;
  document.getElementById("cityDisplay").textContent = name;
  document.getElementById("cityDisplay").style.color = "";
  document.getElementById("districtDisplay").textContent = "Quận, huyện *";
  document.getElementById("districtDisplay").style.color = "var(--gray-400)";
  document.getElementById("wardDisplay").textContent = "Phường, xã *";
  document.getElementById("wardDisplay").style.color = "var(--gray-400)";
  document.getElementById("districtList").innerHTML = "";
  document.getElementById("wardList").innerHTML = "";
  closeDropdown("cityDropdown");
  enableInput("districtInput");
  disableInput("wardInput");
  const res = await fetch(
    `https://provinces.open-api.vn/api/p/${code}?depth=2`,
  );
  const data = await res.json();
  renderDistrictList(data.districts || []);
}

function selectDistrict(code, name) {
  selectedDistrict = { code, name };
  selectedWard = null;
  document.getElementById("districtDisplay").textContent = name;
  document.getElementById("districtDisplay").style.color = "";
  document.getElementById("wardDisplay").textContent = "Phường, xã *";
  document.getElementById("wardDisplay").style.color = "var(--gray-400)";
  closeDropdown("districtDropdown");
  enableInput("wardInput");
  fetch(`https://provinces.open-api.vn/api/d/${code}?depth=2`)
    .then((r) => r.json())
    .then((data) => renderWardList(data.wards || []));
}

function selectWard(code, name) {
  selectedWard = { code, name };
  document.getElementById("wardDisplay").textContent = name;
  document.getElementById("wardDisplay").style.color = "";
  closeDropdown("wardDropdown");
}

function toggleDropdown(type) {
  const ids = {
    city: "cityDropdown",
    district: "districtDropdown",
    ward: "wardDropdown",
  };
  const target = ids[type];
  Object.values(ids).forEach((id) => {
    if (id !== target) document.getElementById(id).style.display = "none";
  });
  const el = document.getElementById(target);
  el.style.display = el.style.display === "none" ? "" : "none";
}

function closeDropdown(id) {
  document.getElementById(id).style.display = "none";
}
function enableInput(id) {
  const el = document.getElementById(id);
  if (el) {
    el.style.opacity = "";
    el.style.pointerEvents = "";
  }
}
function disableInput(id) {
  const el = document.getElementById(id);
  if (el) {
    el.style.opacity = ".45";
    el.style.pointerEvents = "none";
  }
}

function filterLocations(type) {
  const ids = {
    city: { search: "citySearch", list: "cityList" },
    district: { search: "districtSearch", list: "districtList" },
    ward: { search: "wardSearch", list: "wardList" },
  };
  const { search, list } = ids[type];
  const q = document.getElementById(search)?.value.toLowerCase() || "";
  const items =
    document.getElementById(list)?.querySelectorAll(".loc-item") || [];
  items.forEach((item) => {
    item.style.display = item.textContent.toLowerCase().includes(q)
      ? ""
      : "none";
  });
}

function confirmLocation() {
  if (!selectedCity) {
    alert("Vui lòng chọn Tỉnh/Thành phố");
    return;
  }
  if (!selectedDistrict) {
    alert("Vui lòng chọn Quận/Huyện");
    return;
  }
  if (!selectedWard) {
    alert("Vui lòng chọn Phường/Xã");
    return;
  }

  const street = document.getElementById("streetName")?.value.trim() || "";
  const house = document.getElementById("houseNumber")?.value.trim() || "";
  if (!street) {
    alert("Vui lòng nhập tên đường");
    return;
  }

  locationData = {
    city_name: selectedCity.name,
    district_name: selectedDistrict.name,
    ward_name: selectedWard.name,
    street_address: house ? `${street}, ${house}` : street,
    latitude: null,
    longitude: null,
  };

  renderAddressDisplay();
  clearFieldError("addressError");
  closeLocationModal();
}

// Click outside modal để đóng
document.addEventListener("click", (e) => {
  const modal = document.getElementById("locationModal");
  if (modal && e.target === modal) closeLocationModal();
});

// ─── 5. Amenities ─────────────────────────────────────────────────────────────

async function loadAmenities(currentAmenities) {
  const grid = document.getElementById("amenitiesGrid");
  if (!grid) return;

  const currentIds = new Set(currentAmenities.map((a) => a.amenity_id));

  try {
    const res = await fetch("/api/amenities", { credentials: "include" });
    const json = await res.json();
    const list = json.data || json || [];

    grid.innerHTML = "";
    list.forEach((a) => {
      const checked = currentIds.has(a.amenity_id) ? "checked" : "";
      const label = document.createElement("label");
      label.className = "amenity-chip" + (checked ? " selected" : "");
      label.innerHTML = `
        <input type="checkbox" value="${a.amenity_id}" ${checked}>
        <span>${escHtml(a.amenity_name)}</span>
      `;
      label.querySelector("input").addEventListener("change", function () {
        label.classList.toggle("selected", this.checked);
      });
      grid.appendChild(label);
    });
  } catch {
    grid.innerHTML =
      '<span style="color:var(--gray-400);font-size:13px;">Không thể tải tiện ích. Vui lòng thử lại.</span>';
  }
}

function getSelectedAmenityIds() {
  return Array.from(
    document.querySelectorAll('#amenitiesGrid input[type="checkbox"]:checked'),
  ).map((el) => parseInt(el.value));
}

function toggleCustom() {
  const sec = document.getElementById("customSection");
  if (sec) sec.style.display = sec.style.display === "none" ? "" : "none";
}

function addCustom() {
  const input = document.getElementById("customInput");
  const name = input?.value.trim();
  if (!name) return;

  customAmenities.push(name);
  input.value = "";

  const list = document.getElementById("customList");
  const tag = document.createElement("span");
  tag.className = "custom-tag";
  tag.innerHTML = `${escHtml(name)} <button type="button" onclick="removeCustom(this,'${escHtml(name)}')">×</button>`;
  list?.appendChild(tag);
}

function removeCustom(btn, name) {
  customAmenities = customAmenities.filter((n) => n !== name);
  btn.closest(".custom-tag")?.remove();
}

// ─── 6. Price formatting ──────────────────────────────────────────────────────

function formatPriceInput(input, previewId) {
  const raw = input.value.replace(/\D/g, "");
  input.value = raw ? formatNumber(raw) : "";
  updatePricePreview(previewId, raw);
}

function formatNumber(n) {
  return Number(n).toLocaleString("vi-VN");
}

function updatePricePreview(id, raw) {
  const el = document.getElementById(id);
  if (!el) return;
  if (!raw) {
    el.textContent = "";
    return;
  }
  const n = parseInt(String(raw).replace(/\D/g, ""));
  if (isNaN(n)) {
    el.textContent = "";
    return;
  }
  el.textContent = n.toLocaleString("vi-VN") + " đồng";
}

// ─── 7. Validation ────────────────────────────────────────────────────────────

function validate() {
  let ok = true;

  const title = document.getElementById("title")?.value.trim() || "";
  if (!title) {
    showFieldError("titleError", "Tiêu đề không được để trống");
    ok = false;
  } else clearFieldError("titleError");

  const price = getRawNumber("priceInput");
  if (!price) {
    showFieldError("priceError", "Giá thuê không được để trống");
    ok = false;
  } else clearFieldError("priceError");

  const area = document.getElementById("areaSize")?.value || "";
  if (!area) {
    showFieldError("areaError", "Diện tích không được để trống");
    ok = false;
  } else clearFieldError("areaError");

  const roomType = document.getElementById("roomType")?.value || "";
  if (!roomType) {
    showFieldError("roomTypeError", "Vui lòng chọn loại phòng");
    ok = false;
  } else clearFieldError("roomTypeError");

  const cap = document.getElementById("capacity")?.value || "";
  if (!cap) {
    showFieldError("capacityError", "Vui lòng chọn sức chứa");
    ok = false;
  } else clearFieldError("capacityError");

  const validImages = images.filter((img) => img.url && !img.uploading);
  if (!validImages.length) {
    showFieldError("imageError", "Cần ít nhất 1 ảnh phòng");
    ok = false;
  } else clearFieldError("imageError");

  if (
    !locationData.city_name ||
    !locationData.district_name ||
    !locationData.ward_name ||
    !locationData.street_address
  ) {
    showFieldError("addressError", "Vui lòng chọn đầy đủ địa chỉ");
    ok = false;
  } else {
    clearFieldError("addressError");
  }

  const desc = document.getElementById("description")?.value.trim() || "";
  if (!desc) {
    showFieldError("descError", "Vui lòng nhập mô tả");
    ok = false;
  } else clearFieldError("descError");

  return ok;
}

// ─── 8. Submit ────────────────────────────────────────────────────────────────

async function handleSubmit(e) {
  e.preventDefault();
  if (!validate()) {
    document
      .querySelector(".vfield-error:not(:empty)")
      ?.scrollIntoView({ behavior: "smooth", block: "center" });
    return;
  }

  const btn = document.getElementById("submitBtn");
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';

  const payload = {
    title: document.getElementById("title").value.trim(),
    description: document.getElementById("description").value.trim(),
    price_per_month: getRawNumber("priceInput"),
    deposit_amount: getRawNumber("depositInput") || null,
    area_size: parseFloat(document.getElementById("areaSize").value),
    capacity: parseInt(document.getElementById("capacity").value),
    room_type: document.getElementById("roomType").value,
    furnish_level: document.getElementById("furnishLevel").value || null,
    available_from: document.getElementById("availableFrom").value || null,
    address: locationData,
    images: images
      .filter((img) => img.url && !img.uploading)
      .map((img) => img.url),
    amenity_ids: getSelectedAmenityIds(),
    custom_amenities: customAmenities,
  };

  try {
    const res = await fetch(`/api/landlord/rooms/${ROOM_ID}/edit-request`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify(payload),
    });
    const json = await res.json();

    if (!res.ok || !json.success) {
      throw new Error(json.error || "Gửi yêu cầu thất bại");
    }

    // Thành công
    showSuccessOverlay(json.message);
  } catch (err) {
    showGlobalError(err.message);
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi yêu cầu chỉnh sửa';
  }
}

// ─── 9. UI Helpers ────────────────────────────────────────────────────────────

function showSuccessOverlay(msg) {
  const overlay = document.createElement("div");
  overlay.style.cssText = `
    position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;
    display:flex;align-items:center;justify-content:center;padding:20px;
  `;
  overlay.innerHTML = `
    <div style="background:#fff;border-radius:20px;padding:40px;max-width:420px;
                width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);">
      <div style="font-size:56px;margin-bottom:16px;">🎉</div>
      <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-bottom:12px;">
        Gửi yêu cầu thành công!
      </h2>
      <p style="color:#64748b;font-size:14px;line-height:1.6;margin-bottom:24px;">${escHtml(msg)}</p>
      <a href="/landlord/listings"
         style="display:inline-block;padding:12px 28px;background:var(--primary,#2b3cf7);
                color:#fff;border-radius:10px;font-weight:600;text-decoration:none;font-size:14px;">
        <i class="fas fa-list"></i> Xem danh sách tin
      </a>
    </div>`;
  document.body.appendChild(overlay);
}

function showStatusBadge(text, color) {
  const el = document.getElementById("editStatusBadge");
  if (!el) return;
  el.style.display = "";
  el.style.padding = "4px 12px";
  el.style.borderRadius = "20px";
  el.style.fontSize = "12px";
  el.style.fontWeight = "600";
  el.style.background = color + "20";
  el.style.color = color;
  el.textContent = text;
}

function showError(msg) {
  document.getElementById("loadingState").style.display = "none";
  const form = document.getElementById("editRoomForm");
  const notice = document.getElementById("pendingEditNotice");
  if (notice) notice.style.display = "none";
  if (form) {
    form.style.display = "";
    form.innerHTML = `
      <div class="pending-gate-card rejected">
        <div class="gate-icon">❌</div>
        <h2>Không thể tải tin đăng</h2>
        <p>${escHtml(msg)}</p>
        <div class="gate-actions">
          <a href="/landlord/listings" class="gate-btn gate-btn-outline">Quay lại</a>
        </div>
      </div>`;
  }
}

function showGlobalError(msg) {
  const existing = document.getElementById("globalError");
  if (existing) existing.remove();

  const el = document.createElement("div");
  el.id = "globalError";
  el.style.cssText = `
    background:#fee2e2;color:#991b1b;border-radius:10px;
    padding:12px 16px;margin-bottom:16px;font-size:13px;font-weight:600;
    display:flex;align-items:center;gap:8px;
  `;
  el.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${escHtml(msg)}`;
  const form = document.getElementById("editRoomForm");
  if (form) form.insertBefore(el, form.firstChild);
  el.scrollIntoView({ behavior: "smooth", block: "center" });
}

function showFieldError(id, msg) {
  const el = document.getElementById(id);
  if (el) el.textContent = msg;
}
function clearFieldError(id) {
  const el = document.getElementById(id);
  if (el) el.textContent = "";
}

function getRawNumber(inputId) {
  const val = document.getElementById(inputId)?.value.replace(/\D/g, "") || "";
  return val ? parseInt(val) : 0;
}

function setVal(id, value) {
  const el = document.getElementById(id);
  if (el) el.value = value;
}

function setSelect(id, value) {
  const el = document.getElementById(id);
  if (!el) return;
  const opt = Array.from(el.options).find((o) => o.value === value);
  if (opt) el.value = value;
}

function escHtml(str) {
  return String(str || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
