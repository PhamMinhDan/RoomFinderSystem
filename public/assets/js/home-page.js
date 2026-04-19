// ── Search tabs ────────────────────────────────────────────────────────────
document.querySelectorAll(".search-tab").forEach((tab) => {
  tab.addEventListener("click", () => {
    document
      .querySelectorAll(".search-tab")
      .forEach((t) => t.classList.remove("active"));
    tab.classList.add("active");
  });
});

// ── City select ────────────────────────────────────────────────────────────
if (typeof initCitySelect === "function") {
  initCitySelect(document.getElementById("city-select"), () => {});
}

// ── Tìm kiếm ──────────────────────────────────────────────────────────────
function doSearch() {
  const keyword = (
    document.getElementById("search-keyword")?.value || ""
  ).trim();
  const cityEl = document.getElementById("city-select");
  const cityName = cityEl ? cityEl.options[cityEl.selectedIndex]?.text : "";
  const priceVal = document.getElementById("price-select")?.value || "";

  // Lấy tab type đang active
  const activeTab = document.querySelector(".search-tab.active");
  const roomType = activeTab?.dataset.type || "";

  const params = new URLSearchParams();
  if (keyword) params.set("keyword", keyword);
  if (cityEl && cityEl.value) params.set("city", cityName);
  if (priceVal) params.set("price", priceVal);
  if (roomType) params.set("type", roomType);

  window.location.href =
    "/search" + (params.toString() ? "?" + params.toString() : "");
}

document.getElementById("search-keyword")?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") doSearch();
});

// ── Load hot listings ──────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", loadHotListings);

async function loadHotListings() {
  const grid = document.getElementById("hotRoomsGrid");
  if (!grid) return;

  try {
    const res = await fetch("/api/rooms/public?limit=10&sort=newest");
    if (!res.ok) throw new Error("API error");
    const data = await res.json();
    const rooms = data.data || [];

    if (rooms.length === 0) {
      // Giữ skeleton → render fallback
      grid.innerHTML =
        '<div style="grid-column:1/-1;text-align:center;padding:3rem;color:#9ca3af;"><i class="fas fa-home" style="font-size:2.5rem;margin-bottom:1rem;display:block;"></i>Chưa có phòng nào được đăng. Hãy là người đầu tiên!</div>';
      return;
    }

    grid.innerHTML = "";
    rooms.forEach((room, i) => grid.appendChild(buildRoomCard(room, i)));
  } catch (e) {
    // API lỗi → grid im lặng (skeleton đã biến mất)
    console.warn("Hot listings load failed:", e.message);
    grid.innerHTML = "";
  }
}

function buildRoomCard(room, index) {
  const card = document.createElement("div");
  card.className = "room-card";
  card.onclick = () => {
    window.location.href = `/room/${room.room_id}`;
  };

  const img =
    room.primary_image ||
    "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=250&fit=crop";
  const price = new Intl.NumberFormat("vi-VN").format(room.price_per_month);
  const badge = index < 3 ? "hot" : index % 4 === 0 ? "new" : "hot";
  const typeLabel =
    {
      motel: "Nhà trọ",
      mini: "Chung cư mini",
      apt: "Căn hộ",
      house: "Nhà nguyên căn",
      dormitory: "KTX",
    }[room.room_type] || "Phòng trọ";
  const loc = [room.district_name, room.city_name].filter(Boolean).join(", ");

  card.innerHTML = `
        <div class="room-card-img">
            <img src="${esc(img)}" alt="${esc(room.title)}" loading="lazy"
                 onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=250&fit=crop'">
            <span class="badge-${badge}">${badge === "hot" ? "HOT" : "MỚI"}</span>
            ${
              room.landlord_verified
                ? '<span style="position:absolute;top:10px;right:36px;background:rgba(16,185,129,.9);color:#fff;font-size:9px;padding:3px 7px;border-radius:4px;font-weight:700;">✓ eKYC</span>'
                : ""
            }
            <button class="btn-fav" onclick="event.stopPropagation();toggleFav(this,${room.room_id})">
                <i class="far fa-heart"></i>
            </button>
        </div>
        <div class="room-card-body">
            <div class="room-card-name">${esc(room.title)}</div>
            <div class="room-card-price">Từ ${price} đ/tháng</div>
            <div class="room-card-type">
                ${typeLabel}${room.area_size ? " · " + room.area_size + "m²" : ""}
                ${room.average_rating ? " · ⭐ " + parseFloat(room.average_rating).toFixed(1) : ""}
            </div>
            <div class="room-card-loc">
                <i class="fas fa-location-dot"></i> ${esc(loc)}
            </div>
        </div>`;
  return card;
}

function toggleFav(btn, roomId) {
  const faved = btn.dataset.faved === "1";
  btn.dataset.faved = faved ? "0" : "1";
  btn.innerHTML = faved
    ? '<i class="far fa-heart"></i>'
    : '<i class="fas fa-heart" style="color:#ff5a3d"></i>';
}

function esc(s) {
  return String(s || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
