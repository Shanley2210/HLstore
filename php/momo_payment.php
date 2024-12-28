<?php
include '../include/connectdb.php';
session_start();

$pdo = connectDB();
$session_id = session_id();

header('Content-type: text/html; charset=utf-8');

function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
    }

    curl_close($ch);
    return $result;
}

// Kiểm tra và lấy mahd từ URL
$mahd = $_GET['mahd'] ?? '';
if (empty($mahd)) {
    // Tạo mã hóa đơn mới nếu không có
    $mahd = time(); // hoặc có thể dùng uniqid()
}

$totalWithShipping = $_GET['totalWithShipping'] ?? 0;
$orderInfo = $_GET['orderInfo'] ?? "Thanh toán qua QR MoMo";
$tenkh = $_GET['tenkh'] ?? '';
$emailkh = $_GET['emailkh'] ?? '';
$sdt = $_GET['sdt'] ?? '';
$diachi = $_GET['diachi'] ?? '';

$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
$orderId = "HD_$mahd";
$redirectUrl = "http://localhost:3000/php/thankyou.php";
$ipnUrl = "http://localhost:3000/php/ipn_handler.php";
$requestId = time();
$requestType = "captureWallet";

$amount = (int)$totalWithShipping;

// Thêm các tham số bổ sung vào redirectUrl
$redirectUrl = "http://localhost:3000/php/thankyou.php?" . http_build_query([
    'mahd' => $mahd,
    'pt_thanhtoan' => 'QRMoMo',
    'tenkh' => $tenkh,
    'emailkh' => $emailkh,
    'sdt' => $sdt,
    'diachi' => $diachi,
    'totalWithShipping' => $totalWithShipping
]);

// Lấy thông tin giỏ hàng và tính tổng số lượng trước khi lưu
$stmt = $pdo->prepare("SELECT giohang.*, sach.tensach, sach.gia 
                       FROM giohang 
                       JOIN sach ON giohang.masach = sach.masach 
                       WHERE giohang.session_id = ?");
$stmt->execute([$session_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng số lượng
$totalQuantity = 0;
foreach ($cartItems as $item) {
    $totalQuantity += $item['soluong_gh'];
}

// Lưu vào CSDL trước khi chuyển sang MoMo
try {
    $pdo->beginTransaction();

    // Lưu thông tin vào bảng hoadon
    $stmt = $pdo->prepare("INSERT INTO hoadon (tenkh, emailkh, sdt, diachi, pt_thanhtoan, tongtien, tongsoluong, ngaylaphd, trangthai, session_id) 
                          VALUES (?, ?, ?, ?, 'QRMoMo', ?, ?, NOW(), 'Chờ thanh toán', ?)");
    $stmt->execute([$tenkh, $emailkh, $sdt, $diachi, $totalWithShipping, $totalQuantity, $session_id]);

    // Lấy mahd vừa tạo
    $mahd = $pdo->lastInsertId();

    // Lưu chi tiết từng sản phẩm
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("INSERT INTO chitiet_hoadon (mahd, masp, soluongsp, dongia, tongtien) 
                              VALUES (?, ?, ?, ?, ?)");
        $tongtien_sp = $item['soluong_gh'] * $item['gia'];
        $stmt->execute([$mahd, $item['masach'], $item['soluong_gh'], $item['gia'], $tongtien_sp]);
    }

    $pdo->commit();

    // Cập nhật orderId với mã hóa đơn mới
    $orderId = "HD_$mahd";
    
    // Cập nhật redirectUrl với mahd mới
    $redirectUrl = "http://localhost:3000/php/thankyou.php?" . http_build_query([
        'mahd' => $mahd,
        'pt_thanhtoan' => 'QRMoMo',
        'tenkh' => $tenkh,
        'emailkh' => $emailkh,
        'sdt' => $sdt,
        'diachi' => $diachi,
        'totalWithShipping' => $totalWithShipping
    ]);

    // Tạo chữ ký mới với mahd đã cập nhật
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    // Ghi log chữ ký để kiểm tra
    // echo "Raw Hash: " . $rawHash . "<br>";
    // echo "Signature: " . $signature . "<br>";

    $data = [
        'partnerCode' => $partnerCode,
        'partnerName' => "Test",
        "storeId" => "MomoTestStore",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
        'extraData' => '',
        'requestType' => $requestType,
        'signature' => $signature
    ];

    // Gửi yêu cầu đến API MoMo
    $result = execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true);

    // Kiểm tra kết quả từ MoMo
    if (!isset($jsonResult['payUrl'])) {
        throw new Exception("Không nhận được URL thanh toán từ MoMo: " . print_r($jsonResult, true));
    }

    // Chuyển hướng đến URL thanh toán của MoMo
    header('Location: ' . $jsonResult['payUrl']);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    
    // Ghi log lỗi
    error_log("Lỗi thanh toán MoMo: " . $e->getMessage());
    
    // Chuyển hướng đến trang fail với thông tin lỗi
    $errorData = http_build_query([
        'mahd' => $mahd,
        'pt_thanhtoan' => 'QRMoMo',
        'error' => $e->getMessage()
    ]);
    header("Location: fail.php?$errorData");
    exit();
}
?>
