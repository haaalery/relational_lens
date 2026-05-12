<?php
// config/db.php
// Central database connection — include this in every PHP file that needs DB access

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       
define('DB_PASS', '');            
define('DB_NAME', 'relational_lens');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection failed: " . $e->getMessage());
    die("A system error occurred. Please try again later."); 
}
?> 