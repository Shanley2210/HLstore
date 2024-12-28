<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: loginad.php");
    exit();
}

// Kết nối cơ sở dữ liệu
// Kết nối cơ sở dữ liệu
require_once 'db.php';
$conn = getDbConnection();


// Xử lý cập nhật trạng thái hóa đơn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_invoice'])) {
    $mahd = $_POST['mahd'];
    $trangthai = $_POST['trangthai'];
    $sql = "UPDATE hoadon SET trangthai='$trangthai' WHERE mahd=$mahd";
    $conn->query($sql);
}

// Xử lý xóa hóa đơn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_invoice'])) {
    $mahd = $_POST['mahd'];
    $sql = "DELETE FROM hoadon WHERE mahd=$mahd";
    $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý hóa đơn</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- Thanh menu -->
    <div class="menu">
        <a href="quanlysach.php">Quản lý sách</a>
        <a href="quanlyhoadon.php">Quản lý hóa đơn</a>
        <a href="logoutad.php">Đăng xuất</a>
    </div>

    <h1>Quản lý hóa đơn</h1>

    <table>
        <thead>
            <tr>
                <th>Mã hóa đơn</th>
                <th>Tên khách hàng</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Địa chỉ</th>
                <th>Phương thức thanh toán</th>
                <th>Tổng số lượng</th>
                <th>Tổng tiền</th>
                <th>Ngày lập</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM hoadon ORDER BY ngaylaphd DESC");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['mahd']}</td>
                    <td>{$row['tenkh']}</td>
                    <td>{$row['emailkh']}</td>
                    <td>{$row['sdt']}</td>
                    <td>{$row['diachi']}</td>
                    <td>{$row['pt_thanhtoan']}</td>
                    <td>{$row['tongsoluong']}</td>
                    <td>{$row['tongtien']}</td>
                    <td>{$row['ngaylaphd']}</td>
                    <td>{$row['trangthai']}</td>
                    <td>
                        <!-- Cập nhật trạng thái -->
                        <form method='post' style='display:inline;'>
                            <input type='hidden' name='mahd' value='{$row['mahd']}'>
                            <select name='trangthai'>
                                <option value='Đang xử lý' " . ($row['trangthai'] == 'Đang xử lý' ? 'selected' : '') . ">Đang xử lý</option>
                                <option value='Đã thanh toán' " . ($row['trangthai'] == 'Đã thanh toán' ? 'selected' : '') . ">Đã thanh toán</option>
                                <option value='Hủy' " . ($row['trangthai'] == 'Hủy' ? 'selected' : '') . ">Hủy</option>
                            </select>
                            <button type='submit' name='update_invoice'>Cập nhật</button>
                        </form>
                        <!-- Xóa hóa đơn -->
                        <form method='post' style='display:inline;'>
                            <input type='hidden' name='mahd' value='{$row['mahd']}'>
                            <button type='submit' name='delete_invoice' onclick='return confirm(\"Bạn có chắc chắn muốn xóa hóa đơn này?\");'>Xóa</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php $conn->close(); ?>
