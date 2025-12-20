<?php
include 'Database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $check_email = $conn->query("SELECT Email_Address FROM User_T WHERE Email_Address = '$email'");
    if($check_email->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Email already exists!</div>";
    } else {
        $password = $_POST['password']; 
        $role = $conn->real_escape_string($_POST['role']);
        $status = "Active";

        // Start Transaction
        $conn->begin_transaction();

        try {
            // 1. Insert into User_T
            $sql = "INSERT INTO User_T (Name, Password, Email_Address, Status, Permission) VALUES ('$name', '$password', '$email', '$status', '$role')";
            if (!$conn->query($sql)) {
                throw new Exception("Error creating user: " . $conn->error);
            }
            $user_id = $conn->insert_id;

            // 2. Insert into Specific Role Table
            if ($role == 'Company') {
                $reg_num = $conn->real_escape_string($_POST['registration_number']);
                $sector = $conn->real_escape_string($_POST['sector']);
                $sql_role = "INSERT INTO Company_T (Company_User_ID, Registration_Number, Sector) VALUES ('$user_id', '$reg_num', '$sector')";
                if (!$conn->query($sql_role)) throw new Exception("Error creating company profile: " . $conn->error);
            } 
            elseif ($role == 'Institution') {
                $inst_type = $conn->real_escape_string($_POST['institution_type']);
                $license = $conn->real_escape_string($_POST['license_number']);
                $sql_role = "INSERT INTO Institution_T (Institution_User_ID, Institution_Type, License_Number) VALUES ('$user_id', '$inst_type', '$license')";
                if (!$conn->query($sql_role)) throw new Exception("Error creating institution profile: " . $conn->error);
            } 
            elseif ($role == 'Auditor') {
                $firm = $conn->real_escape_string($_POST['auditing_firm']);
                $sql_role = "INSERT INTO Auditor_T (Auditor_User_ID, Auditing_Firm) VALUES ('$user_id', '$firm')";
                if (!$conn->query($sql_role)) throw new Exception("Error creating auditor profile: " . $conn->error);
            } 
            elseif ($role == 'Investor') {
                $sql_role = "INSERT INTO Investor_T (Investor_User_ID) VALUES ('$user_id')";
                if (!$conn->query($sql_role)) throw new Exception("Error creating investor profile: " . $conn->error);
            }

            // Commit Transaction
            $conn->commit();
            header("Location: Log_in.php?signup=success");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Capital AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles.css">
    <style>
        .role-fields { display: none; margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 15px; }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <img src="images/Skyrim_Logo.png" alt="Logo" class="brand-logo" style="height: 60px; margin-bottom: 10px;">
                <h2 class="auth-title">Create Account</h2>
                <p style="color: var(--text-muted);">Join Capital AI today</p>
            </div>
            
            <?php echo $message; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required placeholder="John Doe">
                </div>

                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="john@example.com">
                </div>
                
                <div class="input-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required onchange="toggleFields()">
                        <option value="Investor">Investor</option>
                        <option value="Company">Company</option>
                        <option value="Institution">Institution</option>
                        <option value="Auditor">Auditor</option> 
                    </select>
                </div>

                <!-- DYNAMIC FIELDS -->
                <div id="Company_Fields" class="role-fields">
                    <div class="input-group">
                        <label>Registration Number</label>
                        <input type="text" name="registration_number" placeholder="e.g. REG-556788-BD">
                    </div>
                    <div class="input-group">
                        <label>Sector</label>
                        <input type="text" name="sector" placeholder="e.g. Telecommunications">
                    </div>
                </div>

                <div id="Institution_Fields" class="role-fields">
                    <div class="input-group">
                        <label>Institution Type</label>
                        <input type="text" name="institution_type" placeholder="e.g. Brokerage">
                    </div>
                    <div class="input-group">
                        <label>License Number</label>
                        <input type="text" name="license_number" placeholder="e.g. LIC-BD-44500">
                    </div>
                </div>

                <div id="Auditor_Fields" class="role-fields">
                    <div class="input-group">
                        <label>Auditing Firm</label>
                        <input type="text" name="auditing_firm" placeholder="e.g. Khan Audit & Co.">
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn-primary">Create Account</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <span style="color: var(--text-muted);">Already have an account?</span>
                <a href="Log_in.php" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Log In</a>
            </div>
        </div>
    </div>

    <script>
        function toggleFields() {
            var role = document.getElementById("role").value;
            // Hide all first
            var allFields = document.querySelectorAll('.role-fields');
            allFields.forEach(function(el) {
                el.style.display = 'none';
                // Remove required attribute from hidden inputs to avoid validation error
                var inputs = el.querySelectorAll('input');
                inputs.forEach(function(input) { input.required = false; });
            });

            // Show selected
            var selectedDiv = document.getElementById(role + "_Fields");
            if (selectedDiv) {
                selectedDiv.style.display = 'block';
                // Add required attribute
                var inputs = selectedDiv.querySelectorAll('input');
                inputs.forEach(function(input) { input.required = true; });
            }
        }
        // Run on load
        toggleFields();
    </script>
</body>
</html>
