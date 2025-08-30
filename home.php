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
    <title>TechCorp Solutions - Innovative Technology Solutions</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --text-dark: #2d3748;
            --text-light: #718096;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, #fff, #f0f8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            color: rgba(255,255,255,0.9);
        }
        
        .btn-hero {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px 10px 0;
        }
        
        .btn-hero-primary {
            background: linear-gradient(45deg, #fff, #f8f9fa);
            color: var(--primary-color);
            border: none;
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 3rem;
        }
        
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .project-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            height: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .project-image {
            height: 200px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            height: 100%;
        }
        
        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }
        
        .rating {
            color: #ffc107;
            margin-bottom: 1rem;
        }
        
        .stats-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 4rem 0;
            color: white;
        }
        
        .stat-item {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar {
            background: rgba(255,255,255,0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .footer {
            background: var(--text-dark);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="#home">
                <i class="fas fa-rocket me-2"></i>TechCorp Solutions
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#projects">Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Innovative Technology Solutions</h1>
                        <p class="hero-subtitle">
                            We transform ideas into powerful digital experiences using cutting-edge technology and creative design. 
                            Building the future, one project at a time.
                        </p>
                        <div>
                            <a href="#services" class="btn-hero btn-hero-primary">Our Services</a>
                            <a href="/contact" class="btn-hero btn-hero-outline">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-laptop-code" style="font-size: 15rem; color: rgba(255,255,255,0.1);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['projects']; ?>+</div>
                        <div class="stat-label">Projects Completed</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['clients']; ?>+</div>
                        <div class="stat-label">Happy Clients</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['services']; ?>+</div>
                        <div class="stat-label">Services Offered</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['testimonials']; ?>+</div>
                        <div class="stat-label">Client Reviews</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Our Services</h2>
                    <p class="section-subtitle">Comprehensive solutions tailored to your business needs</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featuredServices as $service): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($service['description']); ?></p>
                        <?php if (!empty($service['price_range'])): ?>
                        <div class="text-primary fw-bold"><?php echo htmlspecialchars($service['price_range']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <?php if (!empty($featuredProjects)): ?>
    <section id="projects" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Featured Projects</h2>
                    <p class="section-subtitle">Showcasing our latest work and innovations</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featuredProjects as $project): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-image">
                            <?php if (!empty($project['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="w-100 h-100" style="object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-project-diagram"></i>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                            <?php if (!empty($project['project_url'])): ?>
                            <a href="<?php echo htmlspecialchars($project['project_url']); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>View Project
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <?php if (!empty($featuredTestimonials)): ?>
    <section id="testimonials" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">What Our Clients Say</h2>
                    <p class="section-subtitle">Real feedback from satisfied customers</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featuredTestimonials as $testimonial): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-avatar">
                            <?php if (!empty($testimonial['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($testimonial['image_url']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : ' text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-3">"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                        <h6 class="mb-1"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                        <?php if (!empty($testimonial['position']) && !empty($testimonial['company'])): ?>
                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['position']); ?> at <?php echo htmlspecialchars($testimonial['company']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="/testimonials" class="btn btn-outline-primary">View All Testimonials</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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
