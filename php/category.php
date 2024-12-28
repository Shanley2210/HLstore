<?php
// category.php
include '../include/connectdb.php'; // Kết nối cơ sở dữ liệu

// Lấy tham số danh mục từ URL
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Kiểm tra xem danh mục có hợp lệ không
if (empty($category)) {
    die("Danh mục không hợp lệ");
}

// Ánh xạ danh mục với mã trong cơ sở dữ liệu
$categoryMap = [
    'sachkynang' => 'dm001',
    'sachlaptrinh' => 'dm002',
    'sachngoaingu' => 'dm004',
    'sachlichsu' => 'dm003'
];

// Mảng ánh xạ danh mục từ mã danh mục
$categoryNames = [
    'sachkynang' => 'Sách kỹ năng',
    'sachlaptrinh' => 'Sách lập trình',
    'sachngoaingu' => 'Sách ngoại ngữ',
    'sachlichsu' => 'Sách lịch sử'
];


// Ánh xạ danh mục trong database sang thư mục hình ảnh
$folderMap = [
    'dm001' => 'SachKyNang',
    'dm002' => 'SachLapTrinh',
    'dm003' => 'SachLichSu',
    'dm004' => 'SachNgoaiNgu'
];

// Kiểm tra nếu danh mục hợp lệ
if (!array_key_exists($category, $categoryMap)) {
    die("Danh mục không tồn tại");
}

// Lấy tên danh mục từ mảng ánh xạ
$categoryName = isset($categoryNames[$category]) ? $categoryNames[$category] : 'Danh mục không xác định';

// Kết nối cơ sở dữ liệu
$conn = connectDB();

// Lấy mã danh mục từ ánh xạ
$categoryCode = $categoryMap[$category];

// Lấy các sách theo danh mục
function loadBooksByCategory($conn, $categoryCode) {
    $sql = "SELECT * FROM sach WHERE madanhmuc = :categoryCode ORDER BY ngaythem DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':categoryCode', $categoryCode, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về danh sách sách thuộc danh mục
}

$products = loadBooksByCategory($conn, $categoryCode);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sách - HLStore</title>
    <!-- Bootstrap Icon -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style/home.css">
</head>
<body>

    <!-- Navbar -->
    <?php include '../include/navbar.php'; ?>

    <div class="container mt-5">
        <?php echo "<h2>$categoryName</h2>"; ?>
        
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
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
