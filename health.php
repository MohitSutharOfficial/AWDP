<?php
// Health check endpoint for Railway
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'environment' => [
        'port' => $_SERVER['SERVER_PORT'] ?? getenv('PORT') ?? '8000',
        'host' => $_SERVER['HTTP_HOST'] ?? 'localhost'
    ]
];

// Check database connection
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $health['database'] = 'connected';
} catch (Exception $e) {
    $health['database'] = 'error: ' . $e->getMessage();
    $health['status'] = 'degraded';
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>
