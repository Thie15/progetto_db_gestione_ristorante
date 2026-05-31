<html>
    <head>
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>
    <body>
        <header>
            <img class="logo" src="img/smartristo_logo.svg" alt="Smart Risto">
            <ul class="menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="personale.php">Personale</a></li>
                <li><a href="prenotazione.php">Prenotazione</a></li>
                <?php
                    if(isset($_SESSION["utente"])){
                        $utente = $_SESSION["utente"]["Username"];
                        echo "<li><a href='dashboard.php'>$utente</a></li>";
                    }else{
                        echo"<li><a href='login.php'>Login</a></li>";
                    }
                ?>
            </ul>
        </header>
    </body>
</html>