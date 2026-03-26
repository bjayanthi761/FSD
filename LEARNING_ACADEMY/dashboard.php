<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user = getCurrentUser();

// Get enrolled courses with details
$enrolled_sql = "SELECT c.*, e.enrolled_at, e.progress_percentage, e.is_completed, e.payment_status
                 FROM courses c 
                 JOIN enrollments e ON c.id = e.course_id 
                 WHERE e.user_id = ?
                 ORDER BY e.enrolled_at DESC";
$stmt = mysqli_prepare($conn, $enrolled_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$enrolled_courses = mysqli_stmt_get_result($stmt);

// Get total stats - FIXED: Count paid courses based on actual course price
$stats_sql = "SELECT 
                COUNT(e.id) as total_enrolled,
                SUM(CASE WHEN e.is_completed = 1 THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN c.price > 0 THEN 1 ELSE 0 END) as paid_courses
              FROM enrollments e
              JOIN courses c ON e.course_id = c.id
              WHERE e.user_id = ?";
$stmt = mysqli_prepare($conn, $stats_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$stats_result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($stats_result);

// Set default stats if no results
if (!$stats) {
    $stats = ['total_enrolled' => 0, 'completed' => 0, 'paid_courses' => 0];
}

// Get all courses for marketplace (free + paid)
$courses_sql = "SELECT c.*, u.username as instructor_name, cat.name as category_name,
                CASE WHEN e.id IS NOT NULL THEN 1 ELSE 0 END as is_enrolled
                FROM courses c 
                JOIN users u ON c.instructor_id = u.id 
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
                WHERE c.is_published = TRUE
                ORDER BY c.price ASC, c.created_at DESC";
$stmt = mysqli_prepare($conn, $courses_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$courses_result = mysqli_stmt_get_result($stmt);
?>

<?php include 'header.php'; ?>

<style>
    .dashboard-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
        padding: 2rem 0;
    }
    
    .dashboard-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    .welcome-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
    }
    
    .welcome-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .welcome-title h1 {
        font-size: 1.8rem;
        color: #1f2937;
        margin-bottom: 0.3rem;
    }
    
    .welcome-title p {
        color: #6b7280;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .stat-card {
        background: #f9fafb;
        border-radius: 16px;
        padding: 1.2rem;
        text-align: center;
        border: 1px solid #e5e7eb;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2563eb;
    }
    
    .stat-label {
        color: #6b7280;
        font-size: 0.85rem;
        margin-top: 0.3rem;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        margin: 2rem 0 1.5rem;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }
    
    .course-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.3s;
    }
    
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    
    .course-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    .badge-free {
        background: #d1fae5;
        color: #10b981;
    }
    
    .badge-paid {
        background: #fef3c7;
        color: #f59e0b;
    }
    
    .course-image {
        height: 160px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
    }
    
    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-content {
        padding: 1.5rem;
    }
    
    .course-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .course-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1rem 0;
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .course-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2563eb;
    }
    
    .course-price.free {
        color: #10b981;
    }
    
    .btn-enroll, .btn-continue {
        display: block;
        width: 100%;
        padding: 0.75rem;
        text-align: center;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        margin-top: 1rem;
    }
    
    .btn-enroll {
        background: #2563eb;
        color: white;
    }
    
    .btn-enroll:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }
    
    .btn-continue {
        background: #f3f4f6;
        color: #374151;
    }
    
    .btn-continue:hover {
        background: #e5e7eb;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
    }
    
    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0 1rem;
        }
        .welcome-header {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-header">
                <div class="welcome-title">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
                    <p>Continue your learning journey</p>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_enrolled']; ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['completed']; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['paid_courses']; ?></div>
                    <div class="stat-label">Paid Courses</div>
                </div>
            </div>
        </div>
        
        <!-- My Courses Section -->
        <h2 class="section-title">📚 My Courses</h2>
        
        <?php if (mysqli_num_rows($enrolled_courses) > 0): ?>
            <div class="courses-grid">
                <?php while ($course = mysqli_fetch_assoc($enrolled_courses)): ?>
                <div class="course-card">
                    <div class="course-image">
                        <?php if(!empty($course['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php else: ?>
                            📚
                        <?php endif; ?>
                    </div>
                    <div class="course-content">
                        <span class="course-badge <?php echo $course['price'] > 0 ? 'badge-paid' : 'badge-free'; ?>">
                            <?php echo $course['price'] > 0 ? '💰 Paid' : '🎁 Free'; ?>
                        </span>
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <div class="course-meta">
                            <span>📅 Enrolled: <?php echo date('M d, Y', strtotime($course['enrolled_at'])); ?></span>
                        </div>
                        <div class="progress-container" style="margin: 0.5rem 0;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.3rem;">
                                <span>Progress</span>
                                <span><?php echo $course['progress_percentage']; ?>%</span>
                            </div>
                            <div style="width: 100%; height: 6px; background: #e5e7eb; border-radius: 3px;">
                                <div style="width: <?php echo $course['progress_percentage']; ?>%; height: 100%; background: #2563eb; border-radius: 3px;"></div>
                            </div>
                        </div>
                        <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn-continue">
                            <?php echo $course['progress_percentage'] > 0 ? 'Continue Learning →' : 'Start Learning →'; ?>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📖</div>
                <h3>No courses yet</h3>
                <p style="color: #6b7280; margin: 0.5rem 0 1rem;">Start your learning journey by enrolling in a course</p>
                <a href="courses.php" class="btn-enroll" style="display: inline-block; width: auto; padding: 0.75rem 1.5rem;">Browse Courses</a>
            </div>
        <?php endif; ?>
        
        <!-- Marketplace Section - All Courses -->
        <h2 class="section-title">🛒 Discover New Courses</h2>
        
        <div class="courses-grid">
            <?php 
            $display_count = 0;
            // Reset the result pointer to beginning
            mysqli_data_seek($courses_result, 0);
            while ($course = mysqli_fetch_assoc($courses_result)): 
                if (!$course['is_enrolled'] && $display_count < 6):
                    $display_count++;
            ?>
            <div class="course-card">
                <div class="course-image">
                    <?php if(!empty($course['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <?php else: ?>
                        📘
                    <?php endif; ?>
                </div>
                <div class="course-content">
                    <span class="course-badge <?php echo $course['price'] > 0 ? 'badge-paid' : 'badge-free'; ?>">
                        <?php echo $course['price'] > 0 ? '💰 Paid - $' . number_format($course['price'], 2) : '🎁 Free'; ?>
                    </span>
                    <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p style="color: #6b7280; font-size: 0.85rem; margin: 0.5rem 0;">👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                    <p style="color: #6b7280; font-size: 0.85rem;"><?php echo substr(htmlspecialchars($course['description']), 0, 80); ?>...</p>
                    <div class="course-meta">
                        <span class="course-price <?php echo $course['price'] == 0 ? 'free' : ''; ?>">
                            <?php echo $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free'; ?>
                        </span>
                        <span>📊 <?php echo ucfirst($course['level']); ?></span>
                    </div>
                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn-enroll">View Course</a>
                </div>
            </div>
            <?php 
                endif;
            endwhile; 
            ?>
        </div>
        
        <?php if ($display_count == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">🎓</div>
                <h3>All courses enrolled!</h3>
                <p style="color: #6b7280; margin-top: 0.5rem;">You've enrolled in all available courses. Check back later for new content!</p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="courses.php" style="color: #2563eb; text-decoration: none; font-weight: 500;">View all courses →</a>
        </div>
        
    </div>
</div>

<?php include 'footer.php'; ?>
