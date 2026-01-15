<?php
include 'Database/dbconfig.php';
if (!isset($_SESSION['bank_user_id'])) header("Location: index.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = (float) $_POST['amount'];
    $user_id = $_SESSION['bank_user_id'];

    if ($amount > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE bank_users SET balance = balance + ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "di", $amount, $user_id);
        mysqli_stmt_execute($stmt);
        
        $log = mysqli_prepare($conn, "INSERT INTO transactions (user_id, amount, type, description, status) VALUES (?, ?, 'deposit', 'ATM Deposit', 'success')");
        mysqli_stmt_bind_param($log, "id", $user_id, $amount);
        mysqli_stmt_execute($log);

        echo "<script>alert('Funds deposited successfully!'); window.location='dashboard.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Deposit | Banktiverse</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="brand">Banktiverse</div>
            <div class="nav-links">
                <a href="dashboard.php">Overview</a>
                <a href="transfer.php">Transfer Money</a>
                <a href="deposit.php" class="active">Deposit Funds</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h2>Deposit Funds</h2>
            <div class="card" style="max-width: 500px;">
                <p style="color:var(--text-muted); margin-bottom:20px;">Add funds instantly to your account.</p>
                <form method="POST">
                    <label>Amount to Deposit ($)</label>
                    <input type="number" step="0.01" name="amount" placeholder="100.00" required>
                    <button type="submit" class="btn">Confirm Deposit</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>