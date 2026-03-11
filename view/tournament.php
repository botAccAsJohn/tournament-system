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
    <title>Create Tournament</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
        input[type="date"],
        select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            outline: none;
            border-color: #4CAF50;
        }

        /* Type selector */
        .type-group {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .type-option {
            flex: 1;
        }

        .type-option input[type="radio"] {
            display: none;
        }

        .type-option label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border: 2px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-align: center;
            transition: all 0.2s;
            margin-bottom: 0;
            user-select: none;
        }

        .type-option input[type="radio"]:checked+label {
            border-color: #4CAF50;
            background-color: #f0fff0;
            color: #2e7d32;
        }

        .type-option label:hover {
            border-color: #4CAF50;
            background-color: #f9fff9;
        }

        /* Message boxes */
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

        /* Tournament Table Styles */
        .table-container {
            margin-top: 40px;
            padding: 0 20px;
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

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-upcoming {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-ongoing {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .no-tournaments {
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
        table td input[type="date"],
        table td select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #4CAF50;
            border-radius: 3px;
            font-size: 13px;
            font-family: Arial, sans-serif;
        }

        table td input[type="text"]:focus,
        table td input[type="date"]:focus,
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
        <h2>Create Tournament</h2>
        <div id="msgBox" style="display:none;"></div>
        <form id="tournamentForm">
            <label for="name">Tournament Name:</label>
            <input type="text" id="name" name="name" placeholder="e.g. Summer League 2025" required>
            <label>Type:</label>
            <div class="type-group">
                <div class="type-option">
                    <input type="radio" id="type_league" name="type" value="league" checked>
                    <label for="type_league">⚽ League</label>
                </div>
                <div class="type-option">
                    <input type="radio" id="type_knockout" name="type" value="knockout">
                    <label for="type_knockout">🏆 Knockout</label>
                </div>
            </div>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
            </select>

            <input type="submit" id="submitBtn" value="Create Tournament">

        </form>

        <a href="welcome.php" class="back-link">← Back to Dashboard</a>
    </div>

    <div class="table-container">
        <h3>All Tournaments</h3>
        <table id="tournamentsTable">
            <thead>
                <tr>
                    <th>Tournament Name</th>
                    <th>Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tournamentBody">
                <tr>
                    <td colspan="6" class="no-tournaments">Loading tournaments...</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>

<script>
    $(document).ready(function() {

        // Set min date for start_date to today
        const today = new Date().toISOString().split('T')[0];
        $('#start_date').attr('min', today);

        // When start date changes, end date must be >= start date
        $('#start_date').on('change', function() {
            $('#end_date').attr('min', $(this).val());
            // Clear end date if it's now before start date
            if ($('#end_date').val() && $('#end_date').val() < $(this).val()) {
                $('#end_date').val('');
            }
        });

        function loadTournaments() {
            $.ajax({
                url: '../api/tournament/getAllTournament.php',
                method: 'GET',
                dataType: 'json',

                success: function(response) {
                    const $tbody = $('#tournamentBody');

                    if (response.status === 'success' && response.data.length > 0) {
                        $tbody.empty();
                        response.data.forEach(element => {
                            const statusClass = 'status-' + (element['status'] || 'upcoming');
                            const row = `<tr data-id="${element['id']}">
                            <td class="col-name">${element['name']}</td>
                            <td class="col-type">${element['type'] ? element['type'].charAt(0).toUpperCase() + element['type'].slice(1) : 'N/A'}</td>
                            <td class="col-start">${element['start_date'] || 'N/A'}</td>
                            <td class="col-end">${element['end_date'] || 'N/A'}</td>
                            <td class="col-status"><span class="status-badge ${statusClass}">${element['status'] || 'Upcoming'}</span></td>
                            <td class="action-cell">
                                <span class="edit-icon" title="Edit">✏️</span>
                                <span class="delete-icon" title="Delete">🗑️</span>
                            </td>
                        </tr>`;
                            $tbody.append(row);
                        });
                        attachActionListeners();
                    } else {
                        $tbody.html('<tr><td colspan="6" class="no-tournaments">No tournaments found.</td></tr>');
                    }
                },

                error: function() {
                    $('#tournamentBody').html('<tr><td colspan="6" class="no-tournaments">Error loading tournaments. Please try again.</td></tr>');
                }
            });
        }

        function attachActionListeners() {
            $('.edit-icon').off('click').on('click', function() {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const name = $row.find('.col-name').text();
                const type = $row.find('.col-type').text().toLowerCase();
                const startDate = $row.find('.col-start').text();
                const endDate = $row.find('.col-end').text();
                const statusText = $row.find('.status-badge').text().toLowerCase();

                // Convert to editable
                $row.find('.col-name').html(`<input type="text" value="${name}" />`);
                $row.find('.col-type').html(`
                <select>
                    <option value="league" ${type === 'league' ? 'selected' : ''}>League</option>
                    <option value="knockout" ${type === 'knockout' ? 'selected' : ''}>Knockout</option>
                </select>
            `);
                $row.find('.col-start').html(`<input type="date" value="${startDate}" />`);
                $row.find('.col-end').html(`<input type="date" value="${endDate}" />`);
                $row.find('.col-status').html(`
                <select>
                    <option value="upcoming" ${statusText === 'upcoming' ? 'selected' : ''}>Upcoming</option>
                    <option value="ongoing" ${statusText === 'ongoing' ? 'selected' : ''}>Ongoing</option>
                    <option value="completed" ${statusText === 'completed' ? 'selected' : ''}>Completed</option>
                </select>
            `);
                $row.find('.action-cell').html(`
                <div class="action-buttons">
                    <button class="btn-save" data-id="${id}">Save</button>
                    <button class="btn-cancel">Cancel</button>
                </div>
            `);

                // Save button
                $row.find('.btn-save').on('click', function(e) {
                    e.preventDefault();
                    const updatedData = {
                        id: id,
                        name: $row.find('.col-name input').val(),
                        type: $row.find('.col-type select').val(),
                        start_date: $row.find('.col-start input').val(),
                        end_date: $row.find('.col-end input').val(),
                        status: $row.find('.col-status select').val()
                    };

                    $.ajax({
                        url: '../api/tournament/editHandler.php',
                        method: 'POST',
                        data: updatedData,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Tournament updated successfully!');
                                loadTournaments();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error updating tournament. Please try again.');
                        }
                    });
                });

                // Cancel button
                $row.find('.btn-cancel').on('click', function(e) {
                    e.preventDefault();
                    loadTournaments();
                });
            });

            $('.delete-icon').off('click').on('click', function() {
                const $row = $(this).closest('tr');
                const id = $row.data('id');
                const name = $row.find('.col-name').text();

                if (confirm(`Are you sure you want to delete "${name}"?`)) {
                    $.ajax({
                        url: '../api/tournament/deleteHandler.php',
                        method: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Tournament deleted successfully!');
                                loadTournaments();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error deleting tournament. Please try again.');
                        }
                    });
                }
            });
        }

        // Load tournaments on page load
        loadTournaments();

        $('#tournamentForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#submitBtn');
            const $msg = $('#msgBox');

            // Client-side date validation
            const start = $('#start_date').val();
            const end = $('#end_date').val();
            if (end < start) {
                $msg.attr('class', 'error-box')
                    .text('End date must be on or after the start date.')
                    .show();
                return;
            }

            $btn.prop('disabled', true).val('Creating...');
            $msg.hide().attr('class', '');

            $.ajax({
                url: '../api/tournament/createHandler.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',

                success: function(response) {
                    if (response.status === 'success') {
                        $msg.attr('class', 'success-box')
                            .text(response.message)
                            .show();
                        $('#tournamentForm')[0].reset();
                        $btn.prop('disabled', false).val('Create Tournament');
                        loadTournaments();
                    } else {
                        $msg.attr('class', 'error-box')
                            .text(response.message)
                            .show();
                        $btn.prop('disabled', false).val('Create Tournament');
                    }
                },

                error: function() {
                    $msg.attr('class', 'error-box')
                        .text('Something went wrong. Please try again.')
                        .show();
                    $btn.prop('disabled', false).val('Create Tournament');
                }
            });
        });
    });
</script>

</html>