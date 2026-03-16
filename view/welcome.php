<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .dashboard-header {
            margin-bottom: 32px;
        }
        .dashboard-header h1 { margin-bottom: 4px; }
        .dashboard-header p  { color: #94a3b8; margin: 0; }

        /* ── Stat cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.4);
        }
        .stat-card .card-icon {
            font-size: 28px;
            margin-bottom: 10px;
            display: block;
        }
        .stat-card .card-value {
            font-size: 36px;
            font-weight: 800;
            color: #7dd3fc;
            line-height: 1;
        }
        .stat-card .card-label {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .card-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
        }

        /* ── Quick links ── */
        .quick-links h3 { margin-bottom: 14px; border-left: 4px solid #7dd3fc; padding-left: 10px; }
        .links-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .links-grid a {
            padding: 12px 22px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            color: #7dd3fc;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s, border-color 0.2s;
        }
        .links-grid a:hover {
            background: #0f2040;
            border-color: #7dd3fc;
        }

        /* ── Tournament status breakdown ── */
        .t-status-row { display: flex; gap: 12px; margin-top: 8px; flex-wrap: wrap; }
        .t-badge {
            font-size: 12px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .t-badge.upcoming  { background:#1e3a5f; color:#7dd3fc; }
        .t-badge.ongoing   { background:#14532d; color:#86efac; }
        .t-badge.completed { background:#3b1f0f; color:#fdba74; }

        .logout-btn { display:inline-block; margin-top:28px; padding:10px 30px; background:#f44336; color:#fff; border:none; border-radius:6px; font-size:15px; cursor:pointer; text-decoration:none; }
        .logout-btn:hover { background:#c62828; }
    </style>
</head>
<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="card" style="margin-top:5%; max-width:1000px;">
        <div class="dashboard-header">
            <h1>👋 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
            <p>Here's a snapshot of your tournament system.</p>
        </div>

        <!-- Stats grid -->
        <div class="stats-grid" id="statsGrid">
            <!-- Filled by JS -->
            <div class="stat-card"><span class="card-icon">⏳</span><div class="card-value">—</div><div class="card-label">Loading...</div></div>
        </div>

        <!-- Quick navigation -->
        <div class="quick-links">
            <h3>Quick Actions</h3>
            <div class="links-grid">
                <a href="./tournament.php">🏆 Tournaments</a>
                <a href="./team.php">👥 Teams</a>
                <a href="./player.php">🧍 Players</a>
                <a href="./match.php">⚽ Matches</a>
                <a href="./matchScore.php">📝 Scores</a>
                <a href="./leaderboard.php">📊 Leaderboard</a>
            </div>
        </div>

        <a id="logoutBtn" class="logout-btn">Logout</a>
    </div>

<script>
$(document).ready(function () {

    // ── Load dashboard stats ─────────────────────────────────────────────────
    $.ajax({
        url: '../api/dashboard/getStats.php',
        method: 'GET',
        dataType: 'json',
        success: function (res) {
            if (res.status !== 'success') return;
            const d = res.data;
            const $grid = $('#statsGrid');
            $grid.html(`
                <div class="stat-card">
                    <span class="card-icon">🏆</span>
                    <div class="card-value">${d.tournaments}</div>
                    <div class="card-label">Tournaments</div>
                    <div class="t-status-row">
                        <span class="t-badge upcoming">${d.tournaments_upcoming} Upcoming</span>
                        <span class="t-badge ongoing">${d.tournaments_ongoing} Ongoing</span>
                        <span class="t-badge completed">${d.tournaments_completed} Done</span>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="card-icon">👥</span>
                    <div class="card-value">${d.teams}</div>
                    <div class="card-label">Teams</div>
                </div>
                <div class="stat-card">
                    <span class="card-icon">🧍</span>
                    <div class="card-value">${d.players}</div>
                    <div class="card-label">Players</div>
                </div>
                <div class="stat-card">
                    <span class="card-icon">⚽</span>
                    <div class="card-value">${d.matches_total}</div>
                    <div class="card-label">Matches</div>
                    <div class="card-sub">${d.matches_completed} completed · ${d.matches_scheduled} scheduled</div>
                </div>
            `);
        }
    });

    // ── Logout ───────────────────────────────────────────────────────────────
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