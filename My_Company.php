<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Logic: If the logged-in user IS a company, show their company details.
// If they are not (e.g. Admin), maybe show "You are not associated with a company" or show a demo.
// Assuming "My Company" means the company the user belongs to.
// The file previously had code for "Share Holders" but was using $Management variable which was undefined in that file (copy-paste error?).
// I will implement a "My Company" details view.

$user_id = $_SESSION['User_ID'];
$company_data = null;
$message = "";

// Check if user is in Company_T
$sql = "SELECT * FROM Company_T WHERE Company_User_ID = '$user_id'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $company_data = $result->fetch_assoc();
} else {
    // Maybe they are an employee OF a company? The schema doesn't explicitly link generic users to companies except via Company_T (which seems to be the company account itself).
    $message = "No company profile found for this account.";
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>My Company</h2>
</div>

<div class="content-section">
    <?php if ($company_data): ?>
        <div class="card" style="background: var(--bg-card); padding: 2rem; border-radius: 12px; border: 1px solid var(--border-color); max-width: 600px;">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">Company Profile</h3>
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                <div style="color: var(--text-muted);">Registration Number:</div>
                <div><?php echo htmlspecialchars($company_data['Registration_Number']); ?></div>
                
                <div style="color: var(--text-muted);">Sector:</div>
                <div><?php echo htmlspecialchars($company_data['Sector']); ?></div>
                
                <div style="color: var(--text-muted);">Company ID:</div>
                <div><?php echo htmlspecialchars($company_data['Company_User_ID']); ?></div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <?php echo $message; ?>
            <p>This page is intended for Company accounts.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>