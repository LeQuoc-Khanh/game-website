<?php
session_start(); // Bắt đầu session
include_once 'connect.php';
// Kiểm tra xem đã đăng nhập và có phải là admin không
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    $_SESSION['redirect_after_login'] = '/game-website/tai-khoan.php';
    // Nếu chưa đăng nhập
    echo "<script>alert('Bạn không có quyền truy cập do chưa đăng nhập!');</script>";
    echo "<script>window.location.href = '/game-website/dang-nhap.php';</script>";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_info'])) {
    $user_id = $_SESSION['user_id'];
    $display_name = trim($_POST['display_name']);

    if (!empty($display_name)) {
        $stmt = $conn->prepare("UPDATE users SET display_name = ? WHERE id = ?");
        $stmt->bind_param("si", $display_name, $user_id);
        if ($stmt->execute()) {
            // ✅ Cập nhật thành công thì lưu vào session
            $_SESSION['display_name'] = $display_name;

            echo "<script>alert('Cập nhật tên hiển thị thành công!');</script>";
        } else {
            echo "<script>alert('Cập nhật thất bại.');</script>";
        }
    } else {
        echo "<script>alert('Tên hiển thị không được để trống!');</script>";
    }
}
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT display_name FROM users WHERE id = $user_id");
$current_display_name = '';
if ($result && $row = $result->fetch_assoc()) {
    $current_display_name = $row['display_name'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản</title>
    <style>
        /* Bố cục chung */
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        /* Banner */
        .banner {
            background: linear-gradient(to right, #2a5298, #0d6efd);
            color: white;
            padding: 5px;
            font-size: 14px;
        }

        /* Container chính */
        .profile-container {
            width: 50%;
            margin: 30px auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: left;
        }

        /* Tiêu đề */
        .user-name {
            font-size: 22px;
            font-weight: bold;
        }

        .user-id {
            font-size: 14px;
            color: gray;
        }

        /* Bố cục avatar + thông tin */
        .profile-content {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        /* Avatar */
        .profile-avatar {
            text-align: center;
        }

        .avatar-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2a5298;
        }

        .change-avatar {
            display: block;
            margin-top: 10px;
            color: #2a5298;
            text-decoration: none;
            font-size: 14px;
        }

        .change-avatar-btn {
            margin-top: 10px;
            padding: 8px;
            background: #2a5298;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        /* Thông tin cá nhân */
        .profile-info {
            width: 60%;
        }

        .profile-info label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
        }

        .profile-info input, .profile-info select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .gender {
            display: flex;
            gap: 0px;
            margin-top: 5px;
        }

        .more-info, .save-info {
            margin-top: 15px;
            padding: 10px;
            width: 48%;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .more-info {
            background: #f0f0f0;
        }

        .save-info {
            background: #2a5298;
            color: white;
        }

    </style>
</head>
<body>
    <body>
        <!-- Banner -->
        <div class="banner">
            <h1>gamevui24h - Cộng Đồng Game Hay</h1>
        </div>
    
        <!-- Giao diện thông tin người dùng -->
        <div class="profile-container">
            <h2 class="user-name"><span class="user-id">id: 1024131</span></h2>
            <div class="profile-content">
                <div class="profile-avatar">
                    <img src="images/user-avatar.png" alt="Avatar" class="avatar-img">
                    <a href="#" class="change-avatar">Thay đổi hình đại diện</a>
                    <button class="change-avatar-btn">Thay ảnh đại diện</button>
                </div>
                <form method="POST" action="">
                <div class="profile-info">
                    <label for="display-name">Tên hiển thị</label>
                    <input type="text" id="display-name" name="display_name" value="<?= htmlspecialchars($current_display_name) ?>">
    
                    <label for="dob">Ngày sinh</label>
                    <select id="day">
                        <option>2</option>
                    </select>
                    <select id="month">
                        <option>Tháng 9</option>
                    </select>
                    <select id="year">
                        <option>2005</option>
                    </select>
    
                    <label>Giới tính</label>
                    <div class="gender">
                        <input type="radio" name="gender" checked> Nam
                        <input type="radio" name="gender"> Nữ
                        <input type="radio" name="gender"> Không tiết lộ
                    </div>
    
                    <label for="address">Địa chỉ</label>
                    <input type="text" id="address" placeholder="Nhập địa chỉ liên lạc">
    
                    <button class="more-info">Thông tin thêm</button>
                    <button type="submit" name="save_info" class="save-info">Lưu thông tin</button>
                </div>
                </form>
            </div>
        </div>
    
        <script>
            document.querySelector(".change-avatar-btn").addEventListener("click", function() {
                alert("Chức năng này chưa được hỗ trợ!");
            });
        </script>
    </body>
    
</body>
</html>