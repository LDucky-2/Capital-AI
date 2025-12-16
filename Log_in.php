<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="Styles.css"> 
</head>
<body class="login-page">
    <header class="top-brand-header">
        <div class="logo-wrap">
            <img src="images/Skyrim_Logo.png" alt="Brand Logo" class="brand-logo"> 
            <div class="brand-text-wrap">
                <span class="brand-name">Financial Institutions Management</span>
            </div>
        </div>
    </header>    
    <div class="login-container">
        <h2>Capital AI</h2>
        <form action="/login" method="post">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-button">Log In</button>
            <button type="submit" class="signup-button">Sign up</button>

        </form>
    </div>

</body>
</html>