<?php
/**
 * Test Supabase Connection with New Credentials
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Database Connection Test</h1>";

// Test 1: Check if we can connect to Supabase directly
echo "<h2>Direct Supabase Connection Test</h2>";
try {
    $dsn = "pgsql:host=db.brdavdukxvilpdzgbsqd.supabase.co;port=5432;dbname=postgres;sslmode=require";
    $username = 'postgres';
    $password = '1f73m7bxpj1i6iaQ'; // New password
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "<p style='color: green;'>✅ Direct Supabase connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p>Database version: " . htmlspecialchars($version) . "</p>";
    
    // Test creating a simple table
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id SERIAL PRIMARY KEY, message TEXT)");
    echo "<p style='color: green;'>✅ Table creation test successful!</p>";
    
    // Clean up test table
    $pdo->exec("DROP TABLE IF EXISTS test_table");
    echo "<p style='color: green;'>✅ Table cleanup successful!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Direct Supabase connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Check current application database connection
echo "<h2>Application Database Connection Test</h2>";
try {
    $db = new Database();
    echo "<p style='color: green;'>✅ Application database connection successful!</p>";
    
    // Test if we can query
    $connection = $db->getConnection();
    $driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p>Using database driver: <strong>" . htmlspecialchars($driver) . "</strong></p>";
    
    if ($driver === 'sqlite') {
        echo "<p style='color: orange;'>ℹ️ Using SQLite fallback database</p>";
        // Show SQLite file location
        $stmt = $connection->query("PRAGMA database_list");
        $databases = $stmt->fetchAll();
        foreach ($databases as $db_info) {
            if ($db_info['name'] === 'main') {
                echo "<p>SQLite file: " . htmlspecialchars($db_info['file']) . "</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>ℹ️ Using PostgreSQL database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Application database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: Environment variables
echo "<h2>Environment Variables</h2>";
echo "<pre>";
echo "DATABASE_URL: " . (getenv('DATABASE_URL') ? 'SET' : 'NOT SET') . "\n";
echo "SUPABASE_HOST: " . (getenv('SUPABASE_HOST') ?: 'NOT SET') . "\n";
echo "SUPABASE_PASSWORD: " . (getenv('SUPABASE_PASSWORD') ? '***SET***' : 'NOT SET') . "\n";
echo "</pre>";
?>
