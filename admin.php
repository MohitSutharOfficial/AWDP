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
    
    try {
        switch ($action) {
            case 'mark_contact_read':
                $contactId = intval($_POST['contact_id'] ?? 0);
                if ($contactId > 0) {
                    $db->execute("UPDATE contacts SET status = 'read' WHERE id = ?", [$contactId]);
                    $response['success'] = true;
                    $response['message'] = 'Contact marked as read';
                }
                break;
                
            case 'delete_contact':
                $contactId = intval($_POST['contact_id'] ?? 0);
                if ($contactId > 0) {
                    $db->execute("DELETE FROM contacts WHERE id = ?", [$contactId]);
                    $response['success'] = true;
                    $response['message'] = 'Contact deleted successfully';
                }
                break;
                
            case 'toggle_testimonial_status':
                $testimonialId = intval($_POST['testimonial_id'] ?? 0);
                if ($testimonialId > 0) {
                    $current = $db->fetchOne("SELECT is_active FROM testimonials WHERE id = ?", [$testimonialId]);
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE testimonials SET is_active = ? WHERE id = ?", [$newStatus, $testimonialId]);
                    $response['success'] = true;
                    $response['message'] = 'Testimonial status updated';
                }
                break;
                
            case 'toggle_testimonial_featured':
                $testimonialId = intval($_POST['testimonial_id'] ?? 0);
                if ($testimonialId > 0) {
                    $current = $db->fetchOne("SELECT is_featured FROM testimonials WHERE id = ?", [$testimonialId]);
                    $newStatus = $current['is_featured'] ? 0 : 1;
                    $db->execute("UPDATE testimonials SET is_featured = ? WHERE id = ?", [$newStatus, $testimonialId]);
                    $response['success'] = true;
                    $response['message'] = 'Featured status updated';
                }
                break;
                
            case 'add_testimonial':
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'company' => trim($_POST['company'] ?? ''),
                    'position' => trim($_POST['position'] ?? ''),
                    'testimonial' => trim($_POST['testimonial'] ?? ''),
                    'rating' => intval($_POST['rating'] ?? 5),
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_active' => 1
                ];
                
                if (!empty($data['name']) && !empty($data['testimonial'])) {
                    $db->execute(
                        "INSERT INTO testimonials (name, company, position, testimonial, rating, is_featured, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                        [$data['name'], $data['company'], $data['position'], $data['testimonial'], $data['rating'], $data['is_featured'], $data['is_active']]
                    );
                    $response['success'] = true;
                    $response['message'] = 'Testimonial added successfully';
                } else {
                    $response['message'] = 'Name and testimonial are required';
                }
                break;
                
            default:
                $response['message'] = 'Unknown action';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
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
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
        }
        
        .admin-nav-link:hover, .admin-nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
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
                width: 280px;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .feature-card {
                margin-bottom: 1rem;
            }
            
            .data-table {
                margin: 0 -0.5rem;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
        
        /* Enhanced animations */
        .tab-content {
            transition: opacity 0.3s ease;
        }
        
        .stat-widget {
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Better button styles */
        .btn-group-sm .btn {
            border-radius: 0.375rem;
            margin: 0 1px;
        }
        
        /* Modal improvements */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
        }
        
        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Global Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .loading-spinner {
            text-align: center;
        }
        
        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 4px;
        }
        
        /* Button loading states */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }
    </style>
</head>
<body>
    <!-- Global Loading Overlay -->
    <div id="globalLoading" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-3 text-primary fw-bold">Processing...</div>
        </div>
    </div>
    
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
        <div class="admin-sidebar" id="adminSidebar">
            <div class="text-center text-white mb-4">
                <i class="fas fa-code fa-2x mb-2"></i>
                <h4>TechCorp Admin</h4>
                <small class="text-white-50">v2.0 Enhanced</small>
            </div>
            
            <nav>
                <a href="#dashboard" class="admin-nav-link active" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    <kbd class="ms-auto">Ctrl+1</kbd>
                </a>
                <a href="#contacts" class="admin-nav-link" data-tab="contacts">
                    <i class="fas fa-envelope me-2"></i>Contacts
                    <?php if ($newContactCount > 0): ?>
                        <span class="badge bg-warning ms-2"><?php echo $newContactCount; ?></span>
                    <?php endif; ?>
                    <kbd class="ms-auto">Ctrl+2</kbd>
                </a>
                <a href="#testimonials" class="admin-nav-link" data-tab="testimonials">
                    <i class="fas fa-star me-2"></i>Testimonials
                    <kbd class="ms-auto">Ctrl+3</kbd>
                </a>
                <a href="#database" class="admin-nav-link" data-tab="database">
                    <i class="fas fa-database me-2"></i>Database
                    <kbd class="ms-auto">Ctrl+4</kbd>
                </a>
                <hr class="my-3 opacity-50">
                <a href="?action=logout" class="admin-nav-link text-white-50">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </nav>
        </div>
        
        <!-- Mobile Toggle Button -->
        <button class="mobile-toggle d-lg-none btn btn-primary position-fixed" id="mobileToggle" style="top: 15px; left: 15px; z-index: 9999;">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Mobile Overlay -->
        <div class="mobile-overlay d-lg-none" id="mobileOverlay"></div>
        
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
                                <?php if (!empty($contacts)): ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr data-id="<?php echo $contact['id']; ?>">
                                            <td><?php echo $contact['id']; ?></td>
                                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($contact['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if (!empty($contact['phone'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($contact['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($contact['company'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($contact['subject'] ?: '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="showMessage(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars(addslashes($contact['message'])); ?>')">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </button>
                                            </td>
                                            <td><?php echo formatDate($contact['created_at'], 'M j, Y H:i'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $contact['status'] === 'new' ? 'warning' : ($contact['status'] === 'read' ? 'info' : 'success'); ?>">
                                                    <?php echo ucfirst($contact['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($contact['status'] === 'new'): ?>
                                                        <button id="mark-read-<?php echo $contact['id']; ?>" class="btn btn-success" onclick="markAsRead(<?php echo $contact['id']; ?>)" title="Mark as Read">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button id="delete-contact-<?php echo $contact['id']; ?>" class="btn btn-danger" onclick="deleteContact(<?php echo $contact['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
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
                    <div>
                        <button class="btn btn-success me-2" onclick="showAddTestimonialModal()">
                            <i class="fas fa-plus me-2"></i>Add New
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
                                <?php if (!empty($testimonials)): ?>
                                    <?php foreach ($testimonials as $testimonial): ?>
                                        <tr data-id="<?php echo $testimonial['id']; ?>">
                                            <td><?php echo $testimonial['id']; ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['name']); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['company'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['position'] ?: '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="showTestimonial(<?php echo $testimonial['id']; ?>, '<?php echo htmlspecialchars(addslashes($testimonial['testimonial'])); ?>')">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </button>
                                            </td>
                                            <td>
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? '' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                    <small class="text-muted ms-1">(<?php echo $testimonial['rating']; ?>)</small>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-<?php echo $testimonial['is_featured'] ? 'warning' : 'outline-secondary'; ?>" 
                                                        onclick="toggleFeatured(<?php echo $testimonial['id']; ?>)">
                                                    <i class="fas fa-star me-1"></i>
                                                    <?php echo $testimonial['is_featured'] ? 'Featured' : 'Regular'; ?>
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-<?php echo $testimonial['is_active'] ? 'success' : 'danger'; ?>" 
                                                        onclick="toggleStatus(<?php echo $testimonial['id']; ?>)">
                                                    <i class="fas fa-<?php echo $testimonial['is_active'] ? 'check' : 'times'; ?> me-1"></i>
                                                    <?php echo $testimonial['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </button>
                                            </td>
                                            <td><?php echo formatDate($testimonial['created_at'], 'M j, Y'); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-primary" onclick="editTestimonial(<?php echo $testimonial['id']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button id="delete-testimonial-<?php echo $testimonial['id']; ?>" class="btn btn-danger" onclick="deleteTestimonial(<?php echo $testimonial['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
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
                    <button class="btn btn-primary" onclick="checkDatabaseStatus()">
                        <i class="fas fa-sync-alt me-2"></i>Check Status
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="data-table p-4">
                            <h5 class="mb-3">
                                <i class="fas fa-database me-2 text-primary"></i>
                                Database Operations
                            </h5>
                            <p class="text-muted mb-4">Manage your database tables and monitor connection status.</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-plus-circle text-success me-2"></i>
                                                Create Tables
                                            </h6>
                                            <p class="card-text">Initialize all required database tables with sample data.</p>
                                            <a href="?action=create_tables" class="btn btn-success">
                                                <i class="fas fa-database me-2"></i>Create Tables
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-info-circle text-info me-2"></i>
                                                Connection Status
                                            </h6>
                                            <p class="card-text">Current database connection information.</p>
                                            <div id="connectionStatus">
                                                <div class="text-success">
                                                    <i class="fas fa-check-circle me-2"></i>Connected
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-tools text-warning me-2"></i>
                                                Debug Information
                                            </h6>
                                            <p class="card-text">View detailed database debug information.</p>
                                            <a href="app-debug.php" target="_blank" class="btn btn-warning">
                                                <i class="fas fa-bug me-2"></i>Debug Page
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card border-secondary">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-cog text-secondary me-2"></i>
                                                Setup Page
                                            </h6>
                                            <p class="card-text">Run the initial setup and configuration.</p>
                                            <a href="setup.php" target="_blank" class="btn btn-secondary">
                                                <i class="fas fa-play me-2"></i>Setup
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="data-table p-4">
                            <h5 class="mb-3">
                                <i class="fas fa-chart-bar me-2 text-info"></i>
                                Database Statistics
                            </h5>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Total Contacts</span>
                                    <span class="badge bg-primary"><?php echo $contactCount; ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo min(100, ($contactCount / 100) * 100); ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>New Contacts</span>
                                    <span class="badge bg-warning"><?php echo $newContactCount; ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $contactCount > 0 ? ($newContactCount / $contactCount) * 100 : 0; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Testimonials</span>
                                    <span class="badge bg-success"><?php echo $testimonialCount; ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo min(100, ($testimonialCount / 50) * 100); ?>%"></div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <h6 class="text-muted">Database Type</h6>
                                <span class="badge bg-info">
                                    <?php 
                                    try {
                                        $driver = $db->getDriverName();
                                        echo ucfirst($driver);
                                    } catch (Exception $e) {
                                        echo 'Unknown';
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="text-muted">Connection Type</h6>
                                <span class="badge bg-secondary">
                                    <?php 
                                    try {
                                        $info = $db->getConnectionInfo();
                                        echo $info['type'] ?? 'Unknown';
                                    } catch (Exception $e) {
                                        echo 'Unknown';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-info-circle me-2"></i>Database Configuration</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Current Environment:</strong> <?php echo getenv('RAILWAY_ENVIRONMENT') ?: 'Development'; ?></p>
                            <p class="mb-2"><strong>Fallback Chain:</strong></p>
                            <ol class="mb-0">
                                <li>Railway Database URL</li>
                                <li>Supabase Transaction Pooler</li>
                                <li>SQLite (Local fallback)</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Features:</strong></p>
                            <ul class="mb-0">
                                <li>IPv4/IPv6 Compatible</li>
                                <li>Auto-connection fallback</li>
                                <li>Real-time monitoring</li>
                                <li>Error logging</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contact Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="messageContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonial Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="testimonialContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Testimonial Modal -->
    <div class="modal fade" id="addTestimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTestimonialForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="testimonialName" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="testimonialName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="testimonialCompany" class="form-label">Company</label>
                            <input type="text" class="form-control" id="testimonialCompany" name="company">
                        </div>
                        <div class="mb-3">
                            <label for="testimonialPosition" class="form-label">Position</label>
                            <input type="text" class="form-control" id="testimonialPosition" name="position">
                        </div>
                        <div class="mb-3">
                            <label for="testimonialText" class="form-label">Testimonial *</label>
                            <textarea class="form-control" id="testimonialText" name="testimonial" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="testimonialRating" class="form-label">Rating</label>
                            <select class="form-control" id="testimonialRating" name="rating">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="testimonialFeatured" name="is_featured">
                            <label class="form-check-label" for="testimonialFeatured">
                                Mark as Featured
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="add-testimonial-submit" type="submit" class="btn btn-success">Add Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Testimonial Modal -->
    <div class="modal fade" id="editTestimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editTestimonialForm">
                    <input type="hidden" id="editTestimonialId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editTestimonialName" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="editTestimonialName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTestimonialCompany" class="form-label">Company</label>
                            <input type="text" class="form-control" id="editTestimonialCompany" name="company">
                        </div>
                        <div class="mb-3">
                            <label for="editTestimonialPosition" class="form-label">Position</label>
                            <input type="text" class="form-control" id="editTestimonialPosition" name="position">
                        </div>
                        <div class="mb-3">
                            <label for="editTestimonialText" class="form-label">Testimonial *</label>
                            <textarea class="form-control" id="editTestimonialText" name="testimonial" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editTestimonialRating" class="form-label">Rating</label>
                            <select class="form-control" id="editTestimonialRating" name="rating">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="editTestimonialActive" name="is_active">
                            <label class="form-check-label" for="editTestimonialActive">
                                Active
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editTestimonialFeatured" name="is_featured">
                            <label class="form-check-label" for="editTestimonialFeatured">
                                Mark as Featured
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="edit-testimonial-submit" type="submit" class="btn btn-primary">Update Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentTab = 'dashboard';
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            showNotifications();
            
            // Test API connectivity on page load
            testAPIConnection();
        });
        
        // Test API connectivity
        function testAPIConnection() {
            console.log('Testing API connection...');
            fetch('api/admin-crud.php?action=test')
                .then(response => {
                    console.log('API test response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`API test failed: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API test successful:', data);
                    if (data.success) {
                        // API is working, load dashboard data
                        updateDashboardStats();
                        refreshContacts();
                        refreshTestimonials();
                    } else {
                        showNotification('API test failed: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('API test error:', error);
                    showNotification('API connection failed: ' + error.message, 'error');
                    
                    // Show detailed error in console for debugging
                    console.error('API Error Details:', {
                        url: 'api/admin-crud.php?action=test',
                        error: error.message,
                        timestamp: new Date().toISOString()
                    });
                });
        }
        
        // Initialize all event listeners
        function initializeEventListeners() {
            // Tab switching functionality
            document.querySelectorAll('.admin-nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                        switchTab(this.getAttribute('href').substring(1));
                    }
                });
            });
            
            // Add testimonial form submission
            const addTestimonialForm = document.getElementById('addTestimonialForm');
            if (addTestimonialForm) {
                addTestimonialForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitTestimonial();
                });
            }
            
            // Edit testimonial form submission
            const editTestimonialForm = document.getElementById('editTestimonialForm');
            if (editTestimonialForm) {
                editTestimonialForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    updateTestimonial();
                });
            }
            
            // Mobile sidebar toggle
            initializeMobileSidebar();
        }
        
        // Tab switching with better UX
        function switchTab(tabId) {
            // Remove active class from all links and content
            document.querySelectorAll('.admin-nav-link').forEach(l => l.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked link
            const activeLink = document.querySelector(`[href="#${tabId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
            
            // Show corresponding content with animation
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.classList.add('active');
                tabContent.style.opacity = '0';
                setTimeout(() => {
                    tabContent.style.opacity = '1';
                }, 50);
            }
            
            currentTab = tabId;
            
            // Load data when switching to specific tabs (without page reload)
            if (tabId === 'contacts') {
                // Only refresh if table exists and is empty or needs updating
                const table = document.getElementById('contactsTable');
                if (table && table.querySelector('tbody').children.length === 0) {
                    refreshContacts();
                }
            } else if (tabId === 'testimonials') {
                // Only refresh if table exists and is empty or needs updating
                const table = document.getElementById('testimonialsTable');
                if (table && table.querySelector('tbody').children.length === 0) {
                    refreshTestimonials();
                }
            }
        }
        
        // AJAX helper function
        function makeAjaxRequest(data, callback) {
            console.log('Making API request to:', 'api/admin-crud.php', 'with data:', data);
            
            fetch('api/admin-crud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => {
                console.log('API response status:', response.status);
                console.log('API response headers:', response.headers);
                
                if (!response.ok) {
                    // Get error details for better debugging
                    return response.text().then(text => {
                        console.error('API Error Response:', text);
                        let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                        
                        // Try to extract meaningful error from response
                        if (text) {
                            try {
                                const errorData = JSON.parse(text);
                                if (errorData.message) {
                                    errorMessage = errorData.message;
                                }
                            } catch (e) {
                                // If not JSON, use first 200 chars of response
                                errorMessage = text.substring(0, 200);
                            }
                        }
                        throw new Error(errorMessage);
                    });
                }
                
                return response.text();
            })
            .then(text => {
                console.log('Raw response text:', text);
                
                try {
                    const result = JSON.parse(text);
                    console.log('Parsed JSON result:', result);
                    
                    if (callback) callback(result);
                    if (result.message) {
                        showNotification(result.message, result.success ? 'success' : 'error');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text that failed to parse:', text);
                    showNotification('Server returned invalid response: ' + text.substring(0, 100), 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('Network error: ' + error.message, 'error');
            });
        }
        
        // Contact management functions
        function markAsRead(contactId) {
            // Show loading state
            showLoading();
            setButtonLoading(`mark-read-${contactId}`, true);
            
            makeAjaxRequest({
                action: 'mark_contact_read',
                contact_id: contactId
            }, function(result) {
                // Hide loading state
                hideLoading();
                setButtonLoading(`mark-read-${contactId}`, false);
                
                if (result.success) {
                    const row = document.querySelector(`#contactsTable tr[data-id="${contactId}"]`);
                    if (row) {
                        const statusBadge = row.querySelector('.badge');
                        statusBadge.className = 'badge bg-info';
                        statusBadge.textContent = 'Read';
                        
                        const actionBtn = row.querySelector('.btn-success');
                        if (actionBtn) actionBtn.remove();
                    }
                    updateDashboardStats();
                    showNotification('Contact marked as read', 'success');
                } else {
                    showNotification('Error marking contact as read', 'error');
                }
            });
        }
        
        function deleteContact(contactId) {
            if (confirm('Are you sure you want to delete this contact?')) {
                // Show loading state
                showLoading();
                setButtonLoading(`delete-contact-${contactId}`, true);
                
                // Set a timeout to ensure loading state clears even if request hangs
                const loadingTimeout = setTimeout(() => {
                    hideLoading();
                    setButtonLoading(`delete-contact-${contactId}`, false);
                    showNotification('Request timeout. Please try again.', 'error');
                }, 30000); // 30 second timeout
                
                makeAjaxRequest({
                    action: 'delete_contact',
                    contact_id: contactId
                }, function(result) {
                    clearTimeout(loadingTimeout);
                    hideLoading();
                    setButtonLoading(`delete-contact-${contactId}`, false);
                    
                    if (result.success) {
                        showNotification('Contact deleted successfully', 'success');
                        // Auto-refresh contacts data
                        refreshContacts();
                        updateDashboardStats();
                    } else {
                        showNotification(result.message || 'Error deleting contact', 'error');
                    }
                }, function(error) {
                    clearTimeout(loadingTimeout);
                    hideLoading();
                    setButtonLoading(`delete-contact-${contactId}`, false);
                    showNotification('Error deleting contact', 'error');
                });
            }
        }
        
        function markAllRead() {
            const newContacts = document.querySelectorAll('#contactsTable .badge.bg-warning');
            if (newContacts.length === 0) {
                showNotification('No new contacts to mark as read.', 'info');
                return;
            }
            
            if (confirm(`Mark all ${newContacts.length} new contacts as read?`)) {
                showLoading('Marking all contacts as read...');
                
                makeAjaxRequest('api/admin-crud.php?action=mark_all_contacts_read', {
                    method: 'POST'
                })
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadContacts(); // Refresh the contacts list
                        updateDashboardStats();
                    } else {
                        showNotification(data.message || 'Failed to mark all contacts as read', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error marking all as read:', error);
                    showNotification('Error marking all contacts as read', 'error');
                });
            }
        }
        
        function showMessage(contactId, message) {
            document.getElementById('messageContent').innerHTML = `
                <div class="alert alert-light">
                    <strong>Contact ID:</strong> ${contactId}
                </div>
                <div class="border rounded p-3" style="white-space: pre-wrap;">${message}</div>
            `;
            new bootstrap.Modal(document.getElementById('messageModal')).show();
        }
        
        // Testimonial management functions
        function toggleStatus(testimonialId) {
            makeAjaxRequest({
                action: 'toggle_testimonial_status',
                testimonial_id: testimonialId
            }, function(result) {
                if (result.success) {
                    location.reload(); // Simple reload for now
                }
            });
        }
        
        function toggleFeatured(testimonialId) {
            makeAjaxRequest({
                action: 'toggle_testimonial_featured',
                testimonial_id: testimonialId
            }, function(result) {
                if (result.success) {
                    location.reload(); // Simple reload for now
                }
            });
        }
        
        function showTestimonial(testimonialId, testimonial) {
            document.getElementById('testimonialContent').innerHTML = `
                <div class="alert alert-light">
                    <strong>Testimonial ID:</strong> ${testimonialId}
                </div>
                <div class="border rounded p-3" style="white-space: pre-wrap;">${testimonial}</div>
            `;
            new bootstrap.Modal(document.getElementById('testimonialModal')).show();
        }
        
        function showAddTestimonialModal() {
            document.getElementById('addTestimonialForm').reset();
            new bootstrap.Modal(document.getElementById('addTestimonialModal')).show();
        }
        
        function submitTestimonial() {
            const form = document.getElementById('addTestimonialForm');
            const formData = new FormData(form);
            formData.append('action', 'add_testimonial');
            
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            makeAjaxRequest(data, function(result) {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addTestimonialModal')).hide();
                    refreshTestimonials();
                }
            });
        }
        
        // Database management functions
        function checkDatabaseStatus() {
            const statusDiv = document.getElementById('connectionStatus');
            statusDiv.innerHTML = '<div class="text-info"><i class="fas fa-spinner fa-spin me-2"></i>Checking...</div>';
            
            fetch('/api/admin.php?action=get_stats')
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        statusDiv.innerHTML = `
                            <div class="text-success">
                                <i class="fas fa-check-circle me-2"></i>Connected
                            </div>
                            <small class="text-muted">Last checked: ${new Date().toLocaleTimeString()}</small>
                        `;
                        showNotification('Database connection verified', 'success');
                    } else {
                        statusDiv.innerHTML = `
                            <div class="text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Connection Error
                            </div>
                            <small class="text-muted">${result.message}</small>
                        `;
                        showNotification('Database connection failed', 'error');
                    }
                })
                .catch(error => {
                    statusDiv.innerHTML = `
                        <div class="text-danger">
                            <i class="fas fa-times-circle me-2"></i>Network Error
                        </div>
                        <small class="text-muted">Cannot reach server</small>
                    `;
                    showNotification('Network error occurred', 'error');
                });
        }
        
        // Enhanced refresh functions with API calls
        function refreshContacts() {
            if (document.getElementById('contactsTable')) {
                const table = document.getElementById('contactsTable');
                table.classList.add('loading');
                
                // Load contacts data via API without page reload
                fetch('api/admin-crud.php?action=get_contacts')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const tbody = table.querySelector('tbody');
                            // Clear the table completely first
                            tbody.innerHTML = '';
                            
                            // Build all rows at once to prevent layout shifts
                            let tableRows = '';
                            
                            data.data.forEach(contact => {
                                const statusBadge = contact.status === 'read' ? 
                                    '<span class="badge bg-info">Read</span>' : 
                                    '<span class="badge bg-warning">New</span>';
                                
                                const markReadBtn = contact.status !== 'read' ? 
                                    `<button id="mark-read-${contact.id}" class="btn btn-success btn-sm me-1" onclick="markAsRead(${contact.id})" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </button>` : '';
                                
                                tableRows += `
                                    <tr data-id="${contact.id}">
                                        <td>${contact.id}</td>
                                        <td>${contact.name}</td>
                                        <td>${contact.email}</td>
                                        <td>${contact.subject}</td>
                                        <td>${statusBadge}</td>
                                        <td>${new Date(contact.created_at).toLocaleDateString()}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                ${markReadBtn}
                                                <button class="btn btn-primary btn-sm me-1" onclick="viewContact(${contact.id})" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button id="delete-contact-${contact.id}" class="btn btn-danger btn-sm" onclick="deleteContact(${contact.id})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            // Set all rows at once to prevent UI jumps
                            tbody.innerHTML = tableRows;
                        }
                        table.classList.remove('loading');
                    })
                    .catch(error => {
                        console.error('Error refreshing contacts:', error);
                        table.classList.remove('loading');
                    });
            }
        }
        
        function refreshTestimonials() {
            if (document.getElementById('testimonialsTable')) {
                const table = document.getElementById('testimonialsTable');
                table.classList.add('loading');
                
                // Load testimonials data via API without page reload
                fetch('api/admin-crud.php?action=get_testimonials')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const tbody = table.querySelector('tbody');
                            // Clear the table completely first
                            tbody.innerHTML = '';
                            
                            // Build all rows at once to prevent layout shifts
                            let tableRows = '';
                            
                            data.data.forEach(testimonial => {
                                const statusBadge = testimonial.is_active ? 
                                    '<span class="badge bg-success">Active</span>' : 
                                    '<span class="badge bg-secondary">Inactive</span>';
                                
                                const featuredBadge = testimonial.is_featured ? 
                                    '<span class="badge bg-warning ms-1">Featured</span>' : '';
                                
                                const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
                                
                                tableRows += `
                                    <tr data-id="${testimonial.id}">
                                        <td>${testimonial.id}</td>
                                        <td>${testimonial.name}</td>
                                        <td>${testimonial.company || 'N/A'}</td>
                                        <td>${testimonial.position || 'N/A'}</td>
                                        <td class="text-warning">${stars}</td>
                                        <td>${statusBadge}${featuredBadge}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary btn-sm me-1" onclick="viewTestimonial(${testimonial.id})" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm me-1" onclick="editTestimonial(${testimonial.id})" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button id="delete-testimonial-${testimonial.id}" class="btn btn-danger btn-sm" onclick="deleteTestimonial(${testimonial.id})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            // Set all rows at once to prevent UI jumps
                            tbody.innerHTML = tableRows;
                        }
                        table.classList.remove('loading');
                    })
                    .catch(error => {
                        console.error('Error refreshing testimonials:', error);
                        table.classList.remove('loading');
                    });
            }
        }
        
        function updateDashboardStats() {
            // Update dashboard statistics via API without page reload
            fetch('api/admin-crud.php?action=get_stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stat numbers if elements exist
                        const totalContactsEl = document.querySelector('#totalContacts .stat-number');
                        const newContactsEl = document.querySelector('#newContacts .stat-number');
                        const totalTestimonialsEl = document.querySelector('#totalTestimonials .stat-number');
                        const activeTestimonialsEl = document.querySelector('#activeTestimonials .stat-number');
                        
                        if (totalContactsEl) totalContactsEl.textContent = data.data.total_contacts || 0;
                        if (newContactsEl) newContactsEl.textContent = data.data.new_contacts || 0;
                        if (totalTestimonialsEl) totalTestimonialsEl.textContent = data.data.total_testimonials || 0;
                        if (activeTestimonialsEl) activeTestimonialsEl.textContent = data.data.active_testimonials || 0;
                        
                        // Update sidebar badge for new contacts
                        const contactsBadge = document.querySelector('.admin-nav-link[data-tab="contacts"] .badge');
                        if (contactsBadge && data.data.new_contacts > 0) {
                            contactsBadge.textContent = data.data.new_contacts;
                        } else if (contactsBadge && data.data.new_contacts === 0) {
                            contactsBadge.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating dashboard stats:', error);
                });
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'error' ? 'alert-danger' : 'alert-info';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Loading system
        // Global loading timeout variable
        let globalLoadingTimeout = null;
        
        function showLoading(message = 'Processing...') {
            const loadingOverlay = document.getElementById('globalLoading');
            const loadingText = loadingOverlay.querySelector('.mt-3');
            loadingText.textContent = message;
            loadingOverlay.style.display = 'flex';
            
            // Clear any existing timeout
            if (globalLoadingTimeout) {
                clearTimeout(globalLoadingTimeout);
            }
            
            // Set a 60-second timeout to automatically hide loading
            globalLoadingTimeout = setTimeout(() => {
                hideLoading();
                showNotification('Operation timed out. Please try again.', 'error');
                console.warn('Global loading timeout triggered after 60 seconds');
            }, 60000);
        }
        
        function hideLoading() {
            const loadingOverlay = document.getElementById('globalLoading');
            loadingOverlay.style.display = 'none';
            
            // Clear the timeout when manually hiding loading
            if (globalLoadingTimeout) {
                clearTimeout(globalLoadingTimeout);
                globalLoadingTimeout = null;
            }
        }
        
        function setButtonLoading(button, loading = true) {
            if (loading) {
                button.disabled = true;
                button.classList.add('btn-loading');
                button.setAttribute('data-original-text', button.textContent);
                button.textContent = 'Processing...';
            } else {
                button.disabled = false;
                button.classList.remove('btn-loading');
                const originalText = button.getAttribute('data-original-text');
                if (originalText) {
                    button.textContent = originalText;
                    button.removeAttribute('data-original-text');
                }
            }
        }
        
        function showNotifications() {
            // Show any PHP-generated notifications
            const alerts = document.querySelectorAll('.alert:not(.position-fixed)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.transition = 'opacity 0.3s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            });
        }
        
        // Contact and Testimonial Management Functions
        function viewContact(contactId) {
            fetch(`api/admin-crud.php?action=get_contact&id=${contactId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const contact = data.data;
                        document.getElementById('contactContent').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${contact.name}</p>
                                    <p><strong>Email:</strong> ${contact.email}</p>
                                    <p><strong>Phone:</strong> ${contact.phone || 'N/A'}</p>
                                    <p><strong>Company:</strong> ${contact.company || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Subject:</strong> ${contact.subject}</p>
                                    <p><strong>Date:</strong> ${new Date(contact.created_at).toLocaleDateString()}</p>
                                    <p><strong>Status:</strong> ${contact.status === 'read' ? 'Read' : 'New'}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6>Message:</h6>
                                <div class="border p-3 bg-light">${contact.message}</div>
                            </div>
                        `;
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('contactModal'));
                        modal.show();
                        
                        // Mark as read if not already
                        if (contact.status !== 'read') {
                            markAsRead(contactId);
                        }
                    } else {
                        showNotification('Error loading contact details', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error loading contact details', 'error');
                });
        }
        
        function viewTestimonial(testimonialId) {
            fetch(`api/admin-crud.php?action=get_testimonial&id=${testimonialId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const testimonial = data.data;
                        const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
                        
                        document.getElementById('testimonialContent').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${testimonial.name}</p>
                                    <p><strong>Company:</strong> ${testimonial.company || 'N/A'}</p>
                                    <p><strong>Position:</strong> ${testimonial.position || 'N/A'}</p>
                                    <p><strong>Rating:</strong> <span class="text-warning">${stars}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> ${testimonial.is_active ? 'Active' : 'Inactive'}</p>
                                    <p><strong>Featured:</strong> ${testimonial.is_featured ? 'Yes' : 'No'}</p>
                                    <p><strong>Date:</strong> ${new Date(testimonial.created_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6>Testimonial:</h6>
                                <div class="border p-3 bg-light">${testimonial.testimonial}</div>
                            </div>
                        `;
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('testimonialModal'));
                        modal.show();
                    } else {
                        showNotification('Error loading testimonial details', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error loading testimonial details', 'error');
                });
        }
        
        function editTestimonial(testimonialId) {
            fetch(`api/admin-crud.php?action=get_testimonial&id=${testimonialId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const testimonial = data.data;
                        
                        // Fill the edit form
                        document.getElementById('editTestimonialId').value = testimonial.id;
                        document.getElementById('editTestimonialName').value = testimonial.name;
                        document.getElementById('editTestimonialCompany').value = testimonial.company || '';
                        document.getElementById('editTestimonialPosition').value = testimonial.position || '';
                        document.getElementById('editTestimonialText').value = testimonial.testimonial;
                        document.getElementById('editTestimonialRating').value = testimonial.rating;
                        document.getElementById('editTestimonialActive').checked = testimonial.is_active;
                        document.getElementById('editTestimonialFeatured').checked = testimonial.is_featured;
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('editTestimonialModal'));
                        modal.show();
                    } else {
                        showNotification('Error loading testimonial for editing', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error loading testimonial for editing', 'error');
                });
        }
        
        function deleteTestimonial(testimonialId) {
            if (confirm('Are you sure you want to delete this testimonial?')) {
                // Show loading state
                showLoading();
                setButtonLoading(`delete-testimonial-${testimonialId}`, true);
                
                // Set a timeout to ensure loading state clears even if request hangs
                const loadingTimeout = setTimeout(() => {
                    hideLoading();
                    setButtonLoading(`delete-testimonial-${testimonialId}`, false);
                    showNotification('Request timeout. Please try again.', 'error');
                }, 30000); // 30 second timeout
                
                fetch('api/admin-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_testimonial&testimonial_id=${testimonialId}`
                })
                .then(response => {
                    clearTimeout(loadingTimeout);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    return response.json();
                })
                .then(data => {
                    // Hide loading state
                    hideLoading();
                    setButtonLoading(`delete-testimonial-${testimonialId}`, false);
                    
                    if (data.success) {
                        showNotification('Testimonial deleted successfully', 'success');
                        // Auto-refresh testimonials data
                        refreshTestimonials();
                        updateDashboardStats();
                    } else {
                        showNotification(data.message || 'Error deleting testimonial', 'error');
                    }
                })
                .catch(error => {
                    clearTimeout(loadingTimeout);
                    console.error('Error:', error);
                    hideLoading();
                    setButtonLoading(`delete-testimonial-${testimonialId}`, false);
                    showNotification('Error deleting testimonial: ' + error.message, 'error');
                });
            }
        }
        
        function submitTestimonial() {
            const form = document.getElementById('addTestimonialForm');
            const formData = new FormData(form);
            formData.append('action', 'add_testimonial');
            
            // Show loading state
            showLoading();
            const submitBtn = form.querySelector('button[type="submit"]');
            setButtonLoading('add-testimonial-submit', true);
            if (submitBtn) submitBtn.disabled = true;
            
            // Convert FormData to URLSearchParams
            const params = new URLSearchParams();
            for (let [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            fetch('api/admin-crud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                hideLoading();
                setButtonLoading('add-testimonial-submit', false);
                if (submitBtn) submitBtn.disabled = false;
                
                if (data.success) {
                    showNotification('Testimonial added successfully', 'success');
                    form.reset();
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTestimonialModal'));
                    modal.hide();
                    
                    // Auto-refresh testimonials data
                    refreshTestimonials();
                    updateDashboardStats();
                } else {
                    showNotification(data.message || 'Error adding testimonial', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                setButtonLoading('add-testimonial-submit', false);
                if (submitBtn) submitBtn.disabled = false;
                showNotification('Error adding testimonial', 'error');
            });
        }
        
        function updateTestimonial() {
            const form = document.getElementById('editTestimonialForm');
            const formData = new FormData(form);
            formData.append('action', 'update_testimonial');
            
            // Show loading state
            showLoading();
            const submitBtn = form.querySelector('button[type="submit"]');
            setButtonLoading('edit-testimonial-submit', true);
            if (submitBtn) submitBtn.disabled = true;
            
            // Change 'id' to 'testimonial_id' for API compatibility
            const id = formData.get('id');
            formData.delete('id');
            formData.append('testimonial_id', id);
            
            // Convert FormData to URLSearchParams
            const params = new URLSearchParams();
            for (let [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            fetch('api/admin-crud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                hideLoading();
                setButtonLoading('edit-testimonial-submit', false);
                if (submitBtn) submitBtn.disabled = false;
                
                if (data.success) {
                    showNotification('Testimonial updated successfully', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTestimonialModal'));
                    modal.hide();
                    
                    // Auto-refresh testimonials data
                    refreshTestimonials();
                    updateDashboardStats();
                } else {
                    showNotification(data.message || 'Error updating testimonial', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                setButtonLoading('edit-testimonial-submit', false);
                if (submitBtn) submitBtn.disabled = false;
                showNotification('Error updating testimonial', 'error');
            });
        }
        
        // Mobile sidebar functionality
        function initializeMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            const toggleBtn = document.getElementById('mobileToggle');
            
            if (toggleBtn && sidebar && overlay) {
                // Toggle sidebar
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
                
                // Close sidebar when clicking overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
                
                // Close sidebar when clicking nav links on mobile
                document.querySelectorAll('.admin-nav-link').forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 992) {
                            sidebar.classList.remove('show');
                            overlay.classList.remove('show');
                        }
                    });
                });
                
                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                });
            }
        }
        
        // Auto-refresh for real-time updates
        setInterval(function() {
            if (currentTab === 'dashboard') {
                // Could implement AJAX refresh for dashboard stats
            }
        }, 30000);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        switchTab('dashboard');
                        break;
                    case '2':
                        e.preventDefault();
                        switchTab('contacts');
                        break;
                    case '3':
                        e.preventDefault();
                        switchTab('testimonials');
                        break;
                    case '4':
                        e.preventDefault();
                        switchTab('database');
                        break;
                }
            }
        });
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
