<?php
session_start();
include 'Database.php';

$message = "";
if (isset($_GET['signup']) && $_GET['signup'] == 'success') {
    $message = "<div class='alert alert-success'>Account created successfully! Please log in.</div>";
}
if (isset($_GET['activation']) && $_GET['activation'] == 'success') {
    $message = "<div class='alert alert-success'>Account activated successfully! You can now log in.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a login attempt
    if (isset($_POST['login_action'])) {
        $username = $conn->real_escape_string($_POST['username']); 
        // Treating 'username' as 'Name' or 'Email' based on previous file, but let's check schema. Schema has 'Name' and 'Email_Address'.
        // Standard is Email. Let's allow Email.
        $password = $_POST['password'];

        // Query by Email or Name
        $sql = "SELECT User_ID, Name, Password, Permission, Status FROM User_T WHERE Email_Address = '$username' OR Name = '$username'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Verify password (plain text as per earlier decision to match schema varchar(30))
            if ($password === $row['Password']) {
                if ($row['Status'] === 'Active') {
                    // session variables set from the query SELECT User_ID, Name, Password, Permission,
                     $_SESSION['User_ID'] = $row['User_ID'];
                     $_SESSION['Name'] = $row['Name'];
                     $_SESSION['Permission'] = $row['Permission'];
                     // redirect to their set dashboard
                     if ($row['Permission'] === 'Company') {
                        header("Location: My_Company.php");
                     } elseif ($row['Permission'] === 'Investor') {
                        header("Location: My_Stocks.php");
                     } elseif ($row['Permission'] === 'Institution') {
                        header("Location: My_Institution.php");
                     } elseif ($row['Permission'] === 'Fraud Detector') {
                        header("Location: Frauds.php");
                     } elseif ($row['Permission'] === 'Administrator') {
                        header("Location: Employee_Database.php");
                     } elseif ($row['Permission'] === 'Management') {
                        header("Location: My_Stocks.php");
                     } elseif ($row['Permission'] === 'Auditor') {
                        header("Location: Audits.php");
                     }
                     exit();
                } else {
                    $message = "<div class='alert alert-danger'>Account is " . $row['Status'] . ".</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Invalid password.</div>";
            }
        } else {
             $message = "<div class='alert alert-danger'>User not found.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Capital AI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <img src="images/Skyrim_Logo.png" alt="Logo" class="brand-logo" style="height: 60px; margin-bottom: 10px;">
                <h2 class="auth-title">Welcome Back</h2>
                <p style="color: var(--text-muted);">Enter your credentials to access the portal</p>
            </div>
            
            <?php echo $message; ?>
            <!--<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" is used to 
            prevent cross-site scripting attacks and it just sends the form to the same page -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="login_action" value="1">
                <div class="input-group">
                    <label for="username">Email or Name</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your email">
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="login-button">Log In</button>
                <a href="Sign_up.php" class="signup-button" style="display: block; text-align: center; text-decoration: none; line-height: normal; margin-bottom: 10px;">Create new account</a>
                <div style="text-align: center;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Internal Staff?</span>
                    <a href="Activate_Account.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">Activate Account</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

