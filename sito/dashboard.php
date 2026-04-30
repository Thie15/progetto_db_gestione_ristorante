<?php
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
        include("inc/checklogin.php");
        if(!$_SESSION["logged"]){
            session_unset();
            session_destroy();
            header("location:login.php");
        }
?>
<html lang="it">
    <head>
        <title>Smart risto</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/dashboard.css">
        <link rel="stylesheet" href="css/card.css">
    </head>
    <body>
        <?php
            include("inc/header.php");
        ?>
        <?php
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
                    $sql = "SELECT * FROM timbrature WHERE IDPersonale = $_SESSION[personale] ORDER BY DataTimbratura DESC, Ora DESC";
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
                    echo "<a href='timbra.php'>Timbra</a>";
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
            if(!isset($_SESSION["personale"]) && !isset($_SESSION["fornitore"])){
                echo "<h1 class='titoloPagina'>Dashboard admin</h1>";
                echo "<div class='card-container'>";
                echo "  <div class='card'>";
                echo "      <h2 class='card-titolo'>Gestione personale</h2>";
                echo "      <p class='card-testo'>Clicca qui per gestire il tuo personale</p>";
                echo "  </div>";
                echo "  <div class='card'>";
                echo "      <h2 class='card-titolo'>Gestione piatti</h2>";
                echo "      <p class='card-testo'>Clicca qui per gestire i tuoi piatti</p>";
                echo "  </div>";
                echo "  <div class='card'>";
                echo "      <h2 class='card-titolo'>Gestione fornitori</h2>";
                echo "      <p class='card-testo'>Clicca qui per gestire i tuoi fornitori</p>";
                echo "  </div>";
                echo "  <div class='card'>";
                echo "      <h2 class='card-titolo'>Gestione ingredienti</h2>";
                echo "      <p class='card-testo'>Clicca qui per gestire i tuoi ingredienti</p>";
                echo "  </div>";
                echo "</div>";
            }
            echo "<div class='bottoni-dashboard'>";
            echo "<a class='btn-dashboard' href='logout.php'>Effettua logout</a>";
            echo "<a class='btn-dashboard' href='modificaPassword.php'>Modifica password</a>";
            echo "</div>";
        ?>    
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>