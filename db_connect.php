<?php
$host = 'localhost';
$db   = 'xeri_game';
$user = 'iee2020193'; 
$pass = '123098'; // Βάλε τον κωδικό που χρησιμοποιείς για να μπαίνεις MariaDB
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Αυτό θα μας βοηθήσει να δούμε το ακριβές λάθος αν αποτύχει πάλι
     die("Connection failed: " . $e->getMessage());
}
?>