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
