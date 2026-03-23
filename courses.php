<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Build query with category name
$sql = "SELECT c.*, u.username as instructor_name, cat.name as category_name 
        FROM courses c 
        JOIN users u ON c.instructor_id = u.id 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        WHERE c.is_published = TRUE";

if (!empty($search)) {
    $sql .= " AND (c.title LIKE '%$search%' OR c.description LIKE '%$search%')";
}

if (!empty($category)) {
    $sql .= " AND cat.name = '$category'";
}

$sql .= " ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<?php include 'header.php'; ?>

<style>
    .courses-wrapper {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 0;
    }
    
    .courses-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .page-title {
        text-align: center;
        color: white;
        margin-bottom: 2.5rem;
        font-size: 2.8rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .search-section {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 3rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .search-form {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: end;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #555;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .form-input {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .form-select {
        width: 200px;
        padding: 0.8rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        background: white;
        cursor: pointer;
    }
    
    .search-btn {
        padding: 0.8rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        height: 48px;
        align-self: end;
    }
    
    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
    }
    
    .course-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        transition: transform 0.3s, box-shadow 0.3s;
        height: fit-content;
    }
    
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    }
    
    .course-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 4rem;
    }
    
    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-content {
        padding: 1.8rem;
    }
    
    .category-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .course-title {
        font-size: 1.3rem;
        margin: 0.5rem 0 1rem 0;
        color: #333;
        font-weight: 600;
        line-height: 1.4;
    }
    
    .course-description {
        color: #666;
        margin-bottom: 1.2rem;
        line-height: 1.6;
        font-size: 0.95rem;
    }
    
    .course-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1.2rem 0;
        padding-top: 1rem;
        border-top: 2px solid #f0f0f0;
        color: #666;
    }
    
    .instructor-info {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #999;
        font-size: 0.95rem;
    }
    
    .btn-enroll {
        display: block;
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        text-align: center;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        cursor: pointer;
        margin-top: 1rem;
    }
    
    .btn-enroll:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-enrolled {
        display: block;
        width: 100%;
        padding: 1rem;
        background: #28a745;
        color: white;
        text-align: center;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        border: none;
        margin-top: 1rem;
    }
    
    .course-footer {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #f0f0f0;
        display: flex;
        gap: 1rem;
        color: #999;
        font-size: 0.85rem;
    }
    
    .footer-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .empty-state {
        background: white;
        border-radius: 15px;
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        grid-column: 1/-1;
    }
    
    .empty-icon {
        font-size: 5rem;
        margin-bottom: 1rem;
    }
    
    .empty-title {
        color: #333;
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }
    
    .empty-text {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    
    .clear-btn {
        display: inline-block;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: transform 0.3s;
    }
    
    .clear-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .results-count {
        text-align: center;
        color: white;
        margin-top: 2rem;
        font-size: 0.95rem;
        opacity: 0.9;
    }
</style>

<div class="courses-wrapper">
    <div class="courses-container">
        
        <h1 class="page-title">Browse Courses</h1>
        
        <!-- Search and Filter Section -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <div class="form-group">
                    <label for="search" class="form-label">Search Courses</label>
                    <input type="text" 
                           id="search"
                           name="search" 
                           class="form-input"
                           placeholder="Search by title or description..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="Programming" <?php echo $category == 'Programming' ? 'selected' : ''; ?>>Programming</option>
                        <option value="Web Development" <?php echo $category == 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                        <option value="Data Science" <?php echo $category == 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                        <option value="Mobile Development" <?php echo $category == 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                        <option value="Cybersecurity" <?php echo $category == 'Cybersecurity' ? 'selected' : ''; ?>>Cybersecurity</option>
                        <option value="Database Design" <?php echo $category == 'Database Design' ? 'selected' : ''; ?>>Database Design</option>
                        <option value="Cloud Computing" <?php echo $category == 'Cloud Computing' ? 'selected' : ''; ?>>Cloud Computing</option>
                    </select>
                </div>
                
                <button type="submit" class="search-btn">
                    Search
                </button>
            </form>
        </div>
        
        <!-- Courses Grid -->
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="courses-grid">
                <?php while ($course = mysqli_fetch_assoc($result)): ?>
                <div class="course-card">
                    
                    <!-- Course Image -->
                    <div class="course-image">
                        <?php if(!empty($course['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php else: ?>
                            📚
                        <?php endif; ?>
                    </div>
                    
                    <!-- Course Content -->
                    <div class="course-content">
                        
                        <!-- Category Badge -->
                        <?php if(!empty($course['category_name'])): ?>
                            <span class="category-badge">
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Course Title -->
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        
                        <!-- Course Description -->
                        <p class="course-description">
                            <?php echo htmlspecialchars(substr($course['description'], 0, 120) . '...'); ?>
                        </p>
                        
                        <!-- Course Meta (NO PRICE) -->
                        <div class="course-meta">
                            <span class="instructor-info">
                                <span>👨‍🏫</span> 
                                <?php echo htmlspecialchars($course['instructor_name']); ?>
                            </span>
                        </div>
                        
                        <!-- Enroll Button / Login Prompt -->
                        <?php if (isset($_SESSION['user_id'])): 
                            // Check if already enrolled
                            $check_sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
                            $stmt = mysqli_prepare($conn, $check_sql);
                            mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $course['id']);
                            mysqli_stmt_execute($stmt);
                            $check_result = mysqli_stmt_get_result($stmt);
                            
                            if (mysqli_num_rows($check_result) > 0): ?>
                                <div class="btn-enrolled">
                                    ✓ Already Enrolled
                                </div>
                            <?php else: ?>
                                <form method="POST" action="enroll.php">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <button type="submit" class="btn-enroll">
                                        Enroll Now
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn-enroll">
                                Login to Enroll
                            </a>
                        <?php endif; ?>
                        
                        <!-- Additional Course Info -->
                        <div class="course-footer">
                            <?php if(!empty($course['level'])): ?>
                                <span class="footer-item">
                                    <span>📊</span> 
                                    <?php echo ucfirst($course['level']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if(!empty($course['duration_hours'])): ?>
                                <span class="footer-item">
                                    <span>⏱️</span> 
                                    <?php echo $course['duration_hours']; ?> hours
                                </span>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3 class="empty-title">No Courses Found</h3>
                <p class="empty-text">
                    Try adjusting your search or filter to find what you're looking for.
                </p>
                <a href="courses.php" class="clear-btn">
                    Clear Filters
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Results Count -->
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="results-count">
                Showing <?php echo mysqli_num_rows($result); ?> course(s)
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php include 'footer.php'; ?>