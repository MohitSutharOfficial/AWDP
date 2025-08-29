<?php
/**
 * Local Development Server for XAMPP
 * 
 * This file handles routing for local development with PHP and MySQL
 */

// Get the request URI
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remove query string for routing
$route = strtok($path, '?');

// Basic routing
switch ($route) {
    case '/':
    case '/index.php':
        include 'public/index.html';
        break;
        
    case '/contact':
        include 'api/contact.php';
        break;
        
    case '/admin':
        include 'api/admin.php';
        break;
        
    case '/testimonials':
        include 'api/testimonials.php';
        break;
        
    case '/setup':
        include 'api/setup.php';
        break;
        
    default:
        // Try to serve static files from public directory
        $file = 'public' . $route;
        if (file_exists($file) && is_file($file)) {
            $mimeType = mime_content_type($file);
            header('Content-Type: ' . $mimeType);
            readfile($file);
        } else {
            http_response_code(404);
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The page you requested could not be found.</p>';
            echo '<a href="/">Go back to homepage</a>';
        }
        break;
}
?>
