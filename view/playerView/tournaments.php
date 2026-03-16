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
    <title>Tournaments</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="table-container" style="margin-top: 5%;">
        <h3>All Tournaments</h3>
        <table id="tournamentsTable">
            <thead>
                <tr>
                    <th>Tournament Name</th>
                    <th>Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="tournamentBody">
                <tr><td colspan="5" class="no-records">Loading tournaments...</td></tr>
            </tbody>
        </table>
    </div>

<script>
$(document).ready(function () {
    $.ajax({
        url: '../../api/tournament/getAllTournament.php',
        method: 'GET',
        dataType: 'json',
        success: function (res) {
            const $tbody = $('#tournamentBody');
            if (res.status === 'success' && res.data.length > 0) {
                $tbody.empty();
                res.data.forEach(t => {
                    const statusClass = 'status-' + (t.status || 'upcoming');
                    $tbody.append(`
                        <tr>
                            <td>${t.name}</td>
                            <td>${t.type ? t.type.charAt(0).toUpperCase() + t.type.slice(1) : 'N/A'}</td>
                            <td>${t.start_date || 'N/A'}</td>
                            <td>${t.end_date   || 'N/A'}</td>
                            <td><span class="status-badge ${statusClass}">${t.status || 'upcoming'}</span></td>
                        </tr>
                    `);
                });
            } else {
                $tbody.html('<tr><td colspan="5" class="no-records">No tournaments found.</td></tr>');
            }
        },
        error: function () {
            $('#tournamentBody').html('<tr><td colspan="5" class="no-records">Failed to load.</td></tr>');
        }
    });
});
</script>
</body>
</html>
