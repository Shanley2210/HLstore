<?php
session_start();
include '../include/connectdb.php'; // Kết nối cơ sở dữ liệu

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['masach'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm!']);
    exit;
}

$masach = $data['masach'];
$soluong = 1; // Mặc định số lượng thêm vào là 1
$session_id = session_id(); // Lấy session ID hiện tại

try {
    $pdo = connectDB();

    // Kiểm tra sản phẩm đã có trong giỏ hàng hay chưa
    $stmt = $pdo->prepare("SELECT * FROM giohang WHERE session_id = ? AND masach = ?");
    $stmt->execute([$session_id, $masach]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Nếu sản phẩm đã có, tăng số lượng
        $stmt = $pdo->prepare("UPDATE giohang SET soluong_gh = soluong_gh + ? WHERE session_id = ? AND masach = ?");
        $stmt->execute([$soluong, $session_id, $masach]);

        // Cập nhật lại bảng chitiet_giohang
        $stmt = $pdo->prepare("UPDATE chitiet_giohang SET quantity = quantity + ? WHERE cart_id = ? AND masach = ?");
        $stmt->execute([$soluong, $cartItem['id'], $masach]);

    } else {
        // Nếu sản phẩm chưa có, thêm mới vào bảng giohang
        $stmt = $pdo->prepare("INSERT INTO giohang (session_id, masach, soluong_gh) VALUES (?, ?, ?)");
        $stmt->execute([$session_id, $masach, $soluong]);

        // Lấy id của giỏ hàng vừa thêm
        $cart_id = $pdo->lastInsertId(); // Lấy id vừa được tạo trong bảng giohang

        // Lấy giá của sách từ bảng sach
        $stmt = $pdo->prepare("SELECT gia FROM sach WHERE masach = ?");
        $stmt->execute([$masach]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            $gia = $product['gia'];

            // Thêm vào bảng chitiet_giohang
            $stmt = $pdo->prepare("INSERT INTO chitiet_giohang (cart_id, masach, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cart_id, $masach, $soluong, $gia]);
        }
    }

    // Đếm số lượng sản phẩm trong giỏ hàng
    $stmt = $pdo->prepare("SELECT SUM(soluong_gh) AS total_items FROM giohang WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'];

    // Trả về dữ liệu giỏ hàng đã cập nhật
    echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được thêm vào giỏ hàng!', 'cartCount' => $cartCount]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm vào giỏ hàng: ' . $e->getMessage()]);
}
?>
