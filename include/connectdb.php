<?php
$pdo = null; // Biến toàn cục để lưu kết nối PDO

function connectDB() {
    global $pdo; // Cho phép hàm truy cập biến toàn cục
    if ($pdo === null) { // Đảm bảo kết nối chỉ được tạo một lần
        $servername = "localhost";
        $database = "webbansachv2";
        $username = "root";
        $password = "";

        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Kết nối thất bại: " . $e->getMessage());
        }
    }
    return $pdo; // Trả về kết nối nếu cần sử dụng
}

function selectSQL($sql) {
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $conn = null; // Đóng kết nối
    return $result;
}
?>
