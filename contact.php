<?php
require_once __DIR__ . '/config/database.php';

// Initialize database connection
try {
    $db = new Database();
} catch (Exception $e) {
    error_log("Database connection error in contact.php: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

// Helper functions
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

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
    
    <style>
        /* Enhanced Contact Page Styles */
        .contact-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .contact-info-item {
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .contact-info-item i {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .contact-info-item h5 {
            color: white;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .contact-info-item p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0;
            line-height: 1.5;
        }
        
        .contact-form {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        
        .form-control,
        .form-select {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f9fafb;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background-color: white;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            background-color: #fff5f5;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            transform: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .social-links a {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }
        
        .social-links a:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }
        
        .map-container {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        
        .map-container:hover {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        
        .section-title {
            color: #1f2937;
            font-weight: 700;
        }
        
        .section-subtitle {
            color: #6b7280;
        }
        
        /* Animation for form */
        .contact-form {
            animation: slideInUp 0.6s ease-out;
        }
        
        .contact-info-card {
            animation: slideInLeft 0.6s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .contact-info-card,
            .contact-form {
                padding: 1.5rem;
                border-radius: 15px;
            }
            
            .contact-info-item {
                margin-bottom: 1.5rem;
            }
            
            .social-links a {
                width: 40px;
                height: 40px;
                margin-right: 0.5rem;
            }
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

    <!-- Call to Action Section -->
    <section class="py-5 bg-gradient-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="mb-4">Ready to Start Your Project?</h2>
                    <p class="mb-4">Let's discuss how we can help bring your ideas to life with our innovative solutions.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="tel:+15551234567" class="btn btn-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Call Now
                        </a>
                        <a href="mailto:info@techcorp.com" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-envelope me-2"></i>Email Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <!-- Enhanced Contact Form Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.getElementById('contactForm');
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const alertContainer = document.getElementById('alertContainer');
        
        // Enhanced form submission with AJAX
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            
            try {
                const formData = new FormData(contactForm);
                
                const response = await fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                // Clear previous alerts
                alertContainer.innerHTML = '';
                
                if (result.success) {
                    // Show success message
                    alertContainer.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            ${result.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                    
                    // Reset form
                    contactForm.reset();
                    
                    // Scroll to alert
                    alertContainer.scrollIntoView({ behavior: 'smooth' });
                } else {
                    // Show error message
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${result.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                    
                    alertContainer.scrollIntoView({ behavior: 'smooth' });
                }
                
            } catch (error) {
                // Show error message
                alertContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred while sending your message. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                
                alertContainer.scrollIntoView({ behavior: 'smooth' });
            } finally {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
        
        // Form validation enhancement
        const inputs = contactForm.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                // Remove error state on input
                this.classList.remove('is-invalid');
                const feedback = this.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.remove();
                }
            });
        });
        
        function validateField(field) {
            const value = field.value.trim();
            let isValid = true;
            let message = '';
            
            // Remove previous validation
            field.classList.remove('is-invalid');
            const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }
            
            // Required field validation
            if (field.required && !value) {
                isValid = false;
                message = 'This field is required.';
            }
            
            // Email validation
            if (field.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    message = 'Please enter a valid email address.';
                }
            }
            
            // Phone validation
            if (field.type === 'tel' && value) {
                // More flexible phone regex that allows numbers starting with 0
                const phoneRegex = /^[\+]?[0-9][\d\s\-\(\)]{8,15}$/;
                if (!phoneRegex.test(value)) {
                    isValid = false;
                    message = 'Please enter a valid phone number.';
                }
            }
            
            if (!isValid) {
                field.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = message;
                field.parentNode.appendChild(feedback);
            }
            
            return isValid;
        }
        
        // Character counter for message field
        const messageField = document.getElementById('message');
        const maxLength = 1000;
        
        if (messageField) {
            const counterDiv = document.createElement('div');
            counterDiv.className = 'form-text text-end';
            counterDiv.id = 'messageCounter';
            messageField.parentNode.appendChild(counterDiv);
            
            function updateCounter() {
                const remaining = maxLength - messageField.value.length;
                counterDiv.textContent = `${messageField.value.length}/${maxLength} characters`;
                counterDiv.className = remaining < 50 ? 'form-text text-end text-warning' : 'form-text text-end text-muted';
            }
            
            messageField.addEventListener('input', updateCounter);
            updateCounter();
        }
    });
    </script>
</body>
</html>
