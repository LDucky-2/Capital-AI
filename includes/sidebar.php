
<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Define menu items
// Format: URL => [Label, Icon, Permissions Array (null = public)]
$menu_items_config = [
    'Audits.php' => [
        'label' => 'Audit Reports', 
        'icon' => 'fas fa-file-contract', 
        'permissions' => ['Administrator', 'Auditor', 'Management', 'Fraud Detector']
    ],
    'Stocks.php' => [
        'label' => 'Browse Stocks', // Renamed for Investor
        'icon' => 'fas fa-chart-line', 
        'permissions' => null // All can see market
    ],
    'My_Stocks.php' => [
        'label' => 'My Stocks', 
        'icon' => 'fas fa-wallet', 
        'permissions' => ['Investor', 'Company']
    ],
    'Stock_Transactions_and_Trades.php' => [
        'label' => 'Transaction History', // Renamed for Investor
        'icon' => 'fas fa-exchange-alt', 
        'permissions' => ['Investor', 'Administrator', 'Management', 'Auditor']
    ],
    'Frauds.php' => [
        'label' => 'Fraud Alerts', 
        'icon' => 'fas fa-exclamation-triangle', 
        'permissions' => ['Fraud Detector', 'Administrator', 'Database Manager', 'Auditor']
    ],
    'Predictions.php' => [
        'label' => 'Stock Prediction', 
        'icon' => 'fas fa-crystal-ball', 
        'permissions' => ['Administrator', 'Management', 'Company']
    ],
    'Company_Database.php' => [
        'label' => 'Company Database', 
        'icon' => 'fas fa-building', 
        'permissions' => ['Administrator', 'Database Manager', 'Management', 'Auditor']
    ],
    'Employee_Database.php' => [
        'label' => 'Employee Database', 
        'icon' => 'fas fa-users', 
        'permissions' => ['Administrator', 'Management']
    ],
    'Investor_Database.php' => [
        'label' => 'Investor Database', 
        'icon' => 'fas fa-user-tie', 
        'permissions' => ['Administrator', 'Database Manager', 'Management']
    ],
    'Institution_Database.php' => [
        'label' => 'Institutions', 
        'icon' => 'fas fa-landmark', 
        'permissions' => ['Administrator', 'Database Manager', 'Management']
    ],
    'Logs.php' => [
        'label' => 'System Logs', 
        'icon' => 'fas fa-history', 
        'permissions' => ['Administrator', 'Database Manager']
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
