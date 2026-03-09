<?php
$host = '127.0.0.1'; // Χρησιμοποιούμε IP αντί για localhost
$db   = 'xeri_game';
$user = 'iee2020193'; 
$pass = '123098'; // Βάλε αυτόν που βάζεις στο HeidiSQL

// Προσθέτουμε το socket ή το port αν χρειάζεται
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Αυτή η γραμμή βοηθάει σε θέματα συμβατότητας με παλιά plugins
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    echo "Σφάλμα σύνδεσης: " . $e->getMessage();
    exit;
}
?>