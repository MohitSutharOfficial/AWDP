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
    <title>Contact Us - TechCorp Learning Solutions</title>
    
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
        
        /* Contact Info Cards */
        .contact-info-item {
            background-color: white;
            border: 1px solid var(--border-gray);
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .contact-info-item h5 {
            color: var(--primary-blue);
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .contact-info-item p {
            color: var(--medium-gray);
            margin: 0;
        }
        
        /* Form Elements */
        .form-control, .form-select {
            border: 2px solid var(--border-gray);
            padding: 12px 15px;
            font-size: 14px;
            background-color: var(--light-gray);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: none;
            background-color: white;
        }
        
        .form-label {
            font-weight: bold;
            color: var(--dark-gray);
            margin-bottom: 8px;
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
        
        /* Alerts */
        .alert {
            border: 2px solid;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            border-color: var(--success-green);
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            border-color: #dc3545;
            background-color: #f8d7da;
            color: #721c24;
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
                    <p class="site-subtitle">Educational Technology Platform - Contact & Support</p>
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
                        <a class="nav-link" href="/testimonials">
                            <i class="fas fa-comments me-2"></i>Testimonials
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/contact">
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
                <h1>Contact Us</h1>
                <p>Get in touch with our educational technology team</p>
            </div>
        </div>
    </section>

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

        <!-- Contact Information Section -->
        <section class="data-section">
            <h3 class="section-header">
                <i class="fas fa-info-circle me-2"></i>Contact Information (Database: contacts table)
            </h3>
            <div class="data-box">
                <div class="data-box-header">
                    Get in Touch - Educational Support Team
                </div>
                <div class="data-box-content">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="contact-info-item">
                                <h5><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
                                <p>123 Learning Street<br>Education City, EC 12345</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="contact-info-item">
                                <h5><i class="fas fa-phone me-2"></i>Phone</h5>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="contact-info-item">
                                <h5><i class="fas fa-envelope me-2"></i>Email</h5>
                                <p>support@techcorp-learning.com</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="contact-info-item">
                                <h5><i class="fas fa-clock me-2"></i>Hours</h5>
                                <p>Mon-Fri: 9:00 AM - 6:00 PM<br>Sat: 10:00 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="data-section">
            <h3 class="section-header">
                <i class="fas fa-paper-plane me-2"></i>Send Message (Saves to: contacts table)
            </h3>
            <div class="data-box">
                <div class="data-box-header">
                    Contact Form - All submissions stored in database
                </div>
                <div class="data-box-content">
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
                                <label for="company" class="form-label">Institution/Company</label>
                                <input type="text" class="form-control" id="company" name="company"
                                       value="<?php echo isset($_POST['company']) ? htmlspecialchars($_POST['company']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject Category</label>
                            <select class="form-control" id="subject" name="subject">
                                <option value="">Select a subject</option>
                                <option value="Learning Platform" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Learning Platform') ? 'selected' : ''; ?>>Learning Platform</option>
                                <option value="Technical Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="Database Issues" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Database Issues') ? 'selected' : ''; ?>>Database Issues</option>
                                <option value="Educational Content" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Educational Content') ? 'selected' : ''; ?>>Educational Content</option>
                                <option value="Account Access" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Account Access') ? 'selected' : ''; ?>>Account Access</option>
                                <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required 
                                      placeholder="Please describe your inquiry or issue..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-gov">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Database Information Section -->
        <section class="data-section">
            <h3 class="section-header">
                <i class="fas fa-database me-2"></i>Contact System Information
            </h3>
            <div class="data-box">
                <div class="data-box-header">
                    Technical Details - Database Integration
                </div>
                <div class="data-box-content">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Contact Form Features</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check me-2 text-success"></i>Real-time form validation</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Database storage (contacts table)</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Input sanitization</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Email validation</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Data Security</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-shield-alt me-2 text-primary"></i>Secure data handling</li>
                                <li><i class="fas fa-shield-alt me-2 text-primary"></i>SQL injection prevention</li>
                                <li><i class="fas fa-shield-alt me-2 text-primary"></i>XSS protection</li>
                                <li><i class="fas fa-shield-alt me-2 text-primary"></i>Error logging</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 p-3" style="background-color: #f8f9fa; border-left: 4px solid var(--primary-blue);">
                        <small>
                            <strong>Learning Note:</strong> This contact form demonstrates secure data handling practices 
                            including input validation, sanitization, and safe database operations.
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
                    <p>Educational contact system demonstrating secure form handling and database integration.</p>
                    <div class="mt-3">
                        <small>
                            <i class="fas fa-info-circle me-2"></i>
                            All contact submissions are securely stored in the database with proper validation.
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
                    <h6>Database Table</h6>
                    <ul class="list-unstyled">
                        <li style="color: #ccc;"><i class="fas fa-table me-1"></i>contacts - Form submissions</li>
                        <li style="color: #ccc;"><i class="fas fa-database me-1"></i>Secure data storage</li>
                        <li style="color: #ccc;"><i class="fas fa-shield-alt me-1"></i>Input validation</li>
                        <li style="color: #ccc;"><i class="fas fa-lock me-1"></i>SQL injection protection</li>
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
    
    <!-- Admin Login & Form Enhancement -->
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
