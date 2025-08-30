<?php
// Debug script to check environment variables
echo "<h1>Environment Debug</h1>";

echo "<h2>Railway Environment Variables:</h2>";
echo "<pre>";
echo "DATABASE_URL: " . (getenv('DATABASE_URL') ?: 'NOT SET') . "\n";
echo "RAILWAY_ENVIRONMENT: " . (getenv('RAILWAY_ENVIRONMENT') ?: 'NOT SET') . "\n";
echo "PORT: " . (getenv('PORT') ?: 'NOT SET') . "\n";
echo "</pre>";

echo "<h2>PHP Environment:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Extensions: " . implode(', ', get_loaded_extensions()) . "\n";
echo "</pre>";

echo "<h2>Connection Test:</h2>";
try {
    // Test Supabase connection
    $dsn = "pgsql:host=db.brdavdukxvilpdzgbsqd.supabase.co;port=5432;dbname=postgres;sslmode=require";
    $username = 'postgres';
    $password = 'rsMwRvhAs3qxIWQ8';
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p>Database version: " . htmlspecialchars($version) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
