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

    if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
        header("location:gestionePiatti.php");
        exit();
    }
    $id = (int)$_GET["id"];

    // Carica dati piatto
    $res = $conn->prepare("SELECT * FROM piatti WHERE IDPiatto = ?");
    $res->execute([$id]);
    if($res->rowCount() == 0){
        $_SESSION["gpi_errore"] = "Piatto non trovato";
        header("location:gestionePiatti.php");
        exit();
    }
    $piatto = $res->fetch(PDO::FETCH_ASSOC);

    // Ingredienti già collegati al piatto
    $resCollegati = $conn->prepare("SELECT IDIngrediente FROM aux_piatti_ingredienti WHERE IDPiatto = ?");
    $resCollegati->execute([$id]);
    $collegati = array_column($resCollegati->fetchAll(PDO::FETCH_ASSOC), "IDIngrediente");

    // Tutti gli ingredienti disponibili
    $resIng = $conn->query("SELECT * FROM ingredienti ORDER BY Nome ASC");
    $ingredienti = $resIng->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartristo - Modifica piatto</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modificaPiatto.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Modifica piatto</h1>

    <?php
    if(isset($_SESSION["mpi_errore"])){
        echo "<p class='mpi-msg mpi-msg-errore'>".$_SESSION["mpi_errore"]."</p>";
        unset($_SESSION["mpi_errore"]);
    }
    if(isset($_SESSION["mpi_ok"])){
        echo "<p class='mpi-msg mpi-msg-ok'>".$_SESSION["mpi_ok"]."</p>";
        unset($_SESSION["mpi_ok"]);
    }
    ?>

    <div class="mpi-sezione">
        <p class="mpi-sezione-label"><?php echo htmlspecialchars($piatto["Nome"]); ?></p>
        <form class="mpi-form" method="post" action="checkModificaPiatto.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="immagine_attuale" value="<?php echo $piatto['Immagine']; ?>">

            <div class="mpi-campo">
                <label>Nome</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($piatto['Nome']); ?>" required>
            </div>
            <div class="mpi-campo">
                <label>Prezzo (€)</label>
                <input type="number" name="prezzo" value="<?php echo $piatto['Prezzo']; ?>" min="0" step="0.01" required>
            </div>

            <div class="mpi-campo">
                <label>Categoria</label>
                <select name="categoria">
                    <?php
                    $categorieValide = ["Antipasto","Primo","Secondo","Contorno","Dessert","Bevanda"];
                    foreach($categorieValide as $cat){
                        $selected = $piatto["Categoria"] === $cat ? "selected" : "";
                        echo "<option value='$cat' $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mpi-sep-form"></div>

            <div class="mpi-campo mpi-campo-full mpi-immagine-attuale">
                <label>Immagine attuale</label>
                <div class="mpi-preview">
                    <img src="img/piatti/<?php echo $piatto['Immagine']; ?>" alt="<?php echo htmlspecialchars($piatto['Nome']); ?>">
                    <span><?php echo $piatto['Immagine']; ?></span>
                </div>
            </div>
            <div class="mpi-campo mpi-campo-full">
                <label>Nuova immagine (opzionale — lascia vuoto per mantenerla)</label>
                <input type="file" name="immagine" accept="image/webp,image/jpeg,image/png">
                <span class="mpi-hint">Max 2MB. webp, jpg, png.</span>
            </div>

            <div class="mpi-sep-form"></div>

            <div class="mpi-campo mpi-campo-full">
                <label>Ingredienti collegati</label>
                <?php if(empty($ingredienti)): ?>
                    <p class="mpi-vuoto">Nessun ingrediente disponibile.</p>
                <?php else: ?>
                    <div class="mpi-checkbox-grid">
                        <?php foreach($ingredienti as $ing):
                            $checked = in_array($ing["IDIngrediente"], $collegati) ? "checked" : "";
                        ?>
                        <label class="mpi-checkbox-label">
                            <input type="checkbox" name="ingredienti[]" value="<?php echo $ing['IDIngrediente']; ?>" <?php echo $checked; ?>>
                            <span><?php echo htmlspecialchars($ing['Nome']); ?> <small>(<?php echo $ing['Quantita']." ".$ing['UnitaMisura']; ?>)</small></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mpi-campo mpi-campo-full mpi-bottoni">
                <a class="mpi-btn-annulla" href="gestionePiatti.php">Annulla</a>
                <button type="submit">Salva modifiche</button>
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