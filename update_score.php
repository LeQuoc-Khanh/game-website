<?php
session_start();
include_once 'connect.php';
if (!isset($_SESSION['username'])) {
    die("Bạn chưa đăng nhập.");
}

if (isset($_POST['score']) && isset($_POST['game'])) {
    $score = intval($_POST['score']);
    $game = $_POST['game']; // tên game: pacman, snake, tetris
    $username = $_SESSION['username'];

    // Xác định cột score tương ứng với game
    $column = '';
    if ($game == 'pacman') {
        $column = 'pacman_score';
    } elseif ($game == 'snake') {
        $column = 'snake_score';
    } elseif ($game == 'tetris') {
        $column = 'tetris_score';
    } else {
        die("Tên game không hợp lệ!");
    }

    // Cập nhật điểm nếu cao hơn
    $sql = "UPDATE users SET $column = GREATEST($column, ?) WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $score, $username);

    if ($stmt->execute()) {
        echo "Cập nhật điểm $game thành công!";
    } else {
        echo "Lỗi cập nhật điểm: " . $stmt->error;
    }

    $stmt->close();
}
?>
