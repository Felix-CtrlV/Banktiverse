<?php
include 'Database/dbconfig.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitize Inputs
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 2. Generate Banking Data Automatically
    // Generate a random 10-digit account number
    $account_number = rand(1000000000, 9999999999); 
    
    // Generate a random 16-digit card number (starting with 4 for Visa-style)
    $card_number = "4" . rand(100000000000000, 999999999999999); 
    
    // Generate random 3-digit CVV
    $card_cvv = rand(100, 999); 
    
    // Set expiry date to 5 years from now (Format: MM/YY)
    $card_expiry = date("m/y", strtotime("+5 years")); 
    
    // Set initial balance
    $balance = 0.00;

    // 3. Check if username exists
    $check = mysqli_query($conn, "SELECT user_id FROM bank_users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "<p class='error'>Username already taken.</p>";
    } else {
        // 4. Update SQL to insert ALL fields
        $sql = "INSERT INTO bank_users 
                (username, password, full_name, account_number, card_number, card_cvv, card_expiry, balance) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        // Bind parameters: s = string, d = double/decimal
        // 8 variables total
        mysqli_stmt_bind_param($stmt, "sssssssd", 
            $username, 
            $password, 
            $fullname, 
            $account_number, 
            $card_number, 
            $card_cvv, 
            $card_expiry, 
            $balance
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $msg = "<p class='success'>Account created! Your Account #: <b>$account_number</b>. <a href='index.php'>Login Now</a></p>";
        } else {
            // Added error logging to see exactly why it fails if it does
            $msg = "<p class='error'>Error creating account: " . mysqli_error($conn) . "</p>";
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
            
            <?php if ($msg != ''): ?>
                <div><?= $msg ?></div>
            <?php endif; ?>

            <form method="POST">
                <label style="text-align: left;">Full Name</label>
                <input type="text" name="fullname" placeholder="Enter Full Name" required>

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