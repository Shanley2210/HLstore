<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: loginad.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- Thanh menu -->
    <div class="menu">
        <a href="quanlysach.php">Quản lý sách</a>
        <a href="quanlyhoadon.php">Quản lý hóa đơn</a>
        <a href="logoutad.php">Đăng xuất</a>
    </div>

    <h1>Chào mừng bạn đến với trang quản lý Admin</h1>

</body>
</html>
