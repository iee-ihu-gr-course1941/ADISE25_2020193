<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ξερή - Online</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #1a472a; color: white; margin: 0; padding: 20px; }
        #game-container { max-width: 900px; margin: 0 auto; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .section { margin-bottom: 30px; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .card { display: inline-flex; flex-direction: column; justify-content: center; align-items: center; width: 70px; height: 100px; margin: 8px; background: white; color: #333; border-radius: 8px; font-weight: bold; font-size: 1.2em; box-shadow: 3px 3px 8px rgba(0,0,0,0.3); border: 1px solid #ccc; }
        .hearts, .diamonds { color: #e74c3c; }
        .spades, .clubs { color: #2c3e50; }
        button { padding: 12px 25px; cursor: pointer; background: #f1c40f; color: #2c3e50; border: none; border-radius: 25px; font-weight: bold; text-transform: uppercase; }
        #turn-display { font-size: 1.5em; padding: 10px; border-radius: 10px; display: inline-block; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div id="login-screen" style="text-align: center; margin-top: 100px;">
        <div style="background: rgba(0,0,0,0.5); padding: 30px; display: inline-block; border-radius: 15px;">
            <h2>Καλώς ήρθατε στην Ξερή</h2>
            <input type="text" id="username-input" placeholder="Username (English)..." style="padding: 12px; width: 250px;">
            <br><br>
            <button onclick="login()">Είσοδος</button>
        </div>
    </div>

    <div id="game-area" style="display: none;">
        <div id="game-container">
            <h1>Παιχνίδι Ξερή</h1>
            
            <div style="display: flex; justify-content: space-around; gap: 20px; margin-bottom: 20px;">
                <div id="score-p1" style="flex: 1; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px; border-left: 5px solid #3498db;">
                    <strong id="display-p1-name" style="color: #3498db;">Παίκτης 1</strong><br>
                    Πόντοι: <span id="p1-points">0</span> | Ξερές: <span id="p1-xeris">0</span>
                </div>

                <div id="score-p2" style="flex: 1; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px; border-left: 5px solid #e67e22;">
                    <strong id="display-p2-name" style="color: #e67e22;">Παίκτης 2</strong><br>
                    Πόντοι: <span id="p2-points">0</span> | Ξερές: <span id="p2-xeris">0</span>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 20px;">
                <button onclick="resetGame()" style="background-color: #27ae60;">Νέο Παιχνίδι</button>
                <button onclick="logout()" style="background-color: #e74c3c; margin-left: 15px;">Έξοδος</button>
            </div>

            <div class="section"><h2 id="turn-display">Φόρτωση...</h2></div>
            <div class="section"><h3>Τραπέζι</h3><div id="table-cards"></div></div>
            <div class="section"><h3>Το χέρι μου</h3><div id="my-hand"></div></div>
        </div>
    </div>

    <script>
        let myUsername = sessionStorage.getItem('username') || "";
        let myPlayerNum = parseInt(sessionStorage.getItem('player_num')) || 0;
        let globalTurn = ""; // Για να ξέρουμε αν είναι η σειρά μας

        window.onload = function() {
            if (!myUsername) {
                document.getElementById('login-screen').style.display = 'block';
                document.getElementById('game-area').style.display = 'none';
            } else {
                document.getElementById('login-screen').style.display = 'none';
                document.getElementById('game-area').style.display = 'block';
                loadBoard();
            }
        };

        function login() {
            const user = document.getElementById('username-input').value;
            if (!user) return;
            fetch('api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: user })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    sessionStorage.setItem('username', data.username);
                    sessionStorage.setItem('player_num', data.player_num);
                    location.reload();
                } else { alert(data.message); }
            });
        }

        function loadBoard() {
            fetch(`api/get_board.php?player_num=${myPlayerNum}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        globalTurn = data.turn;

                        // 1. Ενημέρωση Ονομάτων στο Scoreboard
                        document.getElementById('display-p1-name').innerText = data.p1_name;
                        document.getElementById('display-p2-name').innerText = data.p2_name;

                        // 2. Ενημέρωση Πόντων
                        document.getElementById('p1-points').innerText = data.p1_points;
                        document.getElementById('p2-points').innerText = data.p2_points;
                        document.getElementById('p1-xeris').innerText = data.p1_xeris;
                        document.getElementById('p2-xeris').innerText = data.p2_xeris;

                        // 3. Ενημέρωση Σειράς με το όνομα του παίκτη
                        const activeName = (data.turn === 'P1') ? data.p1_name : data.p2_name;
                        const turnDisplay = document.getElementById('turn-display');
                        turnDisplay.innerText = "Σειρά: " + activeName;
                        turnDisplay.style.background = (data.turn === 'P1' ? "#3498db" : "#e67e22");

                        renderCards('table-cards', data.table, false);
                        renderCards('my-hand', data.hand, true);
                    }
                    if (data.winner) {
                        alert("Το παιχνίδι τελείωσε!\n" + data.winner);
                    }
                });
        }

        function renderCards(id, cards, isHand) {
            const container = document.getElementById(id);
            container.innerHTML = '';
            const symbols = { 'SPADES': '♠', 'HEARTS': '♥', 'DIAMONDS': '♦', 'CLUBS': '♣' };

            cards.forEach(card => {
                const div = document.createElement('div');
                div.className = 'card ' + (card.suit === 'HEARTS' || card.suit === 'DIAMONDS' ? 'hearts' : 'spades');
                div.innerHTML = `<div>${card.rank}</div><div>${symbols[card.suit]}</div>`;

                if (isHand) {
                    const isMyTurn = (myPlayerNum === 1 && globalTurn === 'P1') || (myPlayerNum === 2 && globalTurn === 'P2');
                    if (isMyTurn) {
                        div.style.cursor = 'pointer';
                        div.onclick = () => playCard(card.rank, card.suit);
                    } else {
                        div.style.opacity = '0.5';
                    }
                }
                container.appendChild(div);
            });
        }

        function playCard(rank, suit) {
            fetch('api/play_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rank, suit, player_num: myPlayerNum })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') loadBoard();
                else alert(data.message);
            });
        }

        function resetGame() {
            fetch('api/reset.php').then(() => loadBoard());
        }

        function logout() {
            fetch('api/logout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: myUsername })
            }).then(() => {
                sessionStorage.clear();
                location.reload();
            });
        }

        setInterval(() => { if(myUsername) loadBoard(); }, 2000);
    </script>
</body>
</html>