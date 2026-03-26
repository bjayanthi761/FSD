<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['lesson_id']) || empty($_GET['lesson_id'])) {
    header("Location: dashboard.php");
    exit();
}

$lesson_id = mysqli_real_escape_string($conn, $_GET['lesson_id']);
$user_id = $_SESSION['user_id'];

// Get lesson details with course info
$lesson_sql = "SELECT cl.*, cs.course_id, c.title as course_title, c.price,
               (SELECT COUNT(*) FROM course_lessons WHERE section_id = cs.id) as total_lessons
               FROM course_lessons cl
               JOIN course_sections cs ON cl.section_id = cs.id
               JOIN courses c ON cs.course_id = c.id
               WHERE cl.id = ?";
$stmt = mysqli_prepare($conn, $lesson_sql);
mysqli_stmt_bind_param($stmt, "i", $lesson_id);
mysqli_stmt_execute($stmt);
$lesson_result = mysqli_stmt_get_result($stmt);
$lesson = mysqli_fetch_assoc($lesson_result);

if (!$lesson) {
    header("Location: dashboard.php");
    exit();
}

$course_id = $lesson['course_id'];

// Check if user is enrolled
$check_sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $course_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
$enrollment = mysqli_fetch_assoc($check_result);
$is_enrolled = !empty($enrollment);

// For paid courses, check if enrolled
if ($lesson['price'] > 0 && !$is_enrolled && !$lesson['is_free_preview']) {
    header("Location: course-details.php?id=" . $course_id . "&payment_required=1");
    exit();
}

// Get next and previous lessons
$prev_sql = "SELECT id, title FROM course_lessons 
             WHERE section_id = ? AND lesson_order < ? 
             ORDER BY lesson_order DESC LIMIT 1";
$prev_stmt = mysqli_prepare($conn, $prev_sql);
mysqli_stmt_bind_param($prev_stmt, "ii", $lesson['section_id'], $lesson['lesson_order']);
mysqli_stmt_execute($prev_stmt);
$prev_result = mysqli_stmt_get_result($prev_stmt);
$prev_lesson = mysqli_fetch_assoc($prev_result);

$next_sql = "SELECT id, title FROM course_lessons 
             WHERE section_id = ? AND lesson_order > ? 
             ORDER BY lesson_order ASC LIMIT 1";
$next_stmt = mysqli_prepare($conn, $next_sql);
mysqli_stmt_bind_param($next_stmt, "ii", $lesson['section_id'], $lesson['lesson_order']);
mysqli_stmt_execute($next_stmt);
$next_result = mysqli_stmt_get_result($next_stmt);
$next_lesson = mysqli_fetch_assoc($next_result);

// Mark lesson as completed if not already
if ($is_enrolled) {
    $progress_sql = "INSERT INTO lesson_progress (user_id, lesson_id, is_completed) 
                     VALUES (?, ?, 1) 
                     ON DUPLICATE KEY UPDATE is_completed = 1, watched_at = NOW()";
    $progress_stmt = mysqli_prepare($conn, $progress_sql);
    mysqli_stmt_bind_param($progress_stmt, "ii", $user_id, $lesson_id);
    mysqli_stmt_execute($progress_stmt);
    
    // Update course progress
    $total_lessons_sql = "SELECT COUNT(*) as total FROM course_lessons cl
                          JOIN course_sections cs ON cl.section_id = cs.id
                          WHERE cs.course_id = ?";
    $total_stmt = mysqli_prepare($conn, $total_lessons_sql);
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
    
    $update_progress_sql = "UPDATE enrollments SET progress_percentage = ? WHERE user_id = ? AND course_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_progress_sql);
    mysqli_stmt_bind_param($update_stmt, "dii", $progress_percentage, $user_id, $course_id);
    mysqli_stmt_execute($update_stmt);
}
?>

<?php include 'header.php'; ?>

<style>
    .lesson-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
        padding: 2rem 0;
    }
    
    .lesson-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    .lesson-header {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }
    
    .breadcrumb {
        color: #6b7280;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    
    .breadcrumb a {
        color: #2563eb;
        text-decoration: none;
    }
    
    .lesson-title {
        font-size: 2rem;
        color: #1f2937;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .lesson-meta {
        display: flex;
        gap: 1.5rem;
        color: #6b7280;
        font-size: 0.9rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    
    .lesson-content {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
        line-height: 1.8;
    }
    
    .lesson-content h2 {
        color: #1f2937;
        margin: 1.5rem 0 1rem;
        font-size: 1.5rem;
    }
    
    .lesson-content h3 {
        color: #374151;
        margin: 1.2rem 0 0.8rem;
        font-size: 1.3rem;
    }
    
    .lesson-content h4 {
        color: #4b5563;
        margin: 1rem 0 0.5rem;
        font-size: 1.1rem;
    }
    
    .lesson-content p {
        color: #4b5563;
        margin-bottom: 1rem;
    }
    
    .lesson-content ul, .lesson-content ol {
        margin: 1rem 0 1rem 2rem;
        color: #4b5563;
    }
    
    .lesson-content li {
        margin: 0.5rem 0;
    }
    
    .lesson-content pre {
        background: #1f2937;
        color: #e5e7eb;
        padding: 1rem;
        border-radius: 12px;
        overflow-x: auto;
        margin: 1rem 0;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
    
    .lesson-content code {
        background: #f3f4f6;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        color: #dc2626;
    }
    
    .practice-exercise {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 1.5rem;
        margin: 1.5rem 0;
        border-radius: 12px;
    }
    
    .practice-exercise h3 {
        color: #92400e;
        margin-top: 0;
    }
    
    .key-takeaways {
        background: #e0e7ff;
        border-left: 4px solid #2563eb;
        padding: 1.5rem;
        margin: 1.5rem 0;
        border-radius: 12px;
    }
    
    .lesson-navigation {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }
    
    .nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        color: #374151;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .nav-btn:hover {
        background: #f9fafb;
        transform: translateX(-2px);
    }
    
    .nav-btn-next:hover {
        transform: translateX(2px);
    }
    
    .back-to-course {
        display: inline-block;
        margin-top: 1rem;
        color: #2563eb;
        text-decoration: none;
    }
    
    .completed-badge {
        display: inline-block;
        background: #10b981;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        margin-left: 1rem;
    }
    
    @media (max-width: 768px) {
        .lesson-container {
            padding: 0 1rem;
        }
        .lesson-title {
            font-size: 1.5rem;
        }
        .lesson-navigation {
            flex-direction: column;
        }
        .nav-btn {
            justify-content: center;
        }
    }
</style>

<div class="lesson-wrapper">
    <div class="lesson-container">
        
        <div class="lesson-header">
            <div class="breadcrumb">
                <a href="dashboard.php">Dashboard</a> &gt;
                <a href="course-details.php?id=<?php echo $course_id; ?>"><?php echo htmlspecialchars($lesson['course_title']); ?></a> &gt;
                <span><?php echo htmlspecialchars($lesson['title']); ?></span>
            </div>
            
            <h1 class="lesson-title">
                <?php echo htmlspecialchars($lesson['title']); ?>
                <?php if($is_enrolled && $lesson['is_free_preview'] == 0): ?>
                    <span class="completed-badge">✓ Marked as completed</span>
                <?php endif; ?>
            </h1>
            
            <div class="lesson-meta">
                <span>⏱️ <?php echo $lesson['duration_minutes']; ?> minutes</span>
                <?php if($lesson['is_free_preview']): ?>
                    <span class="preview-badge" style="background:#e0e7ff; color:#2563eb; padding:0.2rem 0.5rem; border-radius:4px;">📺 Free Preview</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="lesson-content">
            <?php echo $lesson['content']; ?>
        </div>
        
        <div class="lesson-navigation">
            <?php if($prev_lesson): ?>
                <a href="lesson.php?lesson_id=<?php echo $prev_lesson['id']; ?>" class="nav-btn">
                    ← Previous: <?php echo htmlspecialchars($prev_lesson['title']); ?>
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>
            
            <?php if($next_lesson): ?>
                <a href="lesson.php?lesson_id=<?php echo $next_lesson['id']; ?>" class="nav-btn nav-btn-next">
                    Next: <?php echo htmlspecialchars($next_lesson['title']); ?> →
                </a>
            <?php else: ?>
                <a href="course-details.php?id=<?php echo $course_id; ?>" class="nav-btn">
                    Course Complete! 🎉 → Back to Course
                </a>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="course-details.php?id=<?php echo $course_id; ?>" class="back-to-course">
                ← Back to Course Overview
            </a>
        </div>
        
    </div>
</div>

<script>
    // Auto-mark lesson as completed when user scrolls to bottom
    let marked = false;
    
    window.addEventListener('scroll', function() {
        if (!marked) {
            const scrollPosition = window.scrollY + window.innerHeight;
            const pageHeight = document.documentElement.scrollHeight;
            
            if (scrollPosition >= pageHeight - 100) {
                // User has scrolled to near bottom, mark as completed
                fetch('mark-lesson-complete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'lesson_id=<?php echo $lesson_id; ?>'
                });
                marked = true;
            }
        }
    });
</script>

<?php include 'footer.php'; ?>
