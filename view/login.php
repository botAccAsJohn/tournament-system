<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['user_role'] ?? '') === 'admin' ? 'welcome.php' : 'playerDashboard.php';
    header('Location: ' . $redirect);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <div class="card">
        <form id="loginForm">
            <h2>Login Form</h2>
            <br>

        <div id="msgBox" style="display:none;"></div>

        <label>Role:</label>
        <div class="role-group">
            <div class="role-option">
                <input type="radio" id="role_player" name="role" value="player" checked>
                <label for="role_player">Player</label>
            </div>
            <div class="role-option">
                <input type="radio" id="role_admin" name="role" value="admin">
                <label for="role_admin">Admin</label>
            </div>
        </div>
        <br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <br>
        <button type="submit" id="submitBtn">Login</button>

        <p>Don't have an account? <a href="registration.php">Register</a></p>
    </form>
</div>
</body>

<script>
$(document).ready(function () {

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#submitBtn');
        const $msg = $('#msgBox');

        $btn.prop('disabled', true).text('Logging in...');
        $msg.hide().attr('class', '');

        $.ajax({
            url:      '../auth/api/loginHandler.php',
            method:   'POST',
            data:     $(this).serialize(),
            dataType: 'json',

            success: function (response) {
                if (response.status === 'success') {
                    $msg.attr('class', 'success-box')
                        .text(response.message)
                        .show();

                    const dest = response.role === 'admin' ? 'welcome.php' : 'playerDashboard.php';
                    setTimeout(() => {
                        window.location.href = dest;
                    }, 1500);

                } else {
                    $msg.attr('class', 'error-box')
                        .text(response.message)
                        .show();
                    $btn.prop('disabled', false).text('Login');
                }
            },

            error: function () {
                $msg.attr('class', 'error-box')
                    .text('Something went wrong. Please try again.')
                    .show();
                $btn.prop('disabled', false).text('Login');
            }
        });
    });

});
</script>
</html>