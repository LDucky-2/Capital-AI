<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && hasRole(['Administrator', 'Fraud Detector'])) {
    $target_user_id = intval($_POST['user_id']);
    $new_status = $_POST['status']; // 'Active' or 'Frozen'
    $redirect = $_POST['redirect'];
    $reason = isset($_POST['reason']) ? $conn->real_escape_string($_POST['reason']) : "No reason provided.";
    $performer_role = $_SESSION['Permission'];
    $performer_name = $_SESSION['Name'];

    // Fetch target user's role to validate permissions
    $check_user = $conn->query("SELECT Permission FROM User_T WHERE User_ID = '$target_user_id'");
    if ($check_user && $check_user->num_rows > 0) {
        $target_data = $check_user->fetch_assoc();
        $target_role = $target_data['Permission'];

        // RULE 1: Nobody can freeze an Administrator
        if ($target_role === 'Administrator') {
            header("Location: $redirect?error=cannot_freeze_admin");
            exit();
        }

        // RULE 2: Fraud Detectors can ONLY freeze External Users (Investor, Institution, Company)
        $external_roles = ['Investor', 'Institution', 'Company'];
        if ($performer_role === 'Fraud Detector' && !in_array($target_role, $external_roles)) {
            header("Location: $redirect?error=unauthorized_target");
            exit();
        }

        // Update status in User_T
        $sql = "UPDATE User_T SET Status = '$new_status' WHERE User_ID = '$target_user_id'";
        
        if ($conn->query($sql) === TRUE) {
            // Log the action
            $activity_type = ($performer_role === 'Fraud Detector') ? "Fraud Action Taken" : "Account Status Update";
            $activity_data = "$performer_role ($performer_name) changed User ID $target_user_id ($target_role) status to $new_status. Reason: $reason";
            $timestamp = date('Y-m-d H:i:s');
            
            $log_sql = "INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) 
                        VALUES ('$timestamp', '$activity_type', '$activity_data')";
            $conn->query($log_sql);
            $log_id = $conn->insert_id;

            // If it's a Fraud Detector taking action, ensure it's recorded in Fraud_Action_T
            if ($performer_role === 'Fraud Detector') {
                $alert_id = isset($_POST['alert_id']) ? intval($_POST['alert_id']) : 0;
                
                // If no alert_id, try to find the latest alert linked to this user's logs
                if ($alert_id === 0) {
                    $find_alert = "
                        SELECT a.Alert_ID 
                        FROM Fraud_Alert_T a
                        JOIN Log_T l ON a.Log_ID = l.Log_ID
                        WHERE l.Activity_Data_Detail LIKE '%User $target_user_id %'
                        ORDER BY a.Alert_ID DESC LIMIT 1
                    ";
                    $alert_res = $conn->query($find_alert);
                    if ($alert_res && $alert_res->num_rows > 0) {
                        $alert_id = $alert_res->fetch_assoc()['Alert_ID'];
                    } else {
                        // Create a "Manual Oversight" Alert so we have a record in Fraud_Action_T
                        $pattern = "Manual Oversight Investigation";
                        $conn->query("INSERT INTO Fraud_Alert_T (Targeted_User_ID, Pattern_Detected, Risk_Score, Log_ID) 
                                     VALUES ('$target_user_id', '$pattern', 5.0, '$log_id')");
                        $alert_id = $conn->insert_id;
                    }
                }

                if ($alert_id > 0) {
                    $action_type = "Account $new_status";
                    $conn->query("INSERT INTO Fraud_Action_T (Alert_ID, Timestamp, Action_taken, Log_ID, Notes) 
                                 VALUES ('$alert_id', '$timestamp', '$action_type', '$log_id', '$reason')");
                }
            }
            
            header("Location: $redirect?msg=status_updated");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        header("Location: $redirect?error=user_not_found");
        exit();
    }
} else {
    header("Location: index.php");
}
?>
