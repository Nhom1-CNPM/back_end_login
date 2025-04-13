<?php
require_once 'config.php'; // File kết nối database
$conn = getDBConnection();
$conn->query("DELETE FROM reset_tokens WHERE expiry_time < NOW()");
$conn->close();
echo "Đã xóa các token hết hạn.";
?>