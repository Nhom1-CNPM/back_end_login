<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$uid = isset($data['uid']) ? $data['uid'] : '';
$token = isset($data['token']) ? $data['token'] : '';
$new_password = isset($data['new_password']) ? $data['new_password'] : '';

if (empty($uid) || empty($token) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
    exit();
}

$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
$conn = getDBConnection();

// Kiểm tra uid và token (đảm bảo token chưa hết hạn)
$stmt = $conn->prepare("SELECT * FROM reset_tokens WHERE uid = ? AND token = ? AND expiry_time > NOW()");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . $conn->error]);
    exit();
}
$stmt->bind_param("is", $uid, $token);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows == 1) {
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("si", $new_password_hash, $uid);
    if ($stmt->execute()) {
        $stmt->close();
        // Xóa token sau khi sử dụng
        $stmt = $conn->prepare("DELETE FROM reset_tokens WHERE uid = ? AND token = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param("is", $uid, $token);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cập nhật mật khẩu thất bại!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Liên kết không hợp lệ hoặc đã hết hạn!']);
}

$conn->close();
?>