// Tabs loại phòng
document.querySelectorAll(".tab").forEach((t) => {
  t.addEventListener("click", () => {
    document
      .querySelectorAll(".tab")
      .forEach((x) => x.classList.remove("active"));
    t.classList.add("active");
  });
});

// Chuyển chế độ hiển thị grid / list
function setView(v) {
  const grid = document.getElementById("rooms-grid");
  const bg = document.getElementById("btn-grid");
  const bl = document.getElementById("btn-list");
  if (v === "grid") {
    grid.classList.remove("list-view");
    bg.classList.add("active");
    bl.classList.remove("active");
  } else {
    grid.classList.add("list-view");
    bl.classList.add("active");
    bg.classList.remove("active");
  }
}

// Áp dụng bộ lọc
function applyFilters() {
  console.log("Filters applied");
  // TODO: Implement filter logic
}

// Đặt lại bộ lọc
function resetFilters() {
  document
    .querySelectorAll('input[type="checkbox"]')
    .forEach((c) => (c.checked = false));
  document
    .querySelectorAll('input[type="radio"]')
    .forEach((r) => (r.checked = false));
  document.getElementById("price-min").value = "0";
  document.getElementById("price-max").value = "";
}

// Phân trang
document.querySelectorAll(".pg-btn").forEach((b) => {
  b.addEventListener("click", function () {
    if (this.querySelector("i")) return;
    document
      .querySelectorAll(".pg-btn")
      .forEach((x) => x.classList.remove("active"));
    this.classList.add("active");
  });
});
