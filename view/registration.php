<?php
session_start();
if (isset($_SESSION["user_id"])) {
    header("location: welcome.php");
    exit();
}
$errors   = $_SESSION['errors']   ?? [];
$old      = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <div class="card">
        <form id="registrationForm">
            <h2>Registration Form</h2>

            <!-- Always in the DOM, shown/hidden by JS -->
            <div id="msgBox" style="display:none;"></div>

        <?php if (!empty($errors)): ?>
            <script>
                $(document).ready(function() {
                    $('#msgBox').attr('class', 'error-box')
                        .html('<strong>Please fix the following errors:</strong><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>')
                        .show();
                });
            </script>
        <?php endif; ?>


        <label for="name">Name:</label>
        <input type="text" id="name" name="name"
            value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <br>
        <input type="submit" id="submitBtn" value="Register">

        <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>
</body>

<script>
    $(document).ready(function() {

        $('#registrationForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#submitBtn');
            const $msg = $('#msgBox');

            // UI feedback
            $btn.prop('disabled', true).val('Registering...');
            $msg.hide().attr('class', '');

            $.ajax({
                url: '../auth/api/registrationHandler.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',

                success: function(response) {
                    if (response.status === 'success') {
                        $msg.attr('class', 'success-box')
                            .text(response.message)
                            .show();

                        setTimeout(() => {
                            window.location.href = 'welcome.php';
                        }, 1500);

                    } else {
                        $msg.attr('class', 'error-box')
                            .text(response.message)
                            .show();
                        $btn.prop('disabled', false).val('Register');
                    }
                },

                error: function() {
                    $msg.attr('class', 'error-box')
                        .text('Something went wrong. Please try again.')
                        .show();
                    $btn.prop('disabled', false).val('Register');
                }
            });
        });

    });
</script>

</html>