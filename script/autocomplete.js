document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM đã tải");
});

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("search-input"); // Ô nhập liệu
  const suggestionsList = document.getElementById("suggestions"); // Danh sách gợi ý

  // Hàm hiển thị gợi ý
  const showSuggestions = (suggestions) => {
    // Nếu không có gợi ý, ẩn danh sách gợi ý
    if (suggestions.length === 0) {
      suggestionsList.style.display = "none";
      return;
    }

    // Tạo danh sách các mục gợi ý
    suggestionsList.innerHTML = suggestions
      .map(
        (item) =>
          `<div class="suggestion-item p-2 border-bottom" data-value="${item.tensach}">
              ${item.tensach}
            </div>`
      )
      .join("");

    // Hiển thị danh sách gợi ý
    suggestionsList.style.display = "block";

    // Gắn sự kiện click cho từng mục gợi ý
    document.querySelectorAll(".suggestion-item").forEach((item) => {
      item.addEventListener("click", () => {
        // Cập nhật giá trị vào ô nhập liệu
        searchInput.value = item.dataset.value;
        // Ẩn danh sách gợi ý
        suggestionsList.style.display = "none";
      });
    });
  };

  // Lắng nghe sự kiện nhập liệu
  searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim(); // Lấy giá trị nhập vào và loại bỏ khoảng trắng

    if (query.length > 1) {
      // Gửi yêu cầu đến API để lấy gợi ý
      fetch(`/php/search_suggestions.php?query=${encodeURIComponent(query)}`)
        .then((response) => {
          if (!response.ok) {
            throw new Error("Lỗi mạng hoặc không tìm thấy API");
          }
          return response.json();
        }) // Chuyển đổi phản hồi API thành JSON
        .then((data) => {
          console.log("Gợi ý nhận được:", data); // Kiểm tra dữ liệu nhận được
          showSuggestions(data); // Hiển thị gợi ý
        })
        .catch((error) => console.error("Lỗi khi lấy gợi ý:", error)); // Bắt lỗi
    } else {
      suggestionsList.style.display = "none"; // Ẩn danh sách gợi ý nếu không có giá trị nhập
    }
  });

  // Ẩn danh sách gợi ý khi người dùng nhấp bên ngoài
  document.addEventListener("click", (event) => {
    if (!event.target.closest("#search-form")) {
      suggestionsList.style.display = "none";
    }
  });
});
