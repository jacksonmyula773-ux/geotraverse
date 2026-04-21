<?php
// backend/test_db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Try to include database.php
echo "<h3>Step 1: Loading database.php</h3>";

if (file_exists(__DIR__ . '/config/database.php')) {
    echo "✓ database.php file found<br>";
    require_once __DIR__ . '/config/database.php';
    echo "✓ database.php loaded successfully<br>";
} else {
    echo "✗ database.php NOT found at: " . __DIR__ . "/config/database.php<br>";
    exit();
}

// Try to get connection
echo "<h3>Step 2: Getting database connection</h3>";

try {
    $db = getDB();
    echo "✓ Database connection successful!<br>";
    
    // Test query
    echo "<h3>Step 3: Testing query</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM employees");
    $result = $stmt->fetch();
    echo "✓ Query successful! Total employees: " . $result['count'] . "<br>";
    
    echo "<h3 style='color:green'>✅ All tests passed! Database is working correctly.</h3>";
    
} catch(PDOException $e) {
    echo "<span style='color:red'>✗ Database error: " . $e->getMessage() . "</span><br>";
} catch(Exception $e) {
    echo "<span style='color:red'>✗ General error: " . $e->getMessage() . "</span><br>";
}
?>