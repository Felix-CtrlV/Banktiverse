<?php
include 'Database/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['bank_user_id'])) {

    $user_id = $_SESSION['bank_user_id'];
    $amount = (float) $_POST['amount'];
    $return_url = $_POST['return_url'];
    $merchant_name = isset($_SESSION['merchant_name']) ? $_SESSION['merchant_name'] : 'Unknown Merchant';
    $stmt = mysqli_prepare($conn, "SELECT balance FROM bank_users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $user['balance'] >= $amount) {
        $update = mysqli_prepare($conn, "UPDATE bank_users SET balance = balance - ? WHERE user_id = ?");
        mysqli_stmt_bind_param($update, "di", $amount, $user_id);

        if (mysqli_stmt_execute($update)) {
            $log = mysqli_prepare($conn, "INSERT INTO transactions (user_id, amount, type, description, status) VALUES (?, ?, 'payment', ?, 'success')");
            mysqli_stmt_bind_param($log, "ids", $user_id, $amount, $merchant_name);
            mysqli_stmt_execute($log);
            $txn_id = mysqli_insert_id($conn);

            $_SESSION['balance'] -= $amount;

            header("Location: " . $return_url . "&status=success&txn_id=" . $txn_id);
            exit();
        }
    } else {
        header("Location: " . $return_url . "&status=failed&reason=insufficient_funds");
        exit();
    }
}   
header("Location: index.php");
?>