<?php
include 'Database/dbconfig.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username exists
    $check = mysqli_query($conn, "SELECT user_id FROM bank_users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "<p class='error'>Username already taken.</p>";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO bank_users (username, password, full_name) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $username, $password, $fullname);
        
        if (mysqli_stmt_execute($stmt)) {
            $msg = "<p class='success'>Account created! <a href='index.php'>Login Now</a></p>";
        } else {
            $msg = "<p class='error'>Error creating account.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register | VirtualBank</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="auth-container">
        <div class="card" style="width: 400px; text-align: center;">
            <div class="brand">Register | Banktiverse</div>
            <?php if ($msg): ?>
                <div class='error'><?= $msg ?></div>
            <?php endif; ?>

            <form method="POST">
                <label style="text-align: left;">Full Name</label>
                <input type="text" name="username" placeholder="Enter Full Name" required>

                <label style="text-align: left;">Username</label>
                <input type="text" name="username" placeholder="Enter username" required>

                <label style="text-align: left;">Password</label>
                <input type="password" name="password" placeholder="Enter password" required>

                <button type="submit">Register</button>
            </form>

            <div style="margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
                Already have an account? <a href="index.php">Login</a>
            </div>
        </div>
    </div>
</body>
</html>