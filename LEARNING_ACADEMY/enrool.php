<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $user_id = $_SESSION['user_id'];
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    
    // Get course details to check if it's free or paid
    $course_sql = "SELECT id, title, price FROM courses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $course_sql);
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    mysqli_stmt_execute($stmt);
    $course_result = mysqli_stmt_get_result($stmt);
    $course = mysqli_fetch_assoc($course_result);
    
    if (!$course) {
        $_SESSION['error'] = "Course not found.";
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
        $_SESSION['error'] = "You are already enrolled in this course.";
        header("Location: course-details.php?id=" . $course_id);
        exit();
    }
    
    // Handle enrollment based on price
    if ($course['price'] > 0) {
        // Paid course - redirect to payment page
        header("Location: payment.php?course_id=" . $course_id);
        exit();
    } else {
        // Free course - direct enrollment
        $enroll_sql = "INSERT INTO enrollments (user_id, course_id, payment_status, payment_amount, enrolled_at) 
                       VALUES (?, ?, 'completed', 0, NOW())";
        $stmt = mysqli_prepare($conn, $enroll_sql);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Successfully enrolled in " . htmlspecialchars($course['title']) . "!";
            header("Location: course-details.php?id=" . $course_id . "&enrolled=1");
            exit();
        } else {
            $_SESSION['error'] = "Enrollment failed. Please try again.";
            header("Location: course-details.php?id=" . $course_id);
            exit();
        }
    }
} else {
    // No course ID provided
    header("Location: courses.php");
    exit();
}
?>
