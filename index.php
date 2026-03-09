<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ξερή - Online</title>
    <div id="game-container">
    <h1>Παιχνίδι Ξερή</h1>

    <div style="display: flex; justify-content: space-around; gap: 20px; margin-bottom: 20px;">
        <div id="score-p1" style="flex: 1; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px; border-left: 5px solid #3498db;">
            <strong style="color: #3498db;">Παίκτης 1</strong><br>
            Πόντοι: <span id="p1-points" style="font-size: 1.2em; font-weight: bold;">0</span> | 
            Ξερές: <span id="p1-xeris" style="font-weight: bold;">0</span>
        </div>
        
        <div id="score-p2" style="flex: 1; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px; border-left: 5px solid #e67e22;">
            <strong style="color: #e67e22;">Παίκτης 2</strong><br>
            Πόντοι: <span id="p2-points" style="font-size: 1.2em; font-weight: bold;">0</span> | 
            Ξερές: <span id="p2-xeris" style="font-weight: bold;">0</span>
        </div>
    </div>

    <button onclick="resetGame()" style="margin-bottom: 20px;">Νέο Παιχνίδι (Reset)</button>

    <div class="section">
        <h2 id="turn-display">Φόρτωση σειράς...</h2>
    </div>

    <div class="section">
        <h3>Τραπέζι (Table)</h3>
        <div id="table-cards" style="min-height: 120px; background: rgba(255,255,255,0.05); border-radius: 10px; padding: 10px;"></div>
    </div>

    <div class="section">
        <h3>Τα φύλλα μου (My Hand)</h3>
        <div id="my-hand" style="min-height: 120px;"></div>
    </div>
</div>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #1a472a; /* Πράσινο τσόχας */
            color: white; 
            margin: 0;
            padding: 20px;
        }

        h1 { text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }

        #game-container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .section { 
            margin-bottom: 30px; 
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Στυλ για τις Κάρτες */
        .card { 
            display: inline-flex; 
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 70px; 
            height: 100px; 
            margin: 8px; 
            background: white; 
            color: #333; 
            border-radius: 8px; 
            font-weight: bold; 
            font-size: 1.2em;
            box-shadow: 3px 3px 8px rgba(0,0,0,0.3);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #ccc;
            position: relative;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 20px rgba(0,0,0,0.4);
        }

        .hearts, .diamonds { color: #e74c3c; }
        .spades, .clubs { color: #2c3e50; }

        /* Κουμπιά */
        button { 
            padding: 12px 25px; 
            font-size: 1em;
            cursor: pointer; 
            background: #f1c40f; 
            color: #2c3e50; 
            border: none; 
            border-radius: 25px; 
            font-weight: bold;
            text-transform: uppercase;
            box-shadow: 0 4px 0 #d4ac0d;
        }

        button:active {
            box-shadow: none;
            transform: translateY(4px);
        }

        #turn-display {
            font-size: 1.5em;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 10px;
        }

        /* Animation για όταν μπαίνει νέα κάρτα */
        @keyframes slideIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }
        .card { animation: slideIn 0.3s ease-out; }
</style>
</head>
<body>
    <h1>Παιχνίδι Ξερή</h1>
    <div class="section">
        <h2 id="turn-display" style="color: #0ff11a;">Φόρτωση σειράς...</h2>
    </div>
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
            console.log("Reset button clicked..."); // Θα το δούμε στο F12
            fetch('api/reset.php')
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);
                    alert(data.message);
                    loadBoard();
                })
                .catch(err => console.error("Reset Error:", err));
        }

        function loadBoard() {
            fetch('api/get_board.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 1. Ενημέρωση Scoreboard
                        document.getElementById('p1-points').innerText = data.p1_points;
                        document.getElementById('p2-points').innerText = data.p2_points;
                        document.getElementById('p1-xeris').innerText = data.p1_xeris;
                        document.getElementById('p2-xeris').innerText = data.p2_xeris;

                        // 2. Ενημέρωση Σειράς (Turn)
                        const turnDisplay = document.getElementById('turn-display');
                        if (data.turn === 'P1') {
                            turnDisplay.innerText = "Σειρά: Παίκτης 1";
                            turnDisplay.style.background = "#3498db"; // Μπλε για P1
                        } else {
                            turnDisplay.innerText = "Σειρά: Παίκτης 2";
                            turnDisplay.style.background = "#e67e22"; // Πορτοκαλί για P2
                        }

                        // 3. Σχεδίαση Καρτών
                        renderCards('table-cards', data.table, false);
                        renderCards('my-hand', data.hand, true);
                    }
                })
                .catch(err => console.error("Σφάλμα κατά τη φόρτωση του board:", err));
        }       
        function renderCards(elementId, cards, isHand) {
            const container = document.getElementById(elementId);
            if(!container) return;
            container.innerHTML = '';
    
            // Αντιστοίχιση συμβόλων
            const symbols = { 'S': '♠', 'H': '♥', 'D': '♦', 'C': '♣' };

            cards.forEach(card => {
                const div = document.createElement('div');
                const suitClass = (card.suit === 'H' || card.suit === 'D') ? 'hearts' : 'spades';
                div.className = 'card ' + suitClass;
        
                // Εμφάνιση π.χ. "A ♠"
                div.innerHTML = `<div>${card.rank}</div><div>${symbols[card.suit]}</div>`;
        
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

        function playCard(rank, suit) {
            console.log("Playing card:", rank, suit);
    
            fetch('api/play_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rank: rank, suit: suit })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log("Success! New turn:", data.turn);
                    // Κάνουμε αμέσως loadBoard για να αλλάξει η σειρά και τα φύλλα
                    loadBoard(); 
                } else {
                    alert("Λάθος: " + data.message);
                }
            })
            .catch(err => {
                console.error("Fetch Error:", err);
            });
        }


        // Φόρτωση του board κατά το άνοιγμα
        loadBoard();
    </script>
</body>
</html>