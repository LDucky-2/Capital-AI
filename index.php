<?php
session_start();

// If the user is logged in, redirect to the main dashboard (Audits.php)
if (isset($_SESSION['User_ID'])) {
    header("Location: Audits.php");
    exit();
}

// If not logged in, redirect to the Login page
header("Location: Log_in.php");
exit();
?>
