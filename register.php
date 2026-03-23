<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        if (registerUser($username, $email, $password, $user_type)) {
            $success = "Registration successful! Please login.";
        } else {
            $error = "Registration failed. Email or username may already exist.";
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
        max-width: 450px;
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
        margin: 1.5rem 0 1rem 0;
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
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .radio-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 0.3rem;
    }
    
    .radio-label {
        display: flex;
        align-items: center;
        padding: 0.8rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.95rem;
    }
    
    .radio-label.selected {
        border-color: #667eea;
        background: #f8f9ff;
    }
    
    .radio-label input[type="radio"] {
        margin-right: 8px;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    
    .password-hint {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: #999;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .login-link {
        text-align: center;
        margin: 1rem 0 0 0;
        color: #666;
        font-size: 0.95rem;
    }
    
    .login-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    
    .login-link a:hover {
        color: #764ba2;
    }
    
    .terms-text {
        text-align: center;
        margin-top: 2rem;
        font-size: 0.8rem;
        color: #999;
        border-top: 1px solid #f0f0f0;
        padding-top: 1.5rem;
    }
    
    .terms-text a {
        color: #667eea;
        text-decoration: none;
    }
</style>

<div class="auth-wrapper">
    <div class="auth-container">
        
        <h2 class="form-title">Create Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <!-- Username Field -->
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-control"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Enter your username"
                       required>
            </div>
            
            <!-- Email Field -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="Enter your email"
                       required>
            </div>
            
            <!-- Password Field -->
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control"
                       placeholder="Enter your password"
                       required>
                <div class="password-hint">
                    <span>🔒</span> Minimum 6 characters
                </div>
            </div>
            
            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       class="form-control"
                       placeholder="Confirm your password"
                       required>
            </div>
            
            <!-- User Type Selection -->
            <div class="form-group">
                <label>I want to</label>
                <div class="radio-group">
                    <label class="radio-label <?php echo (!isset($_POST['user_type']) || $_POST['user_type'] == 'student') ? 'selected' : ''; ?>">
                        <input type="radio" 
                               name="user_type" 
                               value="student" 
                               <?php echo (!isset($_POST['user_type']) || $_POST['user_type'] == 'student') ? 'checked' : ''; ?>>
                        🎓 Learn as Student
                    </label>
                    
                    <label class="radio-label <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'instructor') ? 'selected' : ''; ?>">
                        <input type="radio" 
                               name="user_type" 
                               value="instructor" 
                               <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'instructor') ? 'checked' : ''; ?>>
                        👨‍🏫 Teach as Instructor
                    </label>
                </div>
            </div>
            
            <!-- Register Button -->
            <button type="submit" name="register" class="btn-submit">
                Register
            </button>
            
            <!-- Login Link -->
            <p class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
        
        <!-- Terms -->
        <div class="terms-text">
            By registering, you agree to our 
            <a href="#">Terms of Service</a> and 
            <a href="#">Privacy Policy</a>
        </div>
    </div>
</div>

<script>
// Password match validation
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    
    if (confirm) {
        confirm.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#28a745';
            }
        });
    }
    
    if (password) {
        password.addEventListener('input', function() {
            if (confirm && confirm.value !== '') {
                if (this.value !== confirm.value) {
                    confirm.style.borderColor = '#dc3545';
                } else {
                    confirm.style.borderColor = '#28a745';
                }
            }
            
            // Password strength indicator
            const strength = document.createElement('div');
            let strengthText = '';
            let strengthColor = '';
            
            if (this.value.length >= 6) {
                if (this.value.match(/[a-z]/) && this.value.match(/[0-9]/)) {
                    strengthText = 'Strong';
                    strengthColor = '#28a745';
                } else if (this.value.match(/[a-z]/) || this.value.match(/[0-9]/)) {
                    strengthText = 'Medium';
                    strengthColor = '#ffc107';
                } else {
                    strengthText = 'Weak';
                    strengthColor = '#dc3545';
                }
            }
        });
    }
    
    // Radio label styling
    document.querySelectorAll('.radio-label').forEach(label => {
        label.addEventListener('click', function() {
            document.querySelectorAll('.radio-label').forEach(l => l.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});
</script>

<?php include 'footer.php'; ?>