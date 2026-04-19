// Đổi ảnh gallery
function changeImg(el) {
  const full = el.src.replace("w=200&h=140", "w=900&h=500");
  document.getElementById("main-img").src = full;
  document
    .querySelectorAll(".thumb")
    .forEach((t) => t.classList.remove("active"));
  el.classList.add("active");
}

// Toggle yêu thích
function toggleFav() {
  const btn = document.getElementById("fav-btn");
  const icon = document.getElementById("fav-icon-card");
  btn.classList.toggle("faved");
  if (btn.classList.contains("faved")) {
    btn.innerHTML = '<i class="fas fa-heart" style="color:var(--accent)"></i>';
    icon.className = "fas fa-heart";
  } else {
    btn.innerHTML = '<i class="far fa-heart"></i>';
    icon.className = "far fa-heart";
  }
}

// Hiện / ẩn số điện thoại
function showPhone() {
  const reveal = document.getElementById("phone-reveal");
  const btn = document.getElementById("phone-btn");
  reveal.style.display = reveal.style.display === "none" ? "block" : "none";
  btn.style.display = reveal.style.display === "block" ? "none" : "flex";
}

// Chuyển tab (Mô tả / Bản đồ / Đánh giá / Phòng tương tự)
function showTab(name, el) {
  ["desc", "map", "reviews", "similar"].forEach((t) => {
    document.getElementById("tab-" + t).style.display =
      t === name ? "block" : "none";
  });
  document
    .querySelectorAll(".tab-btn")
    .forEach((b) => b.classList.remove("active"));
  el.classList.add("active");
}

// Chọn số sao đánh giá
let rating = 0;
function setRating(n) {
  rating = n;
  document.querySelectorAll("#star-row .star-btn").forEach((s, i) => {
    s.style.color = i < n ? "var(--gold)" : "var(--gray-300)";
  });
}

async function loadSimilar() {
  const grid = document.getElementById("similarGrid");
  // Lấy dữ liệu từ thuộc tính data- đã đặt ở HTML
  const roomId = grid.dataset.roomId;
  const roomCity = grid.dataset.roomCity;

  if (!grid || grid.dataset.loaded === "1") return;

  try {
    const res = await fetch(
      `/api/rooms/public?city=${encodeURIComponent(roomCity)}&limit=3`,
    );
    const data = await res.json();
    const rooms = (data.data || [])
      .filter((r) => r.room_id != roomId)
      .slice(0, 3);

    if (rooms.length === 0) {
      grid.innerHTML =
        '<p style="color:#9ca3af;font-size:.875rem;grid-column:1/-1;">Không có phòng tương tự.</p>';
      return;
    }

    grid.innerHTML = rooms
      .map(
        (r) => `
            <div class="sim-card" onclick="window.location.href='/room/${r.room_id}'">
                <img src="${r.primary_image || "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=160&fit=crop"}"
                     alt="${r.title}" onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=160&fit=crop'">
                <div class="sim-body">
                    <div class="sim-title">${r.title}</div>
                    <div class="sim-price">${Number(r.price_per_month).toLocaleString("vi-VN")} đ/tháng</div>
                    <div class="sim-loc"><i class="fas fa-location-dot" style="color:var(--primary);font-size:10px"></i> ${r.district_name || r.city_name || ""}</div>
                </div>
            </div>
        `,
      )
      .join("");
    grid.dataset.loaded = "1";
  } catch (e) {
    console.error("Lỗi tải phòng tương tự:", e);
  }
}

function shareRoom() {
  if (navigator.share) {
    navigator.share({ title: document.title, url: window.location.href });
  } else {
    navigator.clipboard.writeText(window.location.href);
    alert("Đã sao chép link vào clipboard");
  }
}

function reportRoom(roomId) {
  const reason = prompt("Lý do báo cáo:");
  if (!reason) return;
  fetch("/api/rooms/" + roomId + "/report", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ reason }),
  }).then(() => alert("Đã gửi báo cáo. Cảm ơn bạn!"));
}

function submitReview(roomId) {
  alert("Chức năng đánh giá sẽ được cập nhật sớm!");
}
