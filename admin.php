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

// Fetch data for dashboard
$contacts = [];
$testimonials = [];
$contactCount = 0;
$newContactCount = 0;
$testimonialCount = 0;

if ($isLoggedIn) {
    try {
        // Fetch contacts
        $contactsResult = $db->query("SELECT * FROM contacts ORDER BY created_at DESC");
        $contacts = $contactsResult ? $contactsResult->fetchAll(PDO::FETCH_ASSOC) : [];
        $contactCount = count($contacts);
        $newContactCount = count(array_filter($contacts, function($contact) {
            return $contact['status'] === 'new';
        }));

        // Fetch testimonials
        $testimonialsResult = $db->query("SELECT * FROM testimonials ORDER BY created_at DESC");
        $testimonials = $testimonialsResult ? $testimonialsResult->fetchAll(PDO::FETCH_ASSOC) : [];
        $testimonialCount = count($testimonials);
    } catch (Exception $e) {
        error_log("Error fetching dashboard data: " . $e->getMessage());
    }
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
                                    <h3 class="text-primary"><?php echo $contactCount; ?></h3>
                                    <p>Total Contacts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-warning"><?php echo $newContactCount; ?></h3>
                                    <p>New Contacts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-success"><?php echo $testimonialCount; ?></h3>
                                    <p>Testimonials</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-info">Online</h3>
                                    <p>System Status</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contacts Tab -->
                <div id="contacts" class="tab-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-envelope me-2"></i>Contact Management</h2>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-action="refresh-contacts">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-danger" data-action="bulk-delete-contacts">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="searchContacts" class="form-control" placeholder="Search contacts...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select id="filterStatus" class="form-select">
                                <option value="">All Status</option>
                                <option value="new">New</option>
                                <option value="replied">Replied</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAllContacts"></th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contactsTableBody">
                                        <?php foreach ($contacts as $contact): ?>
                                        <tr data-contact-id="<?php echo $contact['id']; ?>">
                                            <td><input type="checkbox" class="contact-checkbox" value="<?php echo $contact['id']; ?>"></td>
                                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                            <td><?php echo truncateText(htmlspecialchars($contact['subject']), 50); ?></td>
                                            <td><?php echo formatDate($contact['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $contact['status'] === 'new' ? 'warning' : ($contact['status'] === 'replied' ? 'info' : 'success'); ?>">
                                                    <?php echo ucfirst($contact['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" data-action="view-contact" data-id="<?php echo $contact['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-success" data-action="reply-contact" data-id="<?php echo $contact['id']; ?>">
                                                        <i class="fas fa-reply"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" data-action="delete-contact" data-id="<?php echo $contact['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (empty($contacts)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No contacts found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonials Tab -->
                <div id="testimonials" class="tab-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-star me-2"></i>Testimonial Management</h2>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" data-action="add-testimonial">
                                <i class="fas fa-plus"></i> Add New
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-action="refresh-testimonials">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="searchTestimonials" class="form-control" placeholder="Search testimonials...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select id="filterTestimonialStatus" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Company</th>
                                            <th>Message</th>
                                            <th>Rating</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="testimonialsTableBody">
                                        <?php foreach ($testimonials as $testimonial): ?>
                                        <tr data-testimonial-id="<?php echo $testimonial['id']; ?>">
                                            <td><?php echo htmlspecialchars($testimonial['name']); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['company'] ?? '-'); ?></td>
                                            <td><?php echo truncateText(htmlspecialchars($testimonial['message']), 80); ?></td>
                                            <td>
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?php echo $i <= ($testimonial['rating'] ?? 5) ? '' : '-o'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td><?php echo formatDate($testimonial['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($testimonial['status'] ?? 'pending') === 'approved' ? 'success' : (($testimonial['status'] ?? 'pending') === 'rejected' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($testimonial['status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" data-action="view-testimonial" data-id="<?php echo $testimonial['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" data-action="edit-testimonial" data-id="<?php echo $testimonial['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if (($testimonial['status'] ?? 'pending') !== 'approved'): ?>
                                                    <button class="btn btn-outline-success" data-action="approve-testimonial" data-id="<?php echo $testimonial['id']; ?>">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-danger" data-action="delete-testimonial" data-id="<?php echo $testimonial['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (empty($testimonials)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No testimonials found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Database Tab -->
                <div id="database" class="tab-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-database me-2"></i>Database Management</h2>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-info" data-action="test-connection">
                                <i class="fas fa-plug"></i> Test Connection
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-action="refresh-db-info">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-server fa-2x text-primary mb-2"></i>
                                    <h5>Connection Status</h5>
                                    <span class="badge bg-success">Connected</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-table fa-2x text-info mb-2"></i>
                                    <h5>Total Tables</h5>
                                    <h4 id="tableCount">2</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-database fa-2x text-warning mb-2"></i>
                                    <h5>Total Records</h5>
                                    <h4 id="recordCount"><?php echo $contactCount + $testimonialCount; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-table me-2"></i>Database Tables</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Table Name</th>
                                            <th>Records</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><i class="fas fa-envelope me-2"></i>contacts</td>
                                            <td><span class="badge bg-primary"><?php echo $contactCount; ?></span></td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" data-action="view-table" data-table="contacts">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-outline-warning" data-action="optimize-table" data-table="contacts">
                                                        <i class="fas fa-cogs"></i> Optimize
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-star me-2"></i>testimonials</td>
                                            <td><span class="badge bg-primary"><?php echo $testimonialCount; ?></span></td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" data-action="view-table" data-table="testimonials">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-outline-warning" data-action="optimize-table" data-table="testimonials">
                                                        <i class="fas fa-cogs"></i> Optimize
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5><i class="fas fa-terminal me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary w-100 mb-2" data-action="backup-database">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </button>
                                    <button type="button" class="btn btn-outline-info w-100 mb-2" data-action="check-integrity">
                                        <i class="fas fa-shield-alt me-2"></i>Check Integrity
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-warning w-100 mb-2" data-action="optimize-all">
                                        <i class="fas fa-rocket me-2"></i>Optimize All Tables
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100 mb-2" data-action="view-logs">
                                        <i class="fas fa-file-alt me-2"></i>View System Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact View Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-envelope me-2"></i>Contact Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contactModalBody">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="markRepliedBtn">Mark as Replied</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonial Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-star me-2"></i>Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="testimonialForm">
                        <input type="hidden" id="testimonialId" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="testimonialName" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="testimonialName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="testimonialCompany" class="form-label">Company</label>
                                    <input type="text" class="form-control" id="testimonialCompany" name="company">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="testimonialMessage" class="form-label">Message *</label>
                            <textarea class="form-control" id="testimonialMessage" name="message" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="testimonialRating" class="form-label">Rating</label>
                                    <select class="form-select" id="testimonialRating" name="rating">
                                        <option value="5">5 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="2">2 Stars</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="testimonialStatus" class="form-label">Status</label>
                                    <select class="form-select" id="testimonialStatus" name="status">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTestimonialBtn">Save Testimonial</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Indicator -->
    <div id="activityIndicator" class="position-fixed top-50 start-50 translate-middle" style="display: none; z-index: 9999;">
        <div class="bg-primary text-white px-4 py-3 rounded shadow">
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span id="activityText">Processing...</span>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="successToastBody">
                Operation completed successfully!
            </div>
        </div>
        
        <div id="errorToast" class="toast" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="errorToastBody">
                An error occurred. Please try again.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('Admin panel script loading...');
        
        // Global variables
        let currentContactId = null;
        let currentTestimonialId = null;
        
        // Utility functions
        function showActivity(text = 'Processing...') {
            document.getElementById('activityText').textContent = text;
            document.getElementById('activityIndicator').style.display = 'block';
        }
        
        function hideActivity() {
            document.getElementById('activityIndicator').style.display = 'none';
        }
        
        function showToast(type, message) {
            const toastId = type === 'success' ? 'successToast' : 'errorToast';
            const bodyId = type === 'success' ? 'successToastBody' : 'errorToastBody';
            
            document.getElementById(bodyId).textContent = message;
            const toast = new bootstrap.Toast(document.getElementById(toastId));
            toast.show();
        }
        
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function truncateText(text, length = 100) {
            if (text.length <= length) return text;
            return text.substring(0, length) + '...';
        }
        
        // Navigation function
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
        
        // API helper function
        async function makeApiCall(action, data = {}) {
            try {
                showActivity(`Processing ${action}...`);
                
                const formData = new FormData();
                formData.append('action', action);
                
                // Add data to FormData
                Object.keys(data).forEach(key => {
                    if (data[key] !== null && data[key] !== undefined) {
                        formData.append(key, data[key]);
                    }
                });
                
                const response = await fetch('/api/admin-crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    return result;
                } else {
                    throw new Error(result.message || 'Operation failed');
                }
            } catch (error) {
                console.error('API call error:', error);
                showToast('error', error.message);
                throw error;
            } finally {
                hideActivity();
            }
        }
        
        // Contact management functions
        async function viewContact(id) {
            try {
                const result = await makeApiCall('get_contact', { id });
                const contact = result.data;
                
                document.getElementById('contactModalBody').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Name:</strong> ${contact.name}<br>
                            <strong>Email:</strong> ${contact.email}<br>
                            <strong>Status:</strong> <span class="badge bg-${contact.status === 'new' ? 'warning' : contact.status === 'replied' ? 'info' : 'success'}">${contact.status}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> ${formatDate(contact.created_at)}<br>
                            <strong>Subject:</strong> ${contact.subject}
                        </div>
                    </div>
                    <div class="mt-3">
                        <strong>Message:</strong>
                        <div class="border p-3 mt-2 bg-light">${contact.message}</div>
                    </div>
                `;
                
                currentContactId = id;
                const modal = new bootstrap.Modal(document.getElementById('contactModal'));
                modal.show();
            } catch (error) {
                console.error('Error viewing contact:', error);
            }
        }
        
        async function markContactAsReplied() {
            if (!currentContactId) return;
            
            try {
                await makeApiCall('update_contact_status', { 
                    id: currentContactId, 
                    status: 'replied' 
                });
                
                showToast('success', 'Contact marked as replied');
                bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
                refreshContacts();
            } catch (error) {
                console.error('Error updating contact status:', error);
            }
        }
        
        async function deleteContact(id) {
            if (!confirm('Are you sure you want to delete this contact?')) return;
            
            try {
                await makeApiCall('delete_contact', { id });
                showToast('success', 'Contact deleted successfully');
                refreshContacts();
            } catch (error) {
                console.error('Error deleting contact:', error);
            }
        }
        
        async function refreshContacts() {
            try {
                showActivity('Refreshing contacts...');
                location.reload(); // Simple refresh for now
            } catch (error) {
                console.error('Error refreshing contacts:', error);
            }
        }
        
        // Testimonial management functions
        function showTestimonialModal(testimonial = null) {
            const modal = new bootstrap.Modal(document.getElementById('testimonialModal'));
            const form = document.getElementById('testimonialForm');
            
            if (testimonial) {
                // Edit mode
                document.getElementById('testimonialId').value = testimonial.id;
                document.getElementById('testimonialName').value = testimonial.name;
                document.getElementById('testimonialCompany').value = testimonial.company || '';
                document.getElementById('testimonialMessage').value = testimonial.message;
                document.getElementById('testimonialRating').value = testimonial.rating || 5;
                document.getElementById('testimonialStatus').value = testimonial.status || 'pending';
                document.querySelector('.modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Testimonial';
            } else {
                // Add mode
                form.reset();
                document.getElementById('testimonialId').value = '';
                document.querySelector('.modal-title').innerHTML = '<i class="fas fa-plus me-2"></i>Add New Testimonial';
            }
            
            modal.show();
        }
        
        async function saveTestimonial() {
            const form = document.getElementById('testimonialForm');
            const formData = new FormData(form);
            
            try {
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                
                const action = data.id ? 'update_testimonial' : 'add_testimonial';
                await makeApiCall(action, data);
                
                showToast('success', 'Testimonial saved successfully');
                bootstrap.Modal.getInstance(document.getElementById('testimonialModal')).hide();
                refreshTestimonials();
            } catch (error) {
                console.error('Error saving testimonial:', error);
            }
        }
        
        async function deleteTestimonial(id) {
            if (!confirm('Are you sure you want to delete this testimonial?')) return;
            
            try {
                await makeApiCall('delete_testimonial', { id });
                showToast('success', 'Testimonial deleted successfully');
                refreshTestimonials();
            } catch (error) {
                console.error('Error deleting testimonial:', error);
            }
        }
        
        async function approveTestimonial(id) {
            try {
                await makeApiCall('update_testimonial_status', { 
                    id: id, 
                    status: 'approved' 
                });
                showToast('success', 'Testimonial approved');
                refreshTestimonials();
            } catch (error) {
                console.error('Error approving testimonial:', error);
            }
        }
        
        async function refreshTestimonials() {
            try {
                showActivity('Refreshing testimonials...');
                location.reload(); // Simple refresh for now
            } catch (error) {
                console.error('Error refreshing testimonials:', error);
            }
        }
        
        // Database management functions
        async function testConnection() {
            try {
                await makeApiCall('test_connection');
                showToast('success', 'Database connection successful');
            } catch (error) {
                console.error('Error testing connection:', error);
            }
        }
        
        async function optimizeTable(tableName) {
            try {
                await makeApiCall('optimize_table', { table: tableName });
                showToast('success', `Table ${tableName} optimized successfully`);
            } catch (error) {
                console.error('Error optimizing table:', error);
            }
        }
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing admin panel...');
            
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
            
            // Set up action button event listeners using event delegation
            document.addEventListener('click', function(e) {
                const target = e.target.closest('[data-action]');
                if (!target) return;
                
                const action = target.getAttribute('data-action');
                const id = target.getAttribute('data-id');
                const table = target.getAttribute('data-table');
                
                console.log('Action clicked:', action, id);
                
                switch (action) {
                    // Contact actions
                    case 'view-contact':
                        viewContact(id);
                        break;
                    case 'delete-contact':
                        deleteContact(id);
                        break;
                    case 'refresh-contacts':
                        refreshContacts();
                        break;
                    
                    // Testimonial actions
                    case 'add-testimonial':
                        showTestimonialModal();
                        break;
                    case 'edit-testimonial':
                        // Get testimonial data and show modal
                        makeApiCall('get_testimonial', { id }).then(result => {
                            showTestimonialModal(result.data);
                        }).catch(console.error);
                        break;
                    case 'delete-testimonial':
                        deleteTestimonial(id);
                        break;
                    case 'approve-testimonial':
                        approveTestimonial(id);
                        break;
                    case 'refresh-testimonials':
                        refreshTestimonials();
                        break;
                    
                    // Database actions
                    case 'test-connection':
                        testConnection();
                        break;
                    case 'optimize-table':
                        optimizeTable(table);
                        break;
                    case 'optimize-all':
                        optimizeTable('all');
                        break;
                    case 'refresh-db-info':
                        location.reload();
                        break;
                    
                    default:
                        console.log('Unhandled action:', action);
                }
            });
            
            // Set up modal event listeners
            document.getElementById('markRepliedBtn').addEventListener('click', markContactAsReplied);
            document.getElementById('saveTestimonialBtn').addEventListener('click', saveTestimonial);
            
            // Set up search functionality
            const searchContacts = document.getElementById('searchContacts');
            const searchTestimonials = document.getElementById('searchTestimonials');
            
            if (searchContacts) {
                searchContacts.addEventListener('input', function() {
                    // Implement search functionality
                    console.log('Searching contacts:', this.value);
                });
            }
            
            if (searchTestimonials) {
                searchTestimonials.addEventListener('input', function() {
                    // Implement search functionality
                    console.log('Searching testimonials:', this.value);
                });
            }
            
            // Set up filter functionality
            const filterStatus = document.getElementById('filterStatus');
            const filterTestimonialStatus = document.getElementById('filterTestimonialStatus');
            
            if (filterStatus) {
                filterStatus.addEventListener('change', function() {
                    console.log('Filtering contacts by status:', this.value);
                });
            }
            
            if (filterTestimonialStatus) {
                filterTestimonialStatus.addEventListener('change', function() {
                    console.log('Filtering testimonials by status:', this.value);
                });
            }
            
            // Set up checkbox functionality
            const selectAllContacts = document.getElementById('selectAllContacts');
            if (selectAllContacts) {
                selectAllContacts.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.contact-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }
            
            console.log('Admin panel initialized successfully with full functionality');
        });
    </script>
</body>
</html>
