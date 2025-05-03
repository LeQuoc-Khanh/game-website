<?php
session_start();  // Bắt đầu session

if (isset($_SESSION['user_id'])) {
    // Lưu role tạm ra biến riêng trước khi xóa session
    $role = $_SESSION['role'];

    // Xóa session
    session_unset();
    session_destroy();

    // Kiểm tra role đã lưu
    if ($role == 'admin') {
        // Nếu là admin, chuyển về trang đăng nhập
        echo "<script>alert('Đăng xuất thành công!');</script>";
        echo "<script>window.location.href = '/game-website/login.php';</script>";
    } else {
        // Nếu là user, chuyển về trang chủ
        echo "<script>alert('Đăng xuất thành công!');</script>";
        echo "<script>window.location.href = '/game-website/home.php';</script>";
    }
    exit();
} else {
    // Nếu không có session, trực tiếp chuyển về trang chủ
    header("Location: /game-website/home.php");
    exit();
}
?>
