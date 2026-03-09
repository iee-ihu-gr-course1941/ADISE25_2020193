<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$game_id = 1; // Για την ώρα το έχουμε σταθερό

try {
    // Παίρνουμε τα φύλλα του τραπεζιού
    $stmtTable = $pdo->prepare("SELECT card_suit as suit, card_rank as rank FROM board WHERE game_id = ? AND location = 'table' ORDER BY pos ASC");
    $stmtTable->execute([$game_id]);
    $tableCards = $stmtTable->fetchAll();

    // Παίρνουμε τα φύλλα του Παίκτη 1 
    $stmtHand = $pdo->prepare("SELECT card_suit as suit, card_rank as rank FROM board WHERE game_id = ? AND location = 'hand_p1' ORDER BY pos ASC");
    $stmtHand->execute([$game_id]);
    $handCards = $stmtHand->fetchAll();

    echo json_encode([
        'status' => 'success',
        'table' => $tableCards,
        'hand' => $handCards
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}