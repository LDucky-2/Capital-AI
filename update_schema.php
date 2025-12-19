<?php
include 'Database.php';

// SQL to add column if it doesn't exist
$sql = "
    ALTER TABLE Audit_Report_T 
    ADD COLUMN Company_User_ID INT(9),
    ADD CONSTRAINT fk_audit_company 
    FOREIGN KEY (Company_User_ID) REFERENCES Company_T(Company_User_ID);
";

if ($conn->query($sql) === TRUE) {
    echo "Table Audit_Report_T altered successfully.";
} else {
    echo "Error modifying table: " . $conn->error;
}

$conn->close();
?>
