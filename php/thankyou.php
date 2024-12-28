<?php
session_start();
include '../include/connectdb.php';
require 'send_email.php';

$pdo = connectDB();
$session_id = session_id();

// Lấy thông tin từ URL
$paymentMethod = $_GET['pt_thanhtoan'] ?? 'COD';
$mahd = $_GET['mahd'] ?? '';
$tenkh = $_GET['tenkh'] ?? '';
$emailkh = $_GET['emailkh'] ?? '';
$sdt = $_GET['sdt'] ?? '';
$diachi = $_GET['diachi'] ?? '';
$totalWithShipping = $_GET['totalWithShipping'] ?? 0;

$orderSuccess = false;
$orderItems = [];

// Lấy chi tiết đơn hàng
$stmt = $pdo->prepare("SELECT chitiet_hoadon.*, sach.tensach, sach.madanhmuc, sach.hinhanh
                       FROM chitiet_hoadon 
                       JOIN sach ON chitiet_hoadon.masp = sach.masach 
                       WHERE mahd = ?");
$stmt->execute([$mahd]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý thanh toán
if ($paymentMethod === 'QRMoMo' || $paymentMethod === 'MomoATM') {
    // Lấy các tham số trả về từ MoMo
    $partnerCode = $_GET['partnerCode'] ?? '';
    $orderId = $_GET['orderId'] ?? '';
    $requestId = $_GET['requestId'] ?? '';
    $amount = $_GET['amount'] ?? '';
    $orderInfo = $_GET['orderInfo'] ?? '';
    $orderType = $_GET['orderType'] ?? '';
    $transId = $_GET['transId'] ?? '';
    $resultCode = $_GET['resultCode'] ?? '';
    $message = $_GET['message'] ?? '';
    $payType = $_GET['payType'] ?? '';
    $responseTime = $_GET['responseTime'] ?? '';
    $extraData = $_GET['extraData'] ?? '';
    $signature = $_GET['signature'] ?? '';

    // Lưu thông tin thanh toán MoMo vào CSDL
    try {
        $stmt = $pdo->prepare("INSERT INTO momo (mahd, partnerCode, orderId, requestId, amount, orderInfo, 
                              orderType, transId, resultCode, message, payType, responseTime, extraData, signature) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $mahd, $partnerCode, $orderId, $requestId, $amount, $orderInfo,
            $orderType, $transId, $resultCode, $message, $payType, $responseTime, $extraData, $signature
        ]);
    } catch (PDOException $e) {
        // Xử lý lỗi nếu cần
        error_log("Lỗi lưu thông tin MoMo: " . $e->getMessage());
    }

    if ($resultCode == '0') { // Giao dịch thành công
        // Process order and save to database
        processAndSaveOrder($pdo, $mahd, $tenkh, $sdt, $totalWithShipping, $orderItems, $session_id, $paymentMethod);
        
        // Send email
        sendInvoiceEmail($tenkh, $emailkh, $mahd, $totalWithShipping, $paymentMethod, $diachi, $sdt, $orderItems);
        
        $orderSuccess = true;
        echo "<div class='alert alert-success'>Thanh toán thành công! Cảm ơn bạn đã mua hàng.</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE hoadon SET trangthai = 'Hủy' WHERE mahd = ?");
        $stmt->execute([$mahd]);
        echo "<div class='alert alert-danger'>Thanh toán thất bại. Vui lòng thử lại.</div>";
    }
} elseif ($paymentMethod === 'COD') {
    // Process order and save to database
    processAndSaveOrder($pdo, $mahd, $tenkh, $sdt, $totalWithShipping, $orderItems, $session_id, $paymentMethod);
    
    // Send email
    sendInvoiceEmail($tenkh, $emailkh, $mahd, $totalWithShipping, $paymentMethod, $diachi, $sdt, $orderItems);
    
    $orderSuccess = true;
    echo "<div class='alert alert-success'>Đặt hàng thành công! Bạn sẽ thanh toán khi nhận hàng.</div>";
} else {
    echo "<div class='alert alert-danger'>Phương thức thanh toán không hợp lệ.</div>";
}

// Hàm xử lý và lưu đơn hàng
function processAndSaveOrder($pdo, $mahd, $tenkh, $sdt, $totalWithShipping, $orderItems, $session_id, $paymentMethod) {
    // Tính tổng số lượng
    $totalQuantity = array_sum(array_column($orderItems, 'soluongsp'));
    
    // Lưu thông tin đơn hàng
    $stmt = $pdo->prepare("INSERT INTO donhang (mahd, tenkh, sdt, thanhtien, soluong, ngaydat) 
                          VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$mahd, $tenkh, $sdt, $totalWithShipping, $totalQuantity]);
    
    $orderId = $pdo->lastInsertId();
    
    // Lưu chi tiết đơn hàng
    foreach ($orderItems as $item) {
        $stmt = $pdo->prepare("INSERT INTO chitiet_donhang (order_id, masach, tensp, giasp, soluong, tonggia) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $orderId,
            $item['masp'],
            $item['tensach'] ?? 'Chưa xác định',
            $item['dongia'] ?? 0,
            $item['soluongsp'] ?? 0,
            ($item['soluongsp'] ?? 0) * ($item['dongia'] ?? 0)
        ]);
    }
    
    // Cập nhật trạng thái hóa đơn
    $stmt = $pdo->prepare("UPDATE hoadon SET trangthai = ? WHERE mahd = ?");
    $stmt->execute([($paymentMethod === 'COD' ? 'Chờ xác nhận' : 'Đã thanh toán'), $mahd]);
    
    // Xóa giỏ hàng
    $stmt = $pdo->prepare("DELETE FROM giohang WHERE session_id = ?");
    $stmt->execute([$session_id]);
}

// Ánh xạ danh mục
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
    <title>Trạng thái đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php if ($orderSuccess): ?>
            <!-- Hiển thị thông tin đơn hàng chỉ khi thanh toán thành công -->
            <div class="row mt-4">
                <!-- Thông tin khách hàng -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5>Thông tin khách hàng</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Tên khách hàng:</strong> <?= htmlspecialchars($tenkh) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($emailkh) ?></p>
                            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($sdt) ?></p>
                            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($diachi) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Chi tiết đơn hàng -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5>Đơn Hàng</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tên sách</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <?php
                                        // Đặt hình ảnh mặc định nếu 'hinhanh' không tồn tại
                                        $imageFileName = !empty($item['hinhanh']) ? htmlspecialchars($item['hinhanh']) : 'default-image.jpg';

                                        // Tạo đường dẫn hình ảnh dựa trên danh mục (madanhmuc) và tên file hình (hinhanh)
                                        $imagePath = "../img/" . htmlspecialchars($folderMap[$item['madanhmuc']]) . '/' . $imageFileName;
                                    ?>
                                    <tr>
                                        <td>
                                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['tensach']) ?>" 
                                                class="img-thumbnail" style="width: 80px; height: auto;">
                                        </td>
                                        <td><?= htmlspecialchars($item['tensach'] ?? 'Chưa xác định') ?></td>
                                        <td><?= htmlspecialchars($item['soluongsp'] ?? 0) ?></td>
                                        <td><?= number_format($item['dongia'] ?? 0, 0, ',', '.') ?> đ</td>
                                        <td><?= number_format(($item['soluongsp'] ?? 0) * ($item['dongia'] ?? 0), 0, ',', '.') ?> đ</td>
                                    </tr>
                                <?php endforeach; ?>


                                </tbody>
                            </table>
                            <h5 class="mt-3">Tổng tiền:</h5>
                            <p><strong>Thành tiền:</strong> <?= number_format($totalWithShipping, 0, ',', '.') ?> đ <em>(Đã bao gồm tiền vận chuyển)</em></p>

                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Nút quay lại -->
        <div class="text-center mt-4">
            <a href="../index.php" class="btn btn-primary">Quay lại trang chủ</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

