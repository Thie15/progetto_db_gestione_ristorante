<?php
include("inc/datiConnessione.php");
try {
    include("inc/startConn.php");
    include("inc/checklogin.php");

    if(!$_SESSION["logged"] || isset($_SESSION["personale"]) || isset($_SESSION["fornitore"])){
        session_unset();
        session_destroy();
        header("location:login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartristo - Gestione ingredienti</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/gestioneIngredienti.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Gestione ingredienti</h1>

    <?php
    if(isset($_SESSION["gi_errore"])){
        echo "<p class='gi-msg gi-msg-errore'>".$_SESSION["gi_errore"]."</p>";
        unset($_SESSION["gi_errore"]);
    }
    if(isset($_SESSION["gi_ok"])){
        echo "<p class='gi-msg gi-msg-ok'>".$_SESSION["gi_ok"]."</p>";
        unset($_SESSION["gi_ok"]);
    }
    ?>

    <!-- LISTA INGREDIENTI -->
    <div class="gi-sezione">
        <p class="gi-sezione-label">Magazzino ingredienti</p>
        <?php
        $res = $conn->query("
            SELECT i.*,
                GROUP_CONCAT(s.Nome ORDER BY s.Nome SEPARATOR ', ') AS Specifiche,
                COUNT(DISTINCT api.IDPiatto) AS NumPiatti
            FROM ingredienti i
            LEFT JOIN aux_ingredienti_specifiche ais ON ais.IDIngrediente = i.IDIngrediente
            LEFT JOIN specifiche s ON s.IDSpecifica = ais.IDSpecifica
            LEFT JOIN aux_piatti_ingredienti api ON api.IDIngrediente = i.IDIngrediente
            GROUP BY i.IDIngrediente
            ORDER BY i.Nome ASC
        ");
        if($res->rowCount() < 1){
            echo "<p class='gi-vuoto'>Nessun ingrediente in magazzino.</p>";
        } else {
            $ingredienti = $res->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='gi-tabella'>";
            echo "<thead><tr>
                    <th>Nome</th>
                    <th>Quantità</th>
                    <th>U.M.</th>
                    <th>Specifiche</th>
                    <th>Piatti</th>
                    <th>Azioni</th>
                  </tr></thead><tbody>";
            foreach($ingredienti as $ing){
                $specifiche = $ing["Specifiche"]
                    ? "<span class='gi-specifiche'>{$ing['Specifiche']}</span>"
                    : "<span class='gi-nessuno'>—</span>";

                $qtaClass = $ing["Quantita"] <= 0 ? "gi-qta gi-qta-bassa" : "gi-qta";
                $qta = "<span class='$qtaClass'>{$ing['Quantita']} {$ing['UnitaMisura']}</span>";

                $nomeIngrediente = htmlspecialchars($ing["Nome"], ENT_QUOTES);
                $numPiatti = $ing["NumPiatti"];
                $btnElimina = $numPiatti > 0
                    ? "<span class='gi-btn gi-btn-disabled' title='Collegato a $numPiatti piatto/i'>Elimina</span>"
                    : "<a class='gi-btn gi-btn-elimina' href='checkGestioneIngredienti.php?azione=elimina&id={$ing['IDIngrediente']}'
                        onclick=\"return confirm('Eliminare $nomeIngrediente?')\">Elimina</a>";
                echo "<tr>";
                echo "  <td class='gi-nome'>{$ing['Nome']}</td>";
                echo "  <td>$qta</td>";
                echo "  <td class='gi-um'>{$ing['UnitaMisura']}</td>";
                echo "  <td>$specifiche</td>";
                echo "  <td class='gi-piatti'>{$ing['NumPiatti']}</td>";
                echo "  <td>
                            <a class='gi-btn gi-btn-modifica' href='modificaIngrediente.php?id={$ing['IDIngrediente']}'>Modifica</a>
                            $btnElimina
                        </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>

    <!-- FORM AGGIUNGI INGREDIENTE -->
    <div class="gi-sezione">
        <p class="gi-sezione-label">Aggiungi nuovo ingrediente</p>
        <form class="gi-form" method="post" action="checkGestioneIngredienti.php?azione=aggiungi">
            <div class="gi-campo">
                <label>Nome ingrediente</label>
                <input type="text" name="nome" placeholder="Es. Pomodoro" required>
            </div>
            <div class="gi-campo">
                <label>Unità di misura</label>
                <select name="unita">
                    <option value="g">g — grammi</option>
                    <option value="kg">kg — chilogrammi</option>
                    <option value="pz">pz — pezzi</option>
                    <option value="l">l — litri</option>
                </select>
            </div>

            <div class="gi-sep-form"></div>

            <div class="gi-campo gi-campo-full">
                <label>Specifiche alimentari (opzionale)</label>
                <?php
                $resSpec = $conn->query("SELECT * FROM specifiche ORDER BY Nome ASC");
                if($resSpec->rowCount() < 1){
                    echo "<p class='gi-vuoto'>Nessuna specifica disponibile.</p>";
                } else {
                    $specifiche = $resSpec->fetchAll(PDO::FETCH_ASSOC);
                    echo "<div class='gi-checkbox-grid'>";
                    foreach($specifiche as $sp){
                        echo "<label class='gi-checkbox-label'>";
                        echo "  <input type='checkbox' name='specifiche[]' value='{$sp['IDSpecifica']}'>";
                        echo "  <img src='img/specifiche/{$sp['Immagine']}' alt='{$sp['Nome']}'>";
                        echo "  <span>".htmlspecialchars($sp['Nome'])."</span>";
                        echo "</label>";
                    }
                    echo "</div>";
                }
                ?>
            </div>

            <div class="gi-campo gi-campo-full">
                <button type="submit">Aggiungi ingrediente</button>
            </div>
        </form>
    </div>

    <!-- ORDINI IN ATTESA -->
    <div class="gi-sezione">
        <p class="gi-sezione-label">Ordini in attesa di consegna</p>
        <?php
        $resOrdini = $conn->query("
            SELECT of2.*, f.Nome AS NomeFornitore,
                GROUP_CONCAT(i.Nome, ' (', aio.Quantita, ' ', aio.UnitaMisura, ')' ORDER BY i.Nome SEPARATOR ' — ') AS DettaglioIngredientI
            FROM ordinifornitori of2
            INNER JOIN fornitori f ON f.IDFornitore = of2.IDFornitore
            LEFT JOIN aux_ingredienti_ordinifornitori aio ON aio.IDOrdineFornitore = of2.IDOrdineFornitore
            LEFT JOIN ingredienti i ON i.IDIngrediente = aio.IDIngrediente
            WHERE of2.Consegnato = 0
            GROUP BY of2.IDOrdineFornitore
            ORDER BY of2.DataConsegna ASC
        ");
        if($resOrdini->rowCount() < 1){
            echo "<p class='gi-vuoto'>Nessun ordine in attesa.</p>";
        } else {
            $ordini = $resOrdini->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='gi-tabella'>";
            echo "<thead><tr>
                    <th>Fornitore</th>
                    <th>Data ordine</th>
                    <th>Consegna prevista</th>
                    <th>Ingredienti</th>
                    <th>Azioni</th>
                  </tr></thead><tbody>";
            foreach($ordini as $ord){
                $consegna = $ord["DataConsegna"] ?? "<span class='gi-nessuno'>non specificata</span>";
                echo "<tr>";
                echo "  <td class='gi-nome'>{$ord['NomeFornitore']}</td>";
                echo "  <td>{$ord['DataOrdine']}</td>";
                echo "  <td>{$consegna}</td>";
                echo "  <td class='gi-dettaglio'>{$ord['DettaglioIngredientI']}</td>";
                echo "  <td>
                            <a class='gi-btn gi-btn-consegna'
                               href='checkGestioneIngredienti.php?azione=consegna&id={$ord['IDOrdineFornitore']}'
                               onclick=\"return confirm('Confermare la consegna? Le quantità in magazzino verranno aggiornate.')\">
                               ✓ Consegnato
                            </a>
                        </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>

    <!-- FORM NUOVO ORDINE -->
    <div class="gi-sezione">
        <p class="gi-sezione-label">Crea nuovo ordine fornitore</p>
        <form class="gi-form-ordine" method="post" action="checkGestioneIngredienti.php?azione=ordina">

            <div class="gi-campo">
                <label>Fornitore</label>
                <select name="fornitore" required>
                    <option value="">— Seleziona —</option>
                    <?php
                    $resForn = $conn->query("SELECT IDFornitore, Nome FROM fornitori ORDER BY Nome ASC");
                    foreach($resForn->fetchAll(PDO::FETCH_ASSOC) as $forn){
                        echo "<option value='{$forn['IDFornitore']}'>{$forn['Nome']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="gi-campo">
                <label>Data consegna prevista</label>
                <input type="date" name="data_consegna">
            </div>

            <div class="gi-sep-form"></div>

            <div class="gi-campo gi-campo-full">
                <label>Ingredienti da ordinare</label>
                <?php
                $resIng = $conn->query("SELECT * FROM ingredienti ORDER BY Nome ASC");
                if($resIng->rowCount() < 1){
                    echo "<p class='gi-vuoto'>Nessun ingrediente disponibile.</p>";
                } else {
                    $ingredientiLista = $resIng->fetchAll(PDO::FETCH_ASSOC);
                    echo "<div class='gi-ordine-grid'>";
                    foreach($ingredientiLista as $ing){
                        echo "<div class='gi-ordine-riga'>";
                        echo "  <label class='gi-ordine-check'>";
                        echo "      <input type='checkbox' name='ingredienti[]' value='{$ing['IDIngrediente']}' class='gi-check-ing'>";
                        echo "      <span>{$ing['Nome']}</span>";
                        echo "  </label>";
                        echo "  <div class='gi-ordine-dettagli'>";
                        echo "      <input type='number' name='quantita[{$ing['IDIngrediente']}]' placeholder='Qtà' min='1' class='gi-input-qta' value=''>";
                        echo "      <select name='unita[{$ing['IDIngrediente']}]' class='gi-select-um'>";
                        echo "          <option value='g'" .($ing['UnitaMisura']=='g' ?' selected':'').">g</option>";
                        echo "          <option value='kg'".($ing['UnitaMisura']=='kg'?' selected':'').">kg</option>";
                        echo "          <option value='pz'".($ing['UnitaMisura']=='pz'?' selected':'').">pz</option>";
                        echo "          <option value='l'" .($ing['UnitaMisura']=='l' ?' selected':'').">l</option>";
                        echo "      </select>";
                        echo "  </div>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>

            <div class="gi-campo gi-campo-full">
                <button type="submit">Invia ordine</button>
            </div>
        </form>
    </div>

    <!-- LISTA E GESTIONE SPECIFICHE -->
    <div class="gi-sezione">
        <p class="gi-sezione-label">Specifiche alimentari</p>
        <?php
        $resSpec = $conn->query("
            SELECT s.*,
                COUNT(ais.IDIngrediente) AS NumIngredienti
            FROM specifiche s
            LEFT JOIN aux_ingredienti_specifiche ais ON ais.IDSpecifica = s.IDSpecifica
            GROUP BY s.IDSpecifica
            ORDER BY s.Nome ASC
        ");
        if($resSpec->rowCount() < 1){
            echo "<p class='gi-vuoto'>Nessuna specifica registrata.</p>";
        } else {
            $specifiche = $resSpec->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='gi-tabella'>";
            echo "<thead><tr>
                    <th></th>
                    <th>Nome</th>
                    <th>Ingredienti collegati</th>
                    <th>Azioni</th>
                </tr></thead><tbody>";
            foreach($specifiche as $sp){
                $nomeSpec = htmlspecialchars($sp["Nome"], ENT_QUOTES);
                $btnElimina = $sp["NumIngredienti"] > 0
                    ? "<span class='gi-btn gi-btn-disabled' title='Collegata a {$sp['NumIngredienti']} ingrediente/i'>Elimina</span>"
                    : "<a class='gi-btn gi-btn-elimina' href='checkGestioneIngredienti.php?azione=eliminaSpecifica&id={$sp['IDSpecifica']}'
                        onclick=\"return confirm('Eliminare la specifica $nomeSpec?')\">Elimina</a>";
                echo "<tr>";
                echo "  <td><img src='img/specifiche/{$sp['Immagine']}' style='width:24px;height:24px;object-fit:contain;'></td>";
                echo "  <td class='gi-nome'>$nomeSpec</td>";
                echo "  <td class='gi-piatti'>{$sp['NumIngredienti']}</td>";
                echo "  <td>$btnElimina</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>

        <div class="gi-sep-form" style="margin: 1.4rem 0;"></div>

        <p class="gi-sezione-label">Aggiungi nuova specifica</p>
        <form class="gi-form" method="post" action="checkGestioneIngredienti.php?azione=aggiungiSpecifica" enctype="multipart/form-data">
            <div class="gi-campo">
                <label>Nome specifica</label>
                <input type="text" name="nome" placeholder="Es. Senza glutine" required>
            </div>
            <div class="gi-campo">
                <label>Icona (png, webp — max 512KB)</label>
                <input type="file" name="immagine" accept="image/png,image/webp" required>
                <span class="gi-hint">Usa icone quadrate, meglio se su sfondo trasparente.</span>
            </div>
            <div class="gi-campo gi-campo-full">
                <button type="submit">Aggiungi specifica</button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.gi-check-ing').forEach(function(check){
            check.addEventListener('change', function(){
                const riga = this.closest('.gi-ordine-riga');
                if(this.checked){
                    riga.classList.add('attivo');
                } else {
                    riga.classList.remove('attivo');
                    riga.querySelector('.gi-input-qta').value = '';
                }
            });
        });
    </script>

<?php
} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>
</body>
</html>