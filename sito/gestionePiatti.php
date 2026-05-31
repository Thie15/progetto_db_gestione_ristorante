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
    <title>Smartristo - Gestione piatti</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/gestionePiatti.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Gestione piatti</h1>

    <?php
    if(isset($_SESSION["gpi_errore"])){
        echo "<p class='gpi-msg gpi-msg-errore'>".$_SESSION["gpi_errore"]."</p>";
        unset($_SESSION["gpi_errore"]);
    }
    if(isset($_SESSION["gpi_ok"])){
        echo "<p class='gpi-msg gpi-msg-ok'>".$_SESSION["gpi_ok"]."</p>";
        unset($_SESSION["gpi_ok"]);
    }
    ?>

    <!-- LISTA PIATTI -->
    <div class="gpi-sezione">
        <p class="gpi-sezione-label">Piatti in menu</p>
        <?php
        $res = $conn->query("
            SELECT p.*,
                GROUP_CONCAT(i.Nome ORDER BY i.Nome SEPARATOR ', ') AS Ingredienti
            FROM piatti p
            LEFT JOIN aux_piatti_ingredienti api ON api.IDPiatto = p.IDPiatto
            LEFT JOIN ingredienti i ON i.IDIngrediente = api.IDIngrediente
            GROUP BY p.IDPiatto
            ORDER BY p.Nome ASC
        ");
        if($res->rowCount() < 1){
            echo "<p class='gpi-vuoto'>Nessun piatto nel menu.</p>";
        } else {
            $piatti = $res->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='gpi-tabella'>";
            echo "<thead><tr>
                    <th></th>
                    <th>Nome</th>
                    <th>Prezzo</th>
                    <th>Categoria</th>
                    <th>Ingredienti</th>
                    <th>Azioni</th>
                </tr></thead>";
            foreach($piatti as $p){
                $ingredienti = $p["Ingredienti"] ?? "<span class='gpi-nessuno'>nessuno</span>";
                echo "<tr>";
                echo "  <td><img class='gpi-thumb' src='img/piatti/{$p['Immagine']}' alt='{$p['Nome']}'></td>";
                echo "  <td class='gpi-nome'>{$p['Nome']}</td>";
                echo "  <td class='gpi-prezzo'>".number_format($p['Prezzo'], 2)."€</td>";
                echo "  <td><span class='gpi-badge-cat'>{$p['Categoria']}</span></td>";
                echo "  <td class='gpi-ingredienti'>$ingredienti</td>";
                echo "  <td>
                            <a class='gpi-btn gpi-btn-modifica' href='modificaPiatto.php?id={$p['IDPiatto']}'>Modifica</a>
                            <a class='gpi-btn gpi-btn-elimina' href='checkGestionePiatti.php?azione=elimina&id={$p['IDPiatto']}'
                            onclick=\"return confirm('Eliminare il piatto {$p['Nome']}?')\">Elimina</a>
                        </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>

    <!-- FORM AGGIUNGI -->
    <div class="gpi-sezione">
        <p class="gpi-sezione-label">Aggiungi nuovo piatto</p>
        <form class="gpi-form" method="post" action="checkGestionePiatti.php?azione=aggiungi" enctype="multipart/form-data">

            <!-- Dati base -->
            <div class="gpi-campo">
                <label>Nome</label>
                <input type="text" name="nome" placeholder="Es. Lasagne al ragù" required>
            </div>
            <div class="gpi-campo">
                <label>Prezzo (€)</label>
                <input type="number" name="prezzo" placeholder="12.50" min="0" step="0.01" required>
            </div>

            <div class="gpi-campo">
                <label>Categoria</label>
                <select name="categoria">
                    <option value="Antipasto">Antipasto</option>
                    <option value="Primo">Primo</option>
                    <option value="Secondo">Secondo</option>
                    <option value="Contorno">Contorno</option>
                    <option value="Dessert">Dessert</option>
                    <option value="Bevanda">Bevanda</option>
                </select>
            </div>

            <div class="gpi-sep-form"></div>

            <!-- Upload immagine -->
            <div class="gpi-campo gpi-campo-full">
                <label>Immagine piatto (webp - max 2MB)</label>
                <input type="file" name="immagine" accept="image/webp" required>
                <span class="gpi-hint">Il file verrà rinominato automaticamente con l'ID del piatto.</span>
            </div>

            <div class="gpi-sep-form"></div>

            <!-- Ingredienti -->
            <div class="gpi-campo gpi-campo-full">
                <label>Ingredienti collegati</label>
                <?php
                $resIng = $conn->query("SELECT * FROM ingredienti ORDER BY Nome ASC");
                if($resIng->rowCount() < 1){
                    echo "<p class='gpi-vuoto'>Nessun ingrediente disponibile — aggiungili prima dalla gestione ingredienti.</p>";
                } else {
                    $ingredienti = $resIng->fetchAll(PDO::FETCH_ASSOC);
                    echo "<div class='gpi-checkbox-grid'>";
                    foreach($ingredienti as $ing){
                        echo "<label class='gpi-checkbox-label'>";
                        echo "  <input type='checkbox' name='ingredienti[]' value='{$ing['IDIngrediente']}'>";
                        echo "  <span>{$ing['Nome']} <small>({$ing['Quantita']} {$ing['UnitaMisura']})</small></span>";
                        echo "</label>";
                    }
                    echo "</div>";
                }
                ?>
            </div>

            <div class="gpi-campo gpi-campo-full">
                <button type="submit">Aggiungi piatto</button>
            </div>
        </form>
    </div>

<?php
} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>
</body>
</html>