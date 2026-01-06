<?php
session_start();
// understood
// If the user is logged in, redirect to their set dashboard
if (isset($_SESSION['User_ID'])) {
    $role = $_SESSION['Permission'];
    // determine where the user should be taken
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
    } elseif ($role == 'Administrator') {
        header("Location: Employee_Database.php");
    }
    exit();
}

// If not logged in, redirect to the Login page
header("Location: Log_in.php");
exit();
?>
