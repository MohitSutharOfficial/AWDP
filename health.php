<?php
// Comprehensive health check endpoint for Railway
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$startTime = microtime(true);

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_time' => time(),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Railway/Nixpacks',
    'environment' => [
        'port' => $_SERVER['SERVER_PORT'] ?? getenv('PORT') ?? '8000',
        'host' => $_SERVER['HTTP_HOST'] ?? 'Railway',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'uri' => $_SERVER['REQUEST_URI'] ?? '/health'
    ],
    'deployment' => [
        'platform' => 'Railway',
        'build_system' => 'Nixpacks',
        'router' => 'Custom PHP Router'
    ]
];

// Check database connection
try {
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
        $db = new Database();
        $health['database'] = [
            'status' => 'connected',
            'type' => 'SQLite',
            'path' => $db->getDatabaseInfo()['file'] ?? 'In-memory'
        ];
    } else {
        $health['database'] = [
            'status' => 'config_missing',
            'message' => 'Database config file not found'
        ];
    }
} catch (Exception $e) {
    $health['database'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    $health['status'] = 'degraded';
}

// Check file system
$health['filesystem'] = [
    'current_dir' => __DIR__,
    'files_exist' => [
        'index.php' => file_exists(__DIR__ . '/index.php'),
        'router.php' => file_exists(__DIR__ . '/router.php'),
        'admin.php' => file_exists(__DIR__ . '/admin.php'),
        'contact.php' => file_exists(__DIR__ . '/contact.php'),
        'home.php' => file_exists(__DIR__ . '/home.php'),
        'welcome.php' => file_exists(__DIR__ . '/welcome.php')
    ]
];

// Performance metrics
$endTime = microtime(true);
$health['performance'] = [
    'response_time_ms' => round(($endTime - $startTime) * 1000, 2),
    'memory_usage' => [
        'current' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
        'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB'
    ]
];

// Set appropriate HTTP status
http_response_code($health['status'] === 'ok' ? 200 : 503);

echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
