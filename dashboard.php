<?php
session_start();
include 'Database/dbconfig.php';
if (!isset($_SESSION['bank_user_id'])) header("Location: index.php");

$user_id = $_SESSION['bank_user_id'];

// Refresh User Data
$q = mysqli_query($conn, "SELECT * FROM bank_users WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($q);
$_SESSION['balance'] = $user['balance'];

// Get Transactions
$txns = mysqli_query($conn, "SELECT * FROM transactions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Format Card
$card_display = chunk_split($user['card_number'] ?? '0000000000000000', 4, ' ');
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard | Banktiverse</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="brand">Banktiverse</div>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Overview</a>
                <a href="transfer.php">Transfer Money</a> <a href="deposit.php">Deposit Funds</a>
                <a href="profile.php">Settings</a>
                <a href="logout.php" style="margin-top: auto; color: var(--danger);">Logout</a>
            </div>
        </div>

        <div class="main-content">
            <div class="header">
                <div>
                    <h2 style="margin:0;">Hello, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?> 👋</h2>
                    <small style="color: var(--text-muted);">Welcome back to your financial hub.</small>
                </div>
                <div class="badge"><?= date('l, F j') ?></div>
            </div>

            <div class="grid-2">
                <div>
                    <div class="card">
                        <small style="color: var(--text-muted);">Total Balance</small>
                        <div class="text-huge">$<?= number_format($user['balance'], 2) ?></div>
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <a href="deposit.php" class="btn">Add Money</a>
                            <a href="transfer.php" class="btn btn-outline">Send Money</a>
                        </div>
                    </div>

                    <div class="card" style="padding: 1.5rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <span style="color:var(--text-muted)">Account Number</span>
                            <span style="font-family:monospace; font-size:1.1rem;"><?= $user['account_number'] ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted)">Routing Number</span>
                            <span style="font-family:monospace; font-size:1.1rem;">064000021</span>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="credit-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div class="chip"></div>
                            <span style="font-style:italic; font-weight:800; opacity:0.8;">VISA</span>
                        </div>
                        <div class="card-number"><?= $card_display ?></div>
                        <div class="card-details">
                            <div>
                                <div style="font-size:0.6rem; opacity:0.7;">Card Holder</div>
                                <div><?= htmlspecialchars($user['full_name']) ?></div>
                            </div>
                            <div>
                                <div style="font-size:0.6rem; opacity:0.7;">Expires</div>
                                <div><?= $user['card_expiry'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:center; margin-top:10px; color:var(--text-muted); font-size:0.8rem;">
                        CVV: <?= $user['card_cvv'] ?> • Virtual Debit
                    </div>
                </div>
            </div>

            <h3 style="margin-top: 3rem;">Recent Transactions</h3>
            <div class="card" style="padding: 0 2rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($txns)): ?>
                        <tr>
                            <td style="font-weight: 500;"><?= htmlspecialchars($row['description']) ?></td>
                            <td style="color: var(--text-muted);"><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                            <td><span class="badge badge-success"><?= $row['status'] ?></span></td>
                            <td style="text-align:right;" class="<?= $row['type']=='deposit' ? 'amount-plus' : 'amount-minus' ?>">
                                <?= $row['type']=='deposit' ? '+' : '-' ?> $<?= number_format($row['amount'], 2) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if(mysqli_num_rows($txns) == 0) echo "<p style='text-align:center; padding:20px; color:var(--text-muted);'>No transactions found.</p>"; ?>
            </div>
        </div>
    </div>
</body>
</html>