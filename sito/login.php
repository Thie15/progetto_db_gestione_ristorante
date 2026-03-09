<html lang="it">
    <head>
        <title>Smart risto</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/login.css">
        <noscript><style> #loginForm{display:none;}</style></noscript>
    </head>
    <body>
        <header>
            <img class="logo" src="img/smartristo_logo.svg" alt="Smart Risto">
            <ul class="menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="personale.php">Personale</a></li>
                <li><a href="prenotazione.php">Prenotazione</a></li>
                <li><a href="carrello.php">Carrello</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </header>
        <h1 class="titoloPagina">Login</h1>
        <?php
            session_start();
            if(isset($_SESSION["username_error"])){
                echo "<h2>$_SESSION[username_error]</h2>";
                unset($_SESSION["username_error"]);
            }
            if(isset($_SESSION["password_error"])){
                echo "<h2>$_SESSION[password_error]</h2>";
                unset($_SESSION["password_error"]);
            }
            if(isset($_SESSION["errore"])){
                echo "<h2>$_SESSION[errore]</h2>";
                unset($_SESSION["errore"]);
            }
        ?>
        <form id="loginForm" method='post' action='checklogin.php'>
            <label for="username">Username: </label>
            <input type="text" name='username' placeholder="Username">
            <br>
            <label for="password">Password: </label>
            <input id="pwd" type="password" name='password' placeholder="Password">
            <br>
            <button type='submit'>Login</button>
        </form>

        <noscript>
            <p>Il tuo browser non supporta JavaScript è necessario abilitarlo per proseguire</p>
        </noscript>

        <script>
			// Function to hash string with SHA-256
			async function sha256(message) {
				const msgBuffer = new TextEncoder().encode(message); // encode as UTF-8
				const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer); // hash
				const hashArray = Array.from(new Uint8Array(hashBuffer)); // convert buffer to byte array
				const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join(''); // convert bytes to hex string
				return hashHex;
			}

			document.getElementById('loginForm').addEventListener('submit', async (e) => {
				e.preventDefault(); // Stop form from submitting immediately
				
				const passwordInput = document.getElementById('pwd');
				const passwordValue = passwordInput.value;
				
				// Hash the password
				const hashed = await sha256(passwordValue);
				
				// Replace with hash
				passwordInput.value = hashed;
				
				console.log('Original hashed before submission:', hashed);
				
				// Submit form
				e.target.submit();      
                e.target.reset();
			});
		</script>
    </body>
</html>