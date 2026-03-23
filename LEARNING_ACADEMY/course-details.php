<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Get course ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get course details
$sql = "SELECT c.*, u.username as instructor_name, u.full_name as instructor_full_name,
        cat.name as category_name
        FROM courses c
        JOIN users u ON c.instructor_id = u.id
        LEFT JOIN categories cat ON c.category_id = cat.id
        WHERE c.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($result);

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Check if user is enrolled
$is_enrolled = false;
$enrollment = null;
if (isLoggedIn()) {
    $check_sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $_SESSION['user_id'], $course_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $enrollment = mysqli_fetch_assoc($check_result);
    $is_enrolled = !empty($enrollment);
}

// Get course sections and lessons
$sections_sql = "SELECT * FROM course_sections WHERE course_id = ? ORDER BY section_order";
$sections_stmt = mysqli_prepare($conn, $sections_sql);
mysqli_stmt_bind_param($sections_stmt, "i", $course_id);
mysqli_stmt_execute($sections_stmt);
$sections_result = mysqli_stmt_get_result($sections_stmt);
?>

<?php include 'header.php'; ?>

<style>
    .course-detail-wrapper {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 0;
    }
    
    .course-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .back-link {
        color: white;
        text-decoration: none;
        margin-bottom: 2rem;
        display: inline-block;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.2);
        border-radius: 5px;
        transition: background 0.3s;
    }
    
    .back-link:hover {
        background: rgba(255,255,255,0.3);
    }
    
    .course-header {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .course-title {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .course-meta {
        display: flex;
        gap: 2rem;
        margin-bottom: 1.5rem;
        color: #666;
        flex-wrap: wrap;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .course-description {
        color: #666;
        line-height: 1.8;
        margin-bottom: 2rem;
    }
    
    .enroll-section {
        background: #f8f9ff;
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 1.5rem;
    }
    
    .progress-container {
        margin: 1rem 0;
    }
    
    .progress-bar {
        width: 100%;
        height: 10px;
        background: #f0f0f0;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 5px;
        transition: width 0.3s;
    }
    
    .btn {
        padding: 1rem 2rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-success {
        background: #28a745;
        color: white;
    }
    
    .course-content-section {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .section-title {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    
    .section-card {
        border: 2px solid #f0f0f0;
        border-radius: 10px;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    
    .section-header {
        background: #f8f9ff;
        padding: 1rem 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .section-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.2rem;
    }
    
    .lesson-list {
        padding: 1rem 1.5rem;
        display: none;
    }
    
    .lesson-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .lesson-item:last-child {
        border-bottom: none;
    }
    
    .lesson-title {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        color: #666;
    }
    
    .lesson-duration {
        color: #999;
        font-size: 0.9rem;
    }
    
    .preview-badge {
        background: #667eea;
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 3px;
        font-size: 0.7rem;
    }
    
    .free-badge {
        background: #28a745;
        color: white;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
</style>

<div class="course-detail-wrapper">
    <div class="course-container">
        
        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-link">← Back to Courses</a>
        
        <!-- Course Header -->
        <div class="course-header">
            <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
            
            <div class="course-meta">
                <span class="meta-item">👨‍🏫 Instructor: <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                <span class="meta-item">📚 Category: <?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?></span>
                <span class="meta-item">📊 Level: <?php echo ucfirst($course['level'] ?? 'beginner'); ?></span>
                <span class="meta-item">⏱️ Duration: <?php echo $course['duration_hours'] ?? 0; ?> hours</span>
                <span class="meta-item">🎓 Free Course</span>
            </div>
            
            <p class="course-description"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            
            <!-- Enrollment Section (NO PRICE) -->
            <div class="enroll-section">
                <?php if ($is_enrolled): ?>
                    <div>
                        <h3 style="color: #333; margin-bottom: 1rem;">Your Progress</h3>
                        <div class="progress-container">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Course Progress</span>
                                <span><?php echo $enrollment['progress_percentage'] ?? 0; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $enrollment['progress_percentage'] ?? 0; ?>%;"></div>
                            </div>
                        </div>
                        <a href="learn.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary" style="margin-top: 1rem;">
                            Continue Learning →
                        </a>
                    </div>
                <?php elseif (isLoggedIn()): ?>
                    <div>
                        <div style="margin-bottom: 1rem;">
                            <span class="free-badge">FREE COURSE</span>
                            <p style="margin-top: 0.8rem; color: #666;">Start learning today at no cost!</p>
                        </div>
                        <form method="POST" action="enroll.php">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" class="btn btn-primary">
                                Enroll Now - Free
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div>
                        <div style="margin-bottom: 1rem;">
                            <span class="free-badge">FREE COURSE</span>
                            <p style="margin-top: 0.8rem; color: #666;">Login to enroll and start learning!</p>
                        </div>
                        <a href="login.php" class="btn btn-primary">
                            Login to Enroll
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Course Content -->
        <div class="course-content-section">
            <h2 class="section-title">Course Content</h2>
            
            <?php if (mysqli_num_rows($sections_result) > 0): ?>
                <?php while($section = mysqli_fetch_assoc($sections_result)): ?>
                    <div class="section-card">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><?php echo htmlspecialchars($section['title']); ?></h3>
                            <span>▼</span>
                        </div>
                        <div class="lesson-list">
                            <?php
                            $lessons_sql = "SELECT * FROM course_lessons WHERE section_id = ? ORDER BY lesson_order";
                            $lessons_stmt = mysqli_prepare($conn, $lessons_sql);
                            mysqli_stmt_bind_param($lessons_stmt, "i", $section['id']);
                            mysqli_stmt_execute($lessons_stmt);
                            $lessons_result = mysqli_stmt_get_result($lessons_stmt);
                            
                            while($lesson = mysqli_fetch_assoc($lessons_result)):
                            ?>
                            <div class="lesson-item">
                                <div class="lesson-title">
                                    <span>📺</span>
                                    <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                                    <?php if($lesson['is_free_preview'] && !$is_enrolled): ?>
                                        <span class="preview-badge">Preview</span>
                                    <?php endif; ?>
                                </div>
                                <div class="lesson-duration">
                                    <?php echo $lesson['duration_minutes']; ?> min
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #666; text-align: center; padding: 2rem;">No content available for this course yet.</p>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<script>
function toggleSection(header) {
    const lessonList = header.nextElementSibling;
    const arrow = header.querySelector('span:last-child');
    
    if (lessonList.style.display === 'none' || lessonList.style.display === '') {
        lessonList.style.display = 'block';
        arrow.textContent = '▲';
    } else {
        lessonList.style.display = 'none';
        arrow.textContent = '▼';
    }
}
</script>

<?php include 'footer.php'; ?>