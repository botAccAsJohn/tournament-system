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
    <title>Create Tournament</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="tableUtils.js"></script>
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
        <div class="search-bar">
            <input type="text" id="tournamentSearch" placeholder="🔍 Search tournaments...">
        </div>
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
        const tp = new TablePager('tournamentBody', { pageSize: 10, searchId: 'tournamentSearch' });

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
                        const rows = [];
                        response.data.forEach(element => {
                            const statusClass = 'status-' + (element['status'] || 'upcoming');
                            const tr = $(`<tr data-id="${element['id']}">
                            <td class="col-name">${element['name']}</td>
                            <td class="col-type">${element['type'] ? element['type'].charAt(0).toUpperCase() + element['type'].slice(1) : 'N/A'}</td>
                            <td class="col-start">${element['start_date'] || 'N/A'}</td>
                            <td class="col-end">${element['end_date'] || 'N/A'}</td>
                            <td class="col-status"><span class="status-badge ${statusClass}">${element['status'] || 'Upcoming'}</span></td>
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
                        $tbody.html('<tr><td colspan="6" class="no-tournaments">No tournaments found.</td></tr>');
                        tp.setRows([]);
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