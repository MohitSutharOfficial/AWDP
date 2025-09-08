<?php
require_once 'config/database.php';

// Fetch dynamic content from database
$db = new Database();

// Get featured services
$featuredServices = $db->fetchAll("SELECT * FROM services WHERE is_active = true ORDER BY sort_order ASC, created_at DESC LIMIT 6");

// Get featured projects
$featuredProjects = $db->fetchAll("SELECT * FROM projects WHERE is_featured = true AND is_active = true ORDER BY created_at DESC LIMIT 3");

// Get featured testimonials
$featuredTestimonials = $db->fetchAll("SELECT * FROM testimonials WHERE is_featured = true AND is_active = true ORDER BY rating DESC, created_at DESC LIMIT 3");

// Get latest blog posts
$latestBlogPosts = $db->fetchAll("SELECT * FROM blog_posts WHERE is_published = true ORDER BY published_at DESC LIMIT 3");

// Get company stats
$stats = [
    'projects' => $db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE is_active = true")['count'] ?? 0,
    'clients' => $db->fetchOne("SELECT COUNT(DISTINCT client_name) as count FROM projects WHERE client_name IS NOT NULL AND client_name != ''")['count'] ?? 0,
    'services' => $db->fetchOne("SELECT COUNT(*) as count FROM services WHERE is_active = true")['count'] ?? 0,
    'testimonials' => $db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = true")['count'] ?? 0
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCorp Learning Solutions - Educational Technology Platform</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Simple Government-style CSS -->
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
        
        .main-nav .navbar-nav .nav-link:hover {
            background-color: var(--primary-blue);
            color: white;
        }
        
        /* Hero Section */
        .hero-banner {
            background-color: var(--light-gray);
            border: 2px solid var(--border-gray);
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .hero-content {
            text-align: center;
        }
        
        .hero-title {
            font-size: 32px;
            color: var(--primary-blue);
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .hero-description {
            font-size: 16px;
            color: var(--medium-gray);
            max-width: 800px;
            margin: 0 auto 25px;
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
        
        /* Service Cards */
        .service-item {
            background-color: white;
            border: 1px solid var(--border-gray);
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .service-title {
            color: var(--primary-blue);
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .service-description {
            color: var(--medium-gray);
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Project Cards */
        .project-item {
            background-color: white;
            border: 1px solid var(--border-gray);
            margin-bottom: 15px;
        }
        
        .project-header {
            background-color: var(--light-gray);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-gray);
        }
        
        .project-title {
            color: var(--primary-blue);
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }
        
        .project-content {
            padding: 15px 20px;
        }
        
        /* Testimonial Cards */
        .testimonial-item {
            background-color: var(--light-gray);
            border: 1px solid var(--border-gray);
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .testimonial-text {
            font-style: italic;
            color: var(--medium-gray);
            margin-bottom: 15px;
        }
        
        .testimonial-author {
            color: var(--primary-blue);
            font-weight: bold;
        }
        
        .testimonial-rating {
            color: var(--warning-orange);
            margin-bottom: 10px;
        }
        
        /* Buttons */
        .btn-primary-gov {
            background-color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
            color: white;
            padding: 10px 25px;
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
            padding: 10px 25px;
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
            .hero-title {
                font-size: 24px;
            }
            
            .site-title {
                font-size: 22px;
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
                    <p class="site-subtitle">Educational Technology Platform - Database-Driven Learning System</p>
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
                        <a class="nav-link" href="#home">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">
                            <i class="fas fa-cogs me-2"></i>Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#projects">
                            <i class="fas fa-folder me-2"></i>Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">
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

    <!-- Hero Banner -->
    <section class="hero-banner" id="home">
        <div class="container">
            <div class="hero-content">
                <h2 class="hero-title">Welcome to Our Learning Management System</h2>
                <p class="hero-description">
                    This is a database-driven educational platform designed for learning web development concepts. 
                    All data displayed on this website is dynamically fetched from our database, demonstrating 
                    real-world application development practices.
                </p>
                <div class="mt-4">
                    <a href="#services" class="btn btn-primary-gov me-3">
                        <i class="fas fa-arrow-right me-2"></i>Explore Services
                    </a>
                    <a href="/contact" class="btn btn-secondary-gov">
                        <i class="fas fa-phone me-2"></i>Get Started
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="container data-section">
        <h3 class="section-header">
            <i class="fas fa-chart-bar me-2"></i>Platform Statistics (Database Records)
        </h3>
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['projects']; ?></span>
                    <div class="stat-label">Active Projects</div>
                    <small class="text-muted">From: projects table</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['clients']; ?></span>
                    <div class="stat-label">Registered Clients</div>
                    <small class="text-muted">From: projects table</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['services']; ?></span>
                    <div class="stat-label">Available Services</div>
                    <small class="text-muted">From: services table</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['testimonials']; ?></span>
                    <div class="stat-label">Client Reviews</div>
                    <small class="text-muted">From: testimonials table</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="container data-section" id="services">
        <h3 class="section-header">
            <i class="fas fa-tools me-2"></i>Our Services (Database: services table)
        </h3>
        <div class="data-box">
            <div class="data-box-header">
                Available Services - Dynamically Loaded from Database
            </div>
            <div class="data-box-content">
                <?php if (!empty($featuredServices)): ?>
                    <div class="row">
                        <?php foreach ($featuredServices as $service): ?>
                        <div class="col-lg-6 col-md-12">
                            <div class="service-item">
                                <div class="service-title">
                                    <i class="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-cog'); ?> me-2"></i>
                                    <?php echo htmlspecialchars($service['title']); ?>
                                </div>
                                <div class="service-description">
                                    <?php echo htmlspecialchars($service['description']); ?>
                                </div>
                                <?php if (!empty($service['price_range'])): ?>
                                <div class="mt-2">
                                    <strong>Price Range:</strong> <?php echo htmlspecialchars($service['price_range']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No services found in database. Please add services through the admin panel.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <?php if (!empty($featuredProjects)): ?>
    <section class="container data-section" id="projects">
        <h3 class="section-header">
            <i class="fas fa-briefcase me-2"></i>Featured Projects (Database: projects table)
        </h3>
        <div class="data-box">
            <div class="data-box-header">
                Project Portfolio - Live Data from Database
            </div>
            <div class="data-box-content">
                <div class="row">
                    <?php foreach ($featuredProjects as $project): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="project-item">
                            <div class="project-header">
                                <h5 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                            </div>
                            <div class="project-content">
                                <p><?php echo htmlspecialchars(substr($project['description'], 0, 150)) . '...'; ?></p>
                                <?php if (!empty($project['client_name'])): ?>
                                <div class="mb-2">
                                    <strong>Client:</strong> <?php echo htmlspecialchars($project['client_name']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($project['project_url'])): ?>
                                <a href="<?php echo htmlspecialchars($project['project_url']); ?>" class="btn btn-secondary-gov btn-sm" target="_blank">
                                    <i class="fas fa-external-link-alt me-1"></i>View Project
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="container data-section" id="projects">
        <h3 class="section-header">
            <i class="fas fa-briefcase me-2"></i>Featured Projects (Database: projects table)
        </h3>
        <div class="data-box">
            <div class="data-box-content">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No featured projects found in database. Please add projects through the admin panel.
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <?php if (!empty($featuredTestimonials)): ?>
    <section class="container data-section" id="testimonials">
        <h3 class="section-header">
            <i class="fas fa-star me-2"></i>Client Testimonials (Database: testimonials table)
        </h3>
        <div class="data-box">
            <div class="data-box-header">
                Customer Feedback - Retrieved from Database
            </div>
            <div class="data-box-content">
                <div class="row">
                    <?php foreach ($featuredTestimonials as $testimonial): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonial-item">
                            <div class="testimonial-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : ' text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="testimonial-text">
                                "<?php echo htmlspecialchars($testimonial['testimonial']); ?>"
                            </div>
                            <div class="testimonial-author">
                                <?php echo htmlspecialchars($testimonial['name']); ?>
                                <?php if (!empty($testimonial['position']) && !empty($testimonial['company'])): ?>
                                <br><small class="text-muted">
                                    <?php echo htmlspecialchars($testimonial['position']); ?> at <?php echo htmlspecialchars($testimonial['company']); ?>
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="/testimonials" class="btn btn-secondary-gov">
                        <i class="fas fa-comments me-2"></i>View All Testimonials
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="container data-section" id="testimonials">
        <h3 class="section-header">
            <i class="fas fa-star me-2"></i>Client Testimonials (Database: testimonials table)
        </h3>
        <div class="data-box">
            <div class="data-box-content">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No testimonials found in database. Please add testimonials through the admin panel.
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Database Information Section -->
    <section class="container data-section">
        <h3 class="section-header">
            <i class="fas fa-database me-2"></i>Database Information - Learning Platform
        </h3>
        <div class="data-box">
            <div class="data-box-header">
                System Technical Details
            </div>
            <div class="data-box-content">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Database Tables</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-table me-2 text-primary"></i><strong>services</strong> - Service offerings</li>
                            <li><i class="fas fa-table me-2 text-primary"></i><strong>projects</strong> - Project portfolio</li>
                            <li><i class="fas fa-table me-2 text-primary"></i><strong>testimonials</strong> - Client reviews</li>
                            <li><i class="fas fa-table me-2 text-primary"></i><strong>contacts</strong> - Contact submissions</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Learning Objectives</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check me-2 text-success"></i>Database Integration</li>
                            <li><i class="fas fa-check me-2 text-success"></i>Dynamic Content Loading</li>
                            <li><i class="fas fa-check me-2 text-success"></i>CRUD Operations</li>
                            <li><i class="fas fa-check me-2 text-success"></i>Admin Panel Management</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-3 p-3" style="background-color: #f8f9fa; border-left: 4px solid var(--primary-blue);">
                    <small>
                        <strong>Note for Students:</strong> This website demonstrates how data flows from a database to the frontend. 
                        All content you see here is dynamically generated from our database tables, showing real-world web development practices.
                    </small>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Assignment Learning Platform</h5>
                    <p>Educational database-driven website demonstrating web development concepts.</p>
                    <div class="mt-3">
                        <small>
                            <i class="fas fa-info-circle me-2"></i>
                            This is a learning project showcasing PHP, MySQL, and web development practices.
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
                    <h6>Database Tables</h6>
                    <ul class="list-unstyled">
                        <li style="color: #ccc;"><i class="fas fa-table me-1"></i>services</li>
                        <li style="color: #ccc;"><i class="fas fa-table me-1"></i>projects</li>
                        <li style="color: #ccc;"><i class="fas fa-table me-1"></i>testimonials</li>
                        <li style="color: #ccc;"><i class="fas fa-table me-1"></i>contacts</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color: #555; margin: 30px 0 20px 0;">
            <div class="row">
                <div class="col-md-8">
                    <small>&copy; <?php echo date('Y'); ?> Assignment Learning Platform. All rights reserved.</small>
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
    
    <script>
        // Check if redirected from admin page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('admin_required') === '1') {
            setTimeout(() => {
                if (confirm('Admin access required. Would you like to login now?')) {
                    adminLogin();
                }
            }, 500);
        }
        
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
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255,255,255,0.98)';
            } else {
                navbar.style.background = 'rgba(255,255,255,0.95)';
            }
        });
    </script>
</body>
</html>
