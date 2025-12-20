<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && hasRole(['Administrator', 'Fraud Detector'])) {
    $alert_id = intval($_POST['alert_id']);
    $timestamp = $_POST['timestamp'];
    $notes = $conn->real_escape_string($_POST['notes']);

    $sql = "UPDATE Fraud_Action_T SET Notes = '$notes' WHERE Alert_ID = $alert_id AND Timestamp = '$timestamp'";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: Frauds.php?msg=note_updated");
    } else {
        echo "Error updating note: " . $conn->error;
    }
} else {
    header("Location: Frauds.php");
}
?>
