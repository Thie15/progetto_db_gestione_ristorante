<?php
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
        if(isset($_GET["idPiatto"])){
            $idPiatto = $_GET["idPiatto"];
            $sql = "SELECT * FROM Piatti WHERE IDPiatto = $idPiatto";
            $results = $conn->query($sql);
            if($results->rowCount() < 1){
                $titoloPagina = "Il piatto selezionato risulta inesistente";
                $titolo = "Inesistente";
            }else{
                $piatto = $results->fetch();
                $titolo = $piatto["Nome"];
                $titoloPagina = $titolo;
            }
        }else{
            $idPiatto = "";
            $titolo = "Non settato";
            $titoloPagina = "Errore nella richiesta del piatto";
        }
?>
<html>
    <head>
        <title><?php echo $titolo; ?></title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/header.css">
        <link rel="stylesheet" href="css/visualizzaPiatto.css">
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
        <h1 class="titoloPagina"><?php echo $titoloPagina; ?></h1>
        <div class="container">
            <img class="immagine-cover" src="img/piatti/<?php echo $piatto['Immagine']; ?>" alt="">
        </div>
        <p class="testo">Il costo del piatto è di <?php echo $piatto['Prezzo']; ?>€</p>
        <?php
            $sql = "SELECT * FROM Ingredienti INNER JOIN aux_piatti_ingredienti USING(IDIngrediente) WHERE IDPiatto = $idPiatto";
            $results = $conn->query($sql);
            echo "<p class='testo'>";
            if($results->rowCount() < 1){
                echo "Nessun ingrediente collegato a questo piatto";
            }else{
                echo "Ingredienti: ";
                $ingredienti = $results->fetchAll(PDO::FETCH_ASSOC);
                $cicla = $results->rowCount();
                for($i = 0; $i < $results->rowCount(); $i++){
                    echo $ingredienti[$i]["Nome"];
                    if($i <= $results->rowCount()-3){
                        echo ", ";
                    }elseif($i == $results->rowCount()-2){
                        echo " e ";
                    }else{
                        echo ".";
                    }
                }
                echo "</p>";
                for($i = 0; $i < $cicla; $i++){
                    $sql = "SELECT * FROM specifiche INNER JOIN aux_ingredienti_specifiche USING(IDspecifica) WHERE IDIngrediente = " . $ingredienti[$i]["IDIngrediente"];
                    $results = $conn->query($sql);
                    if($results->rowCount() >= 1){
                        $specifiche = $results->fetchAll(PDO::FETCH_ASSOC);
                        foreach($specifiche as $specifica){
                            echo "<img class='icona-specifica' src='img/specifiche/$specifica[Immagine]'>";
                        }
                    }
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