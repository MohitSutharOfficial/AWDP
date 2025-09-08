<?php
require_once __DIR__ . '/config/database.php';

// Initialize database connection
try {
    $db = new Database();
} catch (Exception $e) {
    error_log("Database connection error in testimonials.php: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

// Fetch testimonials from database
try {
    $testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE is_active = true ORDER BY is_featured DESC, created_at DESC");
} catch (Exception $e) {
    $testimonials = [];
    error_log("Error fetching testimonials: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Testimonials - TechCorp Learning Solutions</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Government-style CSS -->
    <style>
        :root {
            --primary-blue: #2c5aa0;
            --secondary-blue: #1e3d70;
            --light-blue: #e8f0fe;
            --dark-gray: #333333;
            --medium-gray: #666666;
            --light-gray: #f8f9fa;
            --border-gray: #dee2e6;
            --success-green: #28a745;
            --warning-orange: #fd7e14;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: var(--dark-gray);
            background-color: #ffffff;
        }
        
        /* Header */
        .main-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 20px 0;
            border-bottom: 3px solid var(--secondary-blue);
        }
        
        .site-title {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }
        
        .site-subtitle {
            font-size: 14px;
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        /* Navigation */
        .main-nav {
            background-color: var(--light-blue);
            border-bottom: 2px solid var(--border-gray);
            padding: 0;
        }
        
        .main-nav .navbar-nav .nav-link {
            color: var(--primary-blue);
            font-weight: 500;
            padding: 15px 20px;
            border-right: 1px solid var(--border-gray);
        }
        
        .main-nav .navbar-nav .nav-link:hover,
        .main-nav .navbar-nav .nav-link.active {
            background-color: var(--primary-blue);
            color: white;
        }
        
        /* Page Title */
        .page-title {
            background-color: var(--light-gray);
            border: 2px solid var(--border-gray);
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .page-title h1 {
            color: var(--primary-blue);
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .page-title p {
            color: var(--medium-gray);
            font-size: 16px;
            margin: 0;
        }
        
        /* Data Boxes */
        .data-section {
            margin-bottom: 40px;
        }
        
        .section-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 20px;
            margin-bottom: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .data-box {
            border: 2px solid var(--border-gray);
            background-color: white;
            margin-bottom: 20px;
        }
        
        .data-box-header {
            background-color: var(--light-blue);
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-gray);
            font-weight: bold;
            color: var(--primary-blue);
        }
        
        .data-box-content {
            padding: 20px;
        }
        
        /* Testimonial Cards */
        .testimonial-item {
            background-color: white;
            border: 1px solid var(--border-gray);
            margin-bottom: 20px;
            height: 100%;
        }
        
        .testimonial-header {
            background-color: var(--light-gray);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-gray);
            text-align: center;
        }
        
        .testimonial-name {
            color: var(--primary-blue);
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }
        
        .testimonial-position {
            color: var(--medium-gray);
            font-size: 14px;
            margin: 5px 0 0 0;
        }
        
        .testimonial-content {
            padding: 20px;
        }
        
        .testimonial-text {
            font-style: italic;
            color: var(--medium-gray);
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .testimonial-rating {
            color: var(--warning-orange);
            text-align: center;
            margin-bottom: 10px;
        }
        
        .featured-badge {
            background-color: var(--warning-orange);
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 3px;
            margin-left: 10px;
        }
        
        /* Statistics Cards */
        .stat-card {
            background-color: white;
            border: 2px solid var(--border-gray);
            text-align: center;
            padding: 25px 15px;
            margin-bottom: 20px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: var(--primary-blue);
            display: block;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--medium-gray);
            margin-top: 5px;
        }
        
        /* Buttons */
        .btn-primary-gov {
            background-color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
            color: white;
            padding: 12px 30px;
            font-weight: bold;
            text-decoration: none;
        }
        
        .btn-primary-gov:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
            color: white;
        }
        
        .btn-secondary-gov {
            background-color: white;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
            padding: 12px 30px;
            font-weight: bold;
            text-decoration: none;
        }
        
        .btn-secondary-gov:hover {
            background-color: var(--primary-blue);
            color: white;
        }
        
        /* Footer */
        .main-footer {
            background-color: var(--dark-gray);
            color: white;
            padding: 30px 0 10px;
            margin-top: 50px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .site-title {
                font-size: 22px;
            }
            
            .page-title h1 {
                font-size: 24px;
            }
            
            .stat-number {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="site-title">TechCorp Learning Solutions</h1>
                    <p class="site-subtitle">Educational Technology Platform - Client Testimonials</p>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-light">Last Updated: <?php echo date('F j, Y'); ?></small>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg main-nav">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#services">
                            <i class="fas fa-cogs me-2"></i>Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#projects">
                            <i class="fas fa-folder me-2"></i>Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/testimonials">
                            <i class="fas fa-comments me-2"></i>Testimonials
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">
                            <i class="fas fa-envelope me-2"></i>Contact
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="adminLogin(); return false;">
                            <i class="fas fa-user-shield me-2"></i>Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Title -->
    <section class="page-title">
        <div class="container">
            <div class="text-center">
                <h1>Client Testimonials</h1>
                <p>Real feedback from our educational platform users</p>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Statistics Section -->
        <section class="data-section">
            <h3 class="section-header">
                <i class="fas fa-chart-bar me-2"></i>Testimonial Statistics (Database: testimonials table)
            </h3>
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo count($testimonials); ?></span>
                        <div class="stat-label">Total Reviews</div>
                        <small class="text-muted">From: testimonials table</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo count(array_filter($testimonials, function($t) { return $t['is_featured']; })); ?></span>
                        <div class="stat-label">Featured Reviews</div>
                        <small class="text-muted">is_featured = true</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number"><?php 
                            if (!empty($testimonials)) {
                                $avg = array_sum(array_column($testimonials, 'rating')) / count($testimonials);
                                echo number_format($avg, 1);
                            } else {
                                echo "0.0";
                            }
                        ?></span>
                        <div class="stat-label">Average Rating</div>
                        <small class="text-muted">AVG(rating) column</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo count(array_filter($testimonials, function($t) { return $t['rating'] == 5; })); ?></span>
                        <div class="stat-label">5-Star Reviews</div>
                        <small class="text-muted">rating = 5</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="data-section">
            <h3 class="section-header">
                <i class="fas fa-star me-2"></i>Client Testimonials (Database: testimonials table)
            </h3>
            <div class="data-box">
                <div class="data-box-header">
                    Customer Reviews - Dynamically Loaded from Database
                </div>
                <div class="data-box-content">
                    <?php if (!empty($testimonials)): ?>
                        <div class="row">
                            <?php foreach ($testimonials as $testimonial): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="testimonial-item">
                                        <div class="testimonial-header">
                                            <h5 class="testimonial-name">
                                                <?php echo htmlspecialchars($testimonial['name']); ?>
                                                <?php if ($testimonial['is_featured']): ?>
                                                    <span class="featured-badge">Featured</span>
                                                <?php endif; ?>
                                            </h5>
                                            <?php if (!empty($testimonial['position']) || !empty($testimonial['company'])): ?>
                                                <p class="testimonial-position">
                                                    <?php 
                                                    $position_parts = [];
                                                    if (!empty($testimonial['position'])) $position_parts[] = htmlspecialchars($testimonial['position']);
                                                    if (!empty($testimonial['company'])) $position_parts[] = htmlspecialchars($testimonial['company']);
                                                    echo implode(' at ', $position_parts);
                                                    ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="testimonial-content">
                                            <div class="testimonial-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : ' text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <small class="ms-2">(<?php echo $testimonial['rating']; ?>/5)</small>
                                            </div>
                                            <div class="testimonial-text">
                                                "<?php echo htmlspecialchars($testimonial['testimonial']); ?>"
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('F j, Y', strtotime($testimonial['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="/contact" class="btn btn-primary-gov me-3">
                                <i class="fas fa-plus me-2"></i>Add Your Review
                            </a>
                            <a href="/" class="btn btn-secondary-gov">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h4>No testimonials available</h4>
                            <p class="text-muted mb-4">Be the first to share your experience with our platform!</p>
                            <a href="/contact" class="btn btn-primary-gov">
                                <i class="fas fa-plus me-2"></i>Submit Your Review
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Database Information Section -->
        <section class="data-section">
            <h3 class="section-header">
                <i class="fas fa-database me-2"></i>Testimonials System Information
            </h3>
            <div class="data-box">
                <div class="data-box-header">
                    Technical Details - Database Structure
                </div>
                <div class="data-box-content">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Database Fields</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-field me-2 text-primary"></i><strong>name</strong> - Client name</li>
                                <li><i class="fas fa-field me-2 text-primary"></i><strong>testimonial</strong> - Review text</li>
                                <li><i class="fas fa-field me-2 text-primary"></i><strong>rating</strong> - Star rating (1-5)</li>
                                <li><i class="fas fa-field me-2 text-primary"></i><strong>company</strong> - Client company</li>
                                <li><i class="fas fa-field me-2 text-primary"></i><strong>position</strong> - Job title</li>
                                <li><i class="fas fa-field me-2 text-primary"></i><strong>is_featured</strong> - Featured flag</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Query Details</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-filter me-2 text-success"></i>WHERE is_active = true</li>
                                <li><i class="fas fa-sort me-2 text-success"></i>ORDER BY is_featured DESC</li>
                                <li><i class="fas fa-sort me-2 text-success"></i>ORDER BY created_at DESC</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Data sanitization applied</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 p-3" style="background-color: #f8f9fa; border-left: 4px solid var(--primary-blue);">
                        <small>
                            <strong>Learning Note:</strong> This testimonials system demonstrates dynamic content loading, 
                            conditional rendering, and database query optimization with proper sorting and filtering.
                        </small>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>TechCorp Learning Solutions</h5>
                    <p>Educational testimonials system demonstrating dynamic content and database integration.</p>
                    <div class="mt-3">
                        <small>
                            <i class="fas fa-info-circle me-2"></i>
                            All testimonials are dynamically loaded from the database with rating calculations.
                        </small>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="/" style="color: #ccc; text-decoration: none;">Home</a></li>
                        <li><a href="/contact" style="color: #ccc; text-decoration: none;">Contact</a></li>
                        <li><a href="/testimonials" style="color: #ccc; text-decoration: none;">Testimonials</a></li>
                        <li><a href="#" onclick="adminLogin(); return false;" style="color: #ccc; text-decoration: none;">Admin Panel</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Database Features</h6>
                    <ul class="list-unstyled">
                        <li style="color: #ccc;"><i class="fas fa-star me-1"></i>Rating system (1-5)</li>
                        <li style="color: #ccc;"><i class="fas fa-flag me-1"></i>Featured testimonials</li>
                        <li style="color: #ccc;"><i class="fas fa-sort me-1"></i>Smart sorting</li>
                        <li style="color: #ccc;"><i class="fas fa-chart-line me-1"></i>Statistics calculation</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color: #555; margin: 30px 0 20px 0;">
            <div class="row">
                <div class="col-md-8">
                    <small>&copy; <?php echo date('Y'); ?> TechCorp Learning Solutions. All rights reserved.</small>
                </div>
                <div class="col-md-4 text-end">
                    <small>
                        <i class="fas fa-database me-1"></i>
                        Powered by PHP & MySQL
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Admin Login & Testimonials Enhancement -->
    <script>
    // Admin popup authentication
    async function adminLogin() {
        const username = prompt('Enter admin username:');
        if (!username) return;
        
        const password = prompt('Enter admin password:');
        if (!password) return;
        
        try {
            const response = await fetch('/admin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `popup_login=1&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Login successful! Redirecting to admin panel...');
                window.location.href = '/admin';
            } else {
                alert('Invalid credentials. Please try again.');
            }
        } catch (error) {
            console.error('Login error:', error);
            alert('Network error. Please try again.');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Animate testimonial cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe all testimonial items
        document.querySelectorAll('.testimonial-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(item);
        });
        
        // Add click event to testimonial cards for better interactivity
        document.querySelectorAll('.testimonial-item').forEach(item => {
            item.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
        
        // Statistics counter animation
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 50);
        }
        
        // Animate stat numbers when they come into view
        const statObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                    entry.target.classList.add('animated');
                    const target = parseInt(entry.target.textContent);
                    animateCounter(entry.target, target);
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('.stat-number').forEach(stat => {
            statObserver.observe(stat);
        });
        
        console.log('Testimonials page loaded with <?php echo count($testimonials); ?> testimonials');
    });
    </script>
</body>
</html>
