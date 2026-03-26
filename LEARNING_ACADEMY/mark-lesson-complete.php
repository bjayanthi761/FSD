<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lesson_id'])) {
    $user_id = $_SESSION['user_id'];
    $lesson_id = mysqli_real_escape_string($conn, $_POST['lesson_id']);
    
    // Get course_id from lesson
    $course_sql = "SELECT cs.course_id FROM course_lessons cl 
                   JOIN course_sections cs ON cl.section_id = cs.id 
                   WHERE cl.id = ?";
    $stmt = mysqli_prepare($conn, $course_sql);
    mysqli_stmt_bind_param($stmt, "i", $lesson_id);
    mysqli_stmt_execute($stmt);
    $course_result = mysqli_stmt_get_result($stmt);
    $course = mysqli_fetch_assoc($course_result);
    $course_id = $course['course_id'];
    
    // Mark lesson as completed
    $progress_sql = "INSERT INTO lesson_progress (user_id, lesson_id, is_completed) 
                     VALUES (?, ?, 1) 
                     ON DUPLICATE KEY UPDATE is_completed = 1, watched_at = NOW()";
    $progress_stmt = mysqli_prepare($conn, $progress_sql);
    mysqli_stmt_bind_param($progress_stmt, "ii", $user_id, $lesson_id);
    mysqli_stmt_execute($progress_stmt);
    
    // Update course progress
    $total_sql = "SELECT COUNT(*) as total FROM course_lessons cl
                  JOIN course_sections cs ON cl.section_id = cs.id
                  WHERE cs.course_id = ?";
    $total_stmt = mysqli_prepare($conn, $total_sql);
    mysqli_stmt_bind_param($total_stmt, "i", $course_id);
    mysqli_stmt_execute($total_stmt);
    $total_result = mysqli_stmt_get_result($total_stmt);
    $total = mysqli_fetch_assoc($total_result);
    
    $completed_sql = "SELECT COUNT(*) as completed FROM lesson_progress lp
                      JOIN course_lessons cl ON lp.lesson_id = cl.id
                      JOIN course_sections cs ON cl.section_id = cs.id
                      WHERE lp.user_id = ? AND cs.course_id = ? AND lp.is_completed = 1";
    $completed_stmt = mysqli_prepare($conn, $completed_sql);
    mysqli_stmt_bind_param($completed_stmt, "ii", $user_id, $course_id);
    mysqli_stmt_execute($completed_stmt);
    $completed_result = mysqli_stmt_get_result($completed_stmt);
    $completed = mysqli_fetch_assoc($completed_result);
    
    $progress_percentage = ($total['total'] > 0) ? ($completed['completed'] / $total['total']) * 100 : 0;
    
    $update_sql = "UPDATE enrollments SET progress_percentage = ? WHERE user_id = ? AND course_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "dii", $progress_percentage, $user_id, $course_id);
    mysqli_stmt_execute($update_stmt);
    
    echo "success";
}
?>
