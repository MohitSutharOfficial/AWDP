<?php
// Railway-compatible entry point
// This file routes requests to the appropriate PHP files

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = ltrim($path, '/');

// Handle routing for Railway
switch($path) {
    case '':
    case 'index':
    case 'index.html':
        readfile('./index.html');
        break;
        
    case 'admin':
        include './admin.php';
        break;
        
    case 'contact':
        include './contact.php';
        break;
        
    case 'testimonials':
        include './testimonials.php';
        break;
        
    case 'setup':
        include './setup.php';
        break;
        
    default:
        // Handle static files
        $filePath = './' . $path;
        if (file_exists($filePath) && !is_dir($filePath)) {
            $mimeType = mime_content_type($filePath);
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
        } else {
            // Try in public directory
            $publicPath = './public/' . $path;
            if (file_exists($publicPath) && !is_dir($publicPath)) {
                $mimeType = mime_content_type($publicPath);
                header('Content-Type: ' . $mimeType);
                readfile($publicPath);
            } else {
                http_response_code(404);
                readfile('./public/index.html');
            }
        }
        break;
}
?>
