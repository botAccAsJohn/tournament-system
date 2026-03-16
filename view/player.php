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
        <h2>Create Player</h2>
        <div id="msgBox" style="display:none;"></div>
        <form id="playerForm">
            <label for="name">Player Name:</label>
            <input type="text" id="name" name="name" placeholder="Full Name" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="email@example.com" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="At least 8 characters" required>

            <label for="team_id">Assign Team (Optional):</label>
            <select id="team_id" name="team_id">
                <option value="">-- No Team --</option>
            </select>

            <input type="submit" id="submitBtn" value="Create Player">
        </form>
        <a href="welcome.php" class="back-link">← Back to Dashboard</a>
    </div>

    <div class="table-container">
        <h3>All Players</h3>
        <div class="search-bar">
            <input type="text" id="playerSearch" placeholder="🔍 Search players...">
        </div>
        <table id="playersTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Team</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="playerBody">
                <tr>
                    <td colspan="4" class="no-records">Loading players...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
    $(document).ready(function () {
        let allTeams = [];
        const tp = new TablePager('playerBody', { pageSize: 10, searchId: 'playerSearch' });

        function loadTeams() {
            $.ajax({
                url: '../api/team/getAllTeams.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        allTeams = response.data;
                        const $select = $('#team_id');
                        $select.html('<option value="">-- No Team --</option>');
                        allTeams.forEach(team => {
                            $select.append(`<option value="${team.id}">${team.name} (${team.tournament_name})</option>`);
                        });
                    }
                }
            });
        }

        function loadPlayers() {
            $.ajax({
                url: '../api/player/getAllPlayers.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    const $tbody = $('#playerBody');
                    if (response.status === 'success' && response.data.length > 0) {
                        $tbody.empty();
                        const rows = [];
                        response.data.forEach(player => {
                            const tr = $(`<tr data-id="${player.id}" data-team-id="${player.team_id || ''}">
                                <td class="col-name">${player.name}</td>
                                <td class="col-email">${player.email}</td>
                                <td class="col-team">${player.team_name || 'No Team'}</td>
                                <td class="action-cell">
                                    <span class="edit-icon" title="Edit">✏️</span>
                                    <span class="delete-icon" title="Delete">🗑️</span>
                                </td>
                            </tr>`)[0];
                            rows.push(tr);
                        });
                        tp.setRows(rows);
                        attachActionListeners();
                    } else {
                        $tbody.html('<tr><td colspan="4" class="no-records">No players found.</td></tr>');
                        tp.setRows([]);
                    }
                },
                error: function () {
                    $('#playerBody').html('<tr><td colspan="4" class="no-records">Error loading players.</td></tr>');
                }
            });
        }

        function attachActionListeners() {
            $('.edit-icon').off('click').on('click', function() {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const name = $row.find('.col-name').text();
                const teamId = $row.data('team-id');

                $row.find('.col-name').html(`<input type="text" value="${name}" />`);
                
                let teamSelect = '<select class="edit-team">';
                teamSelect += `<option value="" ${teamId === '' ? 'selected' : ''}>-- No Team --</option>`;
                allTeams.forEach(team => {
                    teamSelect += `<option value="${team.id}" ${team.id == teamId ? 'selected' : ''}>${team.name}</option>`;
                });
                teamSelect += '</select>';
                $row.find('.col-team').html(teamSelect);

                $row.find('.action-cell').html(`
                    <div class="action-buttons">
                        <button class="btn-save" data-id="${id}">Save</button>
                        <button class="btn-cancel">Cancel</button>
                    </div>
                `);

                $row.find('.btn-save').on('click', function() {
                    const updatedData = {
                        id: id,
                        name: $row.find('.col-name input').val(),
                        team_id: $row.find('.col-team select').val()
                    };

                    $.ajax({
                        url: '../api/player/editHandler.php',
                        method: 'POST',
                        data: updatedData,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Player updated successfully!');
                                loadPlayers();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        }
                    });
                });

                $row.find('.btn-cancel').on('click', loadPlayers);
            });

            $('.delete-icon').off('click').on('click', function() {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const name = $row.find('.col-name').text();

                if (confirm(`Are you sure you want to delete "${name}"? This will also delete their user account.`)) {
                    $.ajax({
                        url: '../api/player/deleteHandler.php',
                        method: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Player deleted successfully!');
                                loadPlayers();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        }
                    });
                }
            });
        }

        $('#playerForm').on('submit', function (e) {
            e.preventDefault();
            const $btn = $('#submitBtn');
            const $msg = $('#msgBox');

            $btn.prop('disabled', true).val('Creating...');
            $msg.hide();

            $.ajax({
                url: '../api/player/createHandler.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $msg.attr('class', 'success-box').text(response.message).show();
                        $('#playerForm')[0].reset();
                        loadPlayers();
                    } else {
                        $msg.attr('class', 'error-box').text(response.message).show();
                    }
                    $btn.prop('disabled', false).val('Create Player');
                },
                error: function () {
                    $msg.attr('class', 'error-box').text('An error occurred.').show();
                    $btn.prop('disabled', false).val('Create Player');
                }
            });
        });

        loadTeams();
        loadPlayers();
    });
    </script>
</body>
</html>