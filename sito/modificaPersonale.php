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
        header("location:gestionePersonale.php");
        exit();
    }
    $id = (int)$_GET["id"];

    // Carica i dati del dipendente
    $res = $conn->prepare("
        SELECT p.*,
            a.Username, a.Email,
            CASE
                WHEN c.IDPersonale IS NOT NULL THEN 'cuoco'
                WHEN cam.IDPersonale IS NOT NULL THEN 'cameriere'
                ELSE 'nessuno'
            END AS Ruolo
        FROM personale p
        LEFT JOIN account a ON a.IDPersonale = p.IDPersonale
        LEFT JOIN cuochi c ON c.IDPersonale = p.IDPersonale
        LEFT JOIN camerieri cam ON cam.IDPersonale = p.IDPersonale
        WHERE p.IDPersonale = ?
    ");
    $res->execute([$id]);

    if($res->rowCount() == 0){
        $_SESSION["gp_errore"] = "Dipendente non trovato";
        header("location:gestionePersonale.php");
        exit();
    }
    $p = $res->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartristo - Modifica dipendente</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modificaPersonale.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Modifica dipendente</h1>

    <?php
    if(isset($_SESSION["mp_errore"])){
        echo "<p class='mp-msg mp-msg-errore'>".$_SESSION["mp_errore"]."</p>";
        unset($_SESSION["mp_errore"]);
    }
    if(isset($_SESSION["mp_ok"])){
        echo "<p class='mp-msg mp-msg-ok'>".$_SESSION["mp_ok"]."</p>";
        unset($_SESSION["mp_ok"]);
    }
    ?>

    <div class="mp-sezione">
        <p class="mp-sezione-label"><?php echo $p["Nome"]." ".$p["Cognome"]; ?></p>
        <form class="mp-form" method="post" action="checkModificaPersonale.php">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="mp-campo">
                <label>Nome</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($p['Nome']); ?>" required>
            </div>
            <div class="mp-campo">
                <label>Cognome</label>
                <input type="text" name="cognome" value="<?php echo htmlspecialchars($p['Cognome']); ?>" required>
            </div>
            <div class="mp-campo">
                <label>Ruolo</label>
                <div class="mp-campo-readonly">
                    <span class="gp-badge badge-<?php echo $p['Ruolo']; ?>">
                        <?php echo ucfirst($p['Ruolo']); ?>
                    </span>
                </div>
            </div>
            <div class="mp-campo">
                <label>Turno</label>
                <select name="turno">
                    <option value="Pranzo" <?php if($p["Turno"]=="Pranzo") echo "selected"; ?>>Pranzo</option>
                    <option value="Cena"   <?php if($p["Turno"]=="Cena")   echo "selected"; ?>>Cena</option>
                </select>
            </div>
            <div class="mp-campo">
                <label>Stipendio (€)</label>
                <input type="number" name="stipendio" value="<?php echo $p['Stipendio']; ?>" min="0" step="0.01" required>
            </div>
            <div class="mp-campo">
                <label>Comune</label>
                <input type="text" name="comune" value="<?php echo htmlspecialchars($p['Indirizzo_Comune']); ?>" required>
            </div>
            <div class="mp-campo">
                <label>Via</label>
                <input type="text" name="via" value="<?php echo htmlspecialchars($p['Indirizzo_Via']); ?>" required>
            </div>
            <div class="mp-campo">
                <label>Civico</label>
                <input type="text" name="civico" value="<?php echo htmlspecialchars($p['Indirizzo_Civico']); ?>" required>
            </div>
            <div class="mp-campo">
                <label>CAP</label>
                <input type="text" name="cap" value="<?php echo $p['Indirizzo_CAP']; ?>" required>
            </div>

            <div class="mp-sep-form"></div>

            <div class="mp-campo">
                <label>Username account</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($p['Username'] ?? ''); ?>" required>
            </div>
            <div class="mp-campo">
                <label>Email account</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($p['Email'] ?? ''); ?>" required>
            </div>

            <div class="mp-campo mp-campo-full mp-bottoni">
                <a class="mp-btn-annulla" href="gestionePersonale.php">Annulla</a>
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