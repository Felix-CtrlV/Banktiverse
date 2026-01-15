<?php
include 'Database/dbconfig.php';
if (!isset($_SESSION['bank_user_id'])) header("Location: index.php");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['bank_user_id'];
    
    if (!empty($_POST['fullname'])) {
        $name = $_POST['fullname'];
        $stmt = mysqli_prepare($conn, "UPDATE bank_users SET full_name=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "si", $name, $user_id);
        mysqli_stmt_execute($stmt);
        $_SESSION['full_name'] = $name;
        $msg = "<div class='success'>Profile updated.</div>";
    }

    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE bank_users SET password=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "si", $pass, $user_id);
        mysqli_stmt_execute($stmt);
        $msg = "<div class='success'>Password updated.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Profile | Banktiverse</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="brand">Banktiverse</div>
            <div class="nav-links">
                <a href="dashboard.php">Overview</a>
                <a href="transfer.php">Transfer Money</a>
                <a href="deposit.php">Deposit Funds</a>
                <a href="profile.php" class="active">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h2>Edit Profile</h2>
            <div class="card" style="max-width:500px">
                <?= $msg ?>
                <form method="POST">
                    <label>Full Name</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($_SESSION['full_name']) ?>">
                    
                    <label>New Password <small>(leave blank to keep current)</small></label>
                    <input type="password" name="password" placeholder="New Password">
                    
                    <button type="submit" class="btn">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>