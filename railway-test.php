<?php
echo "<h1>Railway Deployment Test</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Test environment variables
echo "<h2>Environment Variables:</h2>";
echo "<p><strong>PORT:</strong> " . ($_ENV['PORT'] ?? getenv('PORT') ?? 'Not set') . "</p>";
echo "<p><strong>DATABASE_URL:</strong> " . (($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL')) ? 'Set' : 'Not set') . "</p>";
echo "<p><strong>RAILWAY_ENVIRONMENT:</strong> " . ($_ENV['RAILWAY_ENVIRONMENT'] ?? getenv('RAILWAY_ENVIRONMENT') ?? 'Not set') . "</p>";

// Test database connection
echo "<h2>Database Connection Test:</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    echo "<p style='color: green;'>✅ Database connection: SUCCESS</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection: FAILED - " . $e->getMessage() . "</p>";
}

// Test file structure
echo "<h2>File Structure Test:</h2>";
$files = ['index.html', 'admin.php', 'contact.php', 'setup.php', 'testimonials.php'];
foreach ($files as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅' : '❌';
    echo "<p>$status $file: " . ($exists ? 'EXISTS' : 'MISSING') . "</p>";
}
?>
