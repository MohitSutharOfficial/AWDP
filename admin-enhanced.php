<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/navigation.php';

// Helper functions
function formatDate($dateString, $format = 'M j, Y') {
    if (empty($dateString)) return '-';
    try {
        $date = new DateTime($dateString);
        return $date->format($format);
    } catch (Exception $e) {
        return $dateString;
    }
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// Initialize database connection
try {
    $db = new Database();
    $adminStats = new AdminStats($db);
} catch (Exception $e) {
    error_log("Database connection error in admin.php: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

// Simple authentication (In production, use proper authentication)
// Start session with proper error handling
if (session_status() == PHP_SESSION_NONE) {
    try {
        session_start();
    } catch (Exception $e) {
        // If session fails, continue without session
        error_log("Session start failed: " . $e->getMessage());
    }
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];
    
    if (!$isLoggedIn) {
        $response['message'] = 'Unauthorized access';
        echo json_encode($response);
        exit;
    }
    
    // Redirect to main CRUD API
    include 'api/admin-crud.php';
    exit;
}

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
$allStats = [];
if ($isLoggedIn) {
    try {
        $allStats = $adminStats->getAllStats();
        $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
        $testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 5");
        $services = $db->fetchAll("SELECT * FROM services ORDER BY sort_order ASC LIMIT 5");
        $projects = $db->fetchAll("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
    } catch (Exception $e) {
        $errorMessage = 'Error fetching data: ' . $e->getMessage();
        $contacts = [];
        $testimonials = [];
        $services = [];
        $projects = [];
    }
}

$currentTab = $_GET['tab'] ?? 'dashboard';
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
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .admin-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            padding-top: 2rem;
            transition: transform 0.3s ease;
        }
        
        .admin-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            background: #f8fafc;
        }
        
        .admin-nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
            margin: 0.2rem 0;
        }
        
        .admin-nav-link:hover, .admin-nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
            transform: translateX(5px);
        }
        
        .admin-nav-link kbd {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.65rem;
            padding: 0.1rem 0.3rem;
            border-radius: 0.25rem;
            font-family: monospace;
        }
        
        .stat-widget {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
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
        
        .mobile-toggle {
            border-radius: 50% !important;
            width: 50px;
            height: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .mobile-overlay.show {
            display: block;
        }
        
        .progress-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .progress-card .progress {
            background: rgba(255, 255, 255, 0.2);
            height: 8px;
        }
        
        .progress-card .progress-bar {
            background: rgba(255, 255, 255, 0.9);
        }
        
        .action-buttons .btn {
            margin: 0 2px;
            border-radius: 8px;
            font-size: 0.8rem;
        }
        
        .search-filter-bar {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 992px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: 300px;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        
        .entity-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .crud-toolbar {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .bulk-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 0.9rem;
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
                    <a href="index.php" class="text-muted">
                        <i class="fas fa-arrow-left me-2"></i>Back to Website
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="admin-sidebar" id="adminSidebar">
            <div class="text-center text-white mb-4">
                <i class="fas fa-cogs fa-2x mb-2"></i>
                <h4>TechCorp Admin</h4>
                <small class="text-white-50">Enhanced CRUD System</small>
            </div>
            
            <?php echo Navigation::renderSidebar($currentTab, $allStats); ?>
        </div>
        
        <!-- Mobile Toggle Button -->
        <button class="mobile-toggle d-lg-none btn btn-primary position-fixed" id="mobileToggle" style="top: 15px; left: 15px; z-index: 9999;">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Mobile Overlay -->
        <div class="mobile-overlay d-lg-none" id="mobileOverlay"></div>
        
        <div class="admin-content">
            <?php echo Navigation::renderBreadcrumb($currentTab); ?>
            
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
            
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Overview</h2>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <?php echo date('F j, Y'); ?>
                    </div>
                </div>
                
                <div class="entity-stats">
                    <div class="stat-widget">
                        <div class="stat-number"><?php echo $allStats['contacts']['total'] ?? 0; ?></div>
                        <h5>Total Contacts</h5>
                        <?php echo Navigation::renderProgressBar($allStats['contacts']['new'] ?? 0, $allStats['contacts']['total'] ?? 1, 'New Contacts', 'warning'); ?>
                    </div>
                    
                    <div class="stat-widget">
                        <div class="stat-number"><?php echo $allStats['testimonials']['total'] ?? 0; ?></div>
                        <h5>Testimonials</h5>
                        <?php echo Navigation::renderProgressBar($allStats['testimonials']['featured'] ?? 0, $allStats['testimonials']['total'] ?? 1, 'Featured', 'success'); ?>
                    </div>
                    
                    <div class="stat-widget">
                        <div class="stat-number"><?php echo $allStats['services']['total'] ?? 0; ?></div>
                        <h5>Services</h5>
                        <?php echo Navigation::renderProgressBar($allStats['services']['active'] ?? 0, $allStats['services']['total'] ?? 1, 'Active Services', 'primary'); ?>
                    </div>
                    
                    <div class="stat-widget">
                        <div class="stat-number"><?php echo $allStats['projects']['total'] ?? 0; ?></div>
                        <h5>Projects</h5>
                        <?php echo Navigation::renderProgressBar($allStats['projects']['featured'] ?? 0, $allStats['projects']['total'] ?? 1, 'Featured Projects', 'info'); ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="data-table">
                            <div class="p-4 border-bottom">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="p-4">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="#contacts" class="btn btn-primary w-100 py-3" onclick="switchTab('contacts')">
                                            <i class="fas fa-envelope fa-2x mb-2"></i><br>
                                            Manage Contacts
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="#testimonials" class="btn btn-success w-100 py-3" onclick="switchTab('testimonials')">
                                            <i class="fas fa-star fa-2x mb-2"></i><br>
                                            Manage Testimonials
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="#services" class="btn btn-info w-100 py-3" onclick="switchTab('services')">
                                            <i class="fas fa-cogs fa-2x mb-2"></i><br>
                                            Manage Services
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="#projects" class="btn btn-warning w-100 py-3" onclick="switchTab('projects')">
                                            <i class="fas fa-briefcase fa-2x mb-2"></i><br>
                                            Manage Projects
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- CRUD Management Tabs -->
            <div id="contacts" class="tab-content"></div>
            <div id="testimonials" class="tab-content"></div>
            <div id="services" class="tab-content"></div>
            <div id="projects" class="tab-content"></div>
            <div id="blog" class="tab-content"></div>
            <div id="newsletter" class="tab-content"></div>
            <div id="database" class="tab-content"></div>
        </div>
    <?php endif; ?>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 9999;">
        <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
            <div class="spinner-border text-light mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Processing...</h5>
            <p class="mb-0">Please wait while we process your request</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Admin JS -->
    <script src="assets/js/admin-enhanced.js"></script>
    
    <?php echo ProgressTracker::updateProgressScript(); ?>
</body>
</html>
