<?php
session_start();
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
        header("location:gestioneIngredienti.php");
        exit();
    }
    $id = (int)$_GET["id"];

    $res = $conn->prepare("SELECT * FROM ingredienti WHERE IDIngrediente = ?");
    $res->execute([$id]);
    if($res->rowCount() == 0){
        $_SESSION["gi_errore"] = "Ingrediente non trovato";
        header("location:gestioneIngredienti.php");
        exit();
    }
    $ing = $res->fetch(PDO::FETCH_ASSOC);

    $resSpec = $conn->prepare("SELECT IDSpecifica FROM aux_ingredienti_specifiche WHERE IDIngrediente = ?");
    $resSpec->execute([$id]);
    $specifColleg = array_column($resSpec->fetchAll(PDO::FETCH_ASSOC), "IDSpecifica");

    $tutteSpec = $conn->query("SELECT * FROM specifiche ORDER BY Nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartristo - Modifica ingrediente</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modificaIngrediente.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Modifica ingrediente</h1>

    <?php
    if(isset($_SESSION["mi_errore"])){
        echo "<p class='mi-msg mi-msg-errore'>".$_SESSION["mi_errore"]."</p>";
        unset($_SESSION["mi_errore"]);
    }
    if(isset($_SESSION["mi_ok"])){
        echo "<p class='mi-msg mi-msg-ok'>".$_SESSION["mi_ok"]."</p>";
        unset($_SESSION["mi_ok"]);
    }
    ?>

    <div class="mi-sezione">
        <p class="mi-sezione-label"><?php echo htmlspecialchars($ing["Nome"]); ?></p>
        <form class="mi-form" method="post" action="checkModificaIngrediente.php">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="mi-campo">
                <label>Nome</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($ing['Nome']); ?>" required>
            </div>
            <div class="mi-campo">
                <label>Unità di misura</label>
                <select name="unita">
                    <option value="g"  <?php if($ing['UnitaMisura']=='g')  echo 'selected'; ?>>g — grammi</option>
                    <option value="kg" <?php if($ing['UnitaMisura']=='kg') echo 'selected'; ?>>kg — chilogrammi</option>
                    <option value="pz" <?php if($ing['UnitaMisura']=='pz') echo 'selected'; ?>>pz — pezzi</option>
                    <option value="l"  <?php if($ing['UnitaMisura']=='l')  echo 'selected'; ?>>l — litri</option>
                </select>
            </div>
            <div class="mi-campo">
                <label>Quantità attuale in magazzino</label>
                <input type="number" name="quantita" value="<?php echo $ing['Quantita']; ?>" min="0" required>
            </div>

            <div class="mi-sep-form"></div>

            <div class="mi-campo mi-campo-full">
                <label>Specifiche alimentari</label>
                <?php if(empty($tutteSpec)): ?>
                    <p class="mi-vuoto">Nessuna specifica disponibile.</p>
                <?php else: ?>
                    <div class="mi-checkbox-grid">
                        <?php foreach($tutteSpec as $sp):
                            $checked = in_array($sp["IDSpecifica"], $specifColleg) ? "checked" : "";
                        ?>
                        <label class="mi-checkbox-label">
                            <input type="checkbox" name="specifiche[]" value="<?php echo $sp['IDSpecifica']; ?>" <?php echo $checked; ?>>
                            <img src="img/specifiche/<?php echo $sp['Immagine']; ?>" alt="<?php echo $sp['Nome']; ?>">
                            <span><?php echo htmlspecialchars($sp['Nome']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mi-campo mi-campo-full mi-bottoni">
                <a class="mi-btn-annulla" href="gestioneIngredienti.php">Annulla</a>
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