<?php
session_start();
$error = "";

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Kết nối cơ sở dữ liệu
    require_once 'db.php';
    $conn = getDbConnection();


    // Kiểm tra tài khoản và mật khẩu
    $sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Đăng nhập thành công
        $_SESSION['admin'] = $username;
        header("Location: admin.php");
        exit();
    } else {
        // Đăng nhập thất bại
        $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập quản trị viên</title>
    <link rel="stylesheet" href="loginad.css">
</head>
<body>

<div class="login-container">
    <h2>Đăng nhập quản trị viên</h2>

    <!-- Hiển thị thông báo lỗi nếu có -->
    <?php if ($error): ?>
        <div class="error-message"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="loginad.php">
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng nhập</button>
    </form>
</div>

</body>
</html>
