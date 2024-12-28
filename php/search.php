<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - HLStore</title>
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
</head>
<body>
    <!-- Navbar -->
    <?php
    include '../include/navbar.php';
    include '../include/connectdb.php';

    // Kết nối cơ sở dữ liệu
    $conn = connectDB();

    // Ánh xạ danh mục trong database sang thư mục
    $folderMap = [
        'dm001' => 'SachKyNang',
        'dm002' => 'SachLapTrinh',
        'dm003' => 'SachLichSu',
        'dm004' => 'SachNgoaiNgu'
    ];

    // Lấy từ khóa tìm kiếm từ query string
    $query = $_GET['query'] ?? '';
    $products = [];

    if (!empty($query)) {
        // Truy vấn sản phẩm theo từ khóa
        $sql = "SELECT * FROM sach WHERE tensach LIKE :query ORDER BY ngaythem DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    ?>

    <div class="container mt-5">
        <h2>Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($query); ?>"</h2>
        <div class="row g-3 justify-content-center">
            <?php if (empty($products)): ?>
                <div class="alert alert-warning text-center">
                    Không tìm thấy sản phẩm nào khớp với từ khóa.
                </div>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </div>

    <div id="notification"></div>
    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>
    <script src="/script/autocomplete.js"></script>
</body>
<footer>
  <?php include '../include/footer.php'; ?>
</footer>
</html>
