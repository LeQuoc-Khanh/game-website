<?php
$conn = new mysqli('localhost', 'root', '', 'game-website');

// Lấy danh sách điểm Pacman
$sql = "SELECT username, pacman_score FROM users ORDER BY pacman_score DESC LIMIT 10";
$result = $conn->query($sql);

echo "<h2>Bảng Xếp Hạng Pacman</h2>";
while ($row = $result->fetch_assoc()) {
    echo htmlspecialchars($row['username']) . "; điểm: " . $row['pacman_score'] . "; trò chơi: Pacman<br>";
}
?>
