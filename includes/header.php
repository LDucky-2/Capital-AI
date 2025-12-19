<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capital AI - Financial Dashboard</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<div class="app-container">
    <!-- Top Header -->
    <header class="top-brand-header">
        <div class="logo-wrap">
            <img src="images/Skyrim_Logo.png" alt="Capital AI Logo" class="brand-logo"> 
            <div class="brand-text-wrap">
                <span class="brand-name">Capital AI</span>
                <span class="brand-subtitle">Financial Institutions Management</span>
            </div>
        </div>
        <div class="user-profile">
            <?php if(isset($_SESSION['Name'])): ?>
                <span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['Name']); ?></span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="main-layout">
