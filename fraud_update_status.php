<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Check if the user has the required permission
if (!hasRole(['Fraud Detector', 'Administrator'])) {
    header("Location: Frauds.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alert_id']) && isset($_POST['status'])) {
    $alert_id = intval($_POST['alert_id']);
    $new_status = intval($_POST['status']); // 1 for True, 0 for False
    $performer_name = $_SESSION['Name'];
    $performer_role = $_SESSION['Permission'];

    // Update the fraud alert status
    $stmt = $conn->prepare("UPDATE Fraud_Alert_T SET Is_False_Positive = ? WHERE Alert_ID = ?");
    $stmt->bind_param("ii", $new_status, $alert_id);

    if ($stmt->execute()) {
        // Log the activity
        $status_text = $new_status ? 'False Positive' : 'Legitimate';
        $activity_type = "Fraud Alert Status Update";
        $activity_data = "$performer_role ($performer_name) marked Alert ID $alert_id as $status_text";
        $timestamp = date('Y-m-d H:i:s');

        $log_stmt = $conn->prepare("INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) VALUES (?, ?, ?)");
        $log_stmt->bind_param("sss", $timestamp, $activity_type, $activity_data);
        $log_stmt->execute();

        header("Location: Frauds.php?msg=status_updated");
    } else {
        header("Location: Frauds.php?error=update_failed");
    }
    exit();
} else {
    header("Location: Frauds.php");
    exit();
}
?>
