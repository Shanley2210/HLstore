<?php
session_start();
include '../include/connectdb.php';

$session_id = session_id();
$pdo = connectDB();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(soluong_gh), 0) AS total_items FROM giohang WHERE session_id = ?");
$stmt->execute([$session_id]);
$cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'];

echo json_encode(['cartCount' => $cartCount ?? 0]); // Đảm bảo luôn trả về số, mặc định là 0
?>
