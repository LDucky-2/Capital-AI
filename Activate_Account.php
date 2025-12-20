<?php
include 'Database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']);
    $password = $_POST['password'];
    $email = $conn->real_escape_string($_POST['email']);

    // Check credentials and status
    $sql = "SELECT * FROM User_T WHERE User_ID = '$user_id' AND Password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['Status'] == 'Active') {
            $message = "<div class='alert alert-warning'>This account is already active. Please <a href='Log_in.php'>Log In</a>.</div>";
        } elseif ($user['Status'] == 'Inactive') {
            // Check if email is already taken
            $check_email = $conn->query("SELECT User_ID FROM User_T WHERE Email_Address = '$email'");
            if ($check_email->num_rows > 0) {
                 $message = "<div class='alert alert-danger'>This email address is already in use.</div>";
            } else {
                // Activate account
                $update = "UPDATE User_T SET Email_Address = '$email', Status = 'Active' WHERE User_ID = '$user_id'";
                if ($conn->query($update)) {
                    header("Location: Log_in.php?activation=success");
                    exit();
                } else {
                    $message = "<div class='alert alert-danger'>Error activating account: " . $conn->error . "</div>";
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>Account is " . $user['Status'] . " and cannot be activated manually.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid User ID or Password. Please contact your Administrator.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Staff Account - Capital AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <img src="images/Skyrim_Logo.png" alt="Logo" class="brand-logo" style="height: 60px; margin-bottom: 10px;">
                <h2 class="auth-title">Staff Activation</h2>
                <p style="color: var(--text-muted);">Claim your pre-registered account</p>
            </div>
            
            <?php echo $message; ?>

            <form action="" method="post">
                <div class="input-group">
                    <label for="user_id">Assigned User ID</label>
                    <input type="number" id="user_id" name="user_id" required placeholder="Enter your ID">
                </div>

                <div class="input-group">
                    <label for="password">Temporary Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <div class="input-group">
                    <label for="email">Work Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="you@company.com">
                </div>
                
                <button type="submit" class="btn-primary">Activate Account</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <span style="color: var(--text-muted);">Back to</span>
                <a href="Log_in.php" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Log In</a>
            </div>
        </div>
    </div>
</body>
</html>
