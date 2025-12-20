<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $user_id = $_SESSION['User_ID']; // Current User (The Buyer in this form logic)

    if ($action == 'create_trade' && hasRole('Institution')) {
        $seller_id = intval($_POST['seller_id']);
        $asset_type = $conn->real_escape_string($_POST['asset_type']);
        $details = $conn->real_escape_string($_POST['trade_details']);

        if (!$seller_id || empty($asset_type) || empty($details)) {
            header("Location: My_Institution.php?msg=error");
            exit();
        }

        // 1. Create Log
        $activity_type = "Institutional Trade";
        $activity_data = "Institution $user_id bought $asset_type from Institution $seller_id";
        $timestamp = date('Y-m-d H:i:s');
        
        $log_sql = "INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) VALUES ('$timestamp', '$activity_type', '$activity_data')";
        $conn->query($log_sql);
        $log_id = $conn->insert_id;

        // 2. Insert Trade
        $sql = "INSERT INTO Trade_T (Buyer_Institution_ID, Seller_Institution_ID, Asset_Type, Trade_Details, Log_ID) 
                VALUES ('$user_id', '$seller_id', '$asset_type', '$details', '$log_id')";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: My_Institution.php?msg=success");
        } else {
            echo "Error: " . $conn->error;
        }
    }
} else {
    header("Location: My_Institution.php");
}
?>
