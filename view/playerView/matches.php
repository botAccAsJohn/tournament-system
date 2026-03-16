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
    <title>Match Results</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .status-scheduled  { background:#1e3a5f; color:#7dd3fc; padding:3px 10px; border-radius:20px; font-size:12px; }
        .status-completed  { background:#14532d; color:#86efac; padding:3px 10px; border-radius:20px; font-size:12px; }
    </style>
</head>
<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="table-container" style="margin-top: 5%;">
        <h3>Match Results</h3>
        <table id="matchTable">
            <thead>
                <tr>
                    <th>Tournament</th>
                    <th>Team 1</th>
                    <th>Team 2</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Winner</th>
                </tr>
            </thead>
            <tbody id="matchBody">
                <tr><td colspan="6" class="no-records">Loading matches...</td></tr>
            </tbody>
        </table>
    </div>

<script>
$(document).ready(function () {
    $.ajax({
        url: '../../api/match/getAllMatch.php',
        method: 'GET',
        dataType: 'json',
        success: function (res) {
            const $body = $('#matchBody');
            if (res.status === 'success' && res.data.length > 0) {
                $body.empty();
                res.data.forEach(m => {
                    const winner = m.winner_team_name
                        ? `🏅 ${m.winner_team_name}`
                        : (m.status === 'completed' ? 'Draw' : '—');
                    $body.append(`
                        <tr>
                            <td>${m.tournament_name}</td>
                            <td>${m.team1_name}</td>
                            <td>${m.team2_name}</td>
                            <td>${m.match_date}</td>
                            <td><span class="status-${m.status}">${m.status}</span></td>
                            <td>${winner}</td>
                        </tr>
                    `);
                });
            } else {
                $body.html('<tr><td colspan="6" class="no-records">No matches found.</td></tr>');
            }
        },
        error: function () {
            $('#matchBody').html('<tr><td colspan="6" class="no-records">Failed to load.</td></tr>');
        }
    });
});
</script>
</body>
</html>
