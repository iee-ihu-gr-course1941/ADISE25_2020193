<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$rank = $input['rank'];
$suit = $input['suit'];
$rank = trim($rank);
$suit = trim($suit);
$game_id = 1;

try {
    // 1. Παίρνουμε την τρέχουσα κατάσταση (Ποιος παίζει;)
    $stmtGame = $pdo->prepare("SELECT current_turn FROM games WHERE game_id = ?");
    $stmtGame->execute([$game_id]);
    $game = $stmtGame->fetch();
    $current_p = $game['current_turn']; // 'P1' ή 'P2'
    $next_p = ($current_p == 'P1') ? 'P2' : 'P1';

    // 2. Βρίσκουμε το τελευταίο φύλλο στο τραπέζι για να δούμε αν τα παίρνει
    $stmtTable = $pdo->prepare("SELECT card_rank FROM board WHERE game_id = ? AND location = 'table' ORDER BY pos DESC LIMIT 1");
    $stmtTable->execute([$game_id]);
    $last_card = $stmtTable->fetch();

    $stmtMaxPos = $pdo->prepare("SELECT IFNULL(MAX(pos), 0) + 1 as next_pos FROM board WHERE game_id = ? AND location = 'table'");
    $stmtMaxPos->execute([$game_id]);
    $next_pos = $stmtMaxPos->fetch()['next_pos'];

    $captured = false;
    $is_xeri = false;

    // Λογική Μαζέματος & Ξερής
    if ($last_card) {
        if ($rank == $last_card['card_rank'] || $rank == 'J') {
            $captured = true;
            // Έλεγχος για Ξερή: Αν υπήρχε μόνο 1 φύλλο κάτω και δεν είναι Βαλές
            $stmtCount = $pdo->prepare("SELECT COUNT(*) as c FROM board WHERE game_id = ? AND location = 'table'");
            $stmtCount->execute([$game_id]);
            if ($stmtCount->fetch()['c'] == 1 && $rank != 'J') {
                $is_xeri = true;
            }
        }
    }

    // 3. Εκτέλεση Κίνησης
    $loc_hand = ($current_p == 'P1') ? 'hand_p1' : 'hand_p2';
    $loc_captured = ($current_p == 'P1') ? 'captured_p1' : 'captured_p2';

    if ($captured) {
        $pdo->prepare("UPDATE board SET location = ?, player_id = ?, pos = 0 WHERE game_id = ? AND location = 'table'")
            ->execute([$loc_captured, ($current_p == 'P1' ? 1 : 2), $game_id]);
        $pdo->prepare("UPDATE board SET location = ?, player_id = ?, pos = 0 WHERE game_id = ? AND card_rank = ? AND card_suit = ? AND location = ?")
            ->execute([$loc_captured, ($current_p == 'P1' ? 1 : 2), $game_id, $rank, $suit, $loc_hand]);
    } else {
        $pdo->prepare("UPDATE board SET location = 'table', pos = ? WHERE game_id = ? AND card_rank = ? AND card_suit = ? AND location = ?")
            ->execute([$next_pos, $game_id, $rank, $suit, $loc_hand]);
    }

    // 4. Αλλαγή Σειράς στη βάση
    $pdo->prepare("UPDATE games SET current_turn = ? WHERE game_id = ?")->execute([$next_p, $game_id]);

    // 5. Έλεγχος αν άδειασαν τα χέρια ΚΑΙ ΤΩΝ ΔΥΟ για νέο μοίρασμα
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) as c FROM board WHERE game_id = ? AND location IN ('hand_p1', 'hand_p2')");
    $stmtCheck->execute([$game_id]);
    
    if ($stmtCheck->fetch()['c'] == 0) {
        // Μοιράζουμε 6 στον καθένα
        $stmtDeck = $pdo->prepare("SELECT card_suit, card_rank FROM board WHERE game_id = ? AND location = 'deck' ORDER BY pos ASC LIMIT 12");
        $stmtDeck->execute([$game_id]);
        $new_cards = $stmtDeck->fetchAll();

        for ($i = 0; $i < count($new_cards); $i++) {
            $target = ($i < 6) ? 'hand_p1' : 'hand_p2';
            $pdo->prepare("UPDATE board SET location = ? WHERE game_id = ? AND card_suit = ? AND card_rank = ?")
                ->execute([$target, $game_id, $new_cards[$i]['card_suit'], $new_cards[$i]['card_rank']]);
        }
    }

    echo json_encode(['status' => 'success', 'turn' => $next_p, 'captured' => $captured, 'xeri' => $is_xeri]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}