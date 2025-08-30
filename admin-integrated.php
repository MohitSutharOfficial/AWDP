<?php
/**
 * Admin System Integration
 * This file provides seamless integration between current and enhanced admin systems
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/admin-config.php';
require_once __DIR__ . '/includes/navigation.php';

// Check if user is logged in
function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: admin-login.php');
        exit();
    }
}

// Get database connection
function getDBConnection() {
    try {
        return new Database();
    } catch (Exception $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Check admin authentication
checkAdminAuth();

// Initialize database connection
$db = getDBConnection();

// Determine which admin system to use
$useEnhancedAdmin = isset($_GET['enhanced']) ? true : (isset($_SESSION['use_enhanced_admin']) ? $_SESSION['use_enhanced_admin'] : false);

// Save preference in session
$_SESSION['use_enhanced_admin'] = $useEnhancedAdmin;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Professional Portfolio</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <?php if ($useEnhancedAdmin): ?>
    <!-- Enhanced Admin CSS -->
    <link href="assets/css/admin-enhanced.css" rel="stylesheet">
    <?php else: ?>
    <!-- Current Admin CSS -->
    <style>
        body { font-family: 'Inter', sans-serif; }
        .admin-sidebar { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
        .admin-nav-link:hover { background-color: rgba(255, 255, 255, 0.1); }
        .admin-nav-link.active { background-color: rgba(255, 255, 255, 0.2); font-weight: 600; }
    </style>
    <?php endif; ?>
    
    <style>
        /* System Switcher Styles */
        .system-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            border: 1px solid #ddd;
        }
        
        .system-switcher h6 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .switcher-buttons {
            display: flex;
            gap: 8px;
        }
        
        .switcher-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            color: #666;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .switcher-btn:hover {
            background: #e9ecef;
            color: #495057;
            text-decoration: none;
        }
        
        .switcher-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        @media (max-width: 768px) {
            .system-switcher {
                position: relative;
                top: auto;
                right: auto;
                margin: 20px;
                width: calc(100% - 40px);
            }
        }
    </style>
</head>
<body>

<!-- System Switcher -->
<div class="system-switcher d-none d-lg-block">
    <h6><i class="fas fa-cog me-2"></i>Admin System</h6>
    <div class="switcher-buttons">
        <a href="?enhanced=0" class="switcher-btn <?php echo !$useEnhancedAdmin ? 'active' : ''; ?>">
            <i class="fas fa-home me-1"></i>Current
        </a>
        <a href="?enhanced=1" class="switcher-btn <?php echo $useEnhancedAdmin ? 'active' : ''; ?>">
            <i class="fas fa-rocket me-1"></i>Enhanced
        </a>
    </div>
</div>

<?php if ($useEnhancedAdmin): ?>
    
    <!-- ENHANCED ADMIN SYSTEM -->
    <div class="admin-wrapper">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-brand">
                    <i class="fas fa-user-shield me-2"></i>
                    Admin Panel
                </a>
            </div>
            
            <div class="sidebar-menu">
                <?php foreach (AdminConfig::getMenuItems() as $item): ?>
                <a href="<?php echo $item['href']; ?>" class="admin-nav-link <?php echo $item['id'] === 'dashboard' ? 'active' : ''; ?>">
                    <i class="<?php echo $item['icon']; ?>"></i>
                    <span class="nav-label"><?php echo $item['label']; ?></span>
                    <?php if (isset($item['shortcut'])): ?>
                    <span class="keyboard-shortcut"><?php echo $item['shortcut']; ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <button class="mobile-toggle d-lg-none" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search anything... (Ctrl+K)">
                </div>
                
                <div class="header-actions">
                    <div class="user-menu">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="admin-content">
                <!-- Dashboard Tab (Default) -->
                <div id="dashboard" class="tab-content active">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1">Dashboard Overview</h1>
                            <p class="text-muted">Welcome back! Here's what's happening with your website.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-sync me-2"></i>Refresh
                            </button>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-download me-2"></i>Export Report
                            </button>
                        </div>
                    </div>
                    
                    <!-- Dashboard Stats -->
                    <div class="dashboard-grid">
                        <?php foreach (AdminConfig::getDashboardWidgets() as $widget): ?>
                        <div class="stat-card <?php echo $widget['color']; ?>" data-stat="<?php echo strtolower($widget['title']); ?>">
                            <div class="stat-header">
                                <h6 class="stat-title"><?php echo $widget['title']; ?></h6>
                                <div class="stat-icon">
                                    <i class="<?php echo $widget['icon']; ?>"></i>
                                </div>
                            </div>
                            <div class="stat-number">0</div>
                            <div class="stat-change">
                                <i class="fas fa-arrow-up"></i>
                                <span>Loading...</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-container">
                                <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Activity Overview</h5>
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Activity charts will be displayed here</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-container">
                                <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Recent activity will be shown here</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Other tabs will be loaded dynamically -->
                <div id="contacts" class="tab-content"></div>
                <div id="testimonials" class="tab-content"></div>
                <div id="services" class="tab-content"></div>
                <div id="projects" class="tab-content"></div>
                <div id="blog" class="tab-content"></div>
                <div id="newsletter" class="tab-content"></div>
                <div id="database" class="tab-content"></div>
            </div>
        </main>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay d-none">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Processing...</h5>
        </div>
    </div>

<?php else: ?>
    
    <!-- CURRENT ADMIN SYSTEM -->
    <?php include 'admin.php'; ?>

<?php endif; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($useEnhancedAdmin): ?>
<script src="assets/js/admin-enhanced.js"></script>
<script>
// Enhanced admin initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced Admin System Loaded');
    
    // Add any additional initialization here
    if (typeof admin !== 'undefined') {
        console.log('Admin dashboard initialized successfully');
    }
});
</script>
<?php else: ?>
<script>
// Current admin system JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Current Admin System Loaded');
    
    // Current admin functionality (existing JavaScript from admin.php)
    // This would include the existing sidebar toggle, AJAX functions, etc.
});
</script>
<?php endif; ?>

</body>
</html>
