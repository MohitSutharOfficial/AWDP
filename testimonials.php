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
    $testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY is_featured DESC, created_at DESC");
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
    <title>Client Testimonials - TechCorp Solutions</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #e5e7eb;
            position: relative;
            overflow: hidden;
        }
        
        .testimonial-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .testimonial-card.featured {
            border: 2px solid #3b82f6;
            transform: scale(1.02);
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 1.5rem;
        }
        
        .rating-stars {
            color: #fbbf24;
            margin-bottom: 1rem;
        }
        
        .testimonial-text {
            font-style: italic;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #4b5563;
        }
        
        .testimonial-author {
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
        
        .author-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .author-position {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .featured-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html">
                <i class="fas fa-code text-primary me-2"></i>TechCorp
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#portfolio">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link active" href="testimonials.php">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="py-5 bg-gradient-primary text-white" style="margin-top: 80px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-3">Client Testimonials</h1>
                    <p class="lead">What our clients say about working with us</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">500+</div>
                        <h5>Projects Completed</h5>
                        <p class="text-muted">Successfully delivered projects</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">50+</div>
                        <h5>Happy Clients</h5>
                        <p class="text-muted">Satisfied customers worldwide</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">10+</div>
                        <h5>Years Experience</h5>
                        <p class="text-muted">Industry expertise</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">24/7</div>
                        <h5>Support</h5>
                        <p class="text-muted">Round-the-clock assistance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">What Our Clients Say</h2>
                    <p class="section-subtitle">Real feedback from real clients</p>
                </div>
            </div>
            
            <?php if (!empty($testimonials)): ?>
                <div class="row">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="testimonial-card <?php echo $testimonial['is_featured'] ? 'featured' : ''; ?>">
                                <?php if ($testimonial['is_featured']): ?>
                                    <div class="featured-badge">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </div>
                                <?php endif; ?>
                                
                                <div class="testimonial-avatar">
                                    <?php 
                                    if (!empty($testimonial['image_url'])) {
                                        echo '<img src="' . htmlspecialchars($testimonial['image_url']) . '" alt="' . htmlspecialchars($testimonial['name']) . '" class="w-100 h-100 object-cover rounded-circle">';
                                    } else {
                                        echo strtoupper(substr($testimonial['name'], 0, 1));
                                    }
                                    ?>
                                </div>
                                
                                <div class="rating-stars text-center">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? '' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                
                                <blockquote class="testimonial-text">
                                    "<?php echo htmlspecialchars($testimonial['testimonial']); ?>"
                                </blockquote>
                                
                                <div class="testimonial-author">
                                    <div class="author-name"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                                    <?php if (!empty($testimonial['position']) && !empty($testimonial['company'])): ?>
                                        <div class="author-position">
                                            <?php echo htmlspecialchars($testimonial['position']); ?> at <?php echo htmlspecialchars($testimonial['company']); ?>
                                        </div>
                                    <?php elseif (!empty($testimonial['company'])): ?>
                                        <div class="author-position"><?php echo htmlspecialchars($testimonial['company']); ?></div>
                                    <?php elseif (!empty($testimonial['position'])): ?>
                                        <div class="author-position"><?php echo htmlspecialchars($testimonial['position']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h4>No testimonials available</h4>
                            <p class="text-muted">Check back soon for client testimonials!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-gradient-secondary">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center text-white">
                    <h2 class="mb-4">Ready to Join Our Success Stories?</h2>
                    <p class="mb-4">Let's work together to create something amazing for your business.</p>
                    <a href="contact.php" class="btn btn-light btn-lg me-3">Start Your Project</a>
                    <a href="index.html#portfolio" class="btn btn-outline-light btn-lg">View Our Work</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Indicators -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h3>Trusted by Industry Leaders</h3>
                    <p class="text-muted">We've had the privilege of working with amazing companies</p>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-4 col-6 mb-4 text-center">
                    <div class="trust-logo">
                        <i class="fab fa-microsoft fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 text-center">
                    <div class="trust-logo">
                        <i class="fab fa-google fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 text-center">
                    <div class="trust-logo">
                        <i class="fab fa-amazon fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 text-center">
                    <div class="trust-logo">
                        <i class="fab fa-apple fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 text-center">
                    <div class="trust-logo">
                        <i class="fab fa-facebook fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 text-center">
                    <div class="trust-logo">
                        <i class="fab fa-twitter fa-3x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-code text-primary me-2"></i>TechCorp Solutions</h5>
                    <p class="mb-3">Innovative technology solutions for modern businesses.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.html#home" class="text-white-50">Home</a></li>
                        <li><a href="index.html#about" class="text-white-50">About</a></li>
                        <li><a href="index.html#services" class="text-white-50">Services</a></li>
                        <li><a href="contact.php" class="text-white-50">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h6>Services</h6>
                    <ul class="list-unstyled">
                        <li><span class="text-white-50">Web Development</span></li>
                        <li><span class="text-white-50">Mobile Apps</span></li>
                        <li><span class="text-white-50">Cloud Solutions</span></li>
                        <li><span class="text-white-50">Consulting</span></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h6>Contact Info</h6>
                    <p class="text-white-50 mb-1"><i class="fas fa-envelope me-2"></i>info@techcorp.com</p>
                    <p class="text-white-50 mb-1"><i class="fas fa-phone me-2"></i>+1 (555) 123-4567</p>
                    <p class="text-white-50"><i class="fas fa-map-marker-alt me-2"></i>123 Tech Street, Digital City</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <p class="text-white-50 mb-0">&copy; 2025 TechCorp Solutions. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
