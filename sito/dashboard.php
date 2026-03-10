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
        <link rel="stylesheet" href="css/dashboard.css">
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
        <?php
            session_start();
            if(isset($_SESSION["personale"])){
                $sql = "SELECT * FROM personale WHERE IDPersonale = $_SESSION[personale]";
                $results = $conn->query($sql);
                if($results->rowCount()==1){
                    $row = $results->fetch();
                    echo "<h1 class='titoloPagina'>Dashboard $row[Nome] $row[Cognome]</h1>";
                    echo "<div class='datipersonale'>";
                    echo "  <h3>Dati personali</h3>";
                    echo "  <p>Indirizzo: $row[Indirizzo_Comune], $row[Indirizzo_Via] n°$row[Indirizzo_Civico] - $row[Indirizzo_CAP]</p>";
                    echo "  <p>Turno: $row[Turno]</p>";
                    echo "  <p>Stipendio: $row[Stipendio]€</p>";
                    echo "</div>";
                    $sql = "SELECT * FROM timbrature WHERE IDPersonale = $_SESSION[personale]";
                    $results = $conn->query($sql);
                    if($results->rowCount()>=1){
                        $timbrature = $results->fetchAll(PDO::FETCH_ASSOC);
                        echo "<div class='timbrature'>";
                        echo "  <h3>Timbrature</h3>";
                        foreach($timbrature as $timbratura){
                            echo "   <p>$timbratura[Tipologia]</p>";
                            echo "   <p>$timbratura[DataTimbratura]</p>";
                            echo "   <p>$timbratura[Ora]</p>";
                        }
                        echo "</div>";
                    }
                    else{
                        echo "<h2>Nessuna timbratura registrata</h2>";
                    }
                }else{
                    echo "<h2>Nessun dato trovato</h2>";
                }
            }
            if(isset($_SESSION["fornitore"])){
                $sql = "SELECT * FROM fornitori WHERE IDFornitore = $_SESSION[fornitore]";
                $results = $conn->query($sql);
                if($results->rowCount()==1){
                    $row = $results->fetch();
                    echo "<h1 class='titoloPagina'>Dashboard $row[Nome]</h1>";
                    echo "<div class='datifornitore'>";
                    echo "  <h3>Dati aziendali</h3>";
                    echo "  <p>Indirizzo: $row[Indirizzo_Comune], $row[Indirizzo_Via] n°$row[Indirizzo_Civico] - $row[Indirizzo_CAP]</p>";
                    echo "  <p>Partita iva: $row[PIVA]</p>";
                    echo "</div>";
                    $sql = "SELECT * FROM ordinifornitori WHERE IDFornitore = $_SESSION[fornitore]";
                    $results = $conn->query($sql);
                    if($results->rowCount()>=1){
                        $ordini = $results->fetchAll(PDO::FETCH_ASSOC);
                        echo "<div class='ordini'>";
                        echo "  <h3>Ordini effettuati</h3>";
                        echo "  <h4>Data ordine</h4>";
                        echo "  <h4>Data consegna</h4>";
                        foreach($ordini as $ordine){
                            echo "   <p>$ordine[DataOrdine]</p>";
                            echo "   <p>$ordine[DataConsegna]</p>";
                        }
                        echo "</div>";
                    }
                    else{
                        echo "<h2>Nessun ordine effettuato</h2>";
                    }
                }else{
                    echo "<h2>Nessun dato trovato</h2>";
                }
            }
        ?>    
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>