<?php
echo "<h2>Database Connection Test</h2>";

// Test 1: Check if config file exists
if (file_exists('includes/config.php')) {
    echo "✅ config.php file exists<br>";
} else {
    echo "❌ config.php file not found<br>";
}

// Test 2: Try to connect
require_once 'includes/config.php';

if ($conn) {
    echo "✅ Database connected successfully!<br>";
    echo "Connected to database: " . $db_name . "<br>";
    
    // Test 3: Check if we can query
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Query successful! Users in database: " . $row['total'] . "<br>";
    } else {
        echo "❌ Query failed: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "❌ Connection failed<br>";
}
?>