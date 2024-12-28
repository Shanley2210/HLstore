document.addEventListener("DOMContentLoaded", function () {
    const notification = document.getElementById("notification");
  
    // Hàm hiển thị thông báo
    function showNotification(message) {
      if (!notification) return;
      notification.innerHTML = message;
      notification.style.display = "block";
      setTimeout(() => (notification.style.display = "none"), 3000); // Ẩn sau 3 giây
    }
  
    // Hàm cập nhật số lượng sản phẩm trong giỏ hàng
    function updateCartCount() {
      fetch("../php/cart_count.php")
        .then((response) => response.json())
        .then((data) => {
          const cartCountElement = document.getElementById("cart-count");
          if (cartCountElement) {
            cartCountElement.textContent = data.cartCount || '0'; // Hiển thị 0 nếu không có giá trị
            cartCountElement.style.display = 'inline'; // Luôn hiển thị badge
          }
        })
        .catch((error) => {
          console.error("Error updating cart count:", error);
          // Nếu có lỗi, vẫn hiển thị 0
          const cartCountElement = document.getElementById("cart-count");
          if (cartCountElement) {
            cartCountElement.textContent = '0';
            cartCountElement.style.display = 'inline';
          }
        });
    }
  
    // Gọi cập nhật số lượng khi tải trang
    updateCartCount();
  
    // Hàm thêm sản phẩm vào giỏ hàng
    function addToCart(masach) {
      fetch("../php/add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ masach }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            updateCartCount(); // Cập nhật số lượng
            showNotification("Thêm vào giỏ hàng thành công!");
          } else {
            showNotification("Có lỗi xảy ra: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showNotification("Lỗi kết nối.");
        });
    }
  
    // Xử lý các nút "Thêm vào giỏ hàng" trong home.php
    document.querySelectorAll(".add-to-cart").forEach((button) => {
      button.addEventListener("click", function () {
        const masach = this.getAttribute("data-id"); // Lấy mã sách
        addToCart(masach); // Gọi hàm thêm vào giỏ hàng
      });
    });
  
    // Xử lý nút "Thêm vào giỏ hàng" trong book_detail.php
    const addToCartButton = document.querySelector(".btn-add-cart");
    if (addToCartButton) {
      const urlParams = new URLSearchParams(window.location.search);
      const masach = urlParams.get("id");
  
      addToCartButton.addEventListener("click", function () {
        if (!masach) {
          showNotification("Không tìm thấy thông tin sách!");
          return;
        }
        addToCart(masach); // Gọi hàm thêm vào giỏ hàng
      });
    }
  });
