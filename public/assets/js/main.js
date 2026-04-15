/**
 * Main JavaScript File
 * Chứa các hàm chung cho toàn bộ ứng dụng
 */

/**
 * Mở modal
 */
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }
}

/**
 * Đóng modal
 */
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
  }
}

/**
 * Chuyển modal (đóng modal này, mở modal khác)
 */
function switchModal(openModalId, closeModalId) {
  closeModal(closeModalId);
  openModal(openModalId);
}

/**
 * Đóng modal khi click vào overlay
 */
document.addEventListener("click", function (event) {
  if (event.target.classList.contains("modal-overlay")) {
    closeModal(event.target.id);
  }
});

/**
 * Đóng modal khi nhấn ESC
 */
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    const modals = document.querySelectorAll(".modal-overlay");
    modals.forEach((modal) => {
      if (modal.style.display === "flex") {
        closeModal(modal.id);
      }
    });
  }
});

/**
 * Thực hiện tìm kiếm
 */
function performSearch() {
  const keyword = document.querySelector(
    'input[placeholder*="Từ khóa"]',
  )?.value;
  const city = document.querySelector("select")?.value || "";

  if (keyword) {
    window.location.href = `/search?keyword=${encodeURIComponent(keyword)}&city=${encodeURIComponent(city)}`;
  } else {
    alert("Vui lòng nhập từ khóa tìm kiếm");
  }
}

/**
 * Toggle view trong trang search
 */
function switchView(viewType) {
  const gridView = document.querySelector(".results-grid");
  const buttons = document.querySelectorAll(".view-toggle button");

  buttons.forEach((btn) => btn.classList.remove("active"));
  event.target.classList.add("active");

  if (viewType === "grid") {
    gridView.classList.remove("list-view");
    gridView.classList.add("grid-view");
  } else {
    gridView.classList.remove("grid-view");
    gridView.classList.add("list-view");
  }
}

/**
 * Áp dụng bộ lọc
 */
function applyFilters() {
  alert("Bộ lọc sẽ được áp dụng");
  // TODO: Implement filter logic
}

/**
 * Đặt lại bộ lọc
 */
function resetFilters() {
  const filters = document.querySelectorAll("#sidebar input, #sidebar select");
  filters.forEach((filter) => {
    if (filter.type === "checkbox" || filter.type === "radio") {
      filter.checked = false;
    } else if (filter.type === "text") {
      filter.value = "";
    }
  });
  applyFilters();
}

/**
 * Lưu/bỏ lưu yêu thích
 */
function toggleFavorite(roomId) {
  const btn = event.target.closest("button");
  const isFavorite = btn.classList.toggle("favorite");

  // TODO: Call API to save/remove favorite
  console.log(`Room ${roomId} favorite status: ${isFavorite}`);
}

/**
 * Đi đến chi tiết phòng
 */
function goToRoomDetail(roomId) {
  window.location.href = `/room/${roomId}`;
}

/**
 * Đi đến trang chat
 */
function goToChat() {
  alert("Chuyển đến trang chat");
  // window.location.href = '/chat';
}

/**
 * Hiển thị số điện thoại
 */
function togglePhoneNumber() {
  const container = document.getElementById("phone-container");
  if (container) {
    container.style.display =
      container.style.display === "none" ? "block" : "none";
  }
}

/**
 * Utility: Format currency
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}

/**
 * Utility: Format area
 */
function formatArea(area) {
  return `${area}m²`;
}

/**
 * Log for debugging
 */
console.log("Smart Room Finder - Main JS loaded");
