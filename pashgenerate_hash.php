<?php
// Đoạn mã băm mật khẩu
$password = 'admin123';  // Mật khẩu bạn muốn sử dụng
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// In ra mật khẩu đã băm
echo "Mật khẩu đã băm: " . $hashed_password;
?>
