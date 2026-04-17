function filterListings(keyword) {
  const cards = document.querySelectorAll(".listing-card");
  cards.forEach((card) => {
    const text = card.textContent.toLowerCase();
    card.style.display = text.includes(keyword.toLowerCase()) ? "" : "none";
  });
}

function filterByStatus(status, btn) {
  // Update active tab
  document.querySelectorAll(".status-tab").forEach((tab) => {
    tab.classList.remove("active");
  });
  btn.classList.add("active");

  // Filter listings
  const cards = document.querySelectorAll(".listing-card");
  cards.forEach((card) => {
    if (status === "all" || card.dataset.status === status) {
      card.style.display = "";
    } else {
      card.style.display = "none";
    }
  });
}

function renewListing() {
  alert("Mở trang gia hạn tin");
}

function editListing() {
  alert("Mở trang sửa tin");
}
