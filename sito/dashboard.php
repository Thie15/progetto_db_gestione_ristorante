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
                $results = $conn->prepare("SELECT * FROM personale WHERE IDPersonale = ?");
                $results->execute([$_SESSION["personale"]]);
                if($results->rowCount()==1){
                    $row = $results->fetch();
                    echo "<h1 class='titoloPagina'>Dashboard $row[Nome] $row[Cognome]</h1>";
                    echo "<div class='datipersonale'>";
                    echo "  <h3>Dati personali</h3>";
                    echo "  <p>Indirizzo: $row[Indirizzo_Comune], $row[Indirizzo_Via] n°$row[Indirizzo_Civico] - $row[Indirizzo_CAP]</p>";
                    echo "  <p>Turno: $row[Turno]</p>";
                    echo "  <p>Stipendio: $row[Stipendio]€</p>";
                    echo "</div>";
                    $results = $conn->prepare("SELECT * FROM timbrature WHERE IDPersonale = ? ORDER BY DataTimbratura DESC, Ora DESC");
                    $results->execute([$_SESSION["personale"]]);
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
                    echo "<div class='bottoni-timbratura'>";
                    echo "<a href='timbra.php?tipo=Entrata' class='btn-timbra'>Timbra entrata</a>";
                    echo "<a href='timbra.php?tipo=Uscita' class='btn-timbra'>Timbra uscita</a>";
                    echo "</div>";
                }else{
                    echo "<h2>Nessun dato trovato</h2>";
                }
            }
            if(isset($_SESSION["fornitore"])){
                $results = $conn->prepare("SELECT * FROM fornitori WHERE IDFornitore = ?");
                $results->execute([$_SESSION["fornitore"]]);
                if($results->rowCount()==1){
                    $row = $results->fetch();
                    echo "<h1 class='titoloPagina'>Dashboard {$row['Nome']}</h1>";
                    echo "<div class='datifornitore'>";
                    echo "  <h3>Dati aziendali</h3>";
                    echo "  <p>Indirizzo: {$row['Indirizzo_Comune']}, {$row['Indirizzo_Via']} n°{$row['Indirizzo_Civico']} - {$row['Indirizzo_CAP']}</p>";
                    echo "  <p>Partita IVA: {$row['PIVA']}</p>";
                    echo "</div>";

                    $results = $conn->prepare("
                        SELECT of2.*,
                            GROUP_CONCAT(i.Nome, ' — ', aio.Quantita, ' ', aio.UnitaMisura ORDER BY i.Nome SEPARATOR ' | ') AS Dettaglio
                        FROM ordinifornitori of2
                        LEFT JOIN aux_ingredienti_ordinifornitori aio ON aio.IDOrdineFornitore = of2.IDOrdineFornitore
                        LEFT JOIN ingredienti i ON i.IDIngrediente = aio.IDIngrediente
                        WHERE of2.IDFornitore = ?
                        GROUP BY of2.IDOrdineFornitore
                        ORDER BY of2.DataOrdine DESC
                    ");
                    $results->execute([$_SESSION["fornitore"]]);

                    if($results->rowCount() >= 1){
                        $ordini = $results->fetchAll(PDO::FETCH_ASSOC);
                        echo "<div class='ordini'>";
                        echo "  <h3>Ordini effettuati</h3>";
                        echo "  <h4>Data ordine</h4>";
                        echo "  <h4>Consegna prevista</h4>";
                        echo "  <h4>Stato</h4>";
                        echo "  <h4>Ingredienti</h4>";
                        foreach($ordini as $ordine){
                            $stato = $ordine["Consegnato"]
                                ? "<span style='color:#22c55e;font-weight:500;'><i class='fa-solid fa-check'></i> Consegnato</span>"
                                : "<span style='color:#f59e0b;font-weight:500;'><i class='fa-solid fa-clock'></i> In attesa</span>";
                            $consegna = $ordine["DataConsegna"] ?? "<span style='color:#555;font-style:italic;'>non specificata</span>";
                            $dettaglio = $ordine["Dettaglio"] ?? "<span style='color:#555;font-style:italic;'>nessun dettaglio</span>";
                            echo "  <p>{$ordine['DataOrdine']}</p>";
                            echo "  <p>$consegna</p>";
                            echo "  <p>$stato</p>";
                            echo "  <p>$dettaglio</p>";
                        }
                        echo "</div>";
                    } else {
                        echo "<h2>Nessun ordine effettuato</h2>";
                    }
                } else {
                    echo "<h2>Nessun dato trovato</h2>";
                }
            }
            if(!isset($_SESSION["personale"]) && !isset($_SESSION["fornitore"])){
                echo "<h1 class='titoloPagina'>Dashboard admin</h1>";

                $nPersonale   = $conn->query("SELECT COUNT(*) FROM personale")->fetchColumn();
                $nPiatti      = $conn->query("SELECT COUNT(*) FROM piatti")->fetchColumn();
                $nFornitori   = $conn->query("SELECT COUNT(*) FROM fornitori")->fetchColumn();
                $nPrenotazioni = $conn->query("SELECT COUNT(*) FROM prenotazioni WHERE DataPrenotazione = CURDATE()")->fetchColumn();

                echo "<div class='admin-stats'>";
                echo "  <div class='stat-box'><span class='stat-num'>$nPersonale</span><span class='stat-label'>Dipendenti</span></div>";
                echo "  <div class='stat-box'><span class='stat-num'>$nPiatti</span><span class='stat-label'>Piatti in menu</span></div>";
                echo "  <div class='stat-box'><span class='stat-num'>$nFornitori</span><span class='stat-label'>Fornitori</span></div>";
                echo "  <div class='stat-box'><span class='stat-num'>$nPrenotazioni</span><span class='stat-label'>Prenotazioni oggi</span></div>";
                echo "</div>";

                echo "<div class='admin-sezioni'>";
                $sezioni = [
                    ["href" => "gestionePersonale.php",   "titolo" => "Gestione personale",    "desc" => "Aggiungi, modifica o rimuovi dipendenti e gestisci i turni",          "classe" => "icona-verde"],
                    ["href" => "gestionePiatti.php",      "titolo" => "Gestione piatti",       "desc" => "Aggiorna il menu, i prezzi e le immagini dei piatti",                  "classe" => "icona-ambra"],
                    ["href" => "gestioneFornitori.php",   "titolo" => "Gestione fornitori",    "desc" => "Gestisci i fornitori e visualizza gli ordini effettuati",              "classe" => "icona-blu"],
                    ["href" => "gestioneIngredienti.php", "titolo" => "Gestione ingredienti",  "desc" => "Controlla il magazzino e le scorte degli ingredienti",                 "classe" => "icona-viola"],
                ];
                foreach($sezioni as $s){
                    echo "<a class='admin-card' href='{$s['href']}'>";
                    echo "  <div class='admin-card-icona {$s['classe']}'></div>";
                    echo "  <div class='admin-card-testo'>";
                    echo "      <h3>{$s['titolo']}</h3>";
                    echo "      <p>{$s['desc']}</p>";
                    echo "  </div>";
                    echo "</a>";
                }
                echo "</div>";
            }
        ?> 
        <div class='bottoni-dashboard'>
            <a class='btn-dashboard' href='logout.php'>Effettua logout</a>
            <a class='btn-dashboard' href='modificaPassword.php'>Modifica password</a>
        </div>
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>