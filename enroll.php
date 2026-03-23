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
    
    // Check if already enrolled
    $check_sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Enroll user
        $enroll_sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $enroll_sql);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Successfully enrolled in course!";
        } else {
            $_SESSION['error'] = "Enrollment failed. Please try again.";
        }
    } else {
        $_SESSION['error'] = "You are already enrolled in this course.";
    }
}

header("Location: dashboard.php");
exit();
?>