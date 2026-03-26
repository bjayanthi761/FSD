<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$price_filter = isset($_GET['price']) ? mysqli_real_escape_string($conn, $_GET['price']) : '';

// Build query
$sql = "SELECT c.*, u.username as instructor_name, cat.name as category_name,
        CASE WHEN e.id IS NOT NULL THEN 1 ELSE 0 END as is_enrolled
        FROM courses c 
        JOIN users u ON c.instructor_id = u.id 
        LEFT JOIN categories cat ON c.category_id = cat.id
        LEFT JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        WHERE c.is_published = TRUE";

$params = [$user_id];
$types = "i";

if (!empty($search)) {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND cat.name = ?";
    $params[] = $category;
    $types .= "s";
}

if ($price_filter == 'free') {
    $sql .= " AND c.price = 0";
} elseif ($price_filter == 'paid') {
    $sql .= " AND c.price > 0";
}

$sql .= " ORDER BY c.price ASC, c.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_courses = mysqli_num_rows($result);
?>

<?php include 'header.php'; ?>

<style>
    .marketplace-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
        padding: 2rem 0;
    }
    
    .marketplace-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .page-subtitle {
        color: #6b7280;
        font-size: 1rem;
    }
    
    .filter-section {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .filter-form {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    
    .filter-group {
        flex: 1;
        min-width: 180px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #374151;
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    .filter-input, .filter-select {
        width: 100%;
        padding: 0.7rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        transition: all 0.2s;
        background: #f8fafc;
    }
    
    .filter-input:focus, .filter-select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        background: white;
    }
    
    .filter-btn {
        padding: 0.7rem 1.5rem;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        height: 44px;
    }
    
    .filter-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }
    
    .clear-btn {
        padding: 0.7rem 1.5rem;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        height: 44px;
        line-height: 1.2;
    }
    
    .clear-btn:hover {
        background: #e2e8f0;
    }
    
    .results-count {
        margin-bottom: 1.5rem;
        color: #6b7280;
        font-size: 0.9rem;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.8rem;
    }
    
    .course-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    
    .course-image {
        height: 180px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        color: white;
    }
    
    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
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
    
    .badge-enrolled {
        background: #e0e7ff;
        color: #2563eb;
    }
    
    .course-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .course-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .course-instructor {
        color: #6b7280;
        font-size: 0.85rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .course-description {
        color: #6b7280;
        font-size: 0.85rem;
        line-height: 1.5;
        margin-bottom: 1rem;
        flex: 1;
    }
    
    .course-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1rem 0 0;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .course-price {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2563eb;
    }
    
    .course-price.free {
        color: #10b981;
    }
    
    .course-level {
        font-size: 0.8rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-view {
        display: block;
        width: 100%;
        padding: 0.75rem;
        text-align: center;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        margin-top: 1rem;
        background: #2563eb;
        color: white;
    }
    
    .btn-view:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }
    
    .btn-enrolled {
        background: #10b981;
        cursor: default;
    }
    
    .btn-enrolled:hover {
        background: #10b981;
        transform: none;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem;
        background: white;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .empty-title {
        font-size: 1.3rem;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .empty-text {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .marketplace-container {
            padding: 0 1rem;
        }
        .filter-form {
            flex-direction: column;
        }
        .filter-group {
            width: 100%;
        }
        .courses-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="marketplace-wrapper">
    <div class="marketplace-container">
        
        <div class="page-header">
            <h1 class="page-title">📚 All Courses</h1>
            <p class="page-subtitle">Discover 20+ courses from expert instructors</p>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label for="search">🔍 Search</label>
                    <input type="text" id="search" name="search" class="filter-input" 
                           placeholder="Search by title or description..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="category">📂 Category</label>
                    <select name="category" id="category" class="filter-select">
                        <option value="">All Categories</option>
                        <option value="Programming" <?php echo $category == 'Programming' ? 'selected' : ''; ?>>Programming</option>
                        <option value="Web Development" <?php echo $category == 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                        <option value="Data Science" <?php echo $category == 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                        <option value="Mobile Development" <?php echo $category == 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                        <option value="Cybersecurity" <?php echo $category == 'Cybersecurity' ? 'selected' : ''; ?>>Cybersecurity</option>
                        <option value="Database Design" <?php echo $category == 'Database Design' ? 'selected' : ''; ?>>Database Design</option>
                        <option value="Cloud Computing" <?php echo $category == 'Cloud Computing' ? 'selected' : ''; ?>>Cloud Computing</option>
                        <option value="DevOps" <?php echo $category == 'DevOps' ? 'selected' : ''; ?>>DevOps</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="price">💰 Price</label>
                    <select name="price" id="price" class="filter-select">
                        <option value="">All Courses</option>
                        <option value="free" <?php echo $price_filter == 'free' ? 'selected' : ''; ?>>Free Only</option>
                        <option value="paid" <?php echo $price_filter == 'paid' ? 'selected' : ''; ?>>Paid Only</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="filter-btn">Apply Filters</button>
                </div>
                
                <div class="filter-group">
                    <a href="courses.php" class="clear-btn">Clear All</a>
                </div>
            </form>
        </div>
        
        <!-- Results Count -->
        <div class="results-count">
            📊 Showing <strong><?php echo $total_courses; ?></strong> courses
        </div>
        
        <!-- Courses Grid -->
        <?php if ($total_courses > 0): ?>
            <div class="courses-grid">
                <?php while ($course = mysqli_fetch_assoc($result)): ?>
                <div class="course-card">
                    <div class="course-image">
                        <?php if(!empty($course['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php else: ?>
                            <?php echo $course['price'] > 0 ? '💰' : '🎓'; ?>
                        <?php endif; ?>
                    </div>
                    <div class="course-content">
                        <div>
                            <span class="course-badge <?php echo $course['price'] > 0 ? 'badge-paid' : 'badge-free'; ?>">
                                <?php echo $course['price'] > 0 ? '💰 Paid - $' . number_format($course['price'], 2) : '🎁 Free'; ?>
                            </span>
                            <?php if($course['is_enrolled']): ?>
                                <span class="course-badge badge-enrolled">✓ Enrolled</span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        
                        <div class="course-instructor">
                            <span>👨‍🏫</span> <?php echo htmlspecialchars($course['instructor_name']); ?>
                        </div>
                        
                        <p class="course-description">
                            <?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...
                        </p>
                        
                        <div class="course-meta">
                            <span class="course-price <?php echo $course['price'] == 0 ? 'free' : ''; ?>">
                                <?php echo $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free'; ?>
                            </span>
                            <span class="course-level">
                                📊 <?php echo ucfirst($course['level']); ?> • <?php echo $course['duration_hours']; ?> hours
                            </span>
                        </div>
                        
                        <?php if($course['is_enrolled']): ?>
                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn-view btn-enrolled">
                                ✓ Continue Learning
                            </a>
                        <?php else: ?>
                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn-view">
                                View Course Details →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3 class="empty-title">No courses found</h3>
                <p class="empty-text">Try adjusting your search or filter criteria</p>
                <a href="courses.php" class="btn-view" style="display: inline-block; width: auto; padding: 0.75rem 2rem;">Clear Filters</a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php include 'footer.php'; ?>
