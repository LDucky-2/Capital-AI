<?php
session_start();

// If the user is logged in, redirect to the main dashboard (Audits.php)
if (isset($_SESSION['User_ID'])) {
    $role = $_SESSION['Permission'];
    
    if ($role == 'Investor') {
        header("Location: My_Stocks.php");
    } elseif ($role == 'Company') {
        header("Location: My_Company.php");
    } elseif ($role == 'Institution') {
        header("Location: My_Institution.php");
    } elseif ($role == 'Fraud Detector') {
        header("Location: Frauds.php");
    } elseif ($role == 'Auditor') {
        header("Location: Audits.php");
    } elseif ($role == 'Management') {
        header("Location: My_Stocks.php");
    } else {
        header("Location: Audits.php"); // Default for admins
    }
    exit();
}

// If not logged in, redirect to the Login page
header("Location: Log_in.php");
exit();
?>
