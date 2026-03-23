<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Academy Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <style>
    /* Add these styles to your existing styles */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
    
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
    }
    
    main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    /* Your existing navbar styles */
    .navbar {
        background: white;
        padding: 1rem 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .navbar .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .nav-menu {
        list-style: none;
        display: flex;
        gap: 2rem;
        margin: 0;
        padding: 0;
    }
    
    .nav-menu a {
        text-decoration: none;
        color: #333;
        font-weight: 500;
    }
    
    .btn-primary {
        background: #667eea;
        color: white !important;
        padding: 0.5rem 1.5rem;
        border-radius: 5px;
    }
    
    .nav-brand a {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
        text-decoration: none;
    }
</style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php" style="font-size: 1.5rem; font-weight: bold; color: #667eea; text-decoration: none;">🎓 Learning Academy</a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Courses</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn-primary">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main>