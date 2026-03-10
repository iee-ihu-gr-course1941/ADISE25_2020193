<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$game_id = 1;

try {
    $stmtGame = $pdo->prepare("SELECT current_turn, p1_xeris, p2_xeris FROM games WHERE game_id = ?");
    $stmtGame->execute([$game_id]);
    $game = $stmtGame->fetch();

    $stmtTable = $pdo->prepare("SELECT card_suit as suit, card_rank as rank FROM board WHERE game_id = ? AND location = 'table' ORDER BY pos ASC");
    $stmtTable->execute([$game_id]);
    $tableCards = $stmtTable->fetchAll();

    $viewer_num = isset($_GET['player_num']) ? intval($_GET['player_num']) : 1;
    $loc = ($viewer_num == 1) ? 'hand_p1' : 'hand_p2';

    $stmtHand = $pdo->prepare("SELECT card_suit as suit, card_rank as rank FROM board WHERE game_id = ? AND location = ? ORDER BY pos ASC");
    $stmtHand->execute([$game_id, $loc]);
    $handCards = $stmtHand->fetchAll();
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

    $stmtPlayers = $pdo->query("SELECT username, player_num FROM players");
    $playersData = $stmtPlayers->fetchAll();

    $p1_name = "Αναμονή...";
    $p2_name = "Αναμονή...";

    foreach ($playersData as $p) {
        if ($p['player_num'] == 1) $p1_name = $p['username'];
        if ($p['player_num'] == 2) $p2_name = $p['username'];
    }

    // Έλεγχος αν τελείωσε το παιχνίδι
    $stmtCountAll = $pdo->prepare("SELECT COUNT(*) as c FROM board WHERE game_id = ? AND location IN ('deck', 'hand_p1', 'hand_p2')");
    $stmtCountAll->execute([$game_id]);
    $remaining = $stmtCountAll->fetch()['c'];

    $winner = null;
    if ($remaining == 0) {
        $p1_total = calculatePoints($pdo, $game_id, 1) + ($game['p1_xeris'] * 10);
        $p2_total = calculatePoints($pdo, $game_id, 2) + ($game['p2_xeris'] * 10);
    
        if ($p1_total > $p2_total) $winner = $p1_name . " (Νικητής!)";
        else if ($p2_total > $p1_total) $winner = $p2_name . " (Νικητής!)";
        else $winner = "Ισοπαλία!";
    }

    echo json_encode([
        'status' => 'success',
        'turn' => $game['current_turn'],
        'p1_name' => $p1_name, // Νέο πεδίο
        'p2_name' => $p2_name, // Νέο πεδίο
        'table' => $tableCards,
        'hand' => $handCards,
        'p1_points' => calculatePoints($pdo, $game_id, 1),
        'p2_points' => calculatePoints($pdo, $game_id, 2),
        'p1_xeris' => $game['p1_xeris'],
        'p2_xeris' => $game['p2_xeris'],
        'winner' => $winner
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}