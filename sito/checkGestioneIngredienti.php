<?php
session_start();
include("inc/datiConnessione.php");
try {
    include("inc/startConn.php");
    include("inc/checklogin.php");

    if(!$_SESSION["logged"] || isset($_SESSION["personale"]) || isset($_SESSION["fornitore"])){
        header("location:login.php");
        exit();
    }

    $azione = $_GET["azione"] ?? "";

    if($azione === "aggiungi"){
        if(!isset($_POST["nome"]) || trim($_POST["nome"]) == ""){
            $_SESSION["gi_errore"] = "Il nome è obbligatorio";
            header("location:gestioneIngredienti.php");
            exit();
        }
        $unitaValide = ["g","kg","pz","l"];
        if(!in_array($_POST["unita"], $unitaValide)){
            $_SESSION["gi_errore"] = "Unità di misura non valida";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $chk = $conn->prepare("SELECT IDIngrediente FROM ingredienti WHERE Nome = ?");
        $chk->execute([trim($_POST["nome"])]);
        if($chk->rowCount() > 0){
            $_SESSION["gi_errore"] = "Ingrediente già esistente";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $ins = $conn->prepare("INSERT INTO ingredienti (Nome, Quantita, UnitaMisura) VALUES (?, 0, ?)");
        $ins->execute([trim($_POST["nome"]), $_POST["unita"]]);
        $idIngrediente = $conn->lastInsertId();

        if(!empty($_POST["specifiche"])){
            $insSpec = $conn->prepare("INSERT INTO aux_ingredienti_specifiche (IDIngrediente, IDSpecifica) VALUES (?, ?)");
            foreach($_POST["specifiche"] as $idSpec){
                $idSpec = (int)$idSpec;
                $chkSpec = $conn->prepare("SELECT IDSpecifica FROM specifiche WHERE IDSpecifica = ?");
                $chkSpec->execute([$idSpec]);
                if($chkSpec->rowCount() == 1){
                    $insSpec->execute([$idIngrediente, $idSpec]);
                }
            }
}

        $_SESSION["gi_ok"] = "Ingrediente \"".trim($_POST["nome"])."\" aggiunto con quantità 0";
        header("location:gestioneIngredienti.php");
        exit();
    }

    if($azione === "elimina"){
        if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
            $_SESSION["gi_errore"] = "ID non valido";
            header("location:gestioneIngredienti.php");
            exit();
        }
        $id = (int)$_GET["id"];

        $chk = $conn->prepare("SELECT COUNT(*) FROM aux_piatti_ingredienti WHERE IDIngrediente = ?");
        $chk->execute([$id]);
        if($chk->fetchColumn() > 0){
            $_SESSION["gi_errore"] = "Impossibile eliminare: l'ingrediente è collegato a uno o più piatti";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $conn->prepare("DELETE FROM aux_ingredienti_specifiche WHERE IDIngrediente = ?")->execute([$id]);
        $conn->prepare("DELETE FROM aux_ingredienti_ordinifornitori WHERE IDIngrediente = ?")->execute([$id]);
        $del = $conn->prepare("DELETE FROM ingredienti WHERE IDIngrediente = ?");
        $del->execute([$id]);

        $_SESSION["gi_ok"] = "Ingrediente eliminato con successo";
        header("location:gestioneIngredienti.php");
        exit();
    }

    if($azione === "consegna"){
        if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
            $_SESSION["gi_errore"] = "ID ordine non valido";
            header("location:gestioneIngredienti.php");
            exit();
        }
        $idOrdine = (int)$_GET["id"];

        $chk = $conn->prepare("SELECT Consegnato FROM ordinifornitori WHERE IDOrdineFornitore = ?");
        $chk->execute([$idOrdine]);
        if($chk->rowCount() == 0){
            $_SESSION["gi_errore"] = "Ordine non trovato";
            header("location:gestioneIngredienti.php");
            exit();
        }
        if($chk->fetch()["Consegnato"] == 1){
            $_SESSION["gi_errore"] = "Ordine già segnato come consegnato";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $resIng = $conn->prepare("
            SELECT IDIngrediente, Quantita
            FROM aux_ingredienti_ordinifornitori
            WHERE IDOrdineFornitore = ?
        ");
        $resIng->execute([$idOrdine]);
        $ingredientiOrdine = $resIng->fetchAll(PDO::FETCH_ASSOC);

        $updQta = $conn->prepare("UPDATE ingredienti SET Quantita = Quantita + ? WHERE IDIngrediente = ?");
        foreach($ingredientiOrdine as $ing){
            $updQta->execute([$ing["Quantita"], $ing["IDIngrediente"]]);
        }

        $conn->prepare("UPDATE ordinifornitori SET Consegnato = 1 WHERE IDOrdineFornitore = ?")
             ->execute([$idOrdine]);

        $_SESSION["gi_ok"] = "Consegna confermata. Le quantità in magazzino sono state aggiornate";
        header("location:gestioneIngredienti.php");
        exit();
    }

    if($azione === "ordina"){
        if(!isset($_POST["fornitore"]) || !is_numeric($_POST["fornitore"])){
            $_SESSION["gi_errore"] = "Seleziona un fornitore";
            header("location:gestioneIngredienti.php");
            exit();
        }

        if(empty($_POST["ingredienti"])){
            $_SESSION["gi_errore"] = "Seleziona almeno un ingrediente da ordinare";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $idFornitore  = (int)$_POST["fornitore"];
        $dataConsegna = (!empty($_POST["data_consegna"])) ? $_POST["data_consegna"] : null;
        $dataOrdine   = date("Y-m-d");

        $chkForn = $conn->prepare("SELECT IDFornitore FROM fornitori WHERE IDFornitore = ?");
        $chkForn->execute([$idFornitore]);
        if($chkForn->rowCount() == 0){
            $_SESSION["gi_errore"] = "Fornitore non valido";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $unitaValide = ["g","kg","pz","l"];
        $ingredientiValidi = [];

        foreach($_POST["ingredienti"] as $idIng){
            $idIngKey = $idIng;
            $idIng    = (int)$idIng;

            $qta   = (int)($_POST["quantita"][$idIngKey] ?? 0);
            $unita = $_POST["unita"][$idIngKey] ?? "";

            if($qta <= 0){
                $_SESSION["gi_errore"] = "Inserisci una quantità maggiore di 0 per tutti gli ingredienti selezionati";
                header("location:gestioneIngredienti.php");
                exit();
            }
            if(!in_array($unita, $unitaValide)){
                $_SESSION["gi_errore"] = "Unità di misura non valida";
                header("location:gestioneIngredienti.php");
                exit();
            }

            $chkIng = $conn->prepare("SELECT IDIngrediente FROM ingredienti WHERE IDIngrediente = ?");
            $chkIng->execute([$idIng]);
            if($chkIng->rowCount() == 1){
                $ingredientiValidi[] = ["id" => $idIng, "qta" => $qta, "unita" => $unita];
            }
        }

        if(empty($ingredientiValidi)){
            $_SESSION["gi_errore"] = "Nessun ingrediente valido selezionato";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $insOrdine = $conn->prepare("INSERT INTO ordinifornitori (DataOrdine, DataConsegna, IDFornitore, Consegnato) VALUES (?, ?, ?, 0)");
        $insOrdine->execute([$dataOrdine, $dataConsegna, $idFornitore]);
        $idOrdine = $conn->lastInsertId();

        $insIng = $conn->prepare("INSERT INTO aux_ingredienti_ordinifornitori (IDIngrediente, IDOrdineFornitore, Quantita, UnitaMisura) VALUES (?, ?, ?, ?)");
        foreach($ingredientiValidi as $ing){
            $insIng->execute([$ing["id"], $idOrdine, $ing["qta"], $ing["unita"]]);
        }

        $_SESSION["gi_ok"] = "Ordine #".str_pad($idOrdine, 6, "0", STR_PAD_LEFT)." creato con successo. Verrà confermato alla consegna";
        header("location:gestioneIngredienti.php");
        exit();
    }

    if($azione === "aggiungiSpecifica"){
        if(!isset($_POST["nome"]) || trim($_POST["nome"]) == ""){
            $_SESSION["gi_errore"] = "Il nome è obbligatorio";
            header("location:gestioneIngredienti.php");
            exit();
        }

        if(!isset($_FILES["immagine"]) || $_FILES["immagine"]["error"] !== UPLOAD_ERR_OK){
            $_SESSION["gi_errore"] = "Errore nel caricamento dell'icona";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $tipiConsentiti = ["image/png", "image/webp"];
        $mimeType = mime_content_type($_FILES["immagine"]["tmp_name"]);
        if(!in_array($mimeType, $tipiConsentiti)){
            $_SESSION["gi_errore"] = "Formato non supportato. Usa png o webp";
            header("location:gestioneIngredienti.php");
            exit();
        }
        if($_FILES["immagine"]["size"] > 512 * 1024){
            $_SESSION["gi_errore"] = "L'icona supera i 512KB";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $chk = $conn->prepare("SELECT IDSpecifica FROM specifiche WHERE Nome = ?");
        $chk->execute([trim($_POST["nome"])]);
        if($chk->rowCount() > 0){
            $_SESSION["gi_errore"] = "Specifica già esistente";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $estensione = $mimeType === "image/png" ? "png" : "webp";
        $ins = $conn->prepare("INSERT INTO specifiche (Nome, Immagine) VALUES (?, '00.png')");
        $ins->execute([trim($_POST["nome"])]);
        $idSpecifica = $conn->lastInsertId();

        $nomeFile    = str_pad($idSpecifica, 2, "0", STR_PAD_LEFT).".$estensione";
        $destinazione = "img/specifiche/".$nomeFile;

        if(!move_uploaded_file($_FILES["immagine"]["tmp_name"], $destinazione)){
            $conn->prepare("DELETE FROM specifiche WHERE IDSpecifica = ?")->execute([$idSpecifica]);
            $_SESSION["gi_errore"] = "Errore nel salvataggio dell'icona. Controlla i permessi della cartella img/specifiche/";
            header("location:gestioneIngredienti.php");
            exit();
        }

        $conn->prepare("UPDATE specifiche SET Immagine = ? WHERE IDSpecifica = ?")->execute([$nomeFile, $idSpecifica]);

        $_SESSION["gi_ok"] = "Specifica \"".trim($_POST["nome"])."\" aggiunta con successo";
        header("location:gestioneIngredienti.php");
        exit();
    }

    if($azione === "eliminaSpecifica"){
        if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
            $_SESSION["gi_errore"] = "ID non valido";
            header("location:gestioneIngredienti.php");
            exit();
        }
        $id = (int)$_GET["id"];

        $chk = $conn->prepare("SELECT COUNT(*) FROM aux_ingredienti_specifiche WHERE IDSpecifica = ?");
        $chk->execute([$id]);
        if($chk->fetchColumn() > 0){
            $_SESSION["gi_errore"] = "Impossibile eliminare: la specifica è collegata a uno o più ingredienti";
            header("location:gestioneIngredienti.php");
            exit();
        }
        $res = $conn->prepare("SELECT Immagine FROM specifiche WHERE IDSpecifica = ?");
        $res->execute([$id]);
        if($res->rowCount() == 0){
            $_SESSION["gi_errore"] = "Specifica non trovata";
            header("location:gestioneIngredienti.php");
            exit();
        }
        $immagine = $res->fetch()["Immagine"];

        $conn->prepare("DELETE FROM specifiche WHERE IDSpecifica = ?")->execute([$id]);

        $percorso = "img/specifiche/".$immagine;
        if(file_exists($percorso)) unlink($percorso);

        $_SESSION["gi_ok"] = "Specifica eliminata con successo";
        header("location:gestioneIngredienti.php");
        exit();
    }
    
    header("location:gestioneIngredienti.php");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>