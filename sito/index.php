<?php
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
        $minimo = 0;
        $massimo = 0;
        if(isset($_GET["prezzoMin"]) && isset($_GET["prezzoMax"]) && $_GET["prezzoMax"]>$_GET["prezzoMin"]){
            $minimo = $_GET["prezzoMin"];
            $massimo = $_GET["prezzoMax"];
        }
        if(isset($_GET["ordinaPer"]) && $_GET["ordinaPer"] != "no"){
            $ordina = true;
        }else{
            $ordina = false;
        }
?>
<html lang="it">
    <head>
        <title>Smart risto</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/index.css">
        <link rel="stylesheet" href="css/card.css">
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
        <h1 class="titoloPagina">Scopri i nostri piatti</h1>
        <div class="filtri-contenitore">
            <input type="checkbox" id="filtri">
            <label for="filtri" class="bottone-filtri">Filtri</label>
            <div class="filtri">
                <form method="GET" action="#">
                    <label for="prezzoMin">Prezzo minimo</label>
                    <input type="number" name="prezzoMin" <?php echo "value='$minimo'" ?>>
                    <label for="prezzoMax">Prezzo massimo</label>
                    <input type="number" name="prezzoMax" <?php echo "value='$massimo'" ?>>
                    <label for="ordinaPer">Ordina</label>
                    <select name="ordinaPer">
                        <option value="no">Nessuno</option>

                        <option value="Prezzo DESC"
                        <?php 
                            if(isset($_GET["ordinaPer"]) && $_GET["ordinaPer"]=="Prezzo DESC"){
                                echo "selected";
                                $ordina = true;
                            } 
                        ?>>Prezzo ↓</option>

                        <option value="Prezzo ASC"
                        <?php 
                            if(isset($_GET["ordinaPer"]) && $_GET["ordinaPer"]=="Prezzo ASC"){
                                echo "selected";
                                $ordina = true;
                            } 
                        ?>>Prezzo ↑</option>

                        <option value="Nome ASC"
                        <?php 
                            if(isset($_GET["ordinaPer"]) && $_GET["ordinaPer"]=="Nome ASC"){
                                echo "selected";
                                $ordina = true;
                            } 
                        ?>>Nome A-Z</option>
                        <option value="Nome DESC"
                        <?php 
                            if(isset($_GET["ordinaPer"]) && $_GET["ordinaPer"]=="Nome DESC"){
                                echo "selected";
                                $ordina = true;
                            } 
                        ?>>Nome Z-A</option>
                    </select>
                    <input type="submit" value="Applica">
                </form>
            </div>
        </div>
        <?php
            $sql = "SELECT * FROM piatti ";
            if(isset($_GET["prezzoMin"]) && isset($_GET["prezzoMax"]) && $_GET["prezzoMax"]>$_GET["prezzoMin"])
                $sql .= " WHERE Prezzo >= $_GET[prezzoMin] AND Prezzo <= $_GET[prezzoMax]";
            if($ordina)
                $sql .= " GROUP BY $_GET[ordinaPer] ";
            $results = $conn->query($sql);
            if($results->rowCount() < 1){
                echo "<h1 class='titoloPagina'>Al momento non abbiamo nessun piatto disponibile</h1>";
            }else{
                $tabella = $results->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class='card-container'>";
                foreach($tabella as $piatto){
                    echo "  <a href='visualizzaPiatto.php?idPiatto=$piatto[IDPiatto]'>";
                    echo "      <div class='card'>";
                    echo "          <img class='card-img' src='img/piatti/$piatto[Immagine]'>";
                    echo "          <h2 class='card-titolo'>$piatto[Nome]</h2>";
                    echo "          <p class='card-testo'>$piatto[Prezzo]€</p>";
                    echo "      </div>";
                    echo "  </a>";
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