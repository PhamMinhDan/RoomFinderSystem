let currentConversationId = 1;

function selectConversation(element, conversationId) {
  // Remove active class from all items
  document.querySelectorAll(".chat-item").forEach((item) => {
    item.classList.remove("active");
    item.classList.remove("unread");
  });

  // Add active class to clicked item
  element.classList.add("active");
  currentConversationId = conversationId;

  // Update chat header based on conversation
  const conversations = {
    1: {
      name: "Nguyễn Văn A",
      role: "Khách tìm trọ",
      avatar: "N",
      status: "Online",
    },
    2: {
      name: "Trần Thị B",
      role: "Khách tìm trọ",
      avatar: "T",
      status: "Online",
    },
    3: {
      name: "Lê Văn C",
      role: "Khách tìm trọ",
      avatar: "L",
      status: "Offline",
    },
    4: {
      name: "Phạm Thị D",
      role: "Khách tìm trọ",
      avatar: "P",
      status: "Offline",
    },
    5: {
      name: "Hoàng Văn E",
      role: "Khách tìm trọ",
      avatar: "H",
      status: "Offline",
    },
  };

  const conv = conversations[conversationId];
  document.querySelector(".chat-header-avatar").textContent = conv.avatar;
  document.querySelector(".chat-header-content h2").textContent = conv.name;
  document.querySelector(".chat-header-content p").textContent =
    conv.role + " | " + conv.status;

  // Load messages for this conversation
  loadConversationMessages(conversationId);
}

function loadConversationMessages(conversationId) {
  // Sample messages for each conversation
  const messages = {
    1: [
      {
        type: "other",
        text: "Xin chào, tôi quan tâm đến phòng của bạn",
        time: "10:15",
      },
      { type: "other", text: "Phòng này có wifi không?", time: "10:16" },
      {
        type: "own",
        text: "Có, wifi miễn phí cho tất cả khách",
        time: "10:18",
      },
      {
        type: "own",
        text: "Phòng được trang bị đầy đủ nội thất",
        time: "10:19",
      },
      { type: "other", text: "Giá thuê bao nhiêu một tháng?", time: "10:25" },
      {
        type: "own",
        text: "3.5 triệu đ/tháng, có thể thương lượng với hợp đồng dài hạn",
        time: "10:26",
      },
      {
        type: "other",
        text: "Được, tôi sẽ đến xem phòng sáng mai lúc 14h được không?",
        time: "10:28",
      },
      {
        type: "own",
        text: "OK, mình sẽ đợi bạn. Địa chỉ là 123 Nguyễn Huệ, Q.1",
        time: "10:30",
      },
      { type: "other", text: "OK, mình sẽ đến lúc 14h", time: "14:30" },
    ],
    2: [
      { type: "other", text: "Phòng này có điều hòa không?", time: "10:45" },
      {
        type: "own",
        text: "Có, phòng được trang bị điều hòa 1 chiều hiệu suất cao",
        time: "11:00",
      },
      { type: "other", text: "Giá điện bao nhiêu?", time: "11:05" },
    ],
    3: [
      {
        type: "other",
        text: "Giá thuê có thể thương lượng không?",
        time: "09:20",
      },
      {
        type: "own",
        text: "Có thể thảo luận, nhất là với hợp đồng dài hạn",
        time: "09:30",
      },
    ],
    4: [
      {
        type: "other",
        text: "Cảm ơn, mình rất thích phòng này!",
        time: "Hôm qua",
      },
    ],
    5: [
      {
        type: "other",
        text: "Mình có thể xem phòng được không?",
        time: "2 ngày trước",
      },
    ],
  };

  const messagesContainer = document.getElementById("chatMessages");
  messagesContainer.innerHTML = "";

  messages[conversationId]?.forEach((msg) => {
    const group = document.createElement("div");
    group.className = `chat-message-group ${msg.type}`;

    const messageEl = document.createElement("div");
    messageEl.className = "chat-message";
    messageEl.textContent = msg.text;

    const timeEl = document.createElement("div");
    timeEl.className = "chat-message-time";
    timeEl.textContent = msg.time;

    group.appendChild(messageEl);
    group.appendChild(timeEl);
    messagesContainer.appendChild(group);
  });

  // Scroll to bottom
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function sendMessage() {
  const input = document.getElementById("messageInput");
  const text = input.value.trim();

  if (!text) return;

  const messagesContainer = document.getElementById("chatMessages");
  const group = document.createElement("div");
  group.className = "chat-message-group own";

  const messageEl = document.createElement("div");
  messageEl.className = "chat-message";
  messageEl.textContent = text;

  const timeEl = document.createElement("div");
  timeEl.className = "chat-message-time";
  const now = new Date();
  timeEl.textContent =
    now.getHours() + ":" + String(now.getMinutes()).padStart(2, "0");

  group.appendChild(messageEl);
  group.appendChild(timeEl);
  messagesContainer.appendChild(group);

  // Clear input
  input.value = "";

  // Scroll to bottom
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function handleMessageKeydown(event) {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    sendMessage();
  }
}

function filterConversations() {
  const searchText = document.getElementById("searchInput").value.toLowerCase();
  const items = document.querySelectorAll(".chat-item");

  items.forEach((item) => {
    const name = item
      .querySelector(".chat-item-name")
      .textContent.toLowerCase();
    const preview = item
      .querySelector(".chat-item-preview")
      .textContent.toLowerCase();

    if (name.includes(searchText) || preview.includes(searchText)) {
      item.style.display = "";
    } else {
      item.style.display = "none";
    }
  });
}

// Initialize
selectConversation(document.querySelector(".chat-item.active"), 1);
