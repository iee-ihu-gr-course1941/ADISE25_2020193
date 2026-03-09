<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$game_id = 1;

try {
    $stmtGame = $pdo->prepare("SELECT current_turn FROM games WHERE game_id = ?");
    $stmtGame->execute([$game_id]);
    $game = $stmtGame->fetch();

    $stmtTable = $pdo->prepare("SELECT card_suit as suit, card_rank as rank FROM board WHERE game_id = ? AND location = 'table' ORDER BY pos ASC");
    $stmtTable->execute([$game_id]);
    $tableCards = $stmtTable->fetchAll();

    $loc = ($game['current_turn'] == 'P1') ? 'hand_p1' : 'hand_p2';
    $stmtHand = $pdo->prepare("SELECT card_suit as suit, card_rank as rank FROM board WHERE game_id = ? AND location = ? ORDER BY pos ASC");
    $stmtHand->execute([$game_id, $loc]);
    $handCards = $stmtHand->fetchAll();

    // ΥΠΟΛΟΓΙΣΜΟΣ ΠΟΝΤΩΝ
    function calculatePoints($pdo, $game_id, $player_num) {
        $loc = 'captured_p' . $player_num;
        $stmt = $pdo->prepare("SELECT card_rank, card_suit FROM board WHERE game_id = ? AND location = ?");
        $stmt->execute([$game_id, $loc]);
        $cards = $stmt->fetchAll();
        
        $pts = 0;
        foreach ($cards as $c) {
            if ($c['card_rank'] == 'A' || $c['card_rank'] == 'J' || $c['card_rank'] == 'Q' || $c['card_rank'] == 'K') $pts += 1;
            if ($c['card_rank'] == '10' && $c['card_suit'] == 'D') $pts += 2; // 10 Καρό
            if ($c['card_rank'] == '2' && $c['card_suit'] == 'C') $pts += 1;  // 2 Σπαθί
        }
        return $pts;
    }

    echo json_encode([
        'status' => 'success',
        'turn' => $game['current_turn'],
        'table' => $tableCards,
        'hand' => $handCards,
        'p1_points' => calculatePoints($pdo, $game_id, 1),
        'p2_points' => calculatePoints($pdo, $game_id, 2),
        'p1_xeris' => 0, // Προς το παρόν 0 μέχρι να φτιάξουμε table για τις ξερές
        'p2_xeris' => 0
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}