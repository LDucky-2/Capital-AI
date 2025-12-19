<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

$user_id = $_SESSION['User_ID'];

// Fetch Institution Details
$sql = "
    SELECT 
        u.Name, 
        u.Email_Address, 
        u.Status, 
        i.Institution_Type, 
        i.License_Number
    FROM Institution_T i
    JOIN User_T u ON i.Institution_User_ID = u.User_ID
    WHERE i.Institution_User_ID = '$user_id'
";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

if (!$data) {
    // If not an institution or not found
    $error = "Institution profile not found.";
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>My Institution Profile</h2>
</div>

<div class="content-section">
    <?php if (isset($error)) { echo "<div class='alert' style='color:red;'>$error</div>"; } else { ?>
    <div class="card" style="max-width: 600px;">
        <h3><?php echo htmlspecialchars($data['Name']); ?></h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($data['Email_Address']); ?></p>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($data['Institution_Type']); ?></p>
        <p><strong>License Number:</strong> <?php echo htmlspecialchars($data['License_Number']); ?></p>
        <p><strong>Status:</strong> <span class="badge"><?php echo htmlspecialchars($data['Status']); ?></span></p>
    </div>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
