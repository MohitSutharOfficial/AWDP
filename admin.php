<?php
require_once __DIR__ . '/config/database.php';

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
} catch (Exception $e) {
    error_log("Database connection error in admin.php: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    try {
        session_start();
    } catch (Exception $e) {
        error_log("Session start failed: " . $e->getMessage());
    }
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple authentication (use proper hashing in production)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
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

// Fetch data for dashboard
$contacts = [];
$testimonials = [];
$contactCount = 0;
$newContactCount = 0;
$testimonialCount = 0;

if ($isLoggedIn) {
    try {
        // Get contacts
        $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC");
        $contactCount = count($contacts);
        $newContactCount = count(array_filter($contacts, function($contact) {
            return $contact['status'] === 'new';
        }));
        
        // Get testimonials
        $testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC");
        $testimonialCount = count($testimonials);
    } catch (Exception $e) {
        error_log("Error fetching dashboard data: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCorp Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-sidebar {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            padding: 2rem 0;
        }
        
        .admin-nav-link {
            display: block;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        
        .admin-nav-link:hover, .admin-nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }
        
        .admin-nav-link kbd {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .stat-widget {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        
        .stat-widget:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .data-table {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 400px;
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
                    <p class="text-muted">Enter your credentials to access the admin panel</p>
                </div>
                
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
                    <button type="submit" name="login" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <small class="text-muted">Demo: admin / admin123</small>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="admin-sidebar">
            <div class="text-center text-white mb-4">
                <i class="fas fa-code fa-2x mb-2"></i>
                <h4>TechCorp Admin</h4>
                <small class="text-white-50">v2.0 Clean</small>
            </div>
            
            <nav>
                <a href="#dashboard" class="admin-nav-link active" data-tab="dashboard" onclick="return showTab('dashboard');">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    <kbd class="ms-auto">Ctrl+1</kbd>
                </a>
                <a href="#contacts" class="admin-nav-link" data-tab="contacts" onclick="return showTab('contacts');">
                    <i class="fas fa-envelope me-2"></i>Contacts
                    <?php if ($newContactCount > 0): ?>
                        <span class="badge bg-warning ms-2"><?php echo $newContactCount; ?></span>
                    <?php endif; ?>
                    <kbd class="ms-auto">Ctrl+2</kbd>
                </a>
                <a href="#testimonials" class="admin-nav-link" data-tab="testimonials" onclick="return showTab('testimonials');">
                    <i class="fas fa-star me-2"></i>Testimonials
                    <kbd class="ms-auto">Ctrl+3</kbd>
                </a>
                <a href="#database" class="admin-nav-link" data-tab="database" onclick="return showTab('database');">
                    <i class="fas fa-database me-2"></i>Database
                    <kbd class="ms-auto">Ctrl+4</kbd>
                </a>
                <a href="?action=logout" class="admin-nav-link text-white-50">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </nav>
        </div>

        <div class="admin-content">
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Overview</h2>
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-widget">
                            <div class="stat-number text-primary"><?php echo $contactCount; ?></div>
                            <h5>Total Contacts</h5>
                            <p class="text-muted mb-0">All submissions</p>
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
                    <div>
                        <button class="btn btn-success me-2" onclick="markAllRead()">
                            <i class="fas fa-check-double me-2"></i>Mark All Read
                        </button>
                        <button class="btn btn-primary" onclick="refreshContacts()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                    </div>
                </div>
                
                <div class="data-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="contactsTable">
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Testimonials Tab -->
            <div id="testimonials" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Client Testimonials</h2>
                    <div class="btn-group">
                        <button class="btn btn-success me-2" onclick="activateAllTestimonials()">
                            <i class="fas fa-toggle-on me-2"></i>Activate All
                        </button>
                        <button class="btn btn-secondary me-2" onclick="deactivateAllTestimonials()">
                            <i class="fas fa-toggle-off me-2"></i>Deactivate All
                        </button>
                        <button class="btn btn-primary" onclick="refreshTestimonials()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                    </div>
                </div>
                
                <div class="data-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="testimonialsTable">
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Database Tab -->
            <div id="database" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Database Management</h2>
                    <button class="btn btn-primary" onclick="refreshDatabase()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
                
                <div id="databaseContent">
                    <!-- Content loaded via JavaScript -->
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ===== GLOBAL VARIABLES =====
        let dataCache = {
            contacts: null,
            testimonials: null,
            stats: null
        };

        // ===== UTILITY FUNCTIONS =====
        function showLoading() {
            const existingLoader = document.querySelector('.loading-overlay');
            if (existingLoader) return;
            
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'loading-overlay';
            loadingOverlay.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(255, 255, 255, 0.8); display: flex;
                justify-content: center; align-items: center; z-index: 9999;
                backdrop-filter: blur(2px);
            `;
            
            loadingOverlay.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-primary">Loading...</div>
                </div>
            `;
            
            document.body.appendChild(loadingOverlay);
        }
        
        function hideLoading() {
            const loadingOverlay = document.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }
        
        function showNotification(message, type = 'info') {
            const existingNotifications = document.querySelectorAll('.notification-toast');
            existingNotifications.forEach(notification => notification.remove());
            
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 10000;
                min-width: 300px; max-width: 500px;
            `;
            
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    <div>${message}</div>
                    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification && notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // ===== NAVIGATION FUNCTION =====
        function showTab(tabName) {
            console.log('Showing tab:', tabName);
            
            try {
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
                
                // Load data for specific tabs
                setTimeout(() => {
                    if (tabName === 'contacts') {
                        loadContactsData();
                    } else if (tabName === 'testimonials') {
                        loadTestimonialsData();
                    } else if (tabName === 'database') {
                        loadDatabaseData();
                    }
                }, 100);
                
                return false;
            } catch (error) {
                console.error('Error in showTab:', error);
                return false;
            }
        }

        // ===== DATA LOADING FUNCTIONS =====
        async function loadContactsData() {
            console.log('Loading contacts data...');
            try {
                const response = await fetch('api/admin-crud.php?action=get_contacts');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                if (data.success) {
                    dataCache.contacts = data.data || [];
                    displayContacts(dataCache.contacts);
                    return dataCache.contacts;
                } else {
                    throw new Error(data.message || 'Failed to load contacts');
                }
            } catch (error) {
                console.error('Error loading contacts:', error);
                showNotification('Error loading contacts: ' + error.message, 'danger');
                
                const tableBody = document.querySelector('#contactsTable tbody');
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error loading contacts: ${error.message}
                            </td>
                        </tr>
                    `;
                }
                return [];
            }
        }
        
        async function loadTestimonialsData() {
            console.log('Loading testimonials data...');
            try {
                const response = await fetch('api/admin-crud.php?action=get_testimonials');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                if (data.success) {
                    dataCache.testimonials = data.data || [];
                    displayTestimonials(dataCache.testimonials);
                    return dataCache.testimonials;
                } else {
                    throw new Error(data.message || 'Failed to load testimonials');
                }
            } catch (error) {
                console.error('Error loading testimonials:', error);
                showNotification('Error loading testimonials: ' + error.message, 'danger');
                
                const tableBody = document.querySelector('#testimonialsTable tbody');
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error loading testimonials: ${error.message}
                            </td>
                        </tr>
                    `;
                }
                return [];
            }
        }
        
        async function loadDatabaseData() {
            console.log('Loading database data...');
            try {
                const response = await fetch('api/admin-crud.php?action=get_stats');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                if (data.success) {
                    displayDatabaseInfo(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load database stats');
                }
            } catch (error) {
                console.error('Error loading database data:', error);
                showNotification('Error loading database data: ' + error.message, 'danger');
                
                const databaseContent = document.getElementById('databaseContent');
                if (databaseContent) {
                    databaseContent.innerHTML = `
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-exclamation-triangle mb-3" style="font-size: 2rem;"></i>
                            <h5>Error Loading Database Data</h5>
                            <p>${error.message}</p>
                            <button class="btn btn-primary" onclick="loadDatabaseData()">
                                <i class="fas fa-redo me-2"></i>Try Again
                            </button>
                        </div>
                    `;
                }
            }
        }

        // ===== DISPLAY FUNCTIONS =====
        function displayContacts(contacts) {
            const tableBody = document.querySelector('#contactsTable tbody');
            if (!tableBody) return;
            
            if (!contacts || contacts.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox me-2"></i>No contact submissions found
                        </td>
                    </tr>
                `;
                return;
            }
            
            let tableRows = '';
            contacts.forEach(contact => {
                const statusBadge = contact.status === 'new' ? 'warning' : 
                                  contact.status === 'read' ? 'info' : 'success';
                
                const dateFormatted = contact.created_at ? 
                    new Date(contact.created_at).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    }) : 'N/A';
                
                tableRows += `
                    <tr data-id="${contact.id}">
                        <td>${contact.id}</td>
                        <td>${contact.name || 'N/A'}</td>
                        <td><a href="mailto:${contact.email}" class="text-decoration-none">${contact.email}</a></td>
                        <td>${contact.phone ? `<a href="tel:${contact.phone}" class="text-decoration-none">${contact.phone}</a>` : '-'}</td>
                        <td>${contact.company || '-'}</td>
                        <td>${contact.subject || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="showMessage('${contact.message || ''}')">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </td>
                        <td>${dateFormatted}</td>
                        <td><span class="badge bg-${statusBadge}">${contact.status ? contact.status.charAt(0).toUpperCase() + contact.status.slice(1) : 'Unknown'}</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info" onclick="viewContactDetails(${contact.id})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${contact.status === 'new' ? `
                                    <button class="btn btn-success" onclick="markAsRead(${contact.id})" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                ` : ''}
                                <button class="btn btn-danger" onclick="deleteContact(${contact.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = tableRows;
        }
        
        function displayTestimonials(testimonials) {
            const tableBody = document.querySelector('#testimonialsTable tbody');
            if (!tableBody) return;
            
            if (!testimonials || testimonials.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="fas fa-star me-2"></i>No testimonials found
                        </td>
                    </tr>
                `;
                return;
            }
            
            let tableRows = '';
            testimonials.forEach(testimonial => {
                const statusBadge = testimonial.is_active ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-secondary">Inactive</span>';
                
                const featuredBadge = testimonial.is_featured ? 
                    '<span class="badge bg-warning">Featured</span>' : 
                    '<span class="badge bg-secondary">Regular</span>';
                
                const stars = '★'.repeat(testimonial.rating || 5) + '☆'.repeat(5 - (testimonial.rating || 5));
                
                const dateFormatted = testimonial.created_at ? 
                    new Date(testimonial.created_at).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    }) : 'N/A';
                
                tableRows += `
                    <tr data-id="${testimonial.id}">
                        <td>${testimonial.id}</td>
                        <td>${testimonial.name || 'N/A'}</td>
                        <td>${testimonial.company || 'N/A'}</td>
                        <td>${testimonial.position || 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="showTestimonial('${(testimonial.testimonial || '').replace(/'/g, "\\'")}')">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </td>
                        <td><span class="text-warning">${stars}</span></td>
                        <td>${featuredBadge}</td>
                        <td>${statusBadge}</td>
                        <td>${dateFormatted}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info" onclick="viewTestimonial(${testimonial.id})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-warning" onclick="editTestimonial(${testimonial.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn ${testimonial.is_active ? 'btn-secondary' : 'btn-success'}" onclick="toggleTestimonialStatus(${testimonial.id})" title="${testimonial.is_active ? 'Deactivate' : 'Activate'}">
                                    <i class="fas fa-${testimonial.is_active ? 'toggle-off' : 'toggle-on'}"></i>
                                </button>
                                <button class="btn btn-danger" onclick="deleteTestimonial(${testimonial.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = tableRows;
        }
        
        function displayDatabaseInfo(stats) {
            const databaseContent = document.getElementById('databaseContent');
            if (!databaseContent) return;
            
            databaseContent.innerHTML = `
                <div class="row">
                    <div class="col-lg-8">
                        <div class="data-table p-4">
                            <h5 class="mb-3">
                                <i class="fas fa-database me-2 text-primary"></i>Database Statistics
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h3 class="text-success">${stats.total_contacts || 0}</h3>
                                            <p class="mb-0">Total Contacts</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h3 class="text-info">${stats.total_testimonials || 0}</h3>
                                            <p class="mb-0">Total Testimonials</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h3 class="text-warning">${stats.new_contacts || 0}</h3>
                                            <p class="mb-0">New Contacts</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Database connection is active and working properly.
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="data-table p-4">
                            <h5 class="mb-3">
                                <i class="fas fa-tools me-2 text-secondary"></i>Quick Actions
                            </h5>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="refreshDatabase()">
                                    <i class="fas fa-sync-alt me-2"></i>Refresh Statistics
                                </button>
                                
                                <button class="btn btn-outline-success" onclick="showNotification('Backup feature coming soon', 'info')">
                                    <i class="fas fa-download me-2"></i>Backup Database
                                </button>
                                
                                <button class="btn btn-outline-info" onclick="showNotification('Optimization feature coming soon', 'info')">
                                    <i class="fas fa-rocket me-2"></i>Optimize Tables
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // ===== REFRESH FUNCTIONS =====
        async function refreshContacts() {
            showLoading();
            try {
                dataCache.contacts = null;
                await loadContactsData();
                await updateDashboardStats();
                showNotification('Contacts refreshed successfully!', 'success');
            } catch (error) {
                showNotification('Error refreshing contacts: ' + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }
        
        async function refreshTestimonials() {
            showLoading();
            try {
                dataCache.testimonials = null;
                await loadTestimonialsData();
                await updateDashboardStats();
                showNotification('Testimonials refreshed successfully!', 'success');
            } catch (error) {
                showNotification('Error refreshing testimonials: ' + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }
        
        async function refreshDashboard() {
            showLoading();
            try {
                dataCache.stats = null;
                dataCache.contacts = null;
                dataCache.testimonials = null;
                await updateDashboardStats();
                showNotification('Dashboard refreshed successfully!', 'success');
            } catch (error) {
                showNotification('Error refreshing dashboard: ' + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }
        
        async function refreshDatabase() {
            showLoading();
            try {
                await loadDatabaseData();
                showNotification('Database stats refreshed successfully!', 'success');
            } catch (error) {
                showNotification('Error refreshing database: ' + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }

        // ===== REAL-TIME UPDATE FUNCTIONS =====
        async function updateDashboardStats() {
            try {
                const response = await fetch('api/admin-crud.php?action=get_stats');
                if (!response.ok) return;
                
                const data = await response.json();
                if (data.success) {
                    updateStatCards(data.data);
                    updateNavBadges(data.data);
                }
            } catch (error) {
                console.error('Error updating dashboard stats:', error);
            }
        }
        
        function updateStatCards(stats) {
            const statCards = [
                { selector: '.stat-widget:nth-child(1) .stat-number', value: stats.total_contacts || 0 },
                { selector: '.stat-widget:nth-child(2) .stat-number', value: stats.new_contacts || 0 },
                { selector: '.stat-widget:nth-child(3) .stat-number', value: stats.total_testimonials || 0 }
            ];
            
            statCards.forEach(card => {
                const element = document.querySelector(card.selector);
                if (element) {
                    element.style.transform = 'scale(1.1)';
                    element.textContent = card.value;
                    setTimeout(() => {
                        element.style.transform = 'scale(1)';
                    }, 200);
                }
            });
        }
        
        function updateNavBadges(stats) {
            const contactsBadge = document.querySelector('.admin-nav-link[data-tab="contacts"] .badge');
            if (contactsBadge && stats.new_contacts) {
                contactsBadge.textContent = stats.new_contacts;
                contactsBadge.style.animation = 'pulse 0.5s';
                setTimeout(() => {
                    contactsBadge.style.animation = '';
                }, 500);
            }
        }

        // ===== ACTION FUNCTIONS =====
        function showMessage(message) {
            alert(message || 'No message available');
        }
        
        function showTestimonial(testimonial) {
            alert(testimonial || 'No testimonial available');
        }
        
        function viewContactDetails(contactId) {
            // Find the contact in the loaded data
            fetch('api/admin-crud.php?action=get_contacts')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contact = data.data.find(c => c.id == contactId);
                    if (contact) {
                        // Create modal for contact details
                        const modalContent = `
                            <div class="modal fade" id="viewContactModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Contact Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Name:</strong> ${contact.name || 'N/A'}<br>
                                                    <strong>Email:</strong> <a href="mailto:${contact.email}">${contact.email}</a><br>
                                                    <strong>Phone:</strong> ${contact.phone ? `<a href="tel:${contact.phone}">${contact.phone}</a>` : 'N/A'}<br>
                                                    <strong>Company:</strong> ${contact.company || 'N/A'}<br>
                                                    <strong>Status:</strong> <span class="badge bg-${contact.status === 'new' ? 'warning' : contact.status === 'read' ? 'info' : 'success'}">${contact.status ? contact.status.charAt(0).toUpperCase() + contact.status.slice(1) : 'Unknown'}</span><br>
                                                    <strong>Date:</strong> ${new Date(contact.created_at).toLocaleString()}
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <strong>Subject:</strong>
                                                <p class="border p-2 mt-2">${contact.subject || 'No subject'}</p>
                                            </div>
                                            <div class="mt-3">
                                                <strong>Message:</strong>
                                                <p class="border p-3 mt-2" style="min-height: 100px; white-space: pre-wrap;">${contact.message || 'No message'}</p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            ${contact.status === 'new' ? `
                                                <button type="button" class="btn btn-success" onclick="markAsRead(${contact.id}); bootstrap.Modal.getInstance(document.getElementById('viewContactModal')).hide();">
                                                    <i class="fas fa-check me-2"></i>Mark as Read
                                                </button>
                                            ` : ''}
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Remove existing modal if any
                        const existingModal = document.getElementById('viewContactModal');
                        if (existingModal) {
                            existingModal.remove();
                        }
                        
                        // Add modal to page
                        document.body.insertAdjacentHTML('beforeend', modalContent);
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('viewContactModal'));
                        modal.show();
                    } else {
                        showNotification('Contact not found', 'danger');
                    }
                } else {
                    showNotification('Error loading contact details', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading contact details', 'danger');
            });
        }
        
        function markAsRead(contactId) {
            if (confirm('Mark this contact as read?')) {
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=mark_contact_read&contact_id=${contactId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadContactsData(); // Refresh the contacts list
                        updateDashboardStats(); // Update stats
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error marking contact as read', 'danger');
                });
            }
        }
        
        function deleteContact(contactId) {
            if (confirm('Are you sure you want to delete this contact? This action cannot be undone.')) {
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_contact&contact_id=${contactId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadContactsData(); // Refresh the contacts list
                        updateDashboardStats(); // Update stats
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting contact', 'danger');
                });
            }
        }
        
        function markAllRead() {
            if (confirm('Mark all unread contacts as read?')) {
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_all_read'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadContactsData(); // Refresh the contacts list
                        updateDashboardStats(); // Update stats
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error marking all contacts as read', 'danger');
                });
            }
        }
        
        function viewTestimonial(testimonialId) {
            // Find the testimonial in the loaded data
            fetch('api/admin-crud.php?action=get_testimonials')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const testimonial = data.data.find(t => t.id == testimonialId);
                    if (testimonial) {
                        // Create modal or show detailed view
                        const modalContent = `
                            <div class="modal fade" id="viewTestimonialModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Testimonial Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Name:</strong> ${testimonial.customer_name}<br>
                                                    <strong>Email:</strong> ${testimonial.email || 'N/A'}<br>
                                                    <strong>Rating:</strong> ${testimonial.rating}/5 ⭐<br>
                                                    <strong>Status:</strong> ${testimonial.is_active ? 'Active' : 'Inactive'}<br>
                                                    <strong>Date:</strong> ${new Date(testimonial.created_at).toLocaleDateString()}
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <strong>Testimonial:</strong>
                                                <p class="border p-3 mt-2">${testimonial.testimonial}</p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Remove existing modal if any
                        const existingModal = document.getElementById('viewTestimonialModal');
                        if (existingModal) {
                            existingModal.remove();
                        }
                        
                        // Add modal to page
                        document.body.insertAdjacentHTML('beforeend', modalContent);
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('viewTestimonialModal'));
                        modal.show();
                    } else {
                        showNotification('Testimonial not found', 'danger');
                    }
                } else {
                    showNotification('Error loading testimonial details', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading testimonial details', 'danger');
            });
        }
        
        function editTestimonial(testimonialId) {
            showNotification('Edit testimonial feature - coming soon', 'info');
        }
        
        function toggleTestimonialStatus(testimonialId) {
            if (confirm('Toggle testimonial active status?')) {
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=toggle_testimonial_status&testimonial_id=${testimonialId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadTestimonialsData(); // Refresh the testimonials list
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error toggling testimonial status', 'danger');
                });
            }
        }
        
        function deleteTestimonial(testimonialId) {
            if (confirm('Are you sure you want to delete this testimonial? This action cannot be undone.')) {
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_testimonial&testimonial_id=${testimonialId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadTestimonialsData(); // Refresh the testimonials list
                        updateDashboardStats(); // Update stats
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting testimonial', 'danger');
                });
            }
        }

        function activateAllTestimonials() {
            if (confirm('Activate all testimonials?')) {
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=activate_all_testimonials'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadTestimonialsData(); // Refresh the testimonials list
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error activating all testimonials', 'danger');
                });
            }
        }

        function deactivateAllTestimonials() {
            if (confirm('Deactivate all testimonials?')) {
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=deactivate_all_testimonials'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadTestimonialsData(); // Refresh the testimonials list
                    } else {
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deactivating all testimonials', 'danger');
                });
            }
        }

        // ===== INITIALIZATION =====
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin Panel Initialized');
            
            // Load initial dashboard data
            setTimeout(() => {
                updateDashboardStats();
            }, 500);
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key) {
                        case '1': e.preventDefault(); showTab('dashboard'); break;
                        case '2': e.preventDefault(); showTab('contacts'); break;
                        case '3': e.preventDefault(); showTab('testimonials'); break;
                        case '4': e.preventDefault(); showTab('database'); break;
                    }
                }
            });
        });
    </script>
</body>
</html>
