<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sách</title>
   <!-- Nhúng Bootstrap CSS -->
   <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <!-- Nhúng Bootstrap Icon -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <!--Nơi nhúng file CSS riêng -->
    <link rel="stylesheet" href="../style/book_detail.css" />
</head>
<body>

    <?php
        include '../include/navbar.php';
        include '../include/connectdb.php';

        $pdo = connectDB(); // Nhận đối tượng PDO từ hàm connectDB()

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            echo "Không tìm thấy thông tin sách!";
            exit;
        }

        $sql = "SELECT * FROM sach JOIN tacgia ON sach.matacgia = tacgia.matacgia JOIN danhmuc ON sach.madanhmuc = danhmuc.madm WHERE masach = ?";
        $stmt = $pdo->prepare($sql); // Sử dụng biến $pdo từ connectDB()
        $stmt->execute([$id]);
        $book = $stmt->fetch();

        if (!$book) {
            echo "Sách không tồn tại!";
            exit;
        }

        // Ánh xạ danh mục trong database sang thư mục
        $folderMap = [
            'dm001' => 'SachKyNang',
            'dm002' => 'SachLapTrinh',
            'dm003' => 'SachLichSu',
            'dm004' => 'SachNgoaiNgu'
        ];
    ?>

    <div class="container mt-5">
        <div class="row">
            <!-- Left Column: Product Image -->
            <div class="col-md-4 text-center">
                <img src="../img/<?php echo htmlspecialchars($folderMap[$book['madanhmuc']]) . '/' . htmlspecialchars($book['hinhanh']); ?>" alt="<?php echo htmlspecialchars($book['tensach']); ?>" class="img-fluid product-image">
                <button class="btn btn-add-cart btn-block mt-3 w-100">Thêm vào giỏ hàng</button>
            </div>
            
            <!-- Right Column: Product Details -->
            <div class="col-md-8">

                <!-- Book Title -->
                <p class="book-title"><?php echo htmlspecialchars($book['tensach']); ?></p>
                
                <!-- Price -->
                <div class="d-flex align-items-center mb-3">
                    <span class="price me-3"><?php echo number_format($book['gia'], 0, ',', '.') . ' VNĐ'; ?></span>
                </div>

                <!-- Extra Details Section -->
                <div class="row mt-5">
                    <div class="col-12">
                        <h5>Thông tin chi tiết</h5>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>Mã sách</td>
                                    <td><?php echo htmlspecialchars($book['masach']); ?></td>
                                </tr>
                                <tr>
                                    <td>Danh mục</td>
                                    <td><?php echo htmlspecialchars($book['tendanhmuc']); ?></td>
                                </tr>
                                <tr>
                                    <td>Tác giả</td>
                                    <td><?php echo htmlspecialchars($book['tentacgia']); ?></td>
                                </tr> 
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="details-section mt-4">
                    <h5>Thông tin vận chuyển</h5>
                    <ul>
                        <li>Thông tin vận chuyển</li>
                        <li>Thông tin vận chuyển</li>
                    </ul>
                </div>

                <!-- Book Description -->
                <div class="details-section mt-4">
                    <h5>Mô tả sản phẩm</h5>
                    <p><?php echo nl2br(htmlspecialchars($book['mota'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông báo hiển thị khi thêm vào giỏ hàng -->
    <div id="notification"></div>

    <!-- Nhúng Bootstrap JS -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>

 

</body>
<footer>
    <?php include '../include/footer.php'; ?>
</footer>
</html>
