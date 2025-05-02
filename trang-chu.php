<?php
session_start();
include_once 'connect.php';
$games = $conn->query("SELECT * FROM games");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Cổ Điển</title>
    <style>
        /* Reset cơ bản */
* {
    margin: 0;
    padding: 0;
    }

/* Body & Layout */
html, body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f5f5;
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Header */
header {
    background: linear-gradient(to right, #2a5298, #0d6efd);
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h1 {
    font-size: 28px;
}
h2 {
    font-size: 24px;
    text-align: center;
}

/* Auth Buttons */
.auth-buttons {
    display: flex;
    align-items: center;
}

.login {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}

.login:hover {
    background-color: #218838;
}

/* Dropdown */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropbtn {
    background-color: #0056b3;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}

.dropbtn:hover {
    background-color: #004494;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 180px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    border-radius: 8px;
    right: 0;
    z-index: 1;
}

.dropdown-content a {
    color: #333;
    padding: 12px 20px;
    text-decoration: none;
    display: block;
    font-size: 16px;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.show {
    display: block;
}

.content-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 500px;
    padding: 40px;
}
.main {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: flex-start;

}


.game-card {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 0px;
    padding: 20px;
    text-align: center;
    width: 100px;
    cursor: pointer;
    transition: 0.3s;
    flex-shrink: 0;
    flex-grow: 0;
}

.game-card a:hover {
    transform: scale(1.1);
    background: rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: orange;
}

.game-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transform: translateY(-5px);
}
    


.leaderboard {
    width: 30%;
    border: 1px solid red;
    background-color: #fff;
    border-radius: 12px;
    padding: 10px;
}
.leaderboard-section ol {
    padding-left: 20px;
}

.leaderboard-section li {
    margin-bottom: 6px;
    font-size: 16px;
}




.game-card img {
    width: 100%;
    aspect-ratio: 1/1; /* Tỉ lệ 1:1 vuông vức */
    object-fit: cover; /* Cắt hình cho vừa khít khung */
    flex-shrink: 0;
}

.game-card a {
    font-size: 20px;
    font-weight: bold;
    color: #007bff;
    text-decoration: none;
}


.ads-container {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    margin-left: 50px;
}
.ads {
    width: 23%;
    height: auto;
    border-radius: 12px;
    overflow: hidden;
    text-align: center;
    border: 1px solid #ccc;
    background-color: #fff;
    transition: transform 0.3s, box-shadow 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ads:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.ads img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

.ads a {
    display: block;
    height: 100%;
    width: 100%;
    text-decoration: none;
}


.footer {
    text-align: center;
    padding: 20px;
    background: #ddd;
    font-size: 16px;
    margin-top: 10px;
}
@media (max-width: 768px) {
    .content-wrapper {
        flex-direction: column;
    }
    .main,
    .leaderboard {
        width: 100%;
    }
}

    </style>
</head>
<body>

<header>
    <h1>gamevui24h.com</h1>
    <div class="auth-buttons">
    <?php if (isset($_SESSION['username'])): ?>
    <div class="dropdown">
        <button onclick="toggleDropdown()" class="dropbtn">
            Xin chào, <?php echo htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username']); ?>
        </button>
        <div id="myDropdown" class="dropdown-content">
            <a href="/game-website/tai-khoan.php">Tài Khoản</a>
            <a href="/game-website/game-yeu-thich/favorite.html">Game Yêu Thích</a>
            <a href="/game-website/dang-xuat.php">Đăng xuất</a>
        </div>
    </div>
        <?php else: ?>
            <a href="/game-website/dang-nhap.php">
                <button class="login">Đăng nhập</button>
            </a>
        <?php endif; ?>
    </div>
</header>
<div class="content-wrapper">
    <div class="main">
    <?php
    if ($games->num_rows > 0) {
        while ($row = $games->fetch_assoc()) {
            echo '<div class="game-card">';
            echo '<a href="' . htmlspecialchars($row['game_url']) . '">';
            // echo '<img src="' . htmlspecialchars($row['game_image']) . '" alt="' . htmlspecialchars($row['game_name']) . '">';
            echo '<p>' . htmlspecialchars($row['game_name']) . '</p>';
            echo '</a>';
            echo '</div>';
        }
    } 
    ?>
        <div class="game-card">
            <a href="/game-website/game/Pacman/pacman.html">Pacman</a>
        </div>
        <div class="game-card">
                <a href="/game-website/game/Tetris/tetris.php">Tetris</a> 
        </div>
        <div class="game-card">
            <a href="/game-website/game/snake.html">Snake</a> 
        </div>
    </div>
    <div class="leaderboard">
        <h2>Bảng Xếp Hạng</h2>
        <div class="leaderboard-section">
            <h3>Pacman</h3>
            <ol>
        <?php
            // Truy vấn lấy bảng xếp hạng, loại bỏ tài khoản admin
            $sql = "SELECT username, pacman_score FROM users WHERE role != 'admin' ORDER BY pacman_score DESC LIMIT 10";
            $result = $conn->query($sql);

            // Hiển thị bảng xếp hạng Pacman
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['username']) . ": <strong>" . $row['pacman_score'] . "</strong></li>";
            }
            ?>
            </ol>
        </div>
        <div class="leaderboard-section">
            <h3>Snake</h3>
            <ol>
        <?php
            // Truy vấn lấy bảng xếp hạng, loại bỏ tài khoản admin
            $sql = "SELECT username, snake_score FROM users WHERE role != 'admin' ORDER BY snake_score DESC LIMIT 10";
            $result = $conn->query($sql);

            // Hiển thị bảng xếp hạng Pacman
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['username']) . ": <strong>" . $row['snake_score'] . "</strong></li>";
            }
            ?>
            </ol>
        </div>
        <div class="leaderboard-section">
            <h3>Tetris</h3>
            <ol>
        <?php
            // Truy vấn lấy bảng xếp hạng, loại bỏ tài khoản admin
            $sql = "SELECT username, tetris_score FROM users WHERE role != 'admin' ORDER BY tetris_score DESC LIMIT 10";
            $result = $conn->query($sql);

            // Hiển thị bảng xếp hạng Pacman
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['username']) . ": <strong>" . $row['tetris_score'] . "</strong></li>";
            }
            ?>
            </ol>
        </div>
    </div>
</div>
    <h2>Quảng cáo</h2>
<div class="ads-container">
  <div class="ads">
    <a href="https://www.thegioididong.com/dtdd/iphone-16e" target="_blank">
      <img src="https://tse2.mm.bing.net/th?id=OIP.dGlsfbBpyyFpjrVQylOGRwHaHa&pid=Api&P=0&h=180" alt="Quảng cáo 1">
    </a>
  </div>

  <div class="ads">
    <a href="https://gamevui.vn/" target="_blank">
      <img src="https://tse3.mm.bing.net/th?id=OIP.THckrV-rsMX4P0KNJlK79QHaHa&pid=Api&P=0&h=180" alt="Quảng cáo 2">
    </a>
  </div>

  <div class="ads">
    <a href="https://www.smartprix.com/laptops/acer-aspire-7-a715-76g-un-qmesi-004-gaming-ppd1i5f0q0id" target="_blank">
      <img src="https://tse3.mm.bing.net/th?id=OIP.vKUIFtPUJK5pg1icFYRSyAHaFO&pid=Api&P=0&h=180" alt="Quảng cáo 3">
    </a>
  </div>

  <div class="ads">
    <a href="https://nhandaithanh.com/collections/may-lanh-panasonic" target="_blank">
      <img src="https://tse1.mm.bing.net/th?id=OIP.K6dpHuWIDjx94btelDLW-gHaE7&pid=Api&P=0&h=180" alt="Quảng cáo 4">
    </a>
  </div>
</div>
<div class="footer">
    &copy; 2025 Game Cổ Điển. All rights reserved.
</div>
</body>
<script>
    function playGame(gameUrl) {
    window.location.href = gameUrl;
}
function toggleDropdown() {
    document.getElementById("myDropdown").classList.toggle("show");
}

window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}
</script>
</html>
