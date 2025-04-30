<?php
session_set_cookie_params(0);
session_start();
include_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Tìm tài khoản trong database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Có user tồn tại
        $user = $result->fetch_assoc();

        // Kiểm tra password
        if (password_verify($password, $user['password'])) {
            // Đúng password → lưu session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];  // Lưu thông tin role vào session

            // Kiểm tra xem người dùng là admin hay user và chuyển hướng tương ứng
            if ($_SESSION['role'] == 'admin') {
                echo "<script>alert('Đăng nhập thành công!');</script>";
                // Nếu là admin, chuyển hướng đến trang quản lý admin
                echo "<script>window.location.href = '/game-website/admin.php';</script>";
            } elseif (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
                exit();
            }else {
                echo "<script>alert('Đăng nhập thành công!');</script>";
                // Nếu là user, chuyển hướng đến trang chủ
                echo "<script>window.location.href= '/game-website/trang-chu.php';</script>";
            }
            exit();  // Đảm bảo không có mã nào chạy thêm sau khi chuyển hướng
        } else {
            echo "<script>alert('Mật khẩu không đúng!');</script>";
        }
    } else {
        echo "<script>alert('Tên đăng nhập không tồn tại!');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        body {
font-family: Arial, sans-serif;
text-align: center;
background-color: #f4f4f4;
}
.login-container {
width: 300px;
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
<div class="login-container">
        <h2>Đăng nhập</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng nhập</button>
        </form>

        Bạn chưa có tài khoản?<a href="/game-website/dang-ky.php">Đăng ký</a>
</div>
</body>
</html>

