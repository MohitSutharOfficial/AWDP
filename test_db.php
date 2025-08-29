<?php
/**
 * Database Connection Test for Local XAMPP
 */

echo "<h1>TechCorp Database Connection Test</h1>";

try {
    require_once 'config/database.php';
    
    echo "<div style='color: green;'>✅ Database connection established successfully!</div>";
    echo "<p><strong>Database Type:</strong> " . ($db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? 'MySQL (Local XAMPP)' : 'PostgreSQL (Supabase)') . "</p>";
    
    // Test query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result['test'] == 1) {
        echo "<div style='color: green;'>✅ Database query test passed!</div>";
    }
    
    // Check if tables exist
    $driver = $db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $tables = $db->fetchAll("SHOW TABLES");
        echo "<h3>Existing Tables:</h3>";
        if (empty($tables)) {
            echo "<p style='color: orange;'>⚠️ No tables found. Run <a href='setup.php'>setup.php</a> to create tables.</p>";
        } else {
            echo "<ul>";
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                echo "<li>$tableName</li>";
            }
            echo "</ul>";
        }
    }
    
    echo "<h3>Quick Links:</h3>";
    echo "<ul>";
    echo "<li><a href='index.html'>Home Page</a></li>";
    echo "<li><a href='contact.php'>Contact Form</a></li>";
    echo "<li><a href='testimonials.php'>Testimonials</a></li>";
    echo "<li><a href='admin.php'>Admin Panel</a></li>";
    echo "<li><a href='setup.php'>Database Setup</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Make sure XAMPP is running</li>";
    echo "<li>Start Apache and MySQL services</li>";
    echo "<li>Create database 'techcorp_db' in phpMyAdmin</li>";
    echo "<li>Check database credentials in config/database.php</li>";
    echo "</ol>";
}
?>
