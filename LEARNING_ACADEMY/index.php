<?php require_once 'includes/config.php'; ?>
<?php include 'header.php'; ?>

<style>
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 0;
        text-align: center;
    }
    
    .hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .hero p {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.95;
    }
    
    .hero-btn {
        background: white;
        color: #667eea !important;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .hero-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .section-title {
        text-align: center;
        margin: 3rem 0 2rem;
        color: white;
        font-size: 2.2rem;
        font-weight: 600;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        padding: 2rem 0;
    }
    
    .course-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        transition: transform 0.3s, box-shadow 0.3s;
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
    
    .course-title {
        font-size: 1.3rem;
        margin-bottom: 0.8rem;
        color: #333;
        font-weight: 600;
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
        margin: 1rem 0;
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
    
    .view-btn {
        display: inline-block;
        padding: 0.8rem 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: transform 0.3s, box-shadow 0.3s;
        margin-top: 1rem;
    }
    
    .view-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .no-courses {
        text-align: center;
        color: white;
        grid-column: 1/-1;
        font-size: 1.2rem;
        padding: 3rem;
        background: rgba(255,255,255,0.1);
        border-radius: 15px;
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to Learning Academy</h1>
        <p>Discover thousands of courses taught by expert instructors</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="hero-btn">Get Started</a>
        <?php endif; ?>
    </div>
</section>

<!-- Popular Courses Section -->
<section class="container">
    <h2 class="section-title">Popular Courses</h2>
    
    <div class="courses-grid">
        <?php
        $sql = "SELECT c.*, u.username as instructor_name, cat.name as category_name
                FROM courses c 
                JOIN users u ON c.instructor_id = u.id 
                LEFT JOIN categories cat ON c.category_id = cat.id
                WHERE c.is_published = TRUE
                ORDER BY c.created_at DESC 
                LIMIT 6";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($course = mysqli_fetch_assoc($result)) {
                ?>
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
                        <!-- Category Badge (if available) -->
                        <?php if(!empty($course['category_name'])): ?>
                            <span style="
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                color: white;
                                padding: 0.2rem 0.6rem;
                                border-radius: 15px;
                                font-size: 0.75rem;
                                display: inline-block;
                                margin-bottom: 0.8rem;
                            ">
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Course Title -->
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        
                        <!-- Course Description -->
                        <p class="course-description">
                            <?php echo htmlspecialchars(substr($course['description'], 0, 100) . '...'); ?>
                        </p>
                        
                        <!-- Course Meta (NO PRICE) -->
                        <div class="course-meta">
                            <span class="instructor-info">
                                <span>👨‍🏫</span> 
                                <?php echo htmlspecialchars($course['instructor_name']); ?>
                            </span>
                            
                            <?php if(!empty($course['level'])): ?>
                                <span class="instructor-info">
                                    <span>📊</span>
                                    <?php echo ucfirst($course['level']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- View Course Button -->
                        <a href="course-details.php?id=<?php echo $course['id']; ?>" class="view-btn">
                            View Course →
                        </a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='no-courses'>No courses available yet.</div>";
        }
        ?>
    </div>
    
    <!-- View All Courses Link -->
    <div style="text-align: center; margin: 2rem 0 3rem;">
        <a href="courses.php" style="
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            padding: 0.8rem 2rem;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            display: inline-block;
            transition: background 0.3s;
        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            View All Courses →
        </a>
    </div>
    
    <!-- Features Section (Optional) -->
    <div style="
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin: 4rem 0;
        color: white;
        text-align: center;
    ">
        <div style="padding: 2rem; background: rgba(255,255,255,0.1); border-radius: 15px;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎓</div>
            <h3 style="margin-bottom: 0.5rem;">Expert Instructors</h3>
            <p style="opacity: 0.9;">Learn from industry professionals</p>
        </div>
        
        <div style="padding: 2rem; background: rgba(255,255,255,0.1); border-radius: 15px;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⏱️</div>
            <h3 style="margin-bottom: 0.5rem;">Learn at Your Pace</h3>
            <p style="opacity: 0.9;">Access courses anytime, anywhere</p>
        </div>
        
        <div style="padding: 2rem; background: rgba(255,255,255,0.1); border-radius: 15px;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🏆</div>
            <h3 style="margin-bottom: 0.5rem;">Get Certified</h3>
            <p style="opacity: 0.9;">Earn certificates upon completion</p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>