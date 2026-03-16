<?php
session_start();

// Only admins can access this page
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
    <title>Match Score Board</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="tableUtils.js"></script>
</head>

<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="table-container" style="margin-top: 5%;">
        <h3>All Matches</h3>
        <div class="search-bar">
            <input type="text" id="scoreSearch" placeholder="🔍 Search by team or tournament...">
        </div>
        <table id="matchTable">
            <thead>
                <tr>
                    <th>Tournament</th>
                    <th>Team 1</th>
                    <th>Team 2</th>
                    <th>Team 1 Score</th>
                    <th>Team 2 Score</th>
                    <th>Result</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="matchBody">
                <tr>
                    <td colspan="7" class="no-records">Loading matches...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {

            const tp = new TablePager('matchBody', { pageSize: 10, searchId: 'scoreSearch' });

            // ── Load & render matches ────────────────────────────────────────
            function loadMatches() {
                $.ajax({
                    url: '../api/matchScore/getAllMatchScore.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        const $body = $('#matchBody');
                        if (response.status === 'success' && response.data.length > 0) {
                            $body.empty();
                            const rows = [];
                            response.data.forEach(match => {
                                const editLabel = match.status === 'completed' ? '✏️' : '➕';
                                const editTitle = match.status === 'completed' ? 'Edit Score' : 'Add Score';
                                const tr = $(`
                                    <tr data-id="${match.id}">
                                        <td class="col-tournament">${match.Tournament}</td>
                                        <td class="col-team1">${match.Team1}</td>
                                        <td class="col-team2">${match.Team2}</td>
                                        <td class="col-team1score">${match.Team1_Score}</td>
                                        <td class="col-team2score">${match.Team2_Score}</td>
                                        <td class="col-result">${match.Result}</td>
                                        <td class="action-cell">
                                            <span class="edit-icon" title="${editTitle}">${editLabel}</span>
                                        </td>
                                    </tr>
                                `)[0];
                                rows.push(tr);
                            });
                            tp.setRows(rows);
                            attachActionListeners();
                        } else {
                            $body.html('<tr><td colspan="7" class="no-records">No matches found.</td></tr>');
                            tp.setRows([]);
                        }
                    },
                    error: function () {
                        $('#matchBody').html('<tr><td colspan="7" class="no-records">Failed to load matches.</td></tr>');
                    }
                });
            }
            loadMatches();

            // ── Inline edit row ──────────────────────────────────────────────
            function attachActionListeners() {
                $('.edit-icon').off('click').on('click', function () {
                    const $row = $(this).closest('tr');

                    // Prevent opening multiple edit rows at once
                    if ($row.find('.edit-team1score').length) return;

                    const id         = $row.data('id');
                    const team1Score = $row.find('.col-team1score').text().trim();
                    const team2Score = $row.find('.col-team2score').text().trim();

                    // Replace score cells with number inputs
                    $row.find('.col-team1score').html(
                        `<input type="number" class="edit-team1score" value="${team1Score}" min="0" style="width:100%">`
                    );
                    $row.find('.col-team2score').html(
                        `<input type="number" class="edit-team2score" value="${team2Score}" min="0" style="width:100%">`
                    );

                    // Replace action cell with Save / Cancel buttons
                    $row.find('.action-cell').html(`
                        <div class="action-buttons">
                            <button class="btn-save"   data-id="${id}">Save</button>
                            <button class="btn-cancel">Cancel</button>
                        </div>
                    `);

                    // ── Save ─────────────────────────────────────────────────
                    $row.find('.btn-save').on('click', function (e) {
                        e.preventDefault();

                        const team1ScoreVal = $row.find('.edit-team1score').val().trim();
                        const team2ScoreVal = $row.find('.edit-team2score').val().trim();

                        if (team1ScoreVal === '' || team2ScoreVal === '') {
                            alert('Both scores are required.');
                            return;
                        }
                        if (parseInt(team1ScoreVal) < 0 || parseInt(team2ScoreVal) < 0) {
                            alert('Scores cannot be negative.');
                            return;
                        }

                        $.ajax({
                            url: '../api/matchScore/editHandler.php',
                            method: 'POST',
                            data: {
                                id:          id,
                                team1_score: team1ScoreVal,
                                team2_score: team2ScoreVal
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.status === 'success') {
                                    alert('Score updated successfully!');
                                    loadMatches();
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function () {
                                alert('Error updating score. Please try again.');
                            }
                        });
                    });

                    // ── Cancel ────────────────────────────────────────────────
                    $row.find('.btn-cancel').on('click', function (e) {
                        e.preventDefault();
                        loadMatches();
                    });
                });
            }
        });
    </script>
</body>

</html>