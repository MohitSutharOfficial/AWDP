<?php
require_once '../config/database.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $company = sanitizeInput($_POST['company'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        
        // Validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !validateEmail($email)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required';
        }
        
        if (empty($errors)) {
            // Insert into database
            $contactData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'company' => $company,
                'subject' => $subject,
                'message' => $message
            ];
            
            $db->insert('contacts', $contactData);
            $success = true;
            
            // If this is an AJAX request, return JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
                exit;
            }
        } else {
            $error = implode(', ', $errors);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
    } catch (Exception $e) {
        $error = 'An error occurred while sending your message. Please try again.';
        error_log("Contact form error: " . $e->getMessage());
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - TechCorp Solutions</title>
    
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
                    <li class="nav-item"><a class="nav-link" href="testimonials.php">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
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
                    <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
                    <p class="lead">Get in touch with our team of experts</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <!-- Alert Container -->
            <div id="alertContainer">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        Thank you! Your message has been sent successfully. We'll get back to you soon.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <!-- Contact Information -->
                <div class="col-lg-4 mb-5">
                    <div class="contact-info-card h-100">
                        <h3 class="mb-4">Get In Touch</h3>
                        <p class="mb-4">Ready to start your next project? Contact us today for a free consultation.</p>
                        
                        <div class="contact-info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <h5>Address</h5>
                            <p>123 Tech Street<br>Digital City, DC 12345</p>
                        </div>
                        
                        <div class="contact-info-item">
                            <i class="fas fa-phone"></i>
                            <h5>Phone</h5>
                            <p>+1 (555) 123-4567</p>
                        </div>
                        
                        <div class="contact-info-item">
                            <i class="fas fa-envelope"></i>
                            <h5>Email</h5>
                            <p>info@techcorp.com</p>
                        </div>
                        
                        <div class="contact-info-item">
                            <i class="fas fa-clock"></i>
                            <h5>Business Hours</h5>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 10:00 AM - 4:00 PM<br>
                            Sunday: Closed</p>
                        </div>
                        
                        <div class="social-links mt-4">
                            <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="contact-form">
                        <h3 class="mb-4">Send Us a Message</h3>
                        <form id="contactForm" method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="company" class="form-label">Company</label>
                                    <input type="text" class="form-control" id="company" name="company"
                                           value="<?php echo isset($_POST['company']) ? htmlspecialchars($_POST['company']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <select class="form-control" id="subject" name="subject">
                                    <option value="">Select a subject</option>
                                    <option value="Web Development" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
                                    <option value="Mobile Development" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Mobile Development') ? 'selected' : ''; ?>>Mobile Development</option>
                                    <option value="Cloud Solutions" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Cloud Solutions') ? 'selected' : ''; ?>>Cloud Solutions</option>
                                    <option value="Consulting" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Consulting') ? 'selected' : ''; ?>>Consulting</option>
                                    <option value="Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Support') ? 'selected' : ''; ?>>Support</option>
                                    <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="6" required 
                                          placeholder="Tell us about your project..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="text-center mb-4">Find Us</h3>
                    <div class="map-container" style="height: 400px; background: #e9ecef; border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                            <h5>Interactive Map</h5>
                            <p class="text-muted">123 Tech Street, Digital City, DC 12345</p>
                            <!-- In a real implementation, you would embed Google Maps or another mapping service here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="section-subtitle">Quick answers to common questions</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How long does a typical project take?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Project timelines vary depending on complexity. Simple websites take 2-4 weeks, while complex applications can take 3-6 months. We provide detailed timelines during consultation.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What technologies do you work with?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We work with modern technologies including React, Vue.js, Angular, Node.js, Python, PHP, MySQL, PostgreSQL, AWS, and more. We choose the best technology stack for each project.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you provide ongoing support?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer comprehensive support and maintenance packages. This includes security updates, bug fixes, performance optimization, and feature enhancements.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    What is your pricing structure?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer flexible pricing based on project scope and requirements. We provide fixed-price quotes for defined projects and hourly rates for ongoing work. Contact us for a free consultation and quote.
                                </div>
                            </div>
                        </div>
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
