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
