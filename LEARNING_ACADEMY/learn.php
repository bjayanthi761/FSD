<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['course_id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = mysqli_real_escape_string($conn, $_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Check if enrolled
$check_sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $course_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) == 0) {
    header("Location: courses.php");
    exit();
}

// Get course details
$course_sql = "SELECT * FROM courses WHERE id = ?";
$course_stmt = mysqli_prepare($conn, $course_sql);
mysqli_stmt_bind_param($course_stmt, "i", $course_id);
mysqli_stmt_execute($course_stmt);
$course_result = mysqli_stmt_get_result($course_stmt);
$course = mysqli_fetch_assoc($course_result);

// Get first lesson
$first_lesson_sql = "SELECT cl.* FROM course_lessons cl
                     JOIN course_sections cs ON cl.section_id = cs.id
                     WHERE cs.course_id = ?
                     ORDER BY cs.section_order, cl.lesson_order
                     LIMIT 1";
$first_lesson_stmt = mysqli_prepare($conn, $first_lesson_sql);
mysqli_stmt_bind_param($first_lesson_stmt, "i", $course_id);
mysqli_stmt_execute($first_lesson_stmt);
$first_lesson_result = mysqli_stmt_get_result($first_lesson_stmt);
$first_lesson = mysqli_fetch_assoc($first_lesson_result);
?>

<?php include 'header.php'; ?>

<div style="min-height: calc(100vh - 200px); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 0;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        
        <div style="background: white; border-radius: 20px; padding: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center;">
            
            <h1 style="font-size: 2rem; color: #333; margin-bottom: 1rem;">Welcome to <?php echo htmlspecialchars($course['title']); ?></h1>
            
            <p style="color: #666; margin-bottom: 2rem;">Your learning journey begins now!</p>
            
            <?php if($first_lesson): ?>
                <div style="margin: 2rem 0;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Start with the first lesson:</h3>
                    <p style="color: #667eea; font-size: 1.2rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($first_lesson['title']); ?></p>
                    <a href="lesson.php?lesson_id=<?php echo $first_lesson['id']; ?>" 
                       class="btn" 
                       style="display: inline-block; padding: 1rem 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        Start Learning Now →
                    </a>
                </div>
            <?php else: ?>
                <p style="color: #999;">No lessons available yet.</p>
            <?php endif; ?>
            
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #f0f0f0;">
                <a href="course-details.php?id=<?php echo $course_id; ?>" style="color: #667eea; text-decoration: none;">← Back to Course Details</a>
            </div>
            
        </div>
        
    </div>
</div>

<?php include 'footer.php'; ?>