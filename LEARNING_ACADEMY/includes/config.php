<?php
// Database configuration with correct port 3307
$db_host = 'localhost:3307';  // Specify the port
$db_user = 'root';
$db_pass = '';  // Empty password for XAMPP
$db_name = 'learning_academy';

// Create connection with correct port
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . " - Make sure MySQL is running on port 3307");
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Uncomment to test
// echo "Connected successfully to database on port 3307!";
?>