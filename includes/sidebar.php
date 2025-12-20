
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role_for_nav = $_SESSION['Permission'] ?? null;

// Define menu items
// Format: URL => [Label, Icon, Permissions Array (null = public)]
$menu_items_config = [
    'Audits.php' => [
        'label' => 'Audit Reports', 
        'icon' => 'fas fa-file-contract', 
        'permissions' => ['Auditor']
    ],
    'Stocks.php' => [
        'label' => 'Market', 
        'icon' => 'fas fa-chart-line', 
        'permissions' => ['Administrator', 'Management', 'Investor']
    ],
    'My_Stocks.php' => [
        'label' => ($role_for_nav === 'Management' ? 'Company Investments' : 'My Stocks'), 
        'icon' => 'fas fa-wallet', 
        'permissions' => ['Investor', 'Management']
    ],
    'Stock_Transactions_and_Trades.php' => [
        'label' => 'Transaction History', 
        'icon' => 'fas fa-exchange-alt', 
        'permissions' => ['Investor', 'Institution', 'Management']
    ],
    'Frauds.php' => [
        'label' => 'Fraud Alerts', 
        'icon' => 'fas fa-exclamation-triangle', 
        'permissions' => ['Fraud Detector', 'Administrator']
    ],
    'Predictions.php' => [
        'label' => 'Stock Prediction', 
        'icon' => 'fas fa-brain', 
        'permissions' => ['Administrator', 'Management']
    ],
    'Company_Database.php' => [
        'label' => 'Company Database', 
        'icon' => 'fas fa-building', 
        'permissions' => ['Administrator', 'Fraud Detector']
    ],
    'Auditor_Database.php' => [
        'label' => 'Auditor Database', 
        'icon' => 'fas fa-user-check', 
        'permissions' => ['Administrator']
    ],
    'Employee_Database.php' => [
        'label' => 'Employee Database', 
        'icon' => 'fas fa-users', 
        'permissions' => ['Administrator']
    ],
    'Investor_Database.php' => [
        'label' => 'Investor Database', 
        'icon' => 'fas fa-user-tie', 
        'permissions' => ['Administrator', 'Fraud Detector']
    ],
    'Institution_Database.php' => [
        'label' => 'Institutions', 
        'icon' => 'fas fa-landmark', 
        'permissions' => ['Administrator', 'Institution', 'Fraud Detector']
    ],
    'Logs.php' => [
        'label' => 'System Logs', 
        'icon' => 'fas fa-history', 
        'permissions' => ['Administrator', 'Fraud Detector']
    ],
    'My_Company.php' => [
        'label' => 'My Company', 
        'icon' => 'fas fa-briefcase', 
        'permissions' => ['Company']
    ],
    'My_Institution.php' => [
        'label' => 'My Institution', 
        'icon' => 'fas fa-university', 
        'permissions' => ['Institution']
    ]
];
?>

<aside class="sidebar">
    <nav class="nav-menu">
        <ul>
            <?php foreach ($menu_items_config as $url => $item): ?>
                <?php 
                // Render if no permissions required OR user has role
                if ($item['permissions'] === null || hasRole($item['permissions'])): 
                ?>
                <li>
                    <a href="<?php echo $url; ?>" class="<?php echo ($current_page == $url) ? 'active' : ''; ?>">
                        <i class="<?php echo $item['icon']; ?> nav-icon"></i>
                        <span class="nav-text"><?php echo $item['label']; ?></span>
                    </a>
                </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>

<main class="content-area">
