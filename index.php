<?php
// Railway-compatible entry point for TechCorp Solutions
// Clean and simple routing for PHP application

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$request = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request, PHP_URL_PATH);
$path = ltrim($path, '/');

// Handle routing for Railway
try {
    switch($path) {
        case '':
        case 'index':
        case 'index.html':
        case 'home':
            // Serve dynamic homepage
            if (file_exists('home.php')) {
                include 'home.php';
            } else {
                include 'welcome.php';
            }
            break;
            
        case 'admin':
            // Include admin panel
            if (file_exists('admin.php')) {
                include 'admin.php';
            } else {
                echo "<h1>Admin panel not found</h1>";
            }
            break;
            
        case 'contact':
            // Include contact form
            if (file_exists('contact.php')) {
                include 'contact.php';
            } else {
                echo "<h1>Contact page not found</h1>";
            }
            break;
        
    case 'testimonials':
        // Include testimonials page
        include 'testimonials.php';
        break;
        
    case 'setup':
        // Include database setup
        include 'setup.php';
        break;
        
    default:
        // Handle static files from assets
        if (strpos($path, 'public/assets/') === 0) {
            $filePath = $path;
            if (file_exists($filePath) && !is_dir($filePath)) {
                $mimeType = mime_content_type($filePath);
                header('Content-Type: ' . $mimeType);
                readfile($filePath);
                exit;
            }
        }
        
        // Also handle assets without public/ prefix for backward compatibility
        if (strpos($path, 'assets/') === 0) {
            $filePath = 'public/' . $path;
            if (file_exists($filePath) && !is_dir($filePath)) {
                $mimeType = mime_content_type($filePath);
                header('Content-Type: ' . $mimeType);
                readfile($filePath);
                exit;
            }
        }
        
        // 404 - redirect to homepage
        http_response_code(404);
        include 'home.php';
        break;
}
?>