<?php
include 'Database/dbconfig.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT user_id, password, full_name, balance FROM bank_users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['bank_user_id'] = $row['user_id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['balance'] = $row['balance'];

        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}

if (isset($_SESSION['bank_user_id'])) {
    if (!empty($_SESSION['logout_from']) && $_SESSION['logout_from'] === 'payment') {
        unset($_SESSION['logout_from']); 
        header("Location: pay.php?amount=" . urlencode($_SESSION['amount'] ?? '') . "&return_url=" . urlencode($_SESSION['return_url'] ?? '') . "&merchant=" . urlencode($_SESSION['merchant_name'] ?? ''));
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Login | Banktiverse</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="auth-container">
        <div class="card" style="width: 400px; text-align: center;">
            <div class="brand">Banktiverse</div>
            <h2 style="margin-top:0;">Welcome Back</h2>
            <p style="color:var(--text-muted); margin-bottom: 20px;">Securely login to your account</p>

            <?php if ($error): ?>
                <div class='error'><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <label style="text-align: left;">Username</label>
                <input type="text" name="username" placeholder="Enter username" required>

                <label style="text-align: left;">Password</label>
                <input type="password" name="password" placeholder="Enter password" required>

                <button type="submit">Sign In</button>
            </form>

            <div style="margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php">Register Now</a>
            </div>
        </div>
    </div>
</body>

</html>