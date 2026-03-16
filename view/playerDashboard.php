<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'player') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .info-box { background:#1e293b; border:1px solid #334155; border-radius:8px; padding:16px 20px; margin:20px 0; }
        .info-box p { margin-bottom:8px; color:#cbd5e1; }
        .info-box span { font-weight:700; color:#e2e8f0; }
        .player-nav {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .player-nav a {
            flex: 1 1 160px;
            padding: 20px;
            background: linear-gradient(135deg, #1e3a5f, #0f2040);
            border: 1px solid rgba(125,211,252,0.2);
            border-radius: 14px;
            color: #7dd3fc;
            text-decoration: none;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .player-nav a:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .player-nav .icon { display:block; font-size:28px; margin-bottom:8px; }
        .logout-btn { display:inline-block; margin-top:16px; padding:10px 30px; background:#f44336; color:#fff; border:none; border-radius:6px; font-size:15px; cursor:pointer; text-decoration:none; }
        .logout-btn:hover { background:#c62828; }
    </style>
</head>
<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="card" style="margin-top:5%;">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>! 👋</h1>
        <div class="info-box">
            <p>📧 Email: <span><?= htmlspecialchars($_SESSION['user_email']) ?></span></p>
            <p>🔖 Role: <span>Player</span></p>
        </div>

        <div class="player-nav">
            <a href="./playerView/tournaments.php">
                <span class="icon">🏆</span>Tournaments
            </a>
            <a href="./playerView/matches.php">
                <span class="icon">⚽</span>Match Results
            </a>
            <a href="./leaderboard.php">
                <span class="icon">📊</span>Leaderboard
            </a>
        </div>

        <a id="logoutBtn" class="logout-btn" style="margin-top:28px; display:inline-block;">Logout</a>
    </div>

<script>
$(document).ready(function () {
    $('#logoutBtn').on('click', function () {
        $.ajax({
            url: '../auth/api/logoutHandler.php',
            method: 'POST',
            success: function () {
                setTimeout(() => { window.location.href = 'login.php'; }, 800);
            },
            error: function () { alert('Logout failed. Please try again.'); }
        });
    });
});
</script>
</body>
</html>
