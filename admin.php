<?php
session_start();
include_once 'connect.php';

// Kiểm tra đăng nhập và vai trò admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Bạn không có quyền truy cập do chưa đăng nhập!');</script>";
    echo "<script>window.location.href = '/game-website/dang-nhap.php';</script>";
    exit();
}

// Xử lý xóa người chơi
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['message'] = "Xóa người chơi thành công!";
    header("Location: admin.php?section=players");
    exit();
}
// Xử lý game
$games = $conn->query("SELECT * FROM games ORDER BY is_active DESC, game_name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $username, $password, $role);
        $stmt->execute();
        $_SESSION['message'] = "Thêm người chơi thành công!";
        header("Location: admin.php?section=players");
        exit();
    }
    if (isset($_POST['add_game'])) {
        $name = $_POST['name'];
        $link = $_POST['link'];
        $stmt = $conn->prepare("INSERT INTO games (game_name, game_url) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $link);
        $stmt->execute();
        $_SESSION['message'] = "Thêm game thành công!";
        header("Location: admin.php?section=games");
        exit;
    }

    if (isset($_POST['edit_game'])) {
        $originalName = $_POST['original_name'];
        $newName = $_POST['name'];
        $newLink = $_POST['link'];

        $stmt = $conn->prepare("UPDATE games SET game_name = ?, game_url = ? WHERE game_name = ?");
        $stmt->bind_param("sss", $newName, $newLink, $originalName);
        $stmt->execute();
        $_SESSION['message'] = "Cập nhật game thành công!";
        header("Location: admin.php?section=games");
        exit;
    }
}

if (isset($_GET['delete_game_id'])) {
    $id = $_GET['delete_game_id'];
    $stmt = $conn->prepare("UPDATE games SET is_active = 0 WHERE game_name = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $_SESSION['message'] = "Game đã được ẩn thành công!";
    header("Location: admin.php?section=games");
    exit;
}

if (isset($_GET['restore_game_id'])) {
    $id = $_GET['restore_game_id'];
    $stmt = $conn->prepare("UPDATE games SET is_active = 1 WHERE game_name = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $_SESSION['message'] = "Game đã được hiển thị lại thành công!";
    header("Location: admin.php?section=games");
    exit;
}

if (isset($_GET['permanently_delete_game_id'])) {
    $id = $_GET['permanently_delete_game_id'];
    $stmt = $conn->prepare("DELETE FROM games WHERE game_name = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $_SESSION['message'] = "Game đã được xóa vĩnh viễn!";
    header("Location: admin.php?section=games");
    exit;
}

$section = $_GET['section'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản trị</title>
    <link rel="stylesheet" href="admin.css">
    <style>
      /* RESET */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body, html {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* HEADER */
.header {
    background-color: #2a5298;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
}

.header img {
    height: 40px;
}

.admin-menu {
    position: relative;
}

.admin-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    cursor: pointer;
}

.dropdown {
    display: none;
    position: absolute;
    right: 0;
    background: white;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    width: 150px;
    text-align: left;
    z-index: 100;
}

.dropdown a {
    display: block;
    padding: 10px;
    color: black;
    text-decoration: none;
    border-bottom: 1px solid #eee;
}

.dropdown a:hover {
    background: #f0f0f0;
}

.admin-menu:hover .dropdown {
    display: block;
}

/* MAIN LAYOUT */
.container {
    flex: 1;
    display: flex;
    padding: 20px;
    gap: 20px;
}

/* SIDEBAR MENU */
.menu {
    width: 25%;
    background: white;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    padding: 20px;
}

.menu h3 {
    margin-bottom: 15px;
    color: #2a5298;
    font-size: 18px;
}

.menu ul {
    list-style: none;
}

.menu li {
    background: #2a5298;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    transition: background 0.3s;
    text-align: center;
}

.menu li:hover {
    background: #0d6efd;
}

.menu a {
    color: white;
    text-decoration: none;
    display: block;
}

.content {
    width: 100%;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    overflow: auto;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 14px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

table th {
    background: #2a5298;
    color: white;
}

table tr:nth-child(even) {
    background: #f9f9f9;
}

table td a {
    color: #dc3545;
    text-decoration: none;
}

table td a:hover {
    text-decoration: underline;
}

/* STATS BOX */
.stats {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
}

.stat-box {
    width: 30%;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.05);
}

.stat-box h3 {
    color: #2a5298;
    font-size: 24px;
}

/* FOOTER */
.footer {
    background: #333;
    color: white;
    padding: 15px;
    text-align: center;
    margin-top: auto;
}
.alert {
    background-color: #e0ffe0;
    border: 1px solid #00a000;
    color: #006000;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    font-weight: bold;
}

  
    </style>
</head>
<body>
<?php
// HIỂN THỊ THÔNG BÁO Ở ĐÂY
if (isset($_SESSION['message'])) {
    echo "<div class='alert' id='message'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']);
}
?>

<!-- Header -->
<div class="header">
    <div class="admin-menu">
        <img src="" alt="Admin Avatar" class="admin-avatar">
        <div class="dropdown">
            <a href="trang-chu.php">Trang chủ</a>
            <a href="admin.php">Quản trị</a>
            <a href="/game-website/dang-xuat.php">Đăng xuất</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Sidebar Menu -->
    <div class="menu">
        <h3>Quản lý</h3>
        <ul>
            <li><a href="?section=games">Quản lý Game</a></li>
            <li><a href="?section=players">Quản lý người chơi</a></li>
            <li><a href="?section=dashboard">Thống kê hệ thống</a></li>
        </ul>
    </div>

    <!-- Content Area -->
<div class="content">
<?php if ($section === 'players'): ?>
    <h2>Danh sách người chơi</h2>
    <h3>Thêm người chơi mới</h3>
<form method="POST" style="margin-bottom: 20px;">
    <input type="email" name="email" placeholder="Email" required style="margin-right:10px;">
    <input type="text" name="username" placeholder="Username" required style="margin-right:10px;">
    <input type="password" name="password" placeholder="Mật khẩu" required style="margin-right:10px;">
    <select name="role" required style="margin-right:10px;">
        <option value="user">user</option>
        <option value="admin">admin</option>
    </select>
    <button type="submit" name="add_user">Thêm người chơi</button>
</form>

    <?php
        $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
        ?>
        <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Display Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Pacman</th>
                    <th>Snake</th>
                    <th>Tetris</th>
                    <th>Hành động</th>
                </tr>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['display_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <!-- <td class="password"><?= htmlspecialchars($row['password']) ?></td> -->
                            <td><?= $row['role'] ?></td>
                            <td><?= $row['pacman_score'] ?></td>
                            <td><?= $row['snake_score'] ?></td>
                            <td><?= $row['tetris_score'] ?></td>
                            <td>
                                <a href="?section=players&delete_id=<?= $row['id'] ?>"
                                   onclick="return confirm('Bạn có chắc muốn xóa người chơi này không?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9">Không có người chơi nào.</td></tr>
                <?php endif; ?>
            </table>

        <?php elseif ($section === 'games'): ?>
            <h2>Quản lý Game</h2>
<!-- Form thêm game -->
<form method="POST" style="margin-bottom: 20px;">
    <h3>Thêm game mới</h3>
    <input type="text" name="name" placeholder="Tên game" required style="margin-right: 10px;">
    <input type="text" name="link" placeholder="Link chơi game" required style="margin-right: 10px;">
    <button type="submit" name="add_game">Thêm</button>
</form>
    <?php
if (isset($_GET['edit_game_id'])) {
    $edit_game_name = $_GET['edit_game_id'];
    $stmt = $conn->prepare("SELECT * FROM games WHERE game_name = ?");
    $stmt->bind_param("s", $edit_game_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editGame = $result->fetch_assoc();
        ?>
        <h3>Sửa thông tin game</h3>
        <form method="POST" style="margin-bottom: 20px;">
            <input type="hidden" name="original_name" value="<?= htmlspecialchars($editGame['game_name']) ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($editGame['game_name']) ?>" required style="margin-right: 10px;">
            <input type="text" name="link" value="<?= htmlspecialchars($editGame['game_url']) ?>" required style="margin-right: 10px;">
            <button type="submit" name="edit_game">Cập nhật</button>
        </form>
        <?php
    }
}
?>


<!-- Danh sách game -->
<table>
    <tr>
        <th>Tên Game</th>
        <th>Link</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
    </tr>
    <?php if ($games->num_rows > 0): ?>
        <?php while ($game = $games->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($game['game_name']) ?></td>
                <td><a href="<?= htmlspecialchars($game['game_url']) ?>" target="_blank">Chơi</a></td>
                <td><?= $game['is_active'] ? 'Đang hiển thị' : 'Đã ẩn' ?></td>
                <td>
    <a href="?section=games&edit_game_id=<?= urlencode($game['game_name']) ?>">Sửa</a> |
    <?php if ($game['is_active']): ?>
        <a href="?section=games&delete_game_id=<?= urlencode($game['game_name']) ?>" onclick="return confirm('Bạn có chắc muốn ẩn game này không?');">Ẩn</a>
    <?php else: ?>
        <a href="?section=games&restore_game_id=<?= urlencode($game['game_name']) ?>" onclick="return confirm('Bạn có chắc muốn hiển thị lại game này không?');">Hiện lại</a>
        |
        <a href="?section=games&permanently_delete_game_id=<?= urlencode($game['game_name']) ?>" onclick="return confirm('Bạn có chắc muốn XÓA VĨNH VIỄN game này không?');">XÓA</a>
    <?php endif; ?>
</td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">Không có game nào.</td></tr>
    <?php endif; ?>
</table>

        <?php elseif ($section === 'dashboard'): ?>
            <h2>Thống kê Hệ thống</h2>
            <div class="stats">
                <div class="stat-box">
                    <h3><?= $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?></h3>
                    <p>Người chơi đăng ký</p>
                </div>
                <div class="stat-box">
                    <h3><?= $conn->query("SELECT COUNT(*) FROM games")->fetch_row()[0] ?></h3>
                    <p>Game đang hoạt động</p>
                </div>
                <div class="stat-box">
                    <h3>
                        <?php
                            $sum = $conn->query("SELECT SUM(pacman_score + snake_score + tetris_score) FROM users")->fetch_row()[0];
                            echo $sum ?: 0;
                        ?>
                    </h3>
                    <p>Tổng lượt chơi</p>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<div class="footer">&copy; 2025 Khánh đẹp taii - Quản trị hệ thống</div>
<script>
    // Tự động ẩn thông báo sau 3 giây
    setTimeout(function() {
        var alert = document.getElementById('message');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = 0;
            setTimeout(function() {
                alert.remove();
            }, 500); // Xóa hẳn sau khi mờ đi
        }
    }, 3000);
</script>
</body>
</html>