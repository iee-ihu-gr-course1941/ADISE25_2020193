<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ξερή - Online</title>
    <style>
        body { font-family: sans-serif; background: #2c3e50; color: white; text-align: center; }
        #game-board { display: flex; flex-direction: column; gap: 20px; margin-top: 20px; }
        .section { background: #34495e; padding: 15px; border-radius: 8px; margin: 0 auto; width: 80%; }
        .card { display: inline-block; padding: 10px; margin: 5px; background: white; color: black; border-radius: 5px; font-weight: bold; border: 2px solid #7f8c8d; }
        .spades, .clubs { color: black; }
        .hearts, .diamonds { color: red; }
        button { padding: 10px 20px; cursor: pointer; background: #27ae60; color: white; border: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Παιχνίδι Ξερή</h1>
    <button onclick="resetGame()">Νέο Παιχνίδι (Reset)</button>

    <div id="game-board">
        <div class="section">
            <h3>Τραπέζι (Table)</h3>
            <div id="table-cards"></div>
        </div>

        <div class="section">
            <h3>Τα φύλλα μου (My Hand)</h3>
            <div id="my-hand"></div>
        </div>
        
        <div id="status-msg"></div>
    </div>

    <script>
        // Εδώ θα μπει η JavaScript για να καλεί το API
        function resetGame() {
            fetch('api/reset.php')
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    loadBoard();
                });
        }

        function loadBoard() {
            fetch('api/get_board.php')
                .then(response => response.json())
                .then(data => {
                    renderCards('table-cards', data.table);
                    renderCards('my-hand', data.hand);
                });
        }

        function renderCards(elementId, cards, isHand = false) {
    const container = document.getElementById(elementId);
    container.innerHTML = '';
    cards.forEach(card => {
        const div = document.createElement('div');
        div.className = 'card ' + getSuitClass(card.suit);
        div.innerText = card.rank + ' ' + card.suit;
        
        // Αν είναι στο χέρι μου, κάνε το clickable
        if (isHand) {
            div.style.cursor = 'pointer';
            div.onclick = () => playCard(card.rank, card.suit);
        }
        
        container.appendChild(div);
    });
}

// Βοηθητική συνάρτηση για χρώματα
function getSuitClass(suit) {
    if (suit === 'H' || suit === 'D') return 'hearts';
    return 'spades';
}

        // Φόρτωση του board κατά το άνοιγμα
        loadBoard();
    </script>
</body>
</html>