const MAPBOX_TOKEN =
  "pk.eyJ1IjoibHVvbmcyMyIsImEiOiJjbW1raDNueWcxZGJ3MnFwemg1aTI2cXF1In0.P4Zv9Up4zZaXXn7wG3ue4g";

let profileData = null;
let map = null;
let marker = null;
let currentLat = null;
let currentLng = null;
let geoGranted = false;

const addrState = { city: null, district: null, ward: null };
let addrPicker = null;

// ─── DOM refs ─────────────────────────────────────────
const $ = (id) => document.getElementById(id);

// ─── Init ──────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  initTabs();
  loadProfile();
  initProfileForm();
  initAddressModal();
});

// ══════════════════════════════════════════════════════
// Tabs
// ══════════════════════════════════════════════════════
function initTabs() {
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".tab-btn")
        .forEach((b) => b.classList.remove("active"));
      document
        .querySelectorAll(".tab-panel")
        .forEach((p) => (p.style.display = "none"));
      btn.classList.add("active");
      const panel = $("tab-" + btn.dataset.tab);
      panel.style.display = "";
      panel.classList.add("active");

      if (btn.dataset.tab === "address") {
        initMap();
        loadDbCoordsToMap();
      }
    });
  });
}

// ══════════════════════════════════════════════════════
// Load Profile
// ══════════════════════════════════════════════════════
async function loadProfile() {
  try {
    const res = await fetch("/api/profile");
    if (!res.ok) throw new Error("unauthorized");
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    profileData = json.user;
    renderProfile(profileData);
  } catch (e) {
    console.error("Load profile error:", e);
    showToast("Không thể tải thông tin. Vui lòng thử lại.", "error");
  }
}

function renderProfile(u) {
  const img = $("avatarImg");
  if (u.avatar_url) {
    img.src = u.avatar_url;
    img.onerror = () =>
      (img.src = generateAvatarUrl(u.full_name || u.username));
  } else {
    img.src = generateAvatarUrl(u.full_name || u.username);
  }

  $("sidebarName").textContent = u.full_name || u.username || "—";
  $("sidebarEmail").textContent = u.email || "—";
  $("roleChip").textContent = roleLabel(u.role_name);

  if (u.identity_verified) $("verifiedBadge").style.display = "flex";

  $("joinedDate").textContent = u.created_at
    ? "Tham gia " + formatDate(u.created_at)
    : "—";
  $("lastLogin").textContent = u.last_login
    ? "Đăng nhập " + timeAgo(u.last_login)
    : "—";

  $("qiPhone").querySelector("span").textContent =
    u.phone_number || "Chưa có số điện thoại";
  $("qiProvider").querySelector("span").textContent =
    u.auth_provider === "GOOGLE" ? "Tài khoản Google" : "Tài khoản thường";
  $("qiIdentity").querySelector("span").textContent = u.identity_verified
    ? "Đã xác minh danh tính"
    : "Chưa xác minh danh tính";

  $("viewFullName").textContent = u.full_name || "—";
  $("viewEmail").textContent = u.email || "—";
  $("viewPhone").textContent = u.phone_number || "Chưa cập nhật";
  $("viewRole").textContent = roleLabel(u.role_name);
  $("viewBio").textContent = u.bio || "Chưa có giới thiệu.";

  renderAddress(u.address);
}

// ══════════════════════════════════════════════════════
// Update Header in real-time (no page reload needed)
// ══════════════════════════════════════════════════════
function updateHeaderUser(u) {
  const name = u.full_name || u.username || "";

  // Update name displays
  const navName = document.querySelector(".nav-name");
  if (navName) navName.textContent = name;

  const dropdownName = document.querySelector(".dropdown-name");
  if (dropdownName) dropdownName.textContent = name;

  // Only update placeholder letters (never touch img.src to avoid broken images)
  const firstLetter = name.toUpperCase().charAt(0) || "U";
  document.querySelectorAll(".nav-avatar-placeholder").forEach((el) => {
    el.textContent = firstLetter;
  });
  document.querySelectorAll(".dropdown-avatar-placeholder").forEach((el) => {
    el.textContent = firstLetter;
  });
}

function renderAddress(addr) {
  if (!addr || !addr.city_name) {
    $("addrEmpty").style.display = "flex";
    $("addrInfo").style.display = "none";
    return;
  }
  $("addrEmpty").style.display = "none";
  $("addrInfo").style.display = "";

  $("addrCity").textContent = addr.city_name || "—";
  $("addrDistrict").textContent = addr.district_name || "—";
  $("addrWard").textContent = addr.ward_name || "—";
  $("addrStreet").textContent = addr.street_address || "Không có";
}

// ══════════════════════════════════════════════════════
// Mapbox
// ══════════════════════════════════════════════════════
function initMap() {
  if (map) {
    // Already init
    refreshMapMarker();
    return;
  }
  mapboxgl.accessToken = MAPBOX_TOKEN;
  map = new mapboxgl.Map({
    container: "profileMap",
    style: "mapbox://styles/mapbox/streets-v12",
    center: [105.8412, 21.0245],
    zoom: 5,
  });

  map.addControl(new mapboxgl.NavigationControl(), "bottom-right");
  map.on("load", () => refreshMapMarker());
}

function refreshMapMarker() {
  if (!map) return;

  if (currentLat && currentLng) {
    $("mapOverlay").style.display = "none";
    setMapMarker(currentLat, currentLng);
    flyToCoords(currentLat, currentLng, 13);
  } else {
    $("mapOverlay").style.display = "flex";
  }
}

// Load DB saved coordinates to map (priority over geolocation)
function loadDbCoordsToMap() {
  const addr = profileData?.address;
  if (addr?.latitude && addr?.longitude) {
    currentLat = addr.latitude;
    currentLng = addr.longitude;
    const label = [
      addr.street_address,
      addr.ward_name,
      addr.district_name,
      addr.city_name,
    ]
      .filter(Boolean)
      .join(", ");
    if (map) {
      $("mapOverlay").style.display = "none";
      setMapMarker(currentLat, currentLng, label || "Địa chỉ của bạn");
      flyToCoords(currentLat, currentLng, 14);
    }
  }
}

function setMapMarker(lat, lng, label = "") {
  if (marker) marker.remove();

  const el = document.createElement("div");
  el.innerHTML = `<div style="
        width:36px;height:36px;border-radius:50% 50% 50% 0;
        background:#16a34a;
        transform:rotate(-45deg);
        box-shadow:0 4px 16px rgba(22,163,74,.40);
        border:2px solid #fff;
    "></div>`;
  el.style.cursor = "pointer";

  marker = new mapboxgl.Marker({ element: el, anchor: "bottom" }).setLngLat([
    lng,
    lat,
  ]);

  if (label) {
    marker.setPopup(
      new mapboxgl.Popup({ offset: 25 }).setHTML(
        `<p style="font-size:.82rem">${label}</p>`,
      ),
    );
  }
  marker.addTo(map);
}

function flyToCoords(lat, lng, zoom = 14) {
  if (!map) return;
  map.flyTo({ center: [lng, lat], zoom, speed: 1.2 });
}

// ══════════════════════════════════════════════════════
// Geolocation
// ══════════════════════════════════════════════════════
function askGeolocationIfNeeded() {
  if (geoGranted || (currentLat && currentLng)) return;

  navigator.permissions
    ?.query({ name: "geolocation" })
    .then((perm) => {
      if (perm.state === "granted") {
        getCurrentLocation();
      } else if (perm.state === "prompt") {
        showGeoPrompt();
      }
    })
    .catch(() => showGeoPrompt());
}

function showGeoPrompt() {
  if (currentLat && currentLng) return;

  const toast = document.getElementById("toast");
  const bar = document.createElement("div");
  bar.id = "geoBar";
  bar.innerHTML = `
        <div style="
            position:fixed;bottom:0;left:0;right:0;
            background:#fff;border-top:1px solid #e5e7eb;
            padding:14px 24px;
            display:flex;align-items:center;justify-content:space-between;gap:16px;
            box-shadow:0 -8px 32px rgba(0,0,0,.10);
            z-index:800;font-family:'Be Vietnam Pro',sans-serif;font-size:.88rem;
            animation:slideUpBar .3s ease;
        ">
            <span style="color:#111827"><i class="fas fa-location-dot" style="color:#16a34a;margin-right:8px"></i>
            Cho phép truy cập vị trí để hiển thị trên bản đồ?</span>
            <div style="display:flex;gap:10px">
                <button onclick="denyGeo()" style="padding:7px 16px;border-radius:99px;border:1.5px solid #d1d5db;background:transparent;cursor:pointer;font-family:'Be Vietnam Pro',sans-serif">Không</button>
                <button onclick="acceptGeo()" style="padding:7px 18px;border-radius:99px;border:none;background:#16a34a;color:#fff;cursor:pointer;font-weight:600;font-family:'Be Vietnam Pro',sans-serif">Cho phép</button>
            </div>
        </div>
    `;
  const style = document.createElement("style");
  style.textContent =
    "@keyframes slideUpBar{from{transform:translateY(100%)}to{transform:translateY(0)}}";
  document.head.appendChild(style);
  document.body.appendChild(bar);
}

window.acceptGeo = function () {
  removeGeoBar();
  getCurrentLocation();
};
window.denyGeo = function () {
  removeGeoBar();
};
function removeGeoBar() {
  document.getElementById("geoBar")?.remove();
}

function getCurrentLocation(forceOverride = false) {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      geoGranted = true;
      removeGeoBar();
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      // Only update map if forced (user clicked "Vị trí hiện tại") or no coords exist
      if (forceOverride || (!currentLat && !currentLng)) {
        currentLat = lat;
        currentLng = lng;
        if (map) {
          $("mapOverlay").style.display = "none";
          setMapMarker(lat, lng, "Vị trí hiện tại của bạn");
          flyToCoords(lat, lng, 14);
        }
      }
    },
    (err) => {
      console.warn("Geolocation denied:", err);
    },
  );
}

// Locate button — user explicitly wants current GPS position
document.getElementById("btnLocate")?.addEventListener("click", () => {
  if (!navigator.geolocation) {
    showToast("Trình duyệt không hỗ trợ định vị.", "error");
    return;
  }
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      geoGranted = true;
      removeGeoBar();
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      // Always override — user explicitly requested GPS
      currentLat = lat;
      currentLng = lng;
      initMap();
      $("mapOverlay").style.display = "none";
      setMapMarker(lat, lng, "Vị trí hiện tại của bạn");
      flyToCoords(lat, lng, 14);
    },
    () =>
      showToast(
        "Không thể lấy vị trí. Vui lòng kiểm tra quyền trình duyệt.",
        "error",
      ),
  );
});

// ══════════════════════════════════════════════════════
// Profile Edit Form
// ══════════════════════════════════════════════════════
function initProfileForm() {
  $("btnEditProfile")?.addEventListener("click", openProfileEdit);
  $("btnCancelEdit")?.addEventListener("click", closeProfileEdit);

  const bioInput = $("inputBio");
  bioInput?.addEventListener("input", () => {
    $("bioCount").textContent = bioInput.value.length;
  });

  $("profileForm")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!validateProfileForm()) return;

    const btn = $("btnSaveProfile");
    setLoading(btn, true);

    const payload = {
      full_name: $("inputFullName").value.trim(),
      phone_number: $("inputPhone").value.trim(),
      bio: $("inputBio").value.trim(),
    };

    try {
      const res = await fetch("/api/profile", {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const json = await res.json();

      if (!res.ok || !json.success) {
        if (json.errors) showFormErrors(json.errors, "profile");
        else showToast(json.message || "Lỗi cập nhật.", "error");
        return;
      }

      profileData = json.user;
      renderProfile(profileData);
      updateHeaderUser(profileData);
      closeProfileEdit();
      showToast("Cập nhật thông tin thành công!", "success");
    } catch {
      showToast("Lỗi kết nối. Vui lòng thử lại.", "error");
    } finally {
      setLoading(btn, false);
    }
  });
}

function openProfileEdit() {
  if (!profileData) return;
  $("inputFullName").value = profileData.full_name || "";
  $("inputPhone").value = profileData.phone_number || "";
  $("inputBio").value = profileData.bio || "";
  $("bioCount").textContent = ($("inputBio").value || "").length;

  clearFormErrors("profile");
  $("profileView").style.display = "none";
  $("profileEdit").style.display = "";
  $("btnEditProfile").style.display = "none";
}

function closeProfileEdit() {
  $("profileView").style.display = "";
  $("profileEdit").style.display = "none";
  $("btnEditProfile").style.display = "";
}

function validateProfileForm() {
  clearFormErrors("profile");
  let valid = true;
  const fullName = $("inputFullName").value.trim();
  const phone = $("inputPhone").value.trim();
  const bio = $("inputBio").value.trim();

  if (!fullName) {
    showFieldError(
      "errFullName",
      "inputFullName",
      "Họ tên không được để trống.",
    );
    valid = false;
  } else if (fullName.length > 255) {
    showFieldError("errFullName", "inputFullName", "Tối đa 255 ký tự.");
    valid = false;
  }
  if (phone && !/^(0|\+84)[0-9]{9,10}$/.test(phone)) {
    showFieldError("errPhone", "inputPhone", "Số điện thoại không hợp lệ.");
    valid = false;
  }
  if (bio.length > 500) {
    showFieldError("errBio", "inputBio", "Bio tối đa 500 ký tự.");
    valid = false;
  }
  return valid;
}

// ══════════════════════════════════════════════════════
// Address Modal
// ══════════════════════════════════════════════════════
function initAddressModal() {
  $("btnUpdateAddress")?.addEventListener("click", openAddressModal);
  $("closeAddressModal")?.addEventListener("click", closeAddressModal);
  $("cancelAddressModal")?.addEventListener("click", closeAddressModal);

  $("addressModal")?.addEventListener("click", (e) => {
    if (e.target === $("addressModal")) closeAddressModal();
  });

  // Init LocationPicker using the existing location-service.js
  addrPicker = LocationPicker({
    cityTrigger: $("cityTrigger"),
    cityList: $("cityList"),
    citySearch: $("citySearch"),
    cityDropdown: $("cityDropdown"),
    districtTrigger: $("districtTrigger"),
    districtList: $("districtList"),
    districtSearch: $("districtSearch"),
    districtDropdown: $("districtDropdown"),
    wardTrigger: $("wardTrigger"),
    wardList: $("wardList"),
    wardSearch: $("wardSearch"),
    wardDropdown: $("wardDropdown"),
    onChange: (state) => {
      Object.assign(addrState, state);
      if (state.city || state.district || state.ward) {
        $("geocodeNote").style.display = "flex";
      }
    },
  });

  $("addressForm")?.addEventListener("submit", submitAddress);
}

function openAddressModal() {
  clearFormErrors("addr");
  addrPicker?.reset();
  Object.assign(addrState, { city: null, district: null, ward: null });
  $("inputStreet").value = profileData?.address?.street_address || "";
  $("geocodeNote").style.display = "none";
  $("addressModal").classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeAddressModal() {
  $("addressModal").classList.remove("open");
  document.body.style.overflow = "";
}

async function submitAddress(e) {
  e.preventDefault();
  clearFormErrors("addr");

  // Validate
  let valid = true;
  if (!addrState.city) {
    showFieldError(
      "errAddrCity",
      "cityTrigger",
      "Vui lòng chọn tỉnh/thành phố.",
    );
    valid = false;
  }
  if (!addrState.district) {
    showFieldError(
      "errAddrDistrict",
      "districtTrigger",
      "Vui lòng chọn quận/huyện.",
    );
    valid = false;
  }
  if (!addrState.ward) {
    showFieldError("errAddrWard", "wardTrigger", "Vui lòng chọn phường/xã.");
    valid = false;
  }
  if (!valid) return;

  const btn = $("btnSaveAddress");
  setLoading(btn, true);

  // Geocode via Mapbox Geocoding API
  const coords = await geocodeAddress(addrState, $("inputStreet").value.trim());

  const payload = {
    city_name: addrState.city.name,
    district_name: addrState.district.name,
    ward_name: addrState.ward.name,
    street_address: $("inputStreet").value.trim() || null,
    latitude: coords?.lat ?? null,
    longitude: coords?.lng ?? null,
  };

  try {
    const res = await fetch("/api/profile/address", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const json = await res.json();

    if (!res.ok || !json.success) {
      if (json.errors) showFormErrors(json.errors, "addr");
      else showToast(json.message || "Lỗi cập nhật địa chỉ.", "error");
      return;
    }

    profileData = json.user;
    renderAddress(profileData.address);
    renderProfile(profileData);
    closeAddressModal();
    showToast("Cập nhật địa chỉ thành công!", "success");

    // Update map
    if (coords) {
      currentLat = coords.lat;
      currentLng = coords.lng;
      if (map) {
        $("mapOverlay").style.display = "none";
        setMapMarker(currentLat, currentLng, buildAddressLabel(payload));
        flyToCoords(currentLat, currentLng, 14);
      }
    }
  } catch {
    showToast("Lỗi kết nối. Vui lòng thử lại.", "error");
  } finally {
    setLoading(btn, false);
  }
}

async function geocodeAddress(addrState, street) {
  const parts = [
    street,
    addrState.ward?.name,
    addrState.district?.name,
    addrState.city?.name,
    "Việt Nam",
  ]
    .filter(Boolean)
    .join(", ");

  try {
    const res = await fetch(
      `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(parts)}.json?access_token=${MAPBOX_TOKEN}&country=VN&limit=1`,
    );
    if (!res.ok) return null;
    const data = await res.json();
    if (!data.features?.length) return null;
    const [lng, lat] = data.features[0].center;
    return { lat, lng };
  } catch {
    return null;
  }
}

function buildAddressLabel(p) {
  return [p.street_address, p.ward_name, p.district_name, p.city_name]
    .filter(Boolean)
    .join(", ");
}

// ══════════════════════════════════════════════════════
// Helpers
// ══════════════════════════════════════════════════════
function setLoading(btn, loading) {
  if (!btn) return;
  btn.querySelector(".btn-text").style.display = loading ? "none" : "";
  btn.querySelector(".btn-loading").style.display = loading ? "" : "none";
  btn.disabled = loading;
}

function showToast(msg, type = "") {
  const t = $("toast");
  t.textContent = msg;
  t.className = "toast " + type;
  t.classList.add("show");
  setTimeout(() => t.classList.remove("show"), 3200);
}

function showFieldError(errId, inputId, msg) {
  const errEl = $(errId);
  const inputEl = $(inputId);
  if (errEl) errEl.textContent = msg;
  if (inputEl) inputEl.classList.add("invalid");
}

function clearFormErrors(prefix) {
  // Clear all err-msg spans with prefix
  if (prefix === "profile") {
    ["errFullName", "errPhone", "errBio"].forEach((id) => {
      const el = $(id);
      if (el) el.textContent = "";
    });
    ["inputFullName", "inputPhone", "inputBio"].forEach((id) => {
      $(id)?.classList.remove("invalid");
    });
  } else {
    ["errAddrCity", "errAddrDistrict", "errAddrWard"].forEach((id) => {
      const el = $(id);
      if (el) el.textContent = "";
    });
  }
}

function showFormErrors(errors, prefix) {
  const map = {
    full_name: ["errFullName", "inputFullName"],
    phone_number: ["errPhone", "inputPhone"],
    bio: ["errBio", "inputBio"],
    city_name: ["errAddrCity", "cityTrigger"],
    district_name: ["errAddrDistrict", "districtTrigger"],
    ward_name: ["errAddrWard", "wardTrigger"],
  };
  Object.entries(errors).forEach(([key, msg]) => {
    if (map[key]) showFieldError(...map[key], msg);
  });
}

function generateAvatarUrl(name) {
  const encoded = encodeURIComponent(name || "?");
  return `https://ui-avatars.com/api/?name=${encoded}&background=0e9e80&color=fff&size=200&bold=true`;
}

function roleLabel(role) {
  const map = {
    ADMIN: "Quản trị viên",
    LANDLORD: "Chủ trọ",
    RENTER: "Người thuê",
  };
  return map[role] || role || "—";
}

function formatDate(str) {
  if (!str) return "—";
  const d = new Date(str);
  return d.toLocaleDateString("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function timeAgo(str) {
  if (!str) return "—";
  const now = new Date();
  const past = new Date(str);
  const secs = Math.floor((now - past) / 1000);
  if (secs < 60) return "vừa xong";
  if (secs < 3600) return Math.floor(secs / 60) + " phút trước";
  if (secs < 86400) return Math.floor(secs / 3600) + " giờ trước";
  if (secs < 604800) return Math.floor(secs / 86400) + " ngày trước";
  return formatDate(str);
}
