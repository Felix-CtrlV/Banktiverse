<?php
include 'Database/dbconfig.php';
if (!isset($_SESSION['bank_user_id']))
    header("Location: index.php");

$msg = '';
$user_id = $_SESSION['bank_user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_name = $_POST['recipient'];
    $amount = (float) $_POST['amount'];

    if ($amount <= 0) {
        $msg = "<div class='error'>Invalid amount.</div>";
    } else {
        // Check Balance
        $sender_q = mysqli_query($conn, "SELECT balance FROM bank_users WHERE user_id = $user_id");
        $sender = mysqli_fetch_assoc($sender_q);

        if ($sender['balance'] >= $amount) {
            // Check Recipient
            $stmt = mysqli_prepare($conn, "SELECT user_id FROM bank_users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $recipient_name);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            if ($recipient = mysqli_fetch_assoc($res)) {
                $recv_id = $recipient['user_id'];
                if ($recv_id == $user_id) {
                    $msg = "<div class='error'>Cannot transfer to self.</div>";
                } else {
                    // Transaction
                    mysqli_begin_transaction($conn);
                    try {
                        // Deduct Sender
                        mysqli_query($conn, "UPDATE bank_users SET balance = balance - $amount WHERE user_id = $user_id");
                        mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) VALUES ($user_id, $amount, 'payment', 'Transfer to $recipient_name', 'success')");

                        // Add Recipient
                        mysqli_query($conn, "UPDATE bank_users SET balance = balance + $amount WHERE user_id = $recv_id");
                        mysqli_query($conn, "INSERT INTO transactions (user_id, amount, type, description, status) VALUES ($recv_id, $amount, 'deposit', 'Received from User #$user_id', 'success')");

                        mysqli_commit($conn);
                        $msg = "<div class='success'>Transfer successful!</div>";
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                        $msg = "<div class='error'>Transfer failed.</div>";
                    }
                }
            } else {
                $msg = "<div class='error'>User not found.</div>";
            }
        } else {
            $msg = "<div class='error'>Insufficient funds.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Transfer | Banktiverse</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="brand">Banktiverse</div>
            <div class="nav-links">
                <a href="dashboard.php">Overview</a>
                <a href="transfer.php" class="active">Transfer Money</a>
                <a href="deposit.php">Deposit Funds</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h2>Send Money</h2>
            <div class="card" style="max-width: 500px;">
                <?= $msg ?>
                <form method="POST">
                    <label>Recipient Username</label>
                    <input type="text" name="recipient" placeholder="e.g. jason_bourne" required>
                    <label>Amount ($)</label>
                    <input type="number" step="0.01" name="amount" placeholder="0.00" required>
                    <button type="submit" class="btn">Send Transfer</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>