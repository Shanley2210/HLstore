<?php
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

$mahd = $_GET['mahd'] ?? '';
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

// Tạo chữ ký (signature)
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

// Kiểm tra phản hồi từ MoMo
if (!isset($jsonResult['payUrl'])) {
    echo "Lỗi: Không nhận được URL thanh toán từ MoMo.<br>";
    echo "Phản hồi từ API MoMo:<br>";
    echo "<pre>" . print_r($jsonResult, true) . "</pre>";
    exit;
}

// Chuyển hướng người dùng đến URL thanh toán của MoMo
header('Location: ' . $jsonResult['payUrl']);
exit();
?>
