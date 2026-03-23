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

// Get user's enrolled courses with details
$enrolled_sql = "SELECT c.*, e.enrolled_at, e.progress_percentage,
                 (SELECT COUNT(*) FROM course_lessons cl 
                  JOIN course_sections cs ON cl.section_id = cs.id 
                  WHERE cs.course_id = c.id) as total_lessons
                 FROM courses c 
                 JOIN enrollments e ON c.id = e.course_id 
                 WHERE e.user_id = ?
                 ORDER BY e.enrolled_at DESC";
$stmt = mysqli_prepare($conn, $enrolled_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$enrolled_courses = mysqli_stmt_get_result($stmt);

// Get total enrolled courses count
$count_sql = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_enrolled = mysqli_fetch_assoc($count_result)['total'];

// Get completed courses count
$completed_sql = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ? AND is_completed = TRUE";
$stmt = mysqli_prepare($conn, $completed_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$completed_result = mysqli_stmt_get_result($stmt);
$total_completed = mysqli_fetch_assoc($completed_result)['total'];

// If instructor, get their stats
if ($user_type == 'instructor') {
    $course_sql = "SELECT COUNT(*) as total FROM courses WHERE instructor_id = ?";
    $stmt = mysqli_prepare($conn, $course_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $course_result = mysqli_stmt_get_result($stmt);
    $total_courses = mysqli_fetch_assoc($course_result)['total'];
    
    $student_sql = "SELECT COUNT(DISTINCT e.user_id) as total 
                    FROM enrollments e 
                    JOIN courses c ON e.course_id = c.id 
                    WHERE c.instructor_id = ?";
    $stmt = mysqli_prepare($conn, $student_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $student_result = mysqli_stmt_get_result($stmt);
    $total_students = mysqli_fetch_assoc($student_result)['total'];
}
?>

<?php include 'header.php'; ?>

<style>
    .dashboard-wrapper {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 0;
    }
    
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    /* Welcome Section */
    .welcome-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        text-align: center;
    }
    
    .welcome-title {
        font-size: 2.2rem;
        color: #333;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .welcome-subtitle {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }
    
    .user-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 3rem;
        color: white;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.8rem;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #667eea;
        line-height: 1.2;
        margin-bottom: 0.3rem;
    }
    
    .stat-label {
        color: #666;
        font-size: 1rem;
        font-weight: 500;
    }
    
    /* Section Title */
    .section-title {
        font-size: 1.8rem;
        color: white;
        margin-bottom: 1.5rem;
        font-weight: 600;
        text-align: center;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    /* Courses Grid */
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .course-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        transition: transform 0.3s;
    }
    
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    }
    
    .course-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    
    .course-content {
        padding: 1.8rem;
    }
    
    .course-title {
        font-size: 1.3rem;
        color: #333;
        margin-bottom: 0.8rem;
        font-weight: 600;
    }
    
    .course-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        color: #666;
        font-size: 0.9rem;
    }
    
    .enrolled-date {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    /* Progress Bar */
    .progress-container {
        margin: 1rem 0;
    }
    
    .progress-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        color: #666;
    }
    
    .progress-bar {
        width: 100%;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 4px;
        transition: width 0.3s;
    }
    
    .continue-btn {
        display: block;
        width: 100%;
        padding: 0.8rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        text-align: center;
        border-radius: 8px;
        font-weight: 500;
        margin-top: 1.2rem;
        transition: transform 0.3s;
        border: none;
        cursor: pointer;
    }
    
    .continue-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .completed-badge {
        background: #28a745;
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        display: inline-block;
    }
    
    .empty-state {
        background: white;
        border-radius: 15px;
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    
    .empty-icon {
        font-size: 5rem;
        margin-bottom: 1rem;
    }
    
    .empty-title {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 1rem;
    }
    
    .empty-text {
        color: #666;
        margin-bottom: 2rem;
    }
    
    .browse-btn {
        display: inline-block;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: transform 0.3s;
    }
    
    .browse-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    /* Quick Actions */
    .quick-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }
    
    .action-btn {
        padding: 0.8rem 1.5rem;
        background: white;
        color: #667eea;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s;
        border: 2px solid white;
    }
    
    .action-btn:hover {
        background: transparent;
        color: white;
    }
</style>

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="user-avatar">
                <?php echo substr($_SESSION['username'], 0, 1); ?>
            </div>
            <h1 class="welcome-title">Welcome, <?php echo $_SESSION['username']; ?>! 👋</h1>
            <p class="welcome-subtitle">Your learning journey continues here</p>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="courses.php" class="action-btn">Browse Courses</a>
                <?php if ($user_type == 'instructor'): ?>
                    <a href="create-course.php" class="action-btn">Create Course</a>
                <?php endif; ?>
                <a href="profile.php" class="action-btn">Edit Profile</a>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-number"><?php echo $total_enrolled; ?></div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $total_completed; ?></div>
                <div class="stat-label">Completed Courses</div>
            </div>
            
            <?php if ($user_type == 'instructor'): ?>
                <div class="stat-card">
                    <div class="stat-icon">🎓</div>
                    <div class="stat-number"><?php echo $total_courses; ?></div>
                    <div class="stat-label">Courses Created</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            <?php else: ?>
                <div class="stat-card">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-number">
                        <?php 
                        $hours_sql = "SELECT SUM(c.duration_hours) as total 
                                     FROM enrollments e 
                                     JOIN courses c ON e.course_id = c.id 
                                     WHERE e.user_id = ?";
                        $stmt = mysqli_prepare($conn, $hours_sql);
                        mysqli_stmt_bind_param($stmt, "i", $user_id);
                        mysqli_stmt_execute($stmt);
                        $hours_result = mysqli_stmt_get_result($stmt);
                        $total_hours = mysqli_fetch_assoc($hours_result)['total'] ?? 0;
                        echo $total_hours;
                        ?>
                    </div>
                    <div class="stat-label">Learning Hours</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number">
                        <?php 
                        $avg_sql = "SELECT AVG(r.rating) as avg_rating 
                                   FROM reviews r 
                                   JOIN enrollments e ON r.course_id = e.course_id 
                                   WHERE e.user_id = ?";
                        $stmt = mysqli_prepare($conn, $avg_sql);
                        mysqli_stmt_bind_param($stmt, "i", $user_id);
                        mysqli_stmt_execute($stmt);
                        $avg_result = mysqli_stmt_get_result($stmt);
                        $avg_rating = mysqli_fetch_assoc($avg_result)['avg_rating'] ?? 0;
                        echo number_format($avg_rating, 1);
                        ?>
                    </div>
                    <div class="stat-label">Avg Rating Given</div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- My Courses Section -->
<h2 class="section-title">My Learning Journey</h2>

<?php if (mysqli_num_rows($enrolled_courses) > 0): ?>
    <div class="courses-grid">
        <?php while ($course = mysqli_fetch_assoc($enrolled_courses)): 
            // Safely check if keys exist before using them
            $title = isset($course['title']) ? $course['title'] : 'Untitled Course';
            $image_url = isset($course['image_url']) ? $course['image_url'] : '';
            $enrolled_at = isset($course['enrolled_at']) ? $course['enrolled_at'] : date('Y-m-d H:i:s');
            $is_completed = isset($course['is_completed']) ? $course['is_completed'] : false;
            $progress = isset($course['progress_percentage']) ? $course['progress_percentage'] : 0;
            $course_id = isset($course['id']) ? $course['id'] : 0;
        ?>
        <div class="course-card">
            <div class="course-image">
                <?php if(!empty($image_url)): ?>
                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                         alt="<?php echo htmlspecialchars($title); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="font-size: 3rem; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        📚
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="course-content">
                <h3 class="course-title"><?php echo htmlspecialchars($title); ?></h3>
                
                <div class="course-meta">
                    <span class="enrolled-date">
                        📅 Enrolled: <?php echo date('M d, Y', strtotime($enrolled_at)); ?>
                    </span>
                    
                    <?php if($is_completed): ?>
                        <span class="completed-badge">Completed ✓</span>
                    <?php endif; ?>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress-container">
                    <div class="progress-header">
                        <span>Progress</span>
                        <span><?php echo $progress; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                </div>
                
                <?php if($is_completed): ?>
                    <a href="certificate.php?course_id=<?php echo $course_id; ?>" 
                       class="continue-btn" style="background: #28a745;">
                        View Certificate 🏆
                    </a>
                <?php else: ?>
                    <a href="course-details.php?id=<?php echo $course_id; ?>" 
                       class="continue-btn">
                        Continue Learning →
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-icon">📖</div>
        <h3 class="empty-title">No Enrolled Courses Yet</h3>
        <p class="empty-text">Start your learning journey today by exploring our courses!</p>
        <a href="courses.php" class="browse-btn">Browse Courses</a>
    </div>
<?php endif; ?>
        
        <!-- Recommended Courses (Optional) -->
        <?php if (mysqli_num_rows($enrolled_courses) == 0): ?>
        <div style="margin-top: 3rem;">
            <h2 class="section-title">Recommended for You</h2>
            <div class="courses-grid">
                <?php
                $rec_sql = "SELECT c.*, u.username as instructor_name 
                           FROM courses c 
                           JOIN users u ON c.instructor_id = u.id 
                           WHERE c.is_published = TRUE 
                           ORDER BY RAND() 
                           LIMIT 3";
                $rec_result = mysqli_query($conn, $rec_sql);
                
                while($rec_course = mysqli_fetch_assoc($rec_result)):
                ?>
                <div class="course-card">
                    <div class="course-image">📚</div>
                    <div class="course-content">
                        <h3 class="course-title"><?php echo htmlspecialchars($rec_course['title']); ?></h3>
                        <p style="color: #666; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars(substr($rec_course['description'], 0, 80) . '...'); ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 1.3rem; color: #667eea; font-weight: bold;">
                                $<?php echo $rec_course['price']; ?>
                            </span>
                            <a href="courses.php?id=<?php echo $rec_course['id']; ?>" 
                               style="color: #667eea; text-decoration: none;">View →</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<?php include 'footer.php'; ?>