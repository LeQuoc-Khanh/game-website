<?php
session_start();
include_once 'connect.php';

// Kiểm tra đăng nhập và vai trò admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Bạn không có quyền truy cập do chưa đăng nhập!');</script>";
    echo "<script>window.location.href = '/game-website/dang-nhap.php';</script>";
    exit();
}

// Xử lý xóa tài khoản nếu có delete_id
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: admin.php?section=players");
    exit();
}
// Xử lý thêm người chơi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash bảo mật
    $role = $_POST['role'];

    $conn->query("INSERT INTO users (email, username, password, role) 
                  VALUES ('$email', '$username', '$password', '$role')");

    header("Location: admin.php?section=players");
    exit();
}
// Lấy giá trị section từ URL
$section = $_GET['section'] ?? 'dashboard';
// Lấy danh sách game
$games = $conn->query("SELECT * FROM games");

// Xử lý thêm game
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_game'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $link = $_POST['link'];
    $conn->query("INSERT INTO games (game_name, game_url) VALUES ('$name', '$link')");
    header("Location: admin.php?section=games");
    exit;
}

// Xử lý xóa game
if (isset($_GET['delete_game_id'])) {
    $id = (int)$_GET['delete_game_id'];
    $conn->query("DELETE FROM games WHERE game_name = $id");
    header("Location: admin.php?section=games");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_game'])) {
    $originalName = $_POST['original_name'];
    $newName = $_POST['name'];
    $newLink = $_POST['link'];

    $stmt = $conn->prepare("UPDATE games SET game_name = ?, game_url = ? WHERE game_name = ?");
    $stmt->bind_param("sss", $newName, $newLink, $originalName);
    $stmt->execute();

    header("Location: admin.php?section=games");
    exit;
}
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
  
    </style>

</head>
<body>

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
                    <th>Password</th>
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
                            <td class="password"><?= htmlspecialchars($row['password']) ?></td>
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
        <th>Hành động</th>
    </tr>
    <?php if ($games->num_rows > 0): ?>
        <?php while ($game = $games->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($game['game_name']) ?></td>
                <td><a href="<?= htmlspecialchars($game['game_url']) ?>" target="_blank">Chơi</a></td>
                <td>
                    <a href="?section=games&edit_game_id=<?= urlencode($game['game_name']) ?>">Sửa</a> |
                    <a href="?section=games&delete_game_id=<?= $game['game_name'] ?>" onclick="return confirm('Xóa game này?');">Xóa</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">Không có game nào.</td></tr>
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

</body>
</html>