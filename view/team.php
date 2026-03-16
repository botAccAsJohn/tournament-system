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
    <title>Team Management</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="tableUtils.js"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 30px 0;
        }

        .card {
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 480px;
        }

        .card h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444;
            font-size: 14px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #4CAF50;
        }

        #submitBtn {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
        }

        #submitBtn:hover {
            background-color: #45a049;
        }

        #submitBtn:disabled {
            background-color: #aaa;
            cursor: not-allowed;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* MESSAGE BOX */

        .error-box {
            background-color: #ffe0e0;
            border: 1px solid #f44336;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 18px;
            color: #b71c1c;
            font-size: 14px;
        }

        .success-box {
            background-color: #e0ffe0;
            border: 1px solid #4CAF50;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 18px;
            color: #2e7d32;
            font-size: 14px;
        }

        /* TABLE */

        .table-container {
            margin-top: 40px;
            padding: 0 20px;
            width: 100%;
            max-width: 900px;
        }

        .table-container h3 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        table thead {
            background-color: #4CAF50;
            color: white;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #45a049;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:hover {
            background-color: #f5f5f5;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .no-teams {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 16px;
        }

        /* Action Icons */
        .action-cell {
            text-align: center;
            white-space: nowrap;
        }

        .edit-icon,
        .delete-icon {
            cursor: pointer;
            font-size: 18px;
            margin: 0 8px;
            transition: transform 0.2s;
        }

        .edit-icon {
            color: #4CAF50;
        }

        .edit-icon:hover {
            transform: scale(1.2);
            color: #45a049;
        }

        .delete-icon {
            color: #f44336;
        }

        .delete-icon:hover {
            transform: scale(1.2);
            color: #da190b;
        }

        /* Editable row styling */
        table td input[type="text"],
        table td select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #4CAF50;
            border-radius: 3px;
            font-size: 13px;
            font-family: Arial, sans-serif;
        }

        table td input[type="text"]:focus,
        table td select:focus {
            outline: none;
            border-color: #45a049;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-save,
        .btn-cancel {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-save {
            background-color: #4CAF50;
            color: white;
        }

        .btn-save:hover {
            background-color: #45a049;
        }

        .btn-cancel {
            background-color: #999;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #777;
        }
    </style>
</head>

<body>
    <?php include_once './components/navbar.php'; ?>

    <div class="card">
        <h2>Create Team</h2>
        <div id="msgBox" style="display:none;"></div>
        <form id="TeamForm">
            <label for="name">Team Name:</label>
            <input type="text" id="name" name="name" placeholder="e.g. Gujarat Titans" required>
            <label>Select Tournament:</label>
            <select id="categoryDropdown" name="id" required>
                <option value="">Select Tournament</option>
            </select>

            <input type="submit" id="submitBtn" value="Create Team">
        </form>

        <a href="welcome.php" class="back-link">&larr; Back to Dashboard</a>
    </div>

    <div class="table-container">
        <h3>All Teams</h3>
        <div class="search-bar">
            <input type="text" id="teamSearch" placeholder="🔍 Search teams...">
        </div>
        <table id="TeamTable">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Tournament</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="teamBody">
                <tr>
                    <td colspan="3" class="no-teams">Loading teams...</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
<script>
    $(document).ready(function () {
        let tournaments = [];
        const tp = new TablePager('teamBody', { pageSize: 10, searchId: 'teamSearch' });

        function populateTournamentOptions($select, selectedId = '') {
            const options = ['<option value="">Select Tournament</option>'];

            tournaments.forEach(function (item) {
                const isSelected = String(item.id) === String(selectedId) ? 'selected' : '';
                options.push(`<option value="${item.id}" ${isSelected}>${item.name}</option>`);
            });

            $select.html(options.join(''));
        }

        function getTournaments() {
            $.ajax({
                url: "../api/tournament/getAllTournament.php",
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        tournaments = response.data || [];
                        populateTournamentOptions($('#categoryDropdown'));
                    }
                },
                error: function () {
                    $('#msgBox')
                        .attr('class', 'error-box')
                        .text('Failed to load tournaments.')
                        .show();
                }
            });
        }
        getTournaments();

        $('#TeamForm').on('submit', function (e) {
            e.preventDefault();
            const $btn = $('#submitBtn');
            const $msg = $('#msgBox');
            $btn.prop('disabled', true).val('Creating...');
            $msg.hide().attr('class', '');
            $.ajax({
                url: '../api/team/createHandler.php',
                method: "POST",
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $msg
                            .attr('class', 'success-box')
                            .text(response.message)
                            .show();
                        $('#TeamForm')[0].reset();
                        populateTournamentOptions($('#categoryDropdown'));
                        loadTeams();
                    } else {
                        $msg
                            .attr('class', 'error-box')
                            .text(response.message)
                            .show();
                    }
                    $btn.prop('disabled', false).val('Create Team');
                },
                error: function () {
                    $msg
                        .attr('class', 'error-box')
                        .text('Something went wrong. Please try again.')
                        .show();
                    $btn.prop('disabled', false).val('Create Team');
                }
            });
        });

        function loadTeams() {
            $.ajax({
                url: "../api/team/getAllTeams.php",
                method: "GET",
                dataType: "json",

                success: function (response) {
                    const $tbody = $('#teamBody');
                    if (response.status === 'success' && response.data.length > 0) {
                        $tbody.empty();
                        const rows = [];
                        response.data.forEach(team => {
                            const tr = $(`
                        <tr data-id="${team.id}" data-tournament-id="${team.tournament_id}">
                            <td class="col-name">${team.name}</td>
                            <td class="col-tournament">${team.tournament_name || 'N/A'}</td>
                            <td class="action-cell">
                                <span class="edit-icon" title="Edit">&#9999;&#65039;</span>
                                <span class="delete-icon" title="Delete">&#128465;&#65039;</span>
                            </td>
                        </tr>
                    `)[0];
                            rows.push(tr);
                        });
                        tp.setRows(rows);
                        attachActionListeners();
                    } else {
                        $tbody.html(
                            '<tr><td colspan="3" class="no-teams">No teams found.</td></tr>'
                        );
                        tp.setRows([]);
                    }
                },
                error: function () {
                    $('#teamBody').html(
                        '<tr><td colspan="3" class="no-teams">Error loading teams.</td></tr>'
                    );
                }
            });
        }

        function attachActionListeners() {
            $('.edit-icon').off('click').on('click', function () {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const currentName = $row.find('.col-name').text().trim();
                const tournamentId = $row.data('tournament-id');

                $row.find('.col-name').html(`<input type="text" value="${currentName}" />`);
                $row.find('.col-tournament').html('<select class="edit-tournament"></select>');
                populateTournamentOptions($row.find('.edit-tournament'), tournamentId);
                $row.find('.action-cell').html(`
                    <div class="action-buttons">
                        <button class="btn-save" data-id="${id}">Save</button>
                        <button class="btn-cancel">Cancel</button>
                    </div>
                `);

                $row.find('.btn-save').on('click', function (e) {
                    e.preventDefault();

                    const updatedData = {
                        id: id,
                        name: $row.find('.col-name input').val().trim(),
                        tournament_id: $row.find('.col-tournament select').val()
                    };

                    $.ajax({
                        url: '../api/team/editHandler.php',
                        method: 'POST',
                        data: updatedData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                alert('Team updated successfully!');
                                loadTeams();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function () {
                            alert('Error updating team. Please try again.');
                        }
                    });
                });

                $row.find('.btn-cancel').on('click', function (e) {
                    e.preventDefault();
                    loadTeams();
                });
            });

            $('.delete-icon').off('click').on('click', function () {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const name = $row.find('.col-name').text().trim();

                if (confirm(`Are you sure you want to delete "${name}"?`)) {
                    $.ajax({
                        url: '../api/team/deletehandler.php',
                        method: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                alert('Team deleted successfully!');
                                loadTeams();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function () {
                            alert('Error deleting team. Please try again.');
                        }
                    });
                }
            });
        }

        loadTeams();
    });
</script>

</html>
