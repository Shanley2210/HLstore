<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HLStore</title>
    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    />
    <!-- Bootstrap Icon -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style/home.css">
    <style>
        #pagination-container {
            display: none; /* Ẩn phần số trang ban đầu */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php
    include '../include/navbar.php';
    include '../include/connectdb.php';

    // Kết nối cơ sở dữ liệu bằng PDO
    $conn = connectDB();

    // Ánh xạ danh mục trong database sang thư mục
    $folderMap = [
        'dm001' => 'SachKyNang',
        'dm002' => 'SachLapTrinh',
        'dm003' => 'SachLichSu',
        'dm004' => 'SachNgoaiNgu'
    ];

    // Xử lý phân trang
    $limit = 10; // Số sản phẩm mỗi trang
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Lấy sản phẩm từ database có phân trang
    $products = loadSachWithPagination($conn, $offset, $limit);

    // Lấy tổng số sản phẩm để tính tổng số trang
    $totalProducts = countTotalSach($conn);
    $totalPages = ceil($totalProducts / $limit);

    // Hàm lấy sách có phân trang
    function loadSachWithPagination($conn, $offset, $limit) {
        $sql = "SELECT * FROM sach ORDER BY ngaythem DESC LIMIT :offset, :limit";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về danh sách sản phẩm dưới dạng mảng kết hợp
    }

    // Hàm đếm tổng số sách
    function countTotalSach($conn) {
        $sql = "SELECT COUNT(*) AS total FROM sach";
        $stmt = $conn->query($sql); // Truy vấn đơn giản không cần `prepare`

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return (int)$row['total'];
        }

        return 0;
    }
    ?>

    <div class="container mt-5">
        <h2>Sách mới</h2>
        <div class="row g-3 justify-content-center">
            <?php foreach ($products as $product): 
                $folder = isset($folderMap[$product['madanhmuc']]) ? $folderMap[$product['madanhmuc']] : 'unknown';
                $imagePath = "../img/{$folder}/" . $product['hinhanh'];
                $detailLink = "./book_detail.php?id=" . $product['masach'];
            ?>
            <div class="col custom-col">
                <div class="card shadow-sm product-card text-center">
                    <a href="<?php echo htmlspecialchars($detailLink); ?>">
                        <img
                            src="<?php echo htmlspecialchars($imagePath); ?>"
                            class="card-img-top"
                            alt="<?php echo htmlspecialchars($product['tensach']); ?>"/>
                    </a>
                    <div class="card-body">
                        <a href="<?php echo htmlspecialchars($detailLink); ?>" class="text-decoration-none">
                            <p class="card-title product-title">
                                <?php echo htmlspecialchars($product['tensach']); ?>
                            </p>
                        </a>
                        <p class="card-text text-danger product-price">
                            <?php echo number_format($product['gia'], 0, ',', '.') . ' VNĐ'; ?>
                        </p>
                        <button class="btn btn-sm btn-primary add-to-cart" data-id="<?php echo htmlspecialchars($product['masach']); ?>">
                            Thêm vào giỏ hàng
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Thanh phân trang -->
        <nav class="mt-4" id="pagination-container" style="display: none;">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <!-- Nút "Xem thêm" -->
        <div id="show-pagination-btn" class="text-center mt-3">
            <button class="btn btn-primary">Xem thêm</button>
        </div>
    </div>

    <!-- Thông báo hiển thị khi thêm vào giỏ hàng -->
    <div id="notification"></div>

    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>
    <!-- Custom JS -->
    <script>
        const showPaginationBtn = document.getElementById('show-pagination-btn');
        const paginationContainer = document.getElementById('pagination-container');
        
        // Kiểm tra nếu trang hiện tại là trang 2, thì hiển thị số trang và ẩn nút "Xem thêm"
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = parseInt(urlParams.get('page')) || 1;

        if (currentPage > 1) {
            paginationContainer.style.display = 'block'; // Hiển thị phân trang khi ở trang 2
            showPaginationBtn.style.display = 'none'; // Ẩn nút "Xem thêm" khi không ở trang 1
        }

        // Hiển thị số trang khi nhấn nút "Xem thêm"
        showPaginationBtn.addEventListener('click', () => {
            // Cập nhật URL để chuyển sang trang 2
            urlParams.set('page', 2);
            window.location.search = urlParams.toString(); // Chuyển hướng đến trang 2

            paginationContainer.style.display = 'block'; // Hiển thị số trang
            showPaginationBtn.style.display = 'none'; // Ẩn nút "Xem thêm"
        });
    </script>
</body>
<footer>
  <?php include '../include/footer.php'; ?>
</footer>
</html>
