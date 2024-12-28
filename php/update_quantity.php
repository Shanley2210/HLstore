<?php
session_start();
include '../include/connectdb.php';

$pdo = connectDB();
$data = json_decode(file_get_contents('php://input'), true);
$masach = $data['masach'];
$quantity = $data['quantity'];

// Kiểm tra dữ liệu
if (!is_numeric($quantity) || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ.']);
    exit;
}

// Cập nhật số lượng trong cơ sở dữ liệu
$session_id = session_id();
$stmt = $pdo->prepare("UPDATE giohang SET soluong_gh = ? WHERE masach = ? AND session_id = ?");
$stmt->execute([$quantity, $masach, $session_id]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật giỏ hàng.']);
}
