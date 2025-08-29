<?php
echo "PHP is working!<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP version: " . phpversion() . "<br>";

// Test database connection
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    echo "Database connection: SUCCESS<br>";
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "<br>";
}

// Test session
try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    echo "Session: SUCCESS<br>";
} catch (Exception $e) {
    echo "Session: FAILED - " . $e->getMessage() . "<br>";
}
?>
