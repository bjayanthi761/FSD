<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Academy Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Your existing styles... */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
        }
        
        .nav-brand a {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            text-decoration: none;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-menu a:hover {
            color: #667eea;
        }
        
        .btn-primary {
            background: #667eea;
            color: white !important;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            transition: background 0.3s !important;
        }
        
        .btn-primary:hover {
            background: #764ba2;
        }
        
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="dashboard.php">🎓 Learning Academy</a>
            </div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn-primary">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main>
