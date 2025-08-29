<?php
require_once '../config/database.php';

// Simple authentication (In production, use proper authentication)
session_start();
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded credentials (In production, use database with hashed passwords)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $loginError = 'Invalid credentials';
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle database table creation
if ($isLoggedIn && isset($_GET['action']) && $_GET['action'] === 'create_tables') {
    try {
        $db->createTables();
        $successMessage = 'Database tables created successfully!';
    } catch (Exception $e) {
        $errorMessage = 'Error creating tables: ' . $e->getMessage();
    }
}

// Fetch data if logged in
if ($isLoggedIn) {
    try {
        $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 20");
        $testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC");
        $contactCount = $db->fetchOne("SELECT COUNT(*) as count FROM contacts")['count'] ?? 0;
        $testimonialCount = $db->fetchOne("SELECT COUNT(*) as count FROM testimonials")['count'] ?? 0;
        $newContactCount = $db->fetchOne("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'")['count'] ?? 0;
    } catch (Exception $e) {
        $errorMessage = 'Error fetching data: ' . $e->getMessage();
        $contacts = [];
        $testimonials = [];
        $contactCount = 0;
        $testimonialCount = 0;
        $newContactCount = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TechCorp Solutions</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .admin-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            padding-top: 2rem;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
            background: #f8fafc;
        }
        
        .admin-nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            display: block;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .admin-nav-link:hover, .admin-nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }
        
        .stat-widget {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-widget:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }
        
        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
        <!-- Login Form -->
        <div class="login-container">
            <div class="login-card">
                <div class="text-center mb-4">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h2>Admin Login</h2>
                    <p class="text-muted">Access the admin panel</p>
                </div>
                
                <?php if (isset($loginError)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($loginError); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <small class="text-muted">Default: admin</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Default: admin123</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <a href="index.html" class="text-muted">
                        <i class="fas fa-arrow-left me-2"></i>Back to Website
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="admin-sidebar">
            <div class="text-center text-white mb-4">
                <i class="fas fa-code fa-2x mb-2"></i>
                <h4>TechCorp Admin</h4>
            </div>
            
            <nav>
                <a href="#dashboard" class="admin-nav-link active" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="#contacts" class="admin-nav-link" data-tab="contacts">
                    <i class="fas fa-envelope me-2"></i>Contacts
                    <?php if ($newContactCount > 0): ?>
                        <span class="badge bg-warning ms-2"><?php echo $newContactCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#testimonials" class="admin-nav-link" data-tab="testimonials">
                    <i class="fas fa-star me-2"></i>Testimonials
                </a>
                <a href="#database" class="admin-nav-link" data-tab="database">
                    <i class="fas fa-database me-2"></i>Database
                </a>
                <a href="?action=logout" class="admin-nav-link">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </nav>
        </div>
        
        <div class="admin-content">
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Overview</h2>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <?php echo date('F j, Y'); ?>
                    </div>
                </div>
                
                <?php if (isset($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($successMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row mb-5">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-widget">
                            <div class="stat-number"><?php echo $contactCount; ?></div>
                            <h5>Total Contacts</h5>
                            <p class="text-muted mb-0">All contact submissions</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-widget">
                            <div class="stat-number text-warning"><?php echo $newContactCount; ?></div>
                            <h5>New Contacts</h5>
                            <p class="text-muted mb-0">Pending review</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-widget">
                            <div class="stat-number text-success"><?php echo $testimonialCount; ?></div>
                            <h5>Testimonials</h5>
                            <p class="text-muted mb-0">Client reviews</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-widget">
                            <div class="stat-number text-info">24/7</div>
                            <h5>Support</h5>
                            <p class="text-muted mb-0">Always online</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="data-table">
                            <div class="p-4 border-bottom">
                                <h5 class="mb-0">Recent Contact Submissions</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($contacts)): ?>
                                            <?php foreach (array_slice($contacts, 0, 5) as $contact): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($contact['subject'] ?: 'No Subject'); ?></td>
                                                    <td><?php echo formatDate($contact['created_at']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $contact['status'] === 'new' ? 'warning' : ($contact['status'] === 'read' ? 'info' : 'success'); ?>">
                                                            <?php echo ucfirst($contact['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    No contact submissions yet
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contacts Tab -->
            <div id="contacts" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Contact Submissions</h2>
                    <button class="btn btn-primary" onclick="refreshContacts()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
                
                <div class="data-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Company</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($contacts)): ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr>
                                            <td><?php echo $contact['id']; ?></td>
                                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['phone'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($contact['company'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($contact['subject'] ?: '-'); ?></td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($contact['message']); ?>">
                                                    <?php echo htmlspecialchars(truncateText($contact['message'], 50)); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($contact['created_at'], 'M j, Y H:i'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $contact['status'] === 'new' ? 'warning' : ($contact['status'] === 'read' ? 'info' : 'success'); ?>">
                                                    <?php echo ucfirst($contact['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            No contact submissions found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Testimonials Tab -->
            <div id="testimonials" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Client Testimonials</h2>
                    <button class="btn btn-primary" onclick="refreshTestimonials()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
                
                <div class="data-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Position</th>
                                    <th>Testimonial</th>
                                    <th>Rating</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($testimonials)): ?>
                                    <?php foreach ($testimonials as $testimonial): ?>
                                        <tr>
                                            <td><?php echo $testimonial['id']; ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['name']); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['company'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['position'] ?: '-'); ?></td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($testimonial['testimonial']); ?>">
                                                    <?php echo htmlspecialchars(truncateText($testimonial['testimonial'], 50)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? '' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $testimonial['is_featured'] ? 'warning' : 'secondary'; ?>">
                                                    <?php echo $testimonial['is_featured'] ? 'Featured' : 'Regular'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $testimonial['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $testimonial['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($testimonial['created_at'], 'M j, Y'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            No testimonials found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Database Tab -->
            <div id="database" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Database Management</h2>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="data-table p-4">
                            <h5 class="mb-3">Database Operations</h5>
                            <p class="text-muted mb-4">Manage your database tables and sample data.</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-plus-circle text-success me-2"></i>
                                                Create Tables
                                            </h6>
                                            <p class="card-text">Create all required database tables with sample data.</p>
                                            <a href="?action=create_tables" class="btn btn-success">
                                                <i class="fas fa-database me-2"></i>Create Tables
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-info-circle text-info me-2"></i>
                                                Database Info
                                            </h6>
                                            <p class="card-text">Current database connection status and information.</p>
                                            <div class="text-success">
                                                <i class="fas fa-check-circle me-2"></i>Connected
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Database Configuration</h6>
                                <p class="mb-2">Make sure to update the database configuration in <code>config/database.php</code> for your hosting environment.</p>
                                <ul class="mb-0">
                                    <li>For local development: Use localhost with your MySQL credentials</li>
                                    <li>For hosting: Update with your hosting provider's database details</li>
                                    <li>Tables will be created automatically when you click "Create Tables"</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Tab switching functionality
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    
                    // Remove active class from all links and content
                    document.querySelectorAll('.admin-nav-link').forEach(l => l.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('href').substring(1);
                    const tabContent = document.getElementById(tabId);
                    if (tabContent) {
                        tabContent.classList.add('active');
                    }
                }
            });
        });
        
        // Refresh functions
        function refreshContacts() {
            location.reload();
        }
        
        function refreshTestimonials() {
            location.reload();
        }
        
        // Auto-refresh every 30 seconds for new contacts
        setInterval(function() {
            // Could implement AJAX refresh here
        }, 30000);
    </script>
    
    <style>
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</body>
</html>
