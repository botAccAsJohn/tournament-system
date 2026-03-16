<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$isAdmin = $_SESSION['user_role'] === 'admin';
$backLink = $isAdmin ? 'welcome.php' : 'playerDashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="tableUtils.js"></script>
    <style>
        .lb-section { margin-bottom: 50px; }
        .lb-section h3 { margin-bottom: 10px; border-left: 4px solid #7dd3fc; padding-left: 10px; }

        .winner-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 40px;
        }
        .winner-card {
            flex: 1 1 260px;
            background: linear-gradient(135deg, #1e3a5f, #0f2040);
            border: 1px solid rgba(125,211,252,0.25);
            border-radius: 14px;
            padding: 22px 24px;
            color: #e0f2fe;
            position: relative;
            overflow: hidden;
        }
        .winner-card::before {
            content: '🏆';
            position: absolute;
            right: 16px;
            top: 12px;
            font-size: 2.2rem;
            opacity: 0.25;
        }
        .winner-card .t-name  { font-size: 13px; color: #7dd3fc; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .winner-card .t-type  { font-size: 11px; color: #94a3b8; margin-bottom: 14px; }
        .winner-card .w-name  { font-size: 22px; font-weight: 700; color: #fde68a; }
        .winner-card .w-label { font-size: 12px; color: #7dd3fc; margin-top: 2px; }
        .winner-card .pending { font-size: 14px; color: #94a3b8; font-style: italic; }
        .winner-card .progress-bar-wrap { margin-top: 12px; background: rgba(255,255,255,0.08); border-radius: 6px; height: 6px; }
        .winner-card .progress-bar      { height: 6px; border-radius: 6px; background: #7dd3fc; transition: width 0.6s ease; }

        .rank-1 td:first-child::before { content: '🥇 '; }
        .rank-2 td:first-child::before { content: '🥈 '; }
        .rank-3 td:first-child::before { content: '🥉 '; }

        .filter-row { display: flex; gap: 12px; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-row select { padding: 8px 14px; border-radius: 8px; border: 1px solid #334155; background: #1e293b; color: #e2e8f0; font-size: 14px; }
        .filter-row label  { color: #94a3b8; font-size: 14px; }
        .points-badge { background: #0f2040; color: #7dd3fc; font-weight: 700; padding: 2px 10px; border-radius: 20px; font-size: 13px; }
    </style>
</head>
<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="table-container" style="margin-top: 5%;">
        <h2 style="margin-bottom:6px;">🏆 Leaderboard</h2>
        <p style="color:#94a3b8; margin-bottom:24px;">Points: Win = 2 &nbsp;|&nbsp; Draw = 1 &nbsp;|&nbsp; Loss = 0</p>

        <!-- Tournament Winners -->
        <div class="lb-section">
            <h3>Tournament Winners</h3>
            <div class="winner-grid" id="winnerGrid">
                <p style="color:#94a3b8;">Loading...</p>
            </div>
        </div>

        <!-- Standings Filter -->
        <div class="lb-section">
            <h3>Standings</h3>
            <div class="filter-row">
                <label for="tournamentFilter">Filter by Tournament:</label>
                <select id="tournamentFilter">
                    <option value="">All Tournaments</option>
                </select>
                <input type="text" id="lbSearch" placeholder="🔍 Search teams..." style="padding:8px 14px; border-radius:8px; border:1px solid #334155; background:#1e293b; color:#e2e8f0; font-size:14px; margin-bottom:0; max-width:220px;">
            </div>
            <table id="leaderboardTable">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Tournament</th>
                        <th>MP</th>
                        <th>W</th>
                        <th>D</th>
                        <th>L</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody id="leaderboardBody">
                    <tr><td colspan="7" class="no-records">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
$(document).ready(function () {

    const tp = new TablePager('leaderboardBody', { pageSize: 15, searchId: 'lbSearch' });

    // ── Load tournament winner cards ─────────────────────────────────────────
    function loadWinners() {
        $.ajax({
            url: '../api/tournament/getWinner.php',
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                const $grid = $('#winnerGrid');
                $grid.empty();
                if (res.status !== 'success' || !res.data.length) {
                    $grid.html('<p style="color:#94a3b8;">No tournament data found.</p>');
                    return;
                }
                res.data.forEach(t => {
                    const pct = t.total_matches > 0
                        ? Math.round((t.completed_matches / t.total_matches) * 100)
                        : 0;
                    const typeLabel = t.type === 'league' ? 'League' : 'Knockout';
                    let winnerHtml = '';
                    if (t.status === 'completed' && t.winner) {
                        winnerHtml = `
                            <div class="w-name">🏅 ${t.winner.team_name}</div>
                            <div class="w-label">Champion</div>`;
                    } else if (t.winner) {
                        winnerHtml = `
                            <div class="w-name">${t.winner.team_name}</div>
                            <div class="w-label">Currently Leading</div>`;
                    } else {
                        winnerHtml = `<div class="pending">No matches completed yet</div>`;
                    }
                    $grid.append(`
                        <div class="winner-card">
                            <div class="t-name">${t.tournament_name}</div>
                            <div class="t-type">${typeLabel} &nbsp;·&nbsp; ${t.status}</div>
                            ${winnerHtml}
                            <div class="progress-bar-wrap" title="${pct}% matches played">
                                <div class="progress-bar" style="width:${pct}%"></div>
                            </div>
                        </div>
                    `);
                });

                // Populate filter dropdown
                const $sel = $('#tournamentFilter');
                res.data.forEach(t => {
                    $sel.append(`<option value="${t.tournament_id}">${t.tournament_name}</option>`);
                });
            }
        });
    }

    // ── Load standings table ─────────────────────────────────────────────────
    function loadLeaderboard(tournamentId) {
        const params = tournamentId ? { tournament_id: tournamentId } : {};
        $.ajax({
            url: '../api/leaderboard/getLeaderboard.php',
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function (res) {
                const $body = $('#leaderboardBody');
                if (res.status !== 'success' || !res.data.length) {
                    $body.html('<tr><td colspan="7" class="no-records">No data found.</td></tr>');
                    return;
                }

                const rows = [];
                $body.empty();
                // Group by tournament to assign per-tournament rank
                let lastTournament = null;
                let rankInTournament = 0;
                res.data.forEach(row => {
                    if (row.tournament_id !== lastTournament) {
                        lastTournament = row.tournament_id;
                        rankInTournament = 0;
                    }
                    rankInTournament++;
                    const rankClass = rankInTournament <= 3 ? `rank-${rankInTournament}` : '';
                    const tr = $(`
                        <tr class="${rankClass}">
                            <td>${row.team_name}</td>
                            <td>${row.tournament_name}</td>
                            <td>${row.matches_played}</td>
                            <td>${row.wins}</td>
                            <td>${row.draws}</td>
                            <td>${row.losses}</td>
                            <td><span class="points-badge">${row.points}</span></td>
                        </tr>
                    `)[0];
                    rows.push(tr);
                });
                tp.setRows(rows);
            },
            error: function () {
                $('#leaderboardBody').html('<tr><td colspan="7" class="no-records">Failed to load standings.</td></tr>');
            }
        });
    }

    $('#tournamentFilter').on('change', function () {
        loadLeaderboard($(this).val());
    });

    loadWinners();
    loadLeaderboard('');
});
</script>
</body>
</html>
