<?php
session_start();

$current = $_SESSION['logout_from'] ?? 'normal';

session_unset();
session_destroy();

session_start();
if (isset($_GET['current']) && $_GET['current'] === 'payment') {
    $_SESSION['logout_from'] = 'payment';
    $_SESSION['amount'] = $_GET['amount'] ?? 0;
    $_SESSION['return_url'] = $_GET['return_url'] ?? '';
    $_SESSION['merchant_name'] = $_GET['merchant'] ?? '';
}

header("Location: index.php");
exit();
