<?php
require_once __DIR__ . '/config/database.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin');
        exit;
    } else {
        $loginError = 'Invalid credentials';
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /admin');
    exit;
}

if (!$isLoggedIn) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TechCorp Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Admin Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($loginError)): ?>
                            <div class="alert alert-danger"><?php echo $loginError; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCorp Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .admin-nav-link { color: rgba(255,255,255,0.8); border-radius: 8px; margin: 4px 0; }
        .admin-nav-link:hover, .admin-nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-3">
                <div class="text-center mb-4">
                    <h4 class="text-white">TechCorp Admin</h4>
                </div>
                
                <nav>
                    <a href="#dashboard" class="admin-nav-link active d-block p-3 text-decoration-none" data-tab="dashboard">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="#contacts" class="admin-nav-link d-block p-3 text-decoration-none" data-tab="contacts">
                        <i class="fas fa-envelope me-2"></i>Contacts
                    </a>
                    <a href="#testimonials" class="admin-nav-link d-block p-3 text-decoration-none" data-tab="testimonials">
                        <i class="fas fa-star me-2"></i>Testimonials
                    </a>
                    <a href="#database" class="admin-nav-link d-block p-3 text-decoration-none" data-tab="database">
                        <i class="fas fa-database me-2"></i>Database
                    </a>
                </nav>
                
                <div class="mt-auto pt-3">
                    <a href="?action=logout" class="admin-nav-link d-block p-3 text-decoration-none text-white-50">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Dashboard Tab -->
                <div id="dashboard" class="tab-content active">
                    <h2>Dashboard Overview</h2>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-primary">7</h3>
                                    <p>Total Contacts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-warning">0</h3>
                                    <p>New Contacts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-success">6</h3>
                                    <p>Testimonials</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-info">24/7</h3>
                                    <p>Support</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contacts Tab -->
                <div id="contacts" class="tab-content">
                    <h2>Contact Management</h2>
                    <p>Contact management functionality will be here.</p>
                </div>
                
                <!-- Testimonials Tab -->
                <div id="testimonials" class="tab-content">
                    <h2>Testimonial Management</h2>
                    <p>Testimonial management functionality will be here.</p>
                </div>
                
                <!-- Database Tab -->
                <div id="database" class="tab-content">
                    <h2>Database Management</h2>
                    <p>Database management functionality will be here.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('Script starting...');
        
        function showTab(tabName) {
            console.log('showTab called with:', tabName);
            
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.admin-nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab
            const selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.classList.add('active');
            }
            
            // Add active class to clicked nav link
            const selectedLink = document.querySelector(`[data-tab="${tabName}"]`);
            if (selectedLink) {
                selectedLink.classList.add('active');
            }
            
            return false;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            // Set up navigation event listeners
            document.querySelectorAll('.admin-nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabName = this.getAttribute('data-tab');
                    if (tabName) {
                        console.log('Navigation clicked for tab:', tabName);
                        showTab(tabName);
                    }
                });
            });
            
            console.log('Admin panel initialized successfully');
        });
    </script>
</body>
</html>
