<?php
$conn = new mysqli("localhost", "root", "", "game-website");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
} else {
    echo "Kết nối thành công";
}
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $sql_delete = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql_delete) === TRUE) {
        header("Location: player.php"); // reload lại để tránh xóa tiếp khi F5
        exit;
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }
}
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý người chơi</title>
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #888;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #333;
            color: #fff;
        }
        h2 {
            text-align: center;
        }
        .banned {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Danh sách người chơi</h2>

<table>
    <tr>
        <th>id</th>
        <th>email</th>
        <th>username</th>
        <th>password</th>
        <th>role</th>
        <th>pacman_score</th>
        <th>snake_score</th>
        <th>tetris_score</th>
        <th>Hành động</th>
    </tr>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td class="password"><?= htmlspecialchars($row['password']) ?></td>
            <td class="<?= $row['role'] == 'admin' ? 'admin' : '' ?>">
                <?= $row['role'] ?>
            </td>
            <td><?= $row['pacman_score'] ?></td>
            <td><?= $row['snake_score'] ?></td>
            <td><?= $row['tetris_score'] ?></td>
            <td><a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa tài khoản này không?');">Xóa</a></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8" style="text-align: center;">Không có người chơi nào.</td></tr>
    <?php endif; ?>
</table>
</body>
</html>
