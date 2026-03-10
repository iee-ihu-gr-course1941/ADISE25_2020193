<?php
require_once '../db_connect.php';
require_once '../lib/board.php';

header('Content-Type: application/json');

// Για χάρη της άσκησης, ας υποθέσουμε ότι δουλεύουμε στο game_id = 1
$game_id = 1;

try {
    $pdo->query("DELETE FROM players");
    $pdo->prepare("UPDATE games SET status='playing', current_turn='P1', p1_xeris=0, p2_xeris=0 WHERE game_id=?")
        ->execute([$game_id]);
    reset_board($game_id, $pdo);
    
    // Ενημέρωση του status του παιχνιδιού
    $pdo->prepare("UPDATE games SET status='playing', current_turn='P1' WHERE game_id=?")
        ->execute([$game_id]);

    echo json_encode(['status' => 'success', 'message' => 'The board is ready! Game started.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>