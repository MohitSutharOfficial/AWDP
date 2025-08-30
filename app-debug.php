<?php
/**
 * Application Database Debug Script
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Application Database Debug</h1>";

// Test the actual application database connection
echo "<h2>Application Database Connection</h2>";
try {
    $db = new Database();
    $connection = $db->getConnection();
    $connectionInfo = $db->getConnectionInfo();
    
    echo "<p style='color: green;'>✅ Application database connection successful!</p>";
    echo "<p><strong>Driver:</strong> " . htmlspecialchars($connectionInfo['driver']) . "</p>";
    
    if ($connectionInfo['driver'] === 'sqlite') {
        echo "<p><strong>SQLite File:</strong> " . htmlspecialchars($connectionInfo['file']) . "</p>";
        echo "<p style='color: orange;'>⚠️ Using SQLite fallback (Supabase connection failed)</p>";
    } else {
        echo "<p style='color: green;'>✅ Using PostgreSQL (Supabase connected successfully)</p>";
    }
    
    // Test table existence
    echo "<h3>Table Check</h3>";
    $tables = ['contacts', 'testimonials'];
    foreach ($tables as $table) {
        try {
            if ($connectionInfo['driver'] === 'sqlite') {
                $stmt = $connection->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
                $stmt->execute([$table]);
                $exists = $stmt->fetch() !== false;
            } else {
                $stmt = $connection->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?");
                $stmt->execute([$table]);
                $exists = $stmt->fetch() !== false;
            }
            
            if ($exists) {
                $countStmt = $connection->prepare("SELECT COUNT(*) as count FROM $table");
                $countStmt->execute();
                $count = $countStmt->fetch()['count'];
                echo "<p style='color: green;'>✅ Table '$table' exists with $count records</p>";
            } else {
                echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error checking table '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Test sample data from testimonials if it exists
    echo "<h3>Sample Data Test</h3>";
    try {
        $testimonials = $db->fetchAll("SELECT name, company FROM testimonials LIMIT 3");
        if (!empty($testimonials)) {
            echo "<p style='color: green;'>✅ Testimonials data found:</p>";
            echo "<ul>";
            foreach ($testimonials as $testimonial) {
                echo "<li>" . htmlspecialchars($testimonial['name']) . " - " . htmlspecialchars($testimonial['company']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ No testimonials data found</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error fetching testimonials: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Application database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Environment variables debug
echo "<h2>Environment Variables Debug</h2>";
echo "<pre>";
echo "SUPABASE_HOST: " . (getenv('SUPABASE_HOST') ?: $_ENV['SUPABASE_HOST'] ?? 'NOT SET') . "\n";
echo "SUPABASE_PORT: " . (getenv('SUPABASE_PORT') ?: $_ENV['SUPABASE_PORT'] ?? 'NOT SET') . "\n";
echo "SUPABASE_USERNAME: " . (getenv('SUPABASE_USERNAME') ?: $_ENV['SUPABASE_USERNAME'] ?? 'NOT SET') . "\n";
echo "SUPABASE_PASSWORD: " . ((getenv('SUPABASE_PASSWORD') ?: $_ENV['SUPABASE_PASSWORD'] ?? false) ? '***SET***' : 'NOT SET') . "\n";
echo "DATABASE_URL: " . ((getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL'] ?? false) ? '***SET***' : 'NOT SET') . "\n";
echo "</pre>";

// Force Supabase connection test
echo "<h2>Force Supabase Connection Test</h2>";
try {
    $dsn = "pgsql:host=aws-1-ap-south-1.pooler.supabase.com;port=6543;dbname=postgres;sslmode=require";
    $username = 'postgres.brdavdukxvilpdzgbsqd';
    $password = '1f73m7bxpj1i6iaQ';
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 30
    ]);
    
    echo "<p style='color: green;'>✅ Direct Supabase connection successful!</p>";
    
    // Test if tables exist in Supabase
    $stmt = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name IN ('contacts', 'testimonials')");
    $stmt->execute();
    $tables = $stmt->fetchAll();
    
    echo "<p>Tables in Supabase:</p>";
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ No application tables found in Supabase. Run setup to create them.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table['table_name']) . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Direct Supabase connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
