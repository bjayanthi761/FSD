<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = mysqli_real_escape_string($conn, $_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Get course details
$course_sql = "SELECT * FROM courses WHERE id = ? AND is_published = TRUE";
$stmt = mysqli_prepare($conn, $course_sql);
mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);
$course_result = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($course_result);

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Check if already enrolled
$check_sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check_result) > 0) {
    header("Location: course-details.php?id=" . $course_id . "&already_enrolled=1");
    exit();
}

// If course is free, enroll directly
if ($course['price'] == 0) {
    $enroll_sql = "INSERT INTO enrollments (user_id, course_id, payment_status, payment_amount) 
                   VALUES (?, ?, 'completed', 0)";
    $stmt = mysqli_prepare($conn, $enroll_sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
    mysqli_stmt_execute($stmt);
    header("Location: course-details.php?id=" . $course_id . "&enrolled=1");
    exit();
}

// Handle payment form submission
$payment_error = '';
$payment_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    $card_number = $_POST['card_number'];
    $card_name = $_POST['card_name'];
    $expiry = $_POST['expiry'];
    $cvv = $_POST['cvv'];
    
    // Simple validation (in real app, integrate with payment gateway)
    if (empty($card_number) || empty($card_name) || empty($expiry) || empty($cvv)) {
        $payment_error = "Please fill in all payment details";
    } elseif (strlen($card_number) < 15) {
        $payment_error = "Invalid card number";
    } elseif (strlen($cvv) < 3) {
        $payment_error = "Invalid CVV";
    } else {
        // Generate transaction ID
        $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
        
        // Process payment (simulated)
        $payment_success = true;
        
        // Enroll user with payment completed
        $enroll_sql = "INSERT INTO enrollments (user_id, course_id, payment_status, payment_amount, payment_method, transaction_id) 
                       VALUES (?, ?, 'completed', ?, 'credit_card', ?)";
        $stmt = mysqli_prepare($conn, $enroll_sql);
        mysqli_stmt_bind_param($stmt, "iidss", $user_id, $course_id, $course['price'], $transaction_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: course-details.php?id=" . $course_id . "&payment_success=1");
            exit();
        } else {
            $payment_error = "Payment failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Learning Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .payment-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: #2563eb;
            text-decoration: none;
        }
        
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .order-summary {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .order-summary h3 {
            font-size: 1.2rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .course-info {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .course-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .price-details {
            display: flex;
            justify-content: space-between;
            margin: 0.5rem 0;
        }
        
        .total-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2563eb;
            border-top: 2px solid #e2e8f0;
            padding-top: 1rem;
            margin-top: 0.5rem;
        }
        
        .payment-form h3 {
            font-size: 1.2rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        
        .card-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-pay {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(to right, #2563eb, #7c3aed);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(37,99,235,0.4);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .secure-badge {
            text-align: center;
            margin-top: 1.5rem;
            color: #94a3b8;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .payment-grid {
                grid-template-columns: 1fr;
            }
            .payment-container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <a href="course-details.php?id=<?php echo $course_id; ?>" class="back-link">← Back to Course</a>
        
        <div class="payment-card">
            <div class="payment-grid">
                <!-- Order Summary -->
                <div class="order-summary">
                    <h3>📋 Order Summary</h3>
                    <div class="course-info">
                        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                        <div class="price-details">
                            <span>Course Price</span>
                            <span>$<?php echo number_format($course['price'], 2); ?></span>
                        </div>
                    </div>
                    <div class="price-details total-price">
                        <span>Total</span>
                        <span>$<?php echo number_format($course['price'], 2); ?></span>
                    </div>
                </div>
                
                <!-- Payment Form -->
                <div class="payment-form">
                    <h3>💳 Payment Details</h3>
                    
                    <?php if ($payment_error): ?>
                        <div class="alert alert-error"><?php echo $payment_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" name="card_number" class="form-control" 
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Cardholder Name</label>
                            <input type="text" name="card_name" class="form-control" 
                                   placeholder="John Doe" required>
                        </div>
                        
                        <div class="card-row">
                            <div class="form-group">
                                <label>Expiry Date</label>
                                <input type="text" name="expiry" class="form-control" 
                                       placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="password" name="cvv" class="form-control" 
                                       placeholder="123" maxlength="4" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="process_payment" class="btn-pay">
                            Pay $<?php echo number_format($course['price'], 2); ?>
                        </button>
                    </form>
                    
                    <div class="secure-badge">
                        <i class="fas fa-lock"></i> Secure payment powered by Stripe
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Format card number with spaces
        document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            if (value.length > 0) {
                value = value.match(/.{1,4}/g).join(' ');
                e.target.value = value;
            }
        });
        
        // Format expiry date
        document.querySelector('input[name="expiry"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\//g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
                e.target.value = value;
            }
        });
    </script>
</body>
</html>
