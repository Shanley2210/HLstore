<?php
session_start();
include '../include/connectdb.php'; 
require 'send_email.php'; // Thêm dòng này để bao gồm file sendmail.php

$pdo = connectDB();
$session_id = session_id(); // Lấy session ID

// Lấy giỏ hàng từ database
$stmt = $pdo->prepare("SELECT giohang.*, sach.tensach, sach.gia, sach.madanhmuc, sach.hinhanh 
                       FROM giohang 
                       JOIN sach ON giohang.masach = sach.masach 
                       WHERE giohang.session_id = ?");
$stmt->execute([$session_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nếu giỏ hàng rỗng, chuyển hướng về trang giỏ hàng
if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

// Tính tổng tiền, tổng số lượng
$total = 0;
$totalQuantity = 0;
foreach ($cartItems as $item) {
    $total += $item['soluong_gh'] * $item['gia'];
    $totalQuantity += $item['soluong_gh'];
}

// Xác định phí vận chuyển dựa trên tổng tiền
$shippingFee = ($total >= 500000) ? 0 : 30000;

$totalWithShipping = $total + $shippingFee; // Tổng cộng bao gồm phí vận chuyển

// Biến để lưu thông báo lỗi
$errorMessage = "";

// Hàm kiểm tra số điện thoại hợp lệ
function validatePhoneNumber($phone) {
    if (preg_match('/^0\d{9}$/', $phone)) {
        return true; // Số điện thoại hợp lệ
    } else {
        return false; // Số điện thoại không hợp lệ
    }
}

// Xử lý khi người dùng nhấn nút Đặt Hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin từ form
    $tenkh = $_POST['tenkh'] ?? '';
    $emailkh = $_POST['emailkh'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $diachi = $_POST['diachi'] ?? '';
    $pt_thanhtoan = $_POST['pt_thanhtoan'] ?? 'COD';

    // Kiểm tra dữ liệu có đầy đủ không
    if (empty($tenkh) || empty($emailkh) || empty($sdt) || empty($diachi)) {
        $errorMessage = "Vui lòng điền đầy đủ thông tin hóa đơn!";
    } elseif (!validatePhoneNumber($sdt)) {
        $errorMessage = "Số điện thoại không hợp lệ. Vui lòng nhập lại!";
    } else {
        // Lưu hóa đơn vào database
        $stmt = $pdo->prepare("INSERT INTO hoadon (tenkh, emailkh, sdt, diachi, ngaylaphd, tongtien, tongsoluong, trangthai, pt_thanhtoan, session_id) 
                                VALUES (?, ?, ?, ?, NOW(), ?, ?, 'Đang xử lý', ?, ?)");
        $stmt->execute([$tenkh, $emailkh, $sdt, $diachi, $total, $totalQuantity, $pt_thanhtoan, $session_id]);

        $mahd = $pdo->lastInsertId(); // Lấy mã hóa đơn vừa tạo

        // Lưu chi tiết hóa đơn
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("INSERT INTO chitiet_hoadon (mahd, masp, soluongsp, dongia, tongtien) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $mahd,
                $item['masach'],
                $item['soluong_gh'],
                $item['gia'],
                $item['soluong_gh'] * $item['gia']
            ]);
        }

        // Xóa giỏ hàng
        $stmt = $pdo->prepare("DELETE FROM giohang WHERE session_id = ?");
        $stmt->execute([$session_id]);

        // Gửi hóa đơn qua email
        $emailResult = sendInvoiceEmail($tenkh, $emailkh, $mahd, $totalWithShipping, $pt_thanhtoan, $diachi, $sdt, $cartItems);


        // Xử lý theo phương thức thanh toán
        if ($pt_thanhtoan === 'QRMoMo') {
            $queryData = http_build_query([
                'mahd' => $mahd,
                'totalWithShipping' => $totalWithShipping,
                'orderInfo' => "Hóa đơn #$mahd - Thanh toán qua QR MoMo",
                'pt_thanhtoan' => $pt_thanhtoan,
                'tenkh' => $tenkh,
                'emailkh' => $emailkh,
                'sdt' => $sdt,
                'diachi' => $diachi,
            ]);
        
            header("Location: momo_payment.php?$queryData");
            exit();
        }if ($pt_thanhtoan === 'MomoATM') {
            $queryData = http_build_query([
                'mahd' => $mahd,
                'totalWithShipping' => $totalWithShipping,
                'orderInfo' => "Hóa đơn #$mahd - Thanh toán qua ATM",
                'pt_thanhtoan' => $pt_thanhtoan,
                'tenkh' => $tenkh,
                'emailkh' => $emailkh,
                'sdt' => $sdt,
                'diachi' => $diachi,
            ]);
        
            header("Location: momo_atm.php?$queryData");
            exit();
        } else  {
            // Kiểm tra nếu email không gửi được
            if ($emailResult !== true) {
                $errorMessage = "Đặt hàng thành công nhưng không thể gửi email: $emailResult";
            } else {
                // Chuyển hướng đến trang cảm ơn
                header("Location: thankyou.php");
            }
        }
        exit();
    }
     // Ánh xạ danh mục sang thư mục ảnh
     $folderMap = [
        'dm001' => 'SachKyNang',
        'dm002' => 'SachLapTrinh',
        'dm003' => 'SachLichSu',
        'dm004' => 'SachNgoaiNgu'
    ];
}
