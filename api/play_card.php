<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$rank = $input['rank'];
$suit = $input['suit'];
$game_id = 1; 
$current_player = 'P1'; // Για την ώρα παίζουμε ως P1

try {
    // 1. Βρίσκουμε το τελευταίο φύλλο στο τραπέζι
    $stmt = $pdo->prepare("SELECT card_rank FROM board WHERE game_id = ? AND location = 'table' ORDER BY pos DESC LIMIT 1");
    $stmt->execute([$game_id]);
    $last_card = $stmt->fetch();

    // 2. Υπολογίζουμε το επόμενο position στο τραπέζι
    $stmtPos = $pdo->prepare("SELECT IFNULL(MAX(pos), 0) + 1 as next_pos FROM board WHERE game_id = ? AND location = 'table'");
    $stmtPos->execute([$game_id]);
    $next_pos = $stmtPos->fetch()['next_pos'];

    $captured = false;
    
    // ΚΑΝΟΝΕΣ ΜΑΖΕΜΑΤΟΣ
    if ($last_card) {
        if ($rank == $last_card['card_rank'] || $rank == 'J') {
            $captured = true;
        }
    }

    if ($captured) {
        // ΠΕΡΙΠΤΩΣΗ Α: ΜΑΖΕΥΟΥΜΕ ΤΑ ΦΥΛΛΑ
        // 1. Μαζεύουμε όλα όσα ήταν ήδη στο τραπέζι
        $pdo->prepare("UPDATE board SET location = 'captured_p1', player_id = 1, pos = 0 
                       WHERE game_id = ? AND location = 'table'")
            ->execute([$game_id]);
        
        // 2. Μαζεύουμε και το φύλλο που μόλις παίξαμε από το χέρι μας
        $pdo->prepare("UPDATE board SET location = 'captured_p1', player_id = 1, pos = 0 
                       WHERE game_id = ? AND card_rank = ? AND card_suit = ? AND location = 'hand_p1'")
            ->execute([$game_id, $rank, $suit]);
    } else {
        // ΠΕΡΙΠΤΩΣΗ Β: ΤΟ ΦΥΛΛΟ ΜΕΝΕΙ ΣΤΟ ΤΡΑΠΕΖΙ
        $pdo->prepare("UPDATE board SET location = 'table', pos = ? 
                       WHERE game_id = ? AND card_rank = ? AND card_suit = ? AND location = 'hand_p1'")
            ->execute([$next_pos, $game_id, $rank, $suit]);
    }

    // ΕΛΕΓΧΟΣ: Μήπως άδειασαν τα χέρια;
    $stmtHand = $pdo->prepare("SELECT COUNT(*) as count FROM board WHERE game_id = ? AND location = 'hand_p1'");
    $stmtHand->execute([$game_id]);
    $cards_left = $stmtHand->fetch()['count'];

    if ($cards_left == 0) {
        // Αν δεν έχει άλλα στο χέρι, τραβάμε 6 νέα από το deck
        // (Σημείωση: Στην κανονική Ξερή μοιράζουμε και στους δύο παίκτες)
        $stmtDeck = $pdo->prepare("SELECT card_suit, card_rank FROM board 
                                   WHERE game_id = ? AND location = 'deck' 
                                   ORDER BY pos ASC LIMIT 6");
        $stmtDeck->execute([$game_id]);
        $new_cards = $stmtDeck->fetchAll();

        foreach ($new_cards as $c) {
            $pdo->prepare("UPDATE board SET location = 'hand_p1' 
                           WHERE game_id = ? AND card_suit = ? AND card_rank = ?")
                ->execute([$game_id, $c['card_suit'], $c['card_rank']]);
        }
    }

    echo json_encode(['status' => 'success', 'captured' => $captured]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}