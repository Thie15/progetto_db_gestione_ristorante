<?php
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
?>
<html lang="it">
    <head>
        <title>Smart risto</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/personale.css">
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
        <h1 class="titoloPagina">Ecco il nostro personale</h1>
        <?php
            $sql = "SELECT * FROM personale ";
            $results = $conn->query($sql);
            if($results->rowCount() < 1){
                echo "<h1 class='titoloPagina'>Al momento non abbiamo nessun dipendente</h1>";
            }else{
                $tabella = $results->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class='card-container'>";
                foreach($tabella as $personale){
                    echo "  <div class='card'>";
                    echo "      <img class='card-img' src='img/personale/$personale[Immagine]'>";
                    echo "      <h2 class='card-titolo'>$personale[Nome] $personale[Cognome]</h2>";
                    echo "  </div>";
                }
                echo "</div>";
            }
        ?>
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>