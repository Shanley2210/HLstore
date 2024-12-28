<link rel="stylesheet" href="/style/icon_cart.css">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg" style="background-color: #e3f2fd">
  <div class="container-fluid">
    <!-- LOGO -->
    <a class="navbar-brand" href="/php/home.php">HL STORE</a>
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarTogglerDemo02"
      aria-controls="navbarTogglerDemo02"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <!-- HOME trong thanh navbar-->
          <a class="nav-link active" aria-current="page" href="/php/home.php">HOME</a>
        </li>
        <li class="nav-item dropdown">
          <!-- DANH MỤC SÁCH trong thanh navbar -->
          <a
            class="nav-link dropdown-toggle"
            href="#"
            id="navbarDropdown"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            DANH MỤC SÁCH
          </a>
          <ul
            class="dropdown-menu"
            aria-labelledby="navbarDropdown"
            style="background-color: #e3f2fd"
          >
            <li><a class="dropdown-item" href="/php/category.php?category=sachkynang">Sách kỹ năng</a></li>
            <li><a class="dropdown-item" href="/php/category.php?category=sachlaptrinh">Sách lập trình</a></li>
            <li><a class="dropdown-item" href="/php/category.php?category=sachngoaingu">Sách ngoại ngữ</a></li>
            <li><a class="dropdown-item" href="/php/category.php?category=sachlichsu">Sách lịch sử</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <!-- GIỚI THIỆU trong thanh navbar -->
          <a class="nav-link" href="/php/about.php">GIỚI THIỆU</a>
        </li>
        <li class="nav-item">
          <!-- LIÊN HỆ trong thanh navbar -->
          <a class="nav-link" href="/php/contact.php">LIÊN HỆ</a>
        </li>
      </ul>
      <!-- Tìm kiếm -->
      <form class="d-flex position-relative" id="search-form" action="/php/search.php" method="get">
        <input
            class="form-control me-2"
            type="search"
            placeholder="Bạn cần tìm gì?"
            aria-label="Search"
            id="search-input"
            name="query"
            autocomplete="off"
        />
        <button class="btn btn-outline-success" type="submit">TÌM</button>
        <div id="suggestions" class="suggestions-list position-absolute bg-white border rounded shadow-sm w-100"></div>
      </form>
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item">
          <!-- GIỎ HÀNG -->
          <a class="nav-link" href="/php/cart.php">
            Giỏ hàng 
            <span class="cart-icon-container">
                <i class="bi bi-cart"></i>
                <span class="cart-badge" id="cart-count">0</span>
            </span>
          </a>
        </li> 
      </ul>
    </div>
  </div>
</nav>

<script src="/script/home.js"></script>
<script src="/script/autocomplete.js"></script>