<?php
include '../include/connectdb.php';

// Kết nối cơ sở dữ liệu
$conn = connectDB();

// Lấy từ khóa từ AJAX request
$query = $_GET['query'] ?? '';

header('Content-Type: application/json');

if (!empty($query)) {
    // Tìm kiếm từ khóa trong bảng `sach`
    $sql = "SELECT tensach FROM sach WHERE tensach LIKE :query LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Trả về kết quả dưới dạng JSON
    echo json_encode($results);
    exit;
}

// Trả về mảng rỗng nếu không có query
echo json_encode([]);
exit;
?>
