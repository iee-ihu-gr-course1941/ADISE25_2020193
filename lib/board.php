<?php
function reset_board($game_id, $pdo) {
    // 1. Καθαρίζουμε το board από προηγούμενο παιχνίδι
    $pdo->prepare("DELETE FROM board WHERE game_id = ?")->execute([$game_id]);

    // 2. Δημιουργούμε μια πλήρη τράπουλα 52 φύλλων
    $suits = ['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS']; // ΣΠΑΘΙΑ, ΚΟΥΠΕΣ, ΚΑΡΟ, ΜΠΑΣΤΟΥΝΙΑ
    $ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
    $deck = [];

    foreach ($suits as $s) {
        foreach ($ranks as $r) {
            $deck[] = ['s' => $s, 'r' => $r];
        }
    }

    // 3. Ανακάτεμα
    shuffle($deck);

    // 4. Προετοιμασία του SQL Query
    $sql = "INSERT INTO board (game_id, card_suit, card_rank, location, pos) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    // 5. Μοίρασμα (6 στον Π1, 6 στον Π2, 4 στο τραπέζι, τα υπόλοιπα deck)
    for ($i = 0; $i < 52; $i++) {
        $card = $deck[$i];
        $location = 'deck';
        $pos = $i;

        if ($i < 6) {
            $location = 'hand_p1';
        } elseif ($i < 12) {
            $location = 'hand_p2';
        } elseif ($i < 16) {
            $location = 'table';
        }

        $stmt->execute([$game_id, $card['s'], $card['r'], $location, $pos]);
    }
}
?>