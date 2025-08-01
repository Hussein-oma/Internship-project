<?php
// Database connection
$host = 'localhost';
$db   = 'out-west';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if the table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'registration_tokens'");
    if ($stmt->rowCount() > 0) {
        // Get table structure
        $columns = $pdo->query("SHOW COLUMNS FROM registration_tokens");
        echo "<h2>Registration Tokens Table Structure:</h2>";
        echo "<pre>";
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            print_r($column);
        }
        echo "</pre>";
    } else {
        echo "Table 'registration_tokens' does not exist.";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>