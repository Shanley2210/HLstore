document.addEventListener("DOMContentLoaded", function () {
  const updateTotal = () => {
    let total = 0;
    document.querySelectorAll(".subtotal").forEach((subtotal) => {
      total += parseFloat(
        subtotal.textContent.replace(" VNĐ", "").replace(/\./g, "")
      );
    });
    document.querySelector("#total").textContent =
      total.toLocaleString("vi-VN").replace(",", ".") + " VNĐ";
  };

  // Hàm xử lý thay đổi số lượng
  const updateQuantity = (masach, quantity, input) => {
    if (quantity < 1 || isNaN(quantity)) {
      alert("Số lượng phải lớn hơn hoặc bằng 1!");
      input.value = 1;
      return;
    }

    fetch("/php/update_quantity.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ masach, quantity }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const price = parseFloat(input.getAttribute("data-price"));
          const newSubtotal = price * quantity;
          const subtotalCell = input.closest("tr").querySelector(".subtotal");

          // Cập nhật thành tiền
          subtotalCell.textContent =
            newSubtotal.toLocaleString("vi-VN").replace(",", ".") + " VNĐ";

          updateTotal(); // Cập nhật tổng tiền
        } else {
          alert(data.message);
        }
      });
  };

  // Bắt sự kiện thay đổi số lượng
  document.querySelectorAll(".quantity-input").forEach((input) => {
    input.addEventListener("input", function () {
      const masach = this.getAttribute("data-id");
      const quantity = parseInt(this.value);
      updateQuantity(masach, quantity, this);
    });
  });

  // Xử lý xóa sản phẩm
  document.querySelectorAll(".remove-item").forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault(); // Ngăn chuyển trang mặc định

      const masach = this.getAttribute("data-id");
      if (confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?")) {
        fetch("/php/remove_item.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ masach }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Xóa sản phẩm khỏi giao diện
              const row = document.querySelector(`tr[data-id="${masach}"]`);
              row.parentNode.removeChild(row);
              updateTotal(); // Cập nhật lại tổng tiền
            } else {
              alert(data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
          });
      }
    });
  });
});
