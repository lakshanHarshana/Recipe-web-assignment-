<?php
// includes/db.php - Database connection configuration using PDO

$host = 'localhost';
$dbname = 'recipe_book';
$username = 'root';
$password = ''; // Default XAMPP / WAMP password is empty string

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Return friendly error message if database is not yet created or offline
    die("Database Connection Error: " . $e->getMessage() . "<br><br>Please make sure MySQL is running in XAMPP/WAMP and you have imported <code>database.sql</code> into phpMyAdmin.");
}
?>
