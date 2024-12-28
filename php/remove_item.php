<?php
session_start();
include '../include/connectdb.php';

$pdo = connectDB();
$session_id = session_id(); // Lấy session ID

// Nhận dữ liệu JSON từ request
$data = json_decode(file_get_contents("php://input"), true);
$masach = $data['masach'] ?? null;

if ($masach) {
    try {
        // Tìm cart_id từ bảng giohang
        $stmt = $pdo->prepare("SELECT id FROM giohang WHERE session_id = ? AND masach = ?");
        $stmt->execute([$session_id, $masach]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            $cart_id = $cart['id'];

            // Xóa sản phẩm trong chitiet_giohang
            $stmt = $pdo->prepare("DELETE FROM chitiet_giohang WHERE cart_id = ? AND masach = ?");
            $stmt->execute([$cart_id, $masach]);

            // Xóa sản phẩm trong giohang
            $stmt = $pdo->prepare("DELETE FROM giohang WHERE id = ?");
            $stmt->execute([$cart_id]);

            echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ hàng.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không có sản phẩm để xóa.']);
}
