<?php
session_start();

// Check session — redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .info-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px 20px;
            margin: 20px 0;
            text-align: left;
        }

        .info-box p {
            margin-bottom: 8px;
        }

        .info-box p:last-child {
            margin-bottom: 0;
        }

        .info-box span {
            font-weight: bold;
            color: #333;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 30px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #c62828;
        }
    </style>
</head>

<body>
    <?php include_once './components/navbar.php'; ?>
    <div class="card">
        <h1>Welcome back!</h1>
        <p>You are successfully logged in.</p>

        <div class="info-box">
            <p>👤 Name: <span><?= htmlspecialchars($_SESSION['user_name']) ?></span></p>
            <p>📧 Email: <span><?= htmlspecialchars($_SESSION['user_email']) ?></span></p>
            <p>🔖 Role:
                <?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'player')) ?>
            </p>
        </div>

        <a id="logoutBtn" class="logout-btn">Logout</a>
    </div>

</body>
<script>
    $(document).ready(function() {

        $('#logoutBtn').on('click', function() {
            $.ajax({
                url: '../auth/api/logoutHandler.php',
                method: 'POST',

                success: function() {
                    // Update UI to show logged out state
                    $('.card h1').text('You have been logged out.');
                    $('.info-box').hide();
                    $('#logoutBtn').hide();
                    $('p').text('');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                },

                error: function() {
                    alert('Logout failed. Please try again.');
                }
            });
        });

    });
</script>

</html>