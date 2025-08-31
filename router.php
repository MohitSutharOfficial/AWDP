<?php
// Simple router for Railway deployment
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// Serve static files
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/', $path)) {
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        $mime = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

// Route to appropriate PHP file
switch ($path) {
    case '/':
    case '/home':
        if (file_exists('home.php')) {
            include 'home.php';
        } else {
            include 'welcome.php';
        }
        break;
    
    case '/admin':
        include 'admin.php';
        break;
    
    case '/contact':
        include 'contact.php';
        break;
    
    case '/testimonials':
        include 'testimonials.php';
        break;
    
    case '/health':
    case '/health.php':
        include 'health.php';
        break;
    
    case '/welcome':
        include 'welcome.php';
        break;
    
    default:
        // For API routes
        if (strpos($path, '/api/') === 0) {
            $apiFile = __DIR__ . $path;
            if (file_exists($apiFile)) {
                include $apiFile;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'API endpoint not found']);
            }
        } else {
            // Default to home page
            if (file_exists('home.php')) {
                include 'home.php';
            } else {
                include 'welcome.php';
            }
        }
        break;
}
?>
