<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$rank = $input['rank'];
$suit = $input['suit'];
$game_id = 1;

try {
    // 1. Βρίσκουμε το μέγιστο position στο τραπέζι για να μπει το φύλλο από πάνω
    $stmtPos = $pdo->prepare("SELECT MAX(pos) as max_pos FROM board WHERE game_id = ? AND location = 'table'");
    $stmtPos->execute([$game_id]);
    $next_pos = $stmtPos->fetch()['max_pos'] + 1;

    // 2. Ενημερώνουμε τη βάση: Το φύλλο πάει στο τραπέζι
    $stmt = $pdo->prepare("UPDATE board SET location = 'table', pos = ? 
                           WHERE game_id = ? AND card_rank = ? AND card_suit = ? AND location = 'hand_p1'");
    $stmt->execute([$next_pos, $game_id, $rank, $suit]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}