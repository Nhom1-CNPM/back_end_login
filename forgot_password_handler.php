<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = isset($data['email']) ? trim($data['email']) : '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email không được để trống!']);
        exit();
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(16)); // Token reset
        $uid = $user['id'];
        $expiry_time = date('Y-m-d H:i:s', strtotime('+5 minutes')); // Hết hạn sau 5 phút

        // Lưu token vào bảng reset_tokens
        $stmt = $conn->prepare("INSERT INTO reset_tokens (uid, token, expiry_time) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $uid, $token, $expiry_time);
        $stmt->execute();

        // Gửi email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tranthientrieu2004@gmail.com';
            $mail->Password = 'owpj fdcm hwyu erlo'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('tranthientrieu2004@gmail.com', 'YourAppName');
            $mail->addAddress($email);
            $mail->Subject = 'RESET PASSWORD REQUEST!';
            $reset_link = "http://localhost:3000/reset-password?uid=$uid&token=$token";
            $mail->Body = "Nhấn vào đây để đặt lại mật khẩu: $reset_link";

            $mail->send();

            echo json_encode([
                'success' => true,
                'message' => 'Email đặt lại mật khẩu đã được gửi. Yêu cầu sẽ hết hạn sau 5 phút!'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi gửi email: ' . $mail->ErrorInfo
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email không tồn tại!'
        ]);
    }
}

$conn->close();
exit();
?>
