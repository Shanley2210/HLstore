<!-- Thanh marquee nằm ngoài Navbar -->
<div class="marquee">
  <p>CHÀO MỪNG BẠN ĐẾN VỚI HLSTORE -- VỚI NHỮNG ƯU ĐÃI HẤP DẪN  --  VỚI NHỮNG CUỐN SÁCH HAY!</p>
</div>

<nav class="navbar navbar-expand-lg" style="background-color: #e3f2fd">
  <div class="container-fluid">
    <!-- LOGO -->
    <a class="navbar-brand mx-auto" style="text-align: center; width: 100%; font-weight: bold;">
      HL STORE
    </a>
  </div>
</nav>

<style>
  /* Thanh marquee nằm ngoài navbar */
  .marquee {
    overflow: hidden;
    position: fixed;  /* Đặt marquee ở vị trí cố định trên trang */
    top: 0;  /* Đặt marquee ở trên cùng */
    left: 0;  /* Căng rộng ra toàn bộ chiều ngang */
    width: 100%;  /* Chiếm toàn bộ chiều rộng màn hình */
    background-color: #e3f2fd;
    height: 30px;
    z-index: 2;  /* Đảm bảo marquee nằm trên navbar */
  }

  /* Nội dung marquee */
  .marquee p {
    position: absolute;
    white-space: nowrap;
    animation: marquee 10s linear infinite;
    margin: 0;
    font-weight: bold;
  }

  /* Animation cho marquee */
  @keyframes marquee {
    0% {
      left: 100%;  /* Bắt đầu từ ngoài bên phải */
    }
    100% {
      left: -100%;  /* Kết thúc ra ngoài bên trái */
    }
  }

  /* Thanh navbar */
  .navbar {
    position: relative;
    z-index: 1;  /* Đảm bảo navbar nằm dưới marquee */
    margin-top: 30px;  /* Đẩy navbar xuống dưới để không bị che khuất bởi marquee */
  }
</style>
