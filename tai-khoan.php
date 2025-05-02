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
// Kiểm tra xem có gửi thông tin cập nhật không
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_info'])) {
    $user_id = $_SESSION['user_id'];
    $display_name = trim($_POST['display_name']);
    $gender = $_POST['gender'];
    $day = $_POST['day'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $dob = "$year-$month-$day";
    $address = trim($_POST['address']);  // Lấy địa chỉ từ form

    // Kiểm tra tên hiển thị không trống
    if (!empty($display_name)) {
        // Cập nhật thông tin người dùng, bao gồm địa chỉ
        $stmt = $conn->prepare("UPDATE users SET display_name = ?, gender = ?, date_of_birth = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $display_name, $gender, $dob, $address, $user_id);
        if ($stmt->execute()) {
            $_SESSION['display_name'] = $display_name;
            echo "<script>alert('Cập nhật thông tin thành công!');</script>";
        } else {
            echo "<script>alert('Cập nhật thất bại.');</script>";
        }
    } else {
        echo "<script>alert('Tên hiển thị không được để trống!');</script>";
    }
}

$user_id = $_SESSION['user_id'];
// Lấy thông tin của người dùng từ cơ sở dữ liệu
$result = $conn->query("SELECT display_name, avatar, date_of_birth, gender, address FROM users WHERE id = $user_id");

$current_display_name = '';
$avatar_path = 'image/user-avatar.png'; // Mặc định
$dob_day = $dob_month = $dob_year = '';
$gender = ''; // Thêm giới tính
$current_address = ''; // Thêm địa chỉ

if ($result && $row = $result->fetch_assoc()) {
    $current_display_name = $row['display_name'];
    $current_address = $row['address']; // Lấy địa chỉ từ CSDL

    // Xử lý avatar, ngày sinh, giới tính
    if (!empty($row['avatar']) && file_exists($row['avatar'])) {
        $avatar_path = $row['avatar'];
    }

    if (!empty($row['date_of_birth'])) {
        $dob_parts = explode('-', $row['date_of_birth']);
        $dob_year = (int)$dob_parts[0];
        $dob_month = (int)$dob_parts[1];
        $dob_day = (int)$dob_parts[2];
    }

    if (!empty($row['gender'])) {
        $gender = $row['gender'];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới trùng khớp
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Mật khẩu mới và xác nhận không khớp!');</script>";
    } else {
        // Lấy mật khẩu hiện tại từ database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Kiểm tra mật khẩu cũ
        if (!password_verify($old_password, $hashed_password)) {
            echo "<script>alert('Mật khẩu hiện tại không đúng!');</script>";
        } else {
            // Mã hóa mật khẩu mới
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // Cập nhật vào DB
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hashed, $user_id);
            if ($stmt->execute()) {
                echo "<script>alert('Đổi mật khẩu thành công!');</script>";
            } else {
                echo "<script>alert('Lỗi khi cập nhật mật khẩu.');</script>";
            }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['avatar']['tmp_name'];
        $file_name = basename($_FILES['avatar']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_ext, $allowed_exts)) {
            $new_file_name = "avatar_user_" . $user_id . "." . $file_ext;
            $upload_dir = "image/";
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->bind_param("si", $upload_path, $user_id);
                if ($stmt->execute()) {
                    echo "<script>alert('Cập nhật ảnh đại diện thành công!');</script>";
                } else {
                    echo "<script>alert('Không thể cập nhật vào CSDL.');</script>";
                }
            } else {
                echo "<script>alert('Không thể lưu file ảnh.');</script>";
            }
        } else {
            echo "<script>alert('Chỉ cho phép file JPG, PNG, GIF, WEBP.');</script>";
        }
    } else {
        echo "<script>alert('Vui lòng chọn file ảnh.');</script>";
    }
}

$day = $_POST['day'];
$month = $_POST['month'];
$year = $_POST['year'];
$dob = "$year-$month-$day"; // định dạng yyyy-mm-dd

// Sau đó lưu vào CSDL như:
$stmt = $conn->prepare("UPDATE users SET date_of_birth = ? WHERE id = ?");
$stmt->bind_param("si", $dob, $user_id);



?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Banner */
        .banner {
            background: linear-gradient(to right, #2a5298, #0d6efd);
            color: white;
            padding: 5px;
            font-size: 14px;
            display: flex;
            align-items: center;
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
        .show {
            display: block;
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
            display: flex;
            align-items: flex-start;
            font-weight: bold;
            margin-top: 10px;
        }

        .profile-info input, select {
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
        .dropbtn {
    font-size: 20px;
    background-color: transparent;
    border: none;
    color: white;
    cursor: pointer;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    position: absolute;
    background-color: white;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    z-index: 1;
    display: none;
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    display: block;
    text-decoration: none;
}

.dropdown-content a:hover {
    background-color: #ddd;
}
/* Đổi mật khẩu */
.change-password-section {
    margin-top: 30px;
    padding: 20px;
}

.change-password-section h3 {
    margin-bottom: 15px;
    color: #2a5298;
}

.change-password-section label {
    display: block;
    font-weight: bold;
    margin-top: 10px;
}

.change-password-section input[type="password"] {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.change-password-section button {
    margin-top: 15px;
    padding: 10px;
    width: 100%;
    background: #2a5298;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.change-password-section button:hover {
    background-color: #1e3c72;
}
    </style>
</head>
<body>
<div class="banner">
    <div class="dropdown">
        <button onclick="toggleDropdown()" class="dropbtn">&#9776;</button>
        <div id="myDropdown" class="dropdown-content" style="display: none;">
            <a href="/game-website/trang-chu.php">Trang chủ</a>
            <a href="/game-website/game-yeu-thich/favorite.html">Game Yêu Thích</a>
            <a href="/game-website/dang-xuat.php">Đăng xuất</a>
        </div>
    </div>
    <div><h1>gamevui24h</h1></div>
</div>
    
        <div class="profile-container">
            <h2 class="user-name"><span class="user-id">id: 1024131</span></h2>
            <div class="profile-content">
            <form method="POST" enctype="multipart/form-data">
            <img src="<?= htmlspecialchars($avatar_path) ?>" alt="Avatar" class="avatar-img">
            <input type="file" name="avatar" accept="image/*" required>
            <button type="submit" name="upload_avatar" class="change-avatar-btn">Cập nhật ảnh đại diện</button>
            </form>
                <div class="profile-info">
                <form method="POST" action="">
                    <label for="display-name">Tên hiển thị</label>
                    <input type="text" id="display-name" name="display_name" value="<?= htmlspecialchars($current_display_name) ?>">
    
                    <label for="dob">Ngày sinh</label>
                    <!-- Ngày -->
<select id="day" name="day">
    <?php
    for ($d = 1; $d <= 31; $d++) {
        $selected = ($d == $dob_day) ? 'selected' : '';
        echo "<option value='$d' $selected>$d</option>";
    }
    ?>
</select>

<!-- Tháng -->
<select id="month" name="month">
    <?php
    for ($m = 1; $m <= 12; $m++) {
        $selected = ($m == $dob_month) ? 'selected' : '';
        echo "<option value='$m' $selected>Tháng $m</option>";
    }
    ?>
</select>

<!-- Năm -->
<select id="year" name="year">
    <?php
    $current_year = date('Y');
    for ($y = $current_year; $y >= $current_year - 100; $y--) {
        $selected = ($y == $dob_year) ? 'selected' : '';
        echo "<option value='$y' $selected>$y</option>";
    }
    ?>
</select>

    
                    <label>Giới tính</label>
                    <div class="gender">
    <label><input type="radio" name="gender" value="male" <?= ($gender === 'male') ? 'checked' : '' ?>> Nam</label>
    <label><input type="radio" name="gender" value="female" <?= ($gender === 'female') ? 'checked' : '' ?>> Nữ</label>
    <label><input type="radio" name="gender" value="other" <?= ($gender === 'other') ? 'checked' : '' ?>> Không tiết lộ</label>
</div>
    
<label for="address">Địa chỉ</label>
<input type="text" id="address" name="address" placeholder="Nhập địa chỉ liên lạc" value="<?= htmlspecialchars($current_address) ?>">

    
                    <button class="more-info">Thông tin thêm</button>
                    <button type="submit" name="save_info" class="save-info">Lưu thông tin</button>
                </form>
                </div>
            </div>
        </div>
        <div class="profile-container">
        <div class="change-password-section">
    <h3>Đổi mật khẩu</h3>
    <form method="POST" action="">
        <label for="old_password">Mật khẩu hiện tại</label>
        <input type="password" id="old_password" name="old_password" required>

        <label for="new_password">Mật khẩu mới</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Xác nhận mật khẩu mới</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" name="change_password">Đổi mật khẩu</button>
    </form>
</div>

    
        <script>
            document.querySelector(".change-avatar-btn").addEventListener("click", function() {
            });
    function toggleDropdown() {
        const dropdown = document.getElementById("myDropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    // Ẩn dropdown khi click ra ngoài
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            const dropdown = document.getElementById("myDropdown");
            dropdown.style.display = "none";
        }
    }
    // Tạo ngày (1-31)
    const daySelect = document.getElementById('day');
    for (let i = 1; i <= 31; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = i;
        daySelect.appendChild(opt);
    }

    // Tạo tháng (1-12)
    const monthSelect = document.getElementById('month');
    for (let i = 1; i <= 12; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = 'Tháng ' + i;
        monthSelect.appendChild(opt);
    }

    // Tạo năm (từ hiện tại đến 100 năm trước)
    const yearSelect = document.getElementById('year');
    const currentYear = new Date().getFullYear();
    for (let i = currentYear; i >= currentYear - 100; i--) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = i;
        yearSelect.appendChild(opt);
    }

        </script>
</body>
</html>