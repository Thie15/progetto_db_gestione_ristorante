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
        header("location:gestioneFornitori.php");
        exit();
    }
    $id = (int)$_GET["id"];

    $res = $conn->prepare("
        SELECT f.*, a.Username, a.Email
        FROM fornitori f
        LEFT JOIN account a ON a.IDFornitore = f.IDFornitore
        WHERE f.IDFornitore = ?
    ");
    $res->execute([$id]);

    if($res->rowCount() == 0){
        $_SESSION["gf_errore"] = "Fornitore non trovato";
        header("location:gestioneFornitori.php");
        exit();
    }
    $f = $res->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartristo - Modifica fornitore</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modificaFornitore.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Modifica fornitore</h1>

    <?php
    if(isset($_SESSION["mf_errore"])){
        echo "<p class='mf-msg mf-msg-errore'>".$_SESSION["mf_errore"]."</p>";
        unset($_SESSION["mf_errore"]);
    }
    if(isset($_SESSION["mf_ok"])){
        echo "<p class='mf-msg mf-msg-ok'>".$_SESSION["mf_ok"]."</p>";
        unset($_SESSION["mf_ok"]);
    }
    ?>

    <div class="mf-sezione">
        <p class="mf-sezione-label"><?php echo htmlspecialchars($f["Nome"]); ?></p>
        <form class="mf-form" method="post" action="checkModificaFornitore.php">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="mf-campo">
                <label>Nome azienda</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($f['Nome']); ?>" required>
            </div>
            <div class="mf-campo">
                <label>Partita IVA</label>
                <input type="text" name="piva" value="<?php echo htmlspecialchars($f['PIVA']); ?>" maxlength="11" required>
            </div>
            <div class="mf-campo">
                <label>Comune</label>
                <input type="text" name="comune" value="<?php echo htmlspecialchars($f['Indirizzo_Comune']); ?>" required>
            </div>
            <div class="mf-campo">
                <label>Via</label>
                <input type="text" name="via" value="<?php echo htmlspecialchars($f['Indirizzo_Via']); ?>" required>
            </div>
            <div class="mf-campo">
                <label>Civico</label>
                <input type="text" name="civico" value="<?php echo htmlspecialchars($f['Indirizzo_Civico']); ?>" required>
            </div>
            <div class="mf-campo">
                <label>CAP</label>
                <input type="text" name="cap" value="<?php echo $f['Indirizzo_CAP']; ?>" required>
            </div>

            <div class="mf-sep-form"></div>

            <div class="mf-campo">
                <label>Username account</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($f['Username'] ?? ''); ?>" required>
            </div>
            <div class="mf-campo">
                <label>Email account</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($f['Email'] ?? ''); ?>" required>
            </div>

            <div class="mf-campo mf-campo-full mf-bottoni">
                <a class="mf-btn-annulla" href="gestioneFornitori.php">Annulla</a>
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