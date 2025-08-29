<?php
// Simple API router
$path = $_GET['path'] ?? '';

switch($path) {
    case 'admin':
        include __DIR__ . '/admin.php';
        break;
    case 'contact':
        include __DIR__ . '/contact.php';
        break;
    case 'testimonials':
        include __DIR__ . '/testimonials.php';
        break;
    case 'setup':
        include __DIR__ . '/setup.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>
