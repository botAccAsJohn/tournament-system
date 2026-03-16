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
    <title>Manage Players</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="tableUtils.js"></script>
</head>

<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="card">
        <h2>Create Match</h2>
        <div id="msgBox" style="display:none;"></div>

        <form id="matchForm">

            <label for="tournament_id">Select Tournament :</label>
            <select id="tournament_id" name="tournament_id" required>
                <option value="">-- No Tournament --</option>
            </select>

            <label for="team_id_1">Select Team 1 :</label>
            <select disabled id="team_id_1" name="team_id_1" required>
                <option value="">-- No Team --</option>
            </select>

            <label for="team_id_2">Select Team 2 :</label>
            <select disabled id="team_id_2" name="team_id_2" required>
                <option value="">-- No Team --</option>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required disabled>

            <input type="submit" id="submitBtn" value="Create Match">
        </form>
        <a href="welcome.php" class="back-link">← Back to Dashboard</a>
    </div>

    <!-- Generate league matches panel (shown only for League tournaments) -->
    <div id="generatePanel" class="card" style="display:none; margin-top:0; padding-top:16px;">
        <h3 style="margin:0 0 8px;">⚡ Auto-Generate League Fixtures</h3>
        <p style="color:#94a3b8; margin:0 0 14px; font-size:14px;">Creates all unique team-pair matches for the selected League tournament. Already-existing fixtures are skipped.</p>
        <div id="generateMsg" style="display:none; margin-bottom:10px;"></div>
        <button id="generateBtn" style="padding:10px 24px; background:#0f2040; border:1px solid #7dd3fc; color:#7dd3fc; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Generate All Matches</button>
    </div>

    <div class="table-container">
        <h3>All Matches</h3>
        <div class="search-bar">
            <input type="text" id="matchSearch" placeholder="🔍 Search matches...">
        </div>
        <table id="matchTable">
            <thead>
                <tr>
                    <th>Tournament</th>
                    <th>Team 1</th>
                    <th>Team 2</th>
                    <th>Start Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="matchBody">
                <tr>
                    <td colspan="5" class="no-records">Loading matches...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {

            let allTeams = [];
            let AllTournament = [];
            const tp = new TablePager('matchBody', { pageSize: 10, searchId: 'matchSearch' });

            function loadMatches() {
                $.ajax({
                    url: '../api/match/getAllMatch.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const $body = $('#matchBody');
                        if (response.status === 'success' && response.data.length > 0) {
                            $body.empty();
                            const rows = [];
                            response.data.forEach(match => {
                                const tr = $(`
                        <tr data-id="${match.id}">
                            <td class="col-tournament">${match.tournament_name}</td>
                            <td class="col-team1">${match.team1_name}</td>
                            <td class="col-team2">${match.team2_name}</td>
                            <td class="col-date">${match.match_date}</td>
                            <td class="action-cell">
                                <span class="edit-icon" title="Edit">✏️</span>
                                <span class="delete-icon" title="Delete">🗑️</span>
                            </td>
                        </tr>
                    `)[0];
                                rows.push(tr);
                            });
                            tp.setRows(rows);
                            attachActionListeners();
                        } else {
                            $body.html('<tr><td colspan="5" class="no-records">No matches found.</td></tr>');
                            tp.setRows([]);
                        }
                    },
                    error: function() {
                        $('#matchBody').html('<tr><td colspan="5" class="no-records">Failed to load matches.</td></tr>');
                    }
                });
            }
            loadMatches();

            $('#tournament_id').on('change', function() {
                const tournamentId = $(this).val();
                $.ajax({
                    url: '../api/team/getTeamsByTournament.php',
                    method: 'POST',
                    data: {
                        tournament_id: tournamentId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            allTeams = response.data;
                            const $select = $('#team_id_1');
                            $select.prop('disabled', false);
                            const $tournament = $('#tournament_id');
                            $tournament.prop('disabled', true);
                            $select.html('<option value="">-- No Team --</option>');
                            allTeams.forEach(team => {
                                $select.append(`<option value="${team.id}">${team.name}</option>`);
                            });
                            setStartDateMin(tournamentId);
                        }
                    }
                });
            });

            $('#team_id_1').on('change', function() {
                this.disabled = true;
                const $select = $('#team_id_2');
                $select.prop('disabled', false);
                $select.html('<option value="">-- No Team --</option>');
                allTeams.forEach(team => {
                    if (this.value != team.id) {
                        $select.append(`<option value="${team.id}">${team.name}</option>`);
                    }
                });
            });

            $('#team_id_2').on('change', function() {
                this.disabled = true;
                const $select = $('#start_date');
                $select.prop('disabled', false);
            });

            function setStartDateMin(tournamentId) {
                const currTournament = AllTournament.find(
                    tournament => tournament.id == tournamentId
                );
                const tournamentStart = currTournament.start_date;
                const tournamentEnd = currTournament.end_date;
                const today = new Date().toISOString().split('T')[0];
                const minDate = (tournamentStart > today) ? tournamentStart : today;
                const maxDate = tournamentEnd;
                $('#start_date').attr({
                    min: minDate,
                    max: maxDate
                });
            }

            function loadTournament() {
                $.ajax({
                    url: '../api/tournament/getAllTournament.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const $select = $('#tournament_id');
                        if (response.status === 'success' && response.data.length > 0) {
                            AllTournament = response.data;
                            response.data.forEach(tournament => {
                                const row =
                                    '<option value="' + tournament.id + '">' +
                                    tournament.name +
                                    '</option>';
                                $select.append(row);
                            });
                        }
                    }
                });
            }
            loadTournament();

            // ── Show/hide generate panel based on tournament type ────────────
            $('#tournament_id').on('change', function() {
                const tId = $(this).val();
                const t = AllTournament.find(t => t.id == tId);
                if (t && t.type === 'league') {
                    $('#generatePanel').show();
                } else {
                    $('#generatePanel').hide();
                }
            });

            // ── Generate league matches ──────────────────────────────────────
            $('#generateBtn').on('click', function() {
                const tId = $('#tournament_id').val();
                if (!tId) { alert('Please select a tournament first.'); return; }
                if (!confirm('Generate all remaining league fixtures for this tournament?')) return;

                const $btn = $(this);
                const $msg = $('#generateMsg');
                $btn.prop('disabled', true).text('Generating...');
                $msg.hide();

                $.ajax({
                    url: '../api/match/generateLeagueMatches.php',
                    method: 'POST',
                    data: { tournament_id: tId },
                    dataType: 'json',
                    success: function(res) {
                        const cls = res.status === 'success' ? 'success-box' : 'error-box';
                        $msg.attr('class', cls).text(res.message).show();
                        if (res.status === 'success') loadMatches();
                    },
                    error: function() {
                        $msg.attr('class', 'error-box').text('Server error occurred.').show();
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Generate All Matches');
                    }
                });
            });

            $('#matchForm').on('submit', function(e) {
                e.preventDefault();
                $('#tournament_id, #team_id_1, #team_id_2').prop('disabled', false);
                const $btn = $('#submitBtn');
                const $msg = $('#msgBox');
                $btn.prop('disabled', true).val('Creating...');
                $msg.hide();
                $.ajax({
                    url: '../api/match/createHandler.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $msg.attr('class', 'success-box')
                                .text(response.message)
                                .show();
                            $('#matchForm')[0].reset();
                            $('#team_id_1').prop('disabled', true);
                            $('#team_id_2').prop('disabled', true);
                            $('#start_date').prop('disabled', true);
                            $('#tournament_id').prop('disabled', false);
                            loadMatches();
                        } else {
                            $msg.attr('class', 'error-box')
                                .text(response.message)
                                .show();
                        }
                        $btn.prop('disabled', false).val('Create Match');
                        loadMatches();
                    },

                    error: function() {
                        $msg.attr('class', 'error-box')
                            .text('Server error occurred.')
                            .show();
                        $btn.prop('disabled', false).val('Create Match');
                    }
                });
            });

            function attachActionListeners() {

                $('.edit-icon').off('click').on('click', function() {
                    const $row = $(this).closest('tr');
                    const id = $row.data('id');
                    const tournamentName = $row.find('.col-tournament').text().trim();
                    const team1Name = $row.find('.col-team1').text().trim();
                    const team2Name = $row.find('.col-team2').text().trim();
                    const matchDate = $row.find('.col-date').text().trim();

                    // Find the original tournament ID to pre-select it
                    const currentTournament = AllTournament.find(t => t.name === tournamentName);
                    if (!currentTournament) return;

                    // Create Tournament Dropdown
                    let tournamentOptions = AllTournament.map(t => `<option value="${t.id}" ${t.id == currentTournament.id ? 'selected' : ''}>${t.name}</option>`).join('');
                    $row.find('.col-tournament').html(`<select class="edit-tournament" style="width: 100%">${tournamentOptions}</select>`);

                    // Create Team 1 Dropdown
                    $row.find('.col-team1').html(`<select class="edit-team1" style="width: 100%"><option value="">Loading...</option></select>`);

                    // Create Team 2 Dropdown
                    $row.find('.col-team2').html(`<select class="edit-team2" style="width: 100%"><option value="">Loading...</option></select>`);

                    // Create Date Input
                    $row.find('.col-date').html(`<input type="date" class="edit-date" value="${matchDate}" style="width: 100%" />`);

                    $row.find('.action-cell').html(`
                    <div class="action-buttons">
                        <button class="btn-save" data-id="${id}">Save</button>
                        <button class="btn-cancel">Cancel</button>
                    </div>
                `);

                    // Function to load teams and setup cascading logic within the row
                    function setupRowConstraints(row, tId, initialT1Name = null, initialT2Name = null) {
                        $.ajax({
                            url: '../api/team/getTeamsByTournament.php',
                            method: 'POST',
                            data: {
                                tournament_id: tId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    const rowTeams = response.data;
                                    const $t1 = row.find('.edit-team1');
                                    const $t2 = row.find('.edit-team2');
                                    const $date = row.find('.edit-date');

                                    // Sync Date Constraints
                                    const tournamentDetails = AllTournament.find(t => t.id == tId);
                                    if (tournamentDetails) {
                                        const today = new Date().toISOString().split('T')[0];
                                        $date.attr({
                                            min: (tournamentDetails.start_date > today) ? tournamentDetails.start_date : today,
                                            max: tournamentDetails.end_date
                                        });
                                    }

                                    function updateTeam2Options() {
                                        const val1 = $t1.val();
                                        const val2Original = initialT2Name ? rowTeams.find(t => t.name === initialT2Name)?.id : $t2.val();

                                        $t2.empty().append('<option value="">-- Select Team 2 --</option>');
                                        rowTeams.forEach(team => {
                                            if (team.id != val1) {
                                                $t2.append(`<option value="${team.id}" ${team.id == val2Original ? 'selected' : ''}>${team.name}</option>`);
                                            }
                                        });
                                        initialT2Name = null; // Clear after first load
                                    }

                                    $t1.empty().append('<option value="">-- Select Team 1 --</option>');
                                    rowTeams.forEach(team => {
                                        const isSelected = initialT1Name && team.name === initialT1Name;
                                        $t1.append(`<option value="${team.id}" ${isSelected ? 'selected' : ''}>${team.name}</option>`);
                                    });
                                    initialT1Name = null; // Clear after first load

                                    updateTeam2Options();

                                    $t1.off('change').on('change', updateTeam2Options);
                                }
                            }
                        });
                    }

                    // Initial setup for the row
                    setupRowConstraints($row, currentTournament.id, team1Name, team2Name);

                    // Handle Tournament Change in Row
                    $row.find('.edit-tournament').on('change', function() {
                        setupRowConstraints($row, $(this).val());
                    });

                    // ── Save ──
                    $row.find('.btn-save').on('click', function(e) {
                        e.preventDefault();
                        const updatedData = {
                            id: id,
                            tournament_id: $row.find('.edit-tournament').val(),
                            team_id_1: $row.find('.edit-team1').val(),
                            team_id_2: $row.find('.edit-team2').val(),
                            start_date: $row.find('.edit-date').val()
                        };

                        if (!updatedData.tournament_id || !updatedData.team_id_1 || !updatedData.team_id_2 || !updatedData.start_date) {
                            alert('All fields are required.');
                            return;
                        }

                        $.ajax({
                            url: '../api/match/editHandler.php',
                            method: 'POST',
                            data: updatedData,
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    alert('Match updated successfully!');
                                    loadMatches();
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Error updating match. Please try again.');
                            }
                        });
                    });

                    // ── Cancel ──
                    $row.find('.btn-cancel').on('click', function(e) {
                        e.preventDefault();
                        loadMatches();
                    });
                });

                $('.delete-icon').off('click').on('click', function() {
                    const $row = $(this).closest('tr');
                    const id = $row.data('id');
                    const team1 = $row.find('.col-team1').text();
                    const team2 = $row.find('.col-team2').text();

                    if (confirm(`Are you sure you want to delete the match "${team1} vs ${team2}"?`)) {
                        $.ajax({
                            url: '../api/match/deleteHandler.php',
                            method: 'POST',
                            data: {
                                id: id
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    alert('Match deleted successfully!');
                                    loadMatches();
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Error deleting match. Please try again.');
                            }
                        });
                    }
                });
            }
        });
    </script>
</body>

</html>