<?php
$host = 'localhost'; // MySQL port is 3306 by default, not 8080
$dbname = 'bookstore_db';
$username = 'root';
$password = '';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5 // 5 seconds timeout
    ];
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, $options);
    // Test the connection with a simple query
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>