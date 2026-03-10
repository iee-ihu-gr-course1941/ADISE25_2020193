<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username']);

try {
    // 1. Έλεγχος αν το όνομα υπάρχει ήδη
    $stmt = $pdo->prepare("SELECT * FROM players WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Το όνομα χρησιμοποιείται!']);
        exit;
    }

    // 2. Βρίσκουμε ποιες θέσεις (1 ή 2) είναι κατειλημμένες
    $stmtUsed = $pdo->query("SELECT player_num FROM players");
    $usedNums = $stmtUsed->fetchAll(PDO::FETCH_COLUMN);

    if (count($usedNums) >= 2) {
        echo json_encode(['status' => 'error', 'message' => 'Το παιχνίδι είναι γεμάτο!']);
        exit;
    }

    // 3. Δίνουμε την ελεύθερη θέση (αν λείπει το 1 δίνουμε 1, αλλιώς 2)
    $p_num = (!in_array(1, $usedNums)) ? 1 : 2;

    $stmtInsert = $pdo->prepare("INSERT INTO players (username, player_num) VALUES (?, ?)");
    $stmtInsert->execute([$username, $p_num]);

    echo json_encode([
        'status' => 'success', 
        'username' => $username, 
        'player_num' => $p_num
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}