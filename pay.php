<?php
include 'Database/dbconfig.php';


$amount = isset($_GET['amount']) ? (float) $_GET['amount'] : 0;
$merchant_name = isset($_GET['merchant']) ? $_GET['merchant'] : 'Unknown Merchant';
$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : '';

if (!isset($_SESSION['bank_user_id']) && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = mysqli_prepare($conn, "SELECT user_id, password, balance, full_name FROM bank_users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['bank_user_id'] = $row['user_id'];
            $_SESSION['balance'] = $row['balance'];
            $_SESSION['full_name'] = $row['full_name'];
        } else { $error = "Invalid credentials"; }
    } else { $error = "User not found"; }
}
?>
<!DOCTYPE html>
<html>
<head><title>Secure Checkout</title><link rel="stylesheet" href="style.css"></head>
<body class="auth-container">
    <div class="card" style="width: 380px; text-align: center; border-top: 4px solid var(--accent);">
        <div style="margin-bottom: 25px;">
            <div class="brand" style="font-size: 1.2rem; margin-bottom: 5px;">Banktiverse Secure</div>
            <small style="color: var(--text-muted);">Paying to: <strong><?= htmlspecialchars($merchant_name) ?></strong></small>
        </div>

        <div style="background: var(--bg-dark); padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #334155;">
            <div style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase;">Total Amount</div>
            <span style="font-size: 2rem; font-weight: 800; color:white;">$<?= number_format($amount, 2) ?></span>
        </div>

        <?php if (!isset($_SESSION['bank_user_id'])): ?>
            <p style="margin-bottom:15px;">Login to approve payment</p>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login & Pay</button>
            </form>
        <?php else: ?>
            <div style="text-align: left; background: rgba(59, 130, 246, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="color: var(--accent); font-weight:bold; font-size:0.9rem;">Logged in as</div>
                <div><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:0.85rem; color:var(--text-muted); margin-top:5px;">Available: $<?= number_format($_SESSION['balance'], 2) ?></div>
            </div>

            <?php if ($_SESSION['balance'] >= $amount): ?>
                <form action="process_transaction.php" method="POST">
                    <input type="hidden" name="amount" value="<?= $amount ?>">
                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
                    <button type="submit" class="btn" style="background: var(--success);">Confirm Payment</button>
                </form>
            <?php else: ?>
                <div class="error">Insufficient Funds</div>
                <a href="deposit.php" target="_blank" class="btn btn-outline">Deposit Funds</a>
            <?php endif; ?>

            <a href="logout.php?current=payment&amount=<?= $amount ?>&return_url=<?= htmlspecialchars($return_url)?>&merchant=<?= htmlspecialchars($merchant_name) ?>" style="display:block; margin-top:15px; font-size:0.8rem; color:var(--text-muted);">Switch Account</a>
        <?php endif; ?>

        <?php if($return_url): ?>
            <br>
            <a href="<?= htmlspecialchars($return_url) ?>?status=cancelled" style="color: var(--text-muted); font-size: 0.8rem;">Cancel Transaction</a>
        <?php endif; ?>
    </div>
</body>
</html>