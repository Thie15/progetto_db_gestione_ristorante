<?php
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
?>
<html lang="it">
    <head>
        <title>Smart risto</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/card.css">
    </head>
    <body>
        <?php
            include("inc/header.php");
        ?>
        <h1 class="titoloPagina">Ecco il nostro personale</h1>
        <h2 class="sottotitolo">I nostri cuochi</h2>
        <?php
            $sql = "SELECT * FROM cuochi ";
            $results = $conn->query($sql);
            if($results->rowCount() < 1){
                echo "<h1 class='titoloPagina'>Al momento non abbiamo nessun cuoco</h1>";
            }else{
                $cuochi = $results->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class='card-container'>";
                foreach($cuochi as $cuoco){
                    $sql = "SELECT * FROM personale WHERE IDPersonale = $cuoco[IDPersonale]";
                    $results = $conn->query($sql);
                    $personale = $results->fetch(PDO::FETCH_ASSOC);
                    echo "  <div class='card'>";
                    echo "      <img class='card-personale' src='img/personale/$personale[Immagine]'>";
                    echo "      <h2 class='card-titolo'>$personale[Nome] $personale[Cognome]</h2>";
                    echo "  </div>";
                }
                echo "</div>";
            }
        ?>
        <h2 class="sottotitolo">I nostri camerieri</h2>
        <?php
            $sql = "SELECT * FROM camerieri ";
            $results = $conn->query($sql);
            if($results->rowCount() < 1){
                echo "<h1 class='titoloPagina'>Al momento non abbiamo nessun cameriere</h1>";
            }else{
                $camerieri = $results->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class='card-container'>";
                foreach($camerieri as $cameriere){
                    $sql = "SELECT * FROM personale WHERE IDPersonale = $cameriere[IDPersonale]";
                    $results = $conn->query($sql);
                    $personale = $results->fetch(PDO::FETCH_ASSOC);
                    echo "  <div class='card'>";
                    echo "      <img class='card-personale' src='img/personale/$personale[Immagine]'>";
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