<?php
session_start();
include '../include/connectdb.php';

$pdo = connectDB();
$session_id = session_id(); // Lấy session ID của người dùng

// Lấy tất cả các sản phẩm trong giỏ hàng dựa trên session ID
$stmt = $pdo->prepare("SELECT * FROM giohang WHERE session_id = ?");
$stmt->execute([$session_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ánh xạ danh mục trong database sang thư mục
$folderMap = [
    'dm001' => 'SachKyNang',
    'dm002' => 'SachLapTrinh',
    'dm003' => 'SachLichSu',
    'dm004' => 'SachNgoaiNgu',
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <!-- Bootstrap Icon -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../include/navbar.php'; ?>

<div class="container mt-5">
<?php if (empty($cartItems)) : ?>
    <div class="alert alert-info text-center">
        Giỏ hàng của bạn hiện tại không có sản phẩm nào.
    </div>
<?php else : ?>
    <h3 class="text-center mb-4">Giỏ Hàng Của Bạn</h3>
    <form action="checkout.php" method="POST">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Ảnh</th>
                    <th>Tên Sách</th>
                    <th>Giá</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody id="cart-body">
                <?php
                $total = 0;
                foreach ($cartItems as $item) {
                    $stmt = $pdo->prepare("SELECT * FROM sach WHERE masach = ?");
                    $stmt->execute([$item['masach']]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);

                    $totalPrice = $product['gia'] * $item['soluong_gh'];
                    $total += $totalPrice;
                    $imagePath = "../img/" . htmlspecialchars($folderMap[$product['madanhmuc']]) . '/' . htmlspecialchars($product['hinhanh']);
                ?>
                <tr data-id="<?= $item['masach'] ?>">
                    <td><img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['tensach']) ?>" class="img-thumbnail" style="width: 80px; height: auto;"></td>
                    <td><?= htmlspecialchars($product['tensach']) ?></td>
                    <td><?= number_format($product['gia'], 0, ',', '.') ?> VNĐ</td>
                    <td>
                        <input type="number" class="form-control text-center quantity-input" style="width: 70px;" min="1"
                               value="<?= $item['soluong_gh'] ?>" 
                               data-id="<?= $item['masach'] ?>" 
                               data-price="<?= $product['gia'] ?>">
                    </td>
                    <td class="subtotal"><?= number_format($totalPrice, 0, ',', '.') ?> VNĐ</td>
                    <td>
                        <button class="btn btn-danger btn-sm remove-item" data-id="<?= $item['masach'] ?>">Xóa</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Tổng Cộng</strong></td>
                    <td><strong id="total"><?= number_format($total, 0, ',', '.') ?> VNĐ</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Thêm lại phần nút -->
        <div class="d-flex justify-content-between">
            <a href="/index.php" class="btn btn-secondary">Tiếp Tục Mua Sắm</a>
            <button type="submit" class="btn btn-success">Thanh Toán</button>
        </div>
    </form>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/script/cart.js"></script>
</body>
</html>
