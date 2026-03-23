<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        if (loginUser($email, $password)) {
            // Update last login
            updateLastLogin($_SESSION['user_id']);
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<?php include 'header.php'; ?>

<style>
    .auth-wrapper {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .auth-container {
        background: white;
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 400px;
    }
    
    .form-title {
        text-align: center;
        margin-bottom: 2rem;
        color: #333;
        font-size: 2rem;
        font-weight: 600;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #555;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.8rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .btn-submit {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        margin: 1rem 0;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .forgot-link {
        text-align: right;
        margin-bottom: 0.5rem;
    }
    
    .forgot-link a {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .register-link {
        text-align: center;
        margin: 1rem 0 0 0;
        color: #666;
        font-size: 0.95rem;
    }
    
    .register-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    
    .demo-credentials {
        margin-top: 2rem;
        padding: 1rem;
        background: #f8f9ff;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #666;
        border: 1px dashed #667eea;
    }
    
    .demo-credentials p {
        margin: 0.3rem 0;
    }
    
    .demo-credentials strong {
        color: #667eea;
    }
</style>

<div class="auth-wrapper">
    <div class="auth-container">
        
        <h2 class="form-title">Welcome Back</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control"
                       placeholder="Enter your email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control"
                       placeholder="Enter your password"
                       required>
            </div>
            
            <div class="forgot-link">
                <a href="#">Forgot Password?</a>
            </div>
            
            <button type="submit" class="btn-submit">
                Login
            </button>
            
            <p class="register-link">
                Don't have an account? <a href="register.php">Sign up here</a>
            </p>
        </form>
        
        <!-- Demo Credentials (remove in production) -->
        <div class="demo-credentials">
            <p><strong>Demo Credentials:</strong></p>
            <p>👤 Student: john@email.com / password123</p>
            <p>👨‍🏫 Instructor: mike@email.com / password123</p>
            <p>👑 Admin: admin@email.com / admin123</p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>