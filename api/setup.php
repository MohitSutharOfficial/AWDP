<?php
/**
 * Database Setup Script for TechCorp Solutions
 * 
 * Run this file once to create the database and tables
 * Visit: http://yoursite.com/setup.php
 */

require_once '../config/database.php';

$messages = [];

try {
    // Create tables
    $db->createTables();
    $messages[] = ['success', 'Database tables created successfully!'];
    
    // Verify tables were created
    $tables = ['contacts', 'testimonials', 'services', 'projects', 'blog_posts', 'newsletter_subscribers'];
    foreach ($tables as $table) {
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM $table");
            $messages[] = ['info', "Table '$table' created with " . $result['count'] . " records"];
        } catch (Exception $e) {
            $messages[] = ['warning', "Table '$table' might not exist: " . $e->getMessage()];
        }
    }
    
} catch (Exception $e) {
    $messages[] = ['danger', 'Error setting up database: ' . $e->getMessage()];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - TechCorp Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .setup-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="text-center mb-4">
            <i class="fas fa-database fa-3x text-primary mb-3"></i>
            <h2>Database Setup</h2>
            <p class="text-muted">TechCorp Solutions Database Initialization</p>
        </div>
        
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $message[0]; ?> alert-dismissible fade show">
                <?php 
                $icon = '';
                switch ($message[0]) {
                    case 'success': $icon = 'fas fa-check-circle'; break;
                    case 'info': $icon = 'fas fa-info-circle'; break;
                    case 'warning': $icon = 'fas fa-exclamation-triangle'; break;
                    case 'danger': $icon = 'fas fa-times-circle'; break;
                }
                ?>
                <i class="<?php echo $icon; ?> me-2"></i>
                <?php echo htmlspecialchars($message[1]); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <a href="index.html" class="btn btn-primary w-100">
                    <i class="fas fa-home me-2"></i>Go to Website
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="admin.php" class="btn btn-outline-primary w-100">
                    <i class="fas fa-cog me-2"></i>Admin Panel
                </a>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <strong>Note:</strong> Delete this file (setup.php) after setup is complete for security.
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
