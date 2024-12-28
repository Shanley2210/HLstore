<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: loginad.php");
    exit();
}

// Kết nối cơ sở dữ liệu
require_once 'db.php';
$conn = getDbConnection();

// Thông báo kết quả hành động
$message = "";
$type = ""; // Loại thông báo (success, error)

// Khai báo folderMap toàn cục
$folderMap = [
    'dm001' => ['SachKyNang', 'kn'],
    'dm002' => ['SachLapTrinh', 'lt'],
    'dm003' => ['SachLichSu', 'ls'],
    'dm004' => ['SachNgoaiNgu', 'nn']
];

// Hàm tự động sinh mã sách dựa trên danh mục
function generateBookId($conn, $prefix) {
    $stmt = $conn->prepare("SELECT masach FROM sach WHERE masach LIKE ? ORDER BY masach DESC LIMIT 1");
    $searchPattern = $prefix . '%';
    $stmt->bind_param("s", $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $lastId = (int) substr($result['masach'], strlen($prefix));
        return $prefix . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
    } else {
        return $prefix . '001';
    }
}

// Hàm tự động sinh mã tác giả
function generateAuthorId($conn) {
    $stmt = $conn->prepare("SELECT matacgia FROM tacgia WHERE matacgia LIKE 'tg%' ORDER BY matacgia DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $lastId = (int) substr($result['matacgia'], 2); // Bỏ "tg" và lấy số
        return 'tg' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
    } else {
        return 'tg001';
    }
}

// Hàm đổi tên file ảnh dựa vào mã sách (masach)
function renameImage($conn, $prefix, $masach, $fileExtension) {
    // Lấy ba số cuối của mã sách
    $lastThreeDigits = substr($masach, -3);

    // Tạo tên file ảnh mới
    return $prefix . "_p_" . $lastThreeDigits . $fileExtension;
}

// Xử lý thêm, sửa, xóa sách
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['delete_book'])) {
        $tensach = $_POST['tensach'] ?? '';
        $gia = $_POST['gia'] ?? '';
        $mota = $_POST['mota'] ?? '';
        $tentacgia = $_POST['tentacgia'] ?? '';
        $madanhmuc = $_POST['madanhmuc'] ?? '';
    }

    // Kiểm tra và xử lý tên tác giả
    $matacgia = null;
    if (!empty($tentacgia)) {
        // Kiểm tra tên tác giả đã tồn tại chưa
        $stmt = $conn->prepare("SELECT matacgia FROM tacgia WHERE tentacgia = ?");
        $stmt->bind_param("s", $tentacgia);
        $stmt->execute();
        $author = $stmt->get_result()->fetch_assoc();

        if ($author) {
            $matacgia = $author['matacgia'];
        } else {
            // Tạo mã tác giả mới và thêm vào bảng `tacgia`
            $matacgia = generateAuthorId($conn);
            $stmt = $conn->prepare("INSERT INTO tacgia (matacgia, tentacgia) VALUES (?, ?)");
            $stmt->bind_param("ss", $matacgia, $tentacgia);
            $stmt->execute();
        }
    }

    // Xử lý thêm sách
    if (isset($_POST['add_book'])) {
        if (!isset($folderMap[$madanhmuc])) {
            $message = "Danh mục không hợp lệ.";
            $type = "error";
        } else {
            [$folder, $prefix] = $folderMap[$madanhmuc];
    
            // Tạo thư mục nếu chưa tồn tại
            if (!is_dir(__DIR__ . "/../img/$folder")) {
                mkdir(__DIR__ . "/../img/$folder", 0777, true);
            }
    
            // Xử lý upload hình ảnh
            $hinhanh = '';
            if (!empty($_FILES['hinhanh']['name'])) {
                $fileExtension = strtolower(pathinfo($_FILES['hinhanh']['name'], PATHINFO_EXTENSION));
                
                // Tạo mã sách trước khi gọi renameImage
                $masach = generateBookId($conn, $prefix);
                
                // Gọi hàm renameImage để lấy tên ảnh mới
                $newFileName = renameImage($conn, $prefix, $masach, '.' . $fileExtension);
    
                $targetPath = __DIR__ . "/../img/$folder/" . $newFileName; // Đường dẫn đầy đủ trên server
                if (move_uploaded_file($_FILES['hinhanh']['tmp_name'], $targetPath)) {
                    $hinhanh = $newFileName; // Lưu tên file vào CSDL
                } else {
                    $message = "Lỗi khi tải lên hình ảnh.";
                    $type = "error";
                }
            }
    
            // Lưu vào cơ sở dữ liệu
            $stmt = $conn->prepare("INSERT INTO sach (masach, tensach, gia, mota, hinhanh, matacgia, madanhmuc) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssissss", $masach, $tensach, $gia, $mota, $hinhanh, $matacgia, $madanhmuc);
            if ($stmt->execute()) {
                header("Location: quanlysach.php");
                exit();
            } else {
                $message = "Lỗi khi thêm sách.";
                $type = "error";
            }
        }
    }

    // Xử lý xóa sách
    if (isset($_POST['delete_book'])) {
        $masach = $_POST['masach'] ?? null;

        if ($masach) {
            // Lấy thông tin sách để xóa ảnh
            $stmt = $conn->prepare("SELECT madanhmuc, hinhanh FROM sach WHERE masach = ?");
            $stmt->bind_param("s", $masach);
            $stmt->execute();
            $book = $stmt->get_result()->fetch_assoc();

            if ($book) {
                $folder = $folderMap[$book['madanhmuc']][0];
                $imagePath = __DIR__ . "/../img/$folder/" . $book['hinhanh'];

                // Xóa ảnh nếu tồn tại
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Xóa sách trong CSDL
            $stmt = $conn->prepare("DELETE FROM sach WHERE masach = ?");
            $stmt->bind_param("s", $masach);
            if ($stmt->execute()) {
                header("Location: quanlysach.php");
                exit();
            } else {
                $message = "Lỗi khi xóa sách.";
                $type = "error";
            }
        }
    }

    // Xử lý cập nhật sách
    if (isset($_POST['edit_book'])) {
        $masach = $_POST['masach'];

        if (!isset($folderMap[$madanhmuc])) {
            $message = "Danh mục không hợp lệ.";
            $type = "error";
        } else {
            [$folder, $prefix] = $folderMap[$madanhmuc];

            // Xử lý upload hình ảnh mới (nếu có)
            $hinhanh = '';
            if (!empty($_FILES['hinhanh']['name'])) {
                $fileExtension = strtolower(pathinfo($_FILES['hinhanh']['name'], PATHINFO_EXTENSION));
                // Truyền đủ 4 tham số vào hàm
                $newFileName = renameImage($conn, $prefix, '.' . $fileExtension, $masach);

                $targetPath = __DIR__ . "/../img/$folder/" . $newFileName; // Đường dẫn đầy đủ trên server
                if (move_uploaded_file($_FILES['hinhanh']['tmp_name'], $targetPath)) {
                    $hinhanh = $newFileName; // Lưu tên file vào CSDL
                }
            }

            // Cập nhật cơ sở dữ liệu
            if (!empty($hinhanh)) { // Nếu có ảnh mới
                $stmt = $conn->prepare("UPDATE sach SET tensach = ?, gia = ?, mota = ?, hinhanh = ?, matacgia = ?, madanhmuc = ? WHERE masach = ?");
                $stmt->bind_param("sisssss", $tensach, $gia, $mota, $hinhanh, $matacgia, $madanhmuc, $masach);
            } else {
                $stmt = $conn->prepare("UPDATE sach SET tensach = ?, gia = ?, mota = ?, matacgia = ?, madanhmuc = ? WHERE masach = ?");
                $stmt->bind_param("sissss", $tensach, $gia, $mota, $matacgia, $madanhmuc, $masach);
            }

            if ($stmt->execute()) {
                header("Location: quanlysach.php");
                exit();
            } else {
                $message = "Lỗi khi cập nhật sách.";
                $type = "error";
            }
        }
    }
}

// Lấy thông tin sách cần sửa
$editBook = null;
if (isset($_GET['edit'])) {
    $masach = $_GET['edit'];
    $result = $conn->prepare("SELECT * FROM sach WHERE masach = ?");
    $result->bind_param("s", $masach);
    $result->execute();
    $editBook = $result->get_result()->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sách</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- Thanh menu -->
    <div class="menu">
        <a href="quanlysach.php">Quản lý sách</a>
        <a href="quanlyhoadon.php">Quản lý hóa đơn</a>
        <a href="logoutad.php">Đăng xuất</a>
    </div>
    <h1>Quản lý sách</h1>
    <?php if ($message): ?>
        <div class="<?php echo $type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <h3><?php echo $editBook ? 'Cập nhật sách' : 'Thêm sách mới'; ?></h3>
    <form method="post" enctype="multipart/form-data">
        <?php if ($editBook): ?>
            <input type="hidden" name="masach" value="<?php echo $editBook['masach']; ?>">
        <?php endif; ?>
        <input type="text" name="tensach" placeholder="Tên sách" value="<?php echo $editBook['tensach'] ?? ''; ?>" required>
        <input type="number" name="gia" placeholder="Giá" value="<?php echo $editBook['gia'] ?? ''; ?>" required><br>
        <textarea name="mota" placeholder="Mô tả"><?php echo $editBook['mota'] ?? ''; ?></textarea><br>
        <label for="file-upload" class="upload-label">Chọn ảnh</label>
        <input type="file" id="file-upload" name="hinhanh" accept="image/*">
        <input type="text" name="tentacgia" placeholder="Tên tác giả" value="<?php echo $editBook['tentacgia'] ?? ''; ?>" required>
        <select name="madanhmuc">
            <?php foreach ($folderMap as $key => $value): ?>
                <option value="<?php echo $key; ?>" <?php echo isset($editBook) && $editBook['madanhmuc'] == $key ? 'selected' : ''; ?>>
                    <?php echo $value[0]; ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="<?php echo $editBook ? 'edit_book' : 'add_book'; ?>">
            <?php echo $editBook ? 'Cập nhật' : 'Thêm sách'; ?>
        </button>
    </form>

    <table>
        <tr>
            <th>Mã sách</th><th>Tên sách</th><th>Giá</th><th>Mô tả</th><th>Hình ảnh</th><th>Hành động</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM sach ORDER BY ngaythem DESC");
        while ($row = $result->fetch_assoc()):
            $folder = $folderMap[$row['madanhmuc']][0];
        ?>
            <tr>
                <td><?php echo $row['masach']; ?></td>
                <td><?php echo $row['tensach']; ?></td>
                <td><?php echo $row['gia']; ?></td>
                <td><?php echo $row['mota']; ?></td>
                <td><img src="../img/<?php echo $folder . '/' . $row['hinhanh']; ?>" width="80"></td>
                <td>
                    <a href="?edit=<?php echo $row['masach']; ?> " class="edit-link">Sửa</a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="masach" value="<?php echo $row['masach']; ?>">
                        <button type="submit" name="delete_book">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>
