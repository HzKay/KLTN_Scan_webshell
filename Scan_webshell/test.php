<?php
// Kết nối MySQL
include_once ("./class/clsSendReq.php");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signature_database";

$req = new clsSendReq();
// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
echo "Kết nối thành công\n";

// Chuyển đổi JSON thành mảng PHP
$data = $req->syncDown();

// Kiểm tra dữ liệu
if (!isset($data['signature']) || !is_array($data['signature'])) {
    die("Dữ liệu JSON không hợp lệ\n");
}

// Chuẩn bị câu truy vấn
$query = "INSERT INTO signatures (number, pattern) VALUES (?, ?)";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Chuẩn bị truy vấn thất bại: " . $conn->error);
}

// Duyệt qua các phần tử trong "signature"
foreach ($data['signature'] as $item) {
    foreach ($item as $number => $base64_pattern) {
        $decoded_pattern = base64_decode($base64_pattern);
        // Gán giá trị vào câu truy vấn
        $stmt->bind_param("is", $number, $decoded_pattern);

        // Thực thi truy vấn
        if ($stmt->execute()) {
            echo "Đã thêm: $number\n";
        } else {
            echo "Lỗi khi thêm: " . $stmt->error . "\n";
        }
    }
}

// Đóng câu truy vấn và kết nối
$stmt->close();
$conn->close();
?>
