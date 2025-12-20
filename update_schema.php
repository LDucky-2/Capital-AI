<?php
include 'Database.php';

// Drop Audit_Logs_T request
$conn->query("DROP TABLE IF EXISTS Audit_Logs_T");

// Add Status column to Stock_T if it doesn't exist
$sql = "SHOW COLUMNS FROM Stock_T LIKE 'Status'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE Stock_T ADD COLUMN Status VARCHAR(10) DEFAULT 'Open'";
    if ($conn->query($sql) === TRUE) {
        echo "Column Status added to Stock_T successfully.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column Status already exists.";
}
?>
