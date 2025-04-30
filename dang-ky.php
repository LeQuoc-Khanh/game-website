<?php
// Kết nối đến database
$conn = new mysqli("localhost", "root", "", "game-website");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy thông tin từ form
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = 'user';  // Mặc định là user

    // Kiểm tra mật khẩu và xác nhận mật khẩu
    if ($password !== $confirmPassword) {
        echo "<script>alert('Mật khẩu và xác nhận mật khẩu không khớp!');</script>";
    } else {
        // Kiểm tra username hoặc email đã tồn tại chưa
        $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Kiểm tra xem tên đăng nhập đã tồn tại
            while ($row = $result->fetch_assoc()) {
                if ($row['username'] === $username) {
                    echo "<script>alert('Tên đăng nhập đã tồn tại! Vui lòng chọn tên đăng nhập khác.');</script>";
                }
                // Kiểm tra xem email đã tồn tại
                if ($row['email'] === $email) {
                    echo "<script>alert('Email đã được sử dụng! Vui lòng nhập email khác.');</script>";
                }
            }
        } else {
            // Chưa tồn tại → tiến hành đăng ký
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss",$email, $username, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                echo "<script>
                    alert('Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.'); 
                    window.location.href = '/game-website/dang-nhap/dang-nhap.php';</script>";
            } else {
                echo "Lỗi: " . $insert_stmt->error;
            }

            $insert_stmt->close();
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    text-align: center;
    background-color: #f4f4f4;
}
.register-container {
    width: 350px;
    margin: 100px auto;
    background: white;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}
input {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}
button {
    width: 100%;
    padding: 10px;
    background: #28a745;
    color: white;
    border: none;
    cursor: pointer;
    margin-top: 10px;
}
button:hover {
    background: #218838;
}
a {
    display: block;
    margin-top: 10px;
    text-decoration: none;
    color: #007bff;
}
a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <div class="register-container">
        <h2>Đăng ký tài khoản</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="password" name="confirmPassword" placeholder="Nhập lại mật khẩu" required>
            <button type="submit">Đăng ký</button>
            Đã có tài khoản?<a href="/game-website/dang-nhap/dang-nhap.php">Đăng nhập ngay</a> 
        </form>
    </div>
</body>
</html>
