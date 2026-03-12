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

    <div class="table-container">
        <h3>All Matches</h3>
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

            function loadMatches() {
                $.ajax({
                    url: '../api/match/getAllMatch.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const $body = $('#matchBody');
                        if (response.status === 'success' && response.data.length > 0) {
                            $body.empty();
                            response.data.forEach(match => {
                                $body.append(`
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
                    `);
                            });
                            attachActionListeners();
                        } else {
                            $body.html('<tr><td colspan="5" class="no-records">No matches found.</td></tr>');
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
                    },

                    error: function() {
                        $msg.attr('class', 'error-box')
                            .text('Server error occurred.')
                            .show();
                        $btn.prop('disabled', false).val('Create Match');
                    }
                });
            });
        });

        function attachActionListeners() {

            $('.edit-icon').off('click').on('click', function() {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const matchDate = $row.find('.col-date').text();

                // Only date is editable; team/tournament names are display-only
                $row.find('.col-date').html(`<input type="date" value="${matchDate}" />`);
                $row.find('.action-cell').html(`
                <div class="action-buttons">
                    <button class="btn-save" data-id="${id}">Save</button>
                    <button class="btn-cancel">Cancel</button>
                </div>
            `);

                // ── Save ──
                $row.find('.btn-save').on('click', function(e) {
                    e.preventDefault();
                    const updatedData = {
                        id: id,
                        match_date: $row.find('.col-date input').val()
                    };

                    if (!updatedData.match_date) {
                        alert('Match date is required.');
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
    </script>
</body>

</html>