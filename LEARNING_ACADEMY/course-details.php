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
        background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
        padding: 3rem 0;
    }
    
    .course-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .back-link {
        color: #2563eb;
        text-decoration: none;
        margin-bottom: 2rem;
        display: inline-block;
        padding: 0.5rem 1rem;
        background: white;
        border-radius: 8px;
        transition: all 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .back-link:hover {
        background: #f8fafc;
        transform: translateX(-4px);
    }
    
    .course-header {
        background: white;
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }
    
    .course-title {
        font-size: 2.5rem;
        color: #1f2937;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .course-meta {
        display: flex;
        gap: 2rem;
        margin-bottom: 1.5rem;
        color: #6b7280;
        flex-wrap: wrap;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .course-description {
        color: #4b5563;
        line-height: 1.8;
        margin-bottom: 2rem;
    }
    
    .enroll-section {
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 16px;
        margin-top: 1.5rem;
        border: 1px solid #e5e7eb;
    }
    
    .badge-free {
        background: #d1fae5;
        color: #10b981;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .badge-paid {
        background: #fef3c7;
        color: #f59e0b;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .price-tag {
        font-size: 2rem;
        font-weight: 700;
        color: #2563eb;
    }
    
    .progress-container {
        margin: 1rem 0;
    }
    
    .progress-bar {
        width: 100%;
        height: 10px;
        background: #e5e7eb;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #2563eb 0%, #7c3aed 100%);
        border-radius: 5px;
        transition: width 0.3s;
    }
    
    .btn {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(37,99,235,0.2);
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-outline {
        background: white;
        color: #2563eb;
        border: 2px solid #2563eb;
    }
    
    .course-content-section {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }
    
    .section-title {
        font-size: 1.5rem;
        color: #1f2937;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    
    .section-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    
    .section-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.3s;
    }
    
    .section-header:hover {
        background: #f1f5f9;
    }
    
    .section-header h3 {
        margin: 0;
        color: #1f2937;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .section-header span {
        transition: transform 0.3s;
    }
    
    .lesson-list {
        padding: 0.5rem 1.5rem;
        display: none;
        border-top: 1px solid #e5e7eb;
    }
    
    .lesson-list.show {
        display: block;
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
        color: #4b5563;
    }
    
    .lesson-duration {
        color: #9ca3af;
        font-size: 0.85rem;
    }
    
    .preview-badge {
        background: #e0e7ff;
        color: #2563eb;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 500;
    }
    
    .locked-icon {
        color: #9ca3af;
        font-size: 0.8rem;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #10b981;
        border: 1px solid #a7f3d0;
    }
    
    @media (max-width: 768px) {
        .course-title {
            font-size: 1.8rem;
        }
        .course-header {
            padding: 1.5rem;
        }
        .course-meta {
            gap: 1rem;
        }
    }
</style>

<div class="course-detail-wrapper">
    <div class="course-container">
        
        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-link">← Back to Courses</a>
        
        <!-- Success Message -->
        <?php if (isset($_GET['payment_success'])): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                ✅ Payment successful! You are now enrolled in this course.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['enrolled'])): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                ✅ You have successfully enrolled in this course!
            </div>
        <?php endif; ?>
        
        <!-- Course Header -->
        <div class="course-header">
            <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
            
            <div class="course-meta">
                <span class="meta-item">👨‍🏫 Instructor: <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                <span class="meta-item">📚 Category: <?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?></span>
                <span class="meta-item">📊 Level: <?php echo ucfirst($course['level'] ?? 'beginner'); ?></span>
                <span class="meta-item">⏱️ Duration: <?php echo $course['duration_hours'] ?? 0; ?> hours</span>
            </div>
            
            <p class="course-description"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            
            <!-- Enrollment Section -->
            <div class="enroll-section">
                <?php if ($is_enrolled): ?>
                    <!-- Already Enrolled -->
                    <div>
                        <h3 style="color: #1f2937; margin-bottom: 1rem;">📖 Your Progress</h3>
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
                    
                <?php elseif ($course['price'] > 0): ?>
                    <!-- Paid Course - Show Payment Option -->
                    <div>
                        <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            <span class="badge-paid">💰 Paid Course</span>
                            <span class="price-tag">$<?php echo number_format($course['price'], 2); ?></span>
                        </div>
                        <p style="margin-bottom: 1rem; color: #6b7280;">
                            💳 Complete payment to unlock full course access including all videos, resources, and certificate.
                        </p>
                        <a href="payment.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">
                            Enroll Now - $<?php echo number_format($course['price'], 2); ?>
                        </a>
                    </div>
                    
                <?php elseif (isLoggedIn()): ?>
                    <!-- Free Course - Direct Enroll -->
                    <div>
                        <div style="margin-bottom: 1rem;">
                            <span class="badge-free">🎁 Free Course</span>
                        </div>
                        <p style="margin-bottom: 1rem; color: #6b7280;">
                            Start learning today at no cost! Get full access to all lessons.
                        </p>
                        <form method="POST" action="enroll.php">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" class="btn btn-primary">
                                Enroll Now - Free
                            </button>
                        </form>
                    </div>
                    
                <?php else: ?>
                    <!-- Not Logged In -->
                    <div>
                        <div style="margin-bottom: 1rem;">
                            <?php if ($course['price'] > 0): ?>
                                <span class="badge-paid">💰 Paid Course - $<?php echo number_format($course['price'], 2); ?></span>
                            <?php else: ?>
                                <span class="badge-free">🎁 Free Course</span>
                            <?php endif; ?>
                        </div>
                        <p style="margin-bottom: 1rem; color: #6b7280;">
                            Please login to enroll in this course.
                        </p>
                        <a href="login.php" class="btn btn-primary">
                            Login to Enroll
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Course Content -->
        <div class="course-content-section">
            <h2 class="section-title">📚 Course Content</h2>
            
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
                                    <?php if ($is_enrolled || $lesson['is_free_preview']): ?>
                                        <span>📺</span>
                                    <?php else: ?>
                                        <span class="locked-icon">🔒</span>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                                    <?php if($lesson['is_free_preview'] && !$is_enrolled && $course['price'] > 0): ?>
                                        <span class="preview-badge">Preview</span>
                                    <?php endif; ?>
                                    <?php if(!$is_enrolled && $course['price'] > 0 && !$lesson['is_free_preview']): ?>
                                        <span class="preview-badge" style="background:#fef3c7; color:#f59e0b;">Locked</span>
                                    <?php endif; ?>
                                </div>
                                <div class="lesson-duration">
                                    <?php echo $lesson['duration_minutes']; ?> min
                                </div>
                            </div>
                            <?php endwhile; ?>
                            
                            <?php if(!$is_enrolled && $course['price'] > 0): ?>
                                <div style="text-align: center; padding: 1rem; color: #6b7280; font-size: 0.85rem;">
                                    <i class="fas fa-lock"></i> Enroll to unlock all lessons
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #6b7280; text-align: center; padding: 2rem;">No content available for this course yet.</p>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<script>
function toggleSection(header) {
    // Get the lesson list (next sibling after header)
    const lessonList = header.nextElementSibling;
    // Get the arrow span (last child of header)
    const arrow = header.querySelector('span:last-child');
    
    // Toggle the lesson list visibility
    if (lessonList.style.display === 'none' || lessonList.style.display === '') {
        lessonList.style.display = 'block';
        arrow.textContent = '▲';
        arrow.style.transform = 'rotate(0deg)';
    } else {
        lessonList.style.display = 'none';
        arrow.textContent = '▼';
        arrow.style.transform = 'rotate(0deg)';
    }
}

// Ensure all sections start closed
document.addEventListener('DOMContentLoaded', function() {
    const allLessonLists = document.querySelectorAll('.lesson-list');
    const allArrows = document.querySelectorAll('.section-header span:last-child');
    
    for(let i = 0; i < allLessonLists.length; i++) {
        allLessonLists[i].style.display = 'none';
        allArrows[i].textContent = '▼';
    }
});
</script>

<?php include 'footer.php'; ?>
