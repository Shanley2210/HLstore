document.addEventListener("DOMContentLoaded", function () {
  const carouselElement = document.getElementById("productCarousel");
  const carousel = new bootstrap.Carousel(carouselElement, {
    interval: 3000, // Thời gian giữa các slide
    ride: "carousel",
  });

  // Thêm smooth transition bằng cách thay đổi thời gian và easing của CSS
  const items = carouselElement.querySelectorAll(".carousel-item");
  items.forEach((item) => {
    item.style.transition = "transform 1s ease"; // Điều chỉnh tốc độ và easing
  });
});
