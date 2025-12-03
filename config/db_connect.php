<?php
// Configuration for database connection
$host = 'sql102.infinityfree.com'; 
$db   = 'if0_40513436_uni_library'; // The name you created in phpMyAdmin
$user = 'if0_40513436';     // Default XAMPP username
$pass = 'xcK2xfwVGDCx';         // Default XAMPP password (usually empty)
$charset = 'utf8mb4';

// Data Source Name (DSN) string
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Establish the PDO connection
     $pdo = new PDO($dsn, $user, $pass, $options);
     // Connection successful, $pdo object is ready for use
} catch (\PDOException $e) {
     // Handle connection error gracefully
     // This is the error message visible to the developer
     die("Database connection failed: " . $e->getMessage());
}
?>