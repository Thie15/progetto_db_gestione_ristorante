<?php
    session_start();
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
        if(isset($_GET["idPiatto"])){
            $idPiatto = $_GET["idPiatto"];
            $results = $conn->prepare("SELECT * FROM Piatti WHERE IDPiatto = ?");
            $results->execute([$idPiatto]);
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
        <link rel="stylesheet" href="css/visualizzaPiatto.css">
    </head>
    <body>
        <?php
            include("inc/header.php");
        ?>
        <h1 class="titoloPagina"><?php echo $titoloPagina; ?></h1>
        <div class="piatto-layout">
            <img class="immagine-cover" src="img/piatti/<?php echo $piatto['Immagine']; ?>" alt="<?php echo $piatto['Nome']; ?>">
            <div class="piatto-info">
                <h2 class="piatto-nome"><?php echo $piatto['Nome']; ?></h2>
                <p class="piatto-prezzo"><?php echo $piatto['Prezzo']; ?>€</p>

                <?php
                $results = $conn->prepare("SELECT * FROM Ingredienti INNER JOIN aux_piatti_ingredienti USING(IDIngrediente) WHERE IDPiatto = ?");
                $results->execute([$idPiatto]);
                if ($results->rowCount() >= 1) {
                    $ingredienti = $results->fetchAll(PDO::FETCH_ASSOC);
                    echo "<div class='piatto-sezione'>";
                    echo "<p class='piatto-sezione-label'>Ingredienti</p>";
                    $nomi = array_column($ingredienti, 'Nome');
                    $ultimo = array_pop($nomi);
                    $testo = count($nomi) > 0 ? implode(', ', $nomi) . ' e ' . $ultimo : $ultimo;
                    echo "<p class='testo'>$testo.</p>";
                    echo "</div>";

                    // Specifiche
                    $tutte_specifiche = [];
                    foreach ($ingredienti as $ing) {
                        $rs = $conn->prepare("SELECT * FROM specifiche INNER JOIN aux_ingredienti_specifiche USING(IDspecifica) WHERE IDIngrediente = ?");
                        $rs->execute([$ing['IDIngrediente']]);
                        if ($rs->rowCount() >= 1) {
                            foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $sp) {
                                $tutte_specifiche[$sp['IDSpecifica']] = $sp;
                            }
                        }
                    }
                    if (!empty($tutte_specifiche)) {
                        echo "<div class='piatto-sezione'>";
                        echo "<p class='piatto-sezione-label'>Specifiche</p>";
                        echo "<ul class='specifiche-lista'>";
                        foreach ($tutte_specifiche as $sp) {
                            echo "<li class='specifica-badge'>";
                            echo "<img class='icona-specifica' src='img/specifiche/$sp[Immagine]' alt='$sp[Nome]'>";
                            echo "$sp[Nome]";
                            echo "</li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>