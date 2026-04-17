// Search tabs
document.querySelectorAll(".search-tab").forEach((tab) => {
  tab.addEventListener("click", () => {
    document
      .querySelectorAll(".search-tab")
      .forEach((t) => t.classList.remove("active"));
    tab.classList.add("active");
  });
});

function doSearch() {
  const keyword = document.getElementById("search-keyword").value;
  window.location.href =
    "/search" + (keyword ? "?keyword=" + encodeURIComponent(keyword) : "");
}

document.getElementById("search-keyword").addEventListener("keydown", (e) => {
  if (e.key === "Enter") doSearch();
});

function handleLogin() {
  const phone = document.getElementById("phone-input").value;
  if (!phone) {
    alert("Vui lòng nhập số điện thoại");
    return;
  }
  alert("Gửi OTP đến: " + phone);
  closeModal("login-modal");
}
