<?php
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
        session_start();
?>
<html lang="it">
    <head>
        <title>Smart risto</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/login.css">
        <noscript><style> #loginForm{display:none;}</style></noscript>
    </head>
    <body>
        <?php
            include("inc/header.php");
        ?>
        <h1 class="titoloPagina">Modifica password</h1>
        <?php
            if(isset($_SESSION["password_vecchia"])){
                echo "<h2>$_SESSION[password_vecchia]</h2>";
                unset($_SESSION["password_vecchia"]);
            }
            if(isset($_SESSION["password_nuova"])){
                echo "<h2>$_SESSION[password_nuova]</h2>";
                unset($_SESSION["password_nuova"]);
            }
            if(isset($_SESSION["password_error"])){
                echo "<h2>$_SESSION[password_error]</h2>";
                unset($_SESSION["password_error"]);
            }
        ?>
        <form id="loginForm" method='post' action='checkModificaPassword.php'>
            <label for="passwordVecchia">Password vecchia: </label>
            <input id="pwdvecchia" type="password" name='passwordVecchia' placeholder="Password">
            <br>
            <label for="passwordNuova">Password nuova: </label>
            <input id="pwdnuova" type="password" name='passwordNuova' placeholder="Password">
            <br>
            <button type='submit'>Modifica</button>
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
				
				const passwordInputVecchia = document.getElementById('pwdvecchia');
				const passwordValueVecchia = passwordInputVecchia.value;
                const passwordInputNuova = document.getElementById('pwdnuova');
				const passwordValueNuova = passwordInputNuova.value;
				
				// Hash the password
				const hashedVecchia = await sha256(passwordValueVecchia);
                const hashedNuova = await sha256(passwordValueNuova);
				
				// Replace with hash
				passwordInputVecchia.value = hashedVecchia;
                passwordInputNuova.value = hashedNuova;
				
				console.log('Original hashed before submission:', hashedVecchia);
				console.log('Original hashed before submission:', hashedNuova);
				
				// Submit form
				e.target.submit();      
                e.target.reset();
			});
		</script>
    </body>
</html>
<?php
}catch(PDOException $e) {
    // stampando il messaggio di errore
    echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
}
?>