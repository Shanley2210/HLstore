<?php
include 'checkout_process.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../include/navbar1.php'; ?>

<div class="container mt-5">
    <!-- Hiển thị lỗi nếu có -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Cột bên trái: Chi tiết giỏ hàng -->
        <div class="col-md-7">
            <h3>Chi Tiết Giỏ Hàng</h3>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Hình Ảnh</th>
                        <th>Tên Sách</th>
                        <th>Giá</th>
                        <th>Số Lượng</th>
                        <th>Thành Tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <?php 
                            // Lấy đường dẫn hình ảnh
                            $imagePath = "../img/" . htmlspecialchars($folderMap[$item['madanhmuc']]) . '/' . htmlspecialchars($item['hinhanh']);
                        ?>
                        <tr>
                            <td>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['tensach']) ?>" 
                                     class="img-thumbnail" style="width: 80px; height: auto;">
                            </td>
                            <td><?= htmlspecialchars($item['tensach']) ?></td>
                            <td><?= number_format($item['gia'], 0, ',', '.') ?> VNĐ</td>
                            <td><?= $item['soluong_gh'] ?></td>
                            <td><?= number_format($item['soluong_gh'] * $item['gia'], 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <!-- Gộp các cột và tạo bảng con -->
                        <td colspan="5" class="text-center">
                            <table class="table table-borderless m-0 text-center align-middle">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tổng Số Lượng:</strong></td>
                                <td><?= $totalQuantity ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Phí Vận Chuyển:</strong></td>
                                <td><?= number_format($shippingFee, 0, ',', '.') ?> VNĐ</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tổng Cộng:</strong></td>
                                <td><?= number_format($totalWithShipping, 0, ',', '.') ?> VNĐ</td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    
                </tfoot>


            </table>
        </div>

        <!-- Cột bên phải: Thông tin hóa đơn -->
        <div class="col-md-5">
            <h3>1. THÔNG TIN HÓA ĐƠN</h3>
            <form action="checkout.php" method="post">
                <div class="mb-3">
                    <label for="tenkh" class="form-label">Họ Tên:</label>
                    <input type="text" class="form-control" id="tenkh" name="tenkh" required>
                </div>
                <div class="mb-3">
                    <label for="emailkh" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="emailkh" name="emailkh" required>
                </div>
                <div class="mb-3">
                    <label for="sdt" class="form-label">Số Điện Thoại:</label>
                    <input type="text" class="form-control" id="sdt" name="sdt" required>
                </div>
                <div class="mb-3">
                    <label for="diachi" class="form-label">Địa Chỉ:</label>
                    <input type="text" class="form-control" id="diachi" name="diachi" required>
                </div>
                <div class="mb-3">
            <label for="pt_thanhtoan" class="form-label">Phương Thức Thanh Toán:</label>
            <div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="pt_thanhtoan" id="pt_thanhtoan_cod" value="COD" checked>
                    <label class="form-check-label" for="pt_thanhtoan_cod">
                        Thanh toán khi nhận hàng (COD)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="pt_thanhtoan" id="pt_thanhtoan_momo" value="QRMoMo">
                    <label class="form-check-label" for="pt_thanhtoan_momo">
                        Thanh toán qua QR MoMo
                    </label>
                </div><div class="form-check">
                    <input class="form-check-input" type="radio" name="pt_thanhtoan" id="pt_thanhtoan_momoThe" value="MomoATM">
                    <label class="form-check-label" for="pt_thanhtoan_momoThe">
                        Thanh toán qua MoMo ATM
                    </label>
                </div>
            </div>
        </div>

                <button type="submit" class="btn btn-primary w-100">Đặt Hàng</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>