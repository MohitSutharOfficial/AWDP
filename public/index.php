<?php
// Railway-compatible entry point for TechCorp Solutions
// Clean and simple routing for PHP application

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = ltrim($path, '/');

// Handle routing for Railway
switch($path) {
    case '':
    case 'index':
    case 'index.html':
        // Serve homepage
        readfile('../index.html');
        break;
        
    case 'admin':
        // Include admin panel
        include '../admin.php';
        break;
        
    case 'contact':
        // Include contact form
        include '../contact.php';
        break;
        
    case 'testimonials':
        // Include testimonials page
        include '../testimonials.php';
        break;
        
    case 'setup':
        // Include database setup
        include '../setup.php';
        break;
        
    default:
        // Handle static files from assets
        if (strpos($path, 'assets/') === 0) {
            $filePath = './' . $path;
            if (file_exists($filePath) && !is_dir($filePath)) {
                $mimeType = mime_content_type($filePath);
                header('Content-Type: ' . $mimeType);
                readfile($filePath);
                exit;
            }
        }
        
        // 404 - redirect to homepage
        http_response_code(404);
        readfile('../index.html');
        break;
}
?>
