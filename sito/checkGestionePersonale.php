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
        $campi = ["nome","cognome","ruolo","turno","stipendio","comune","via","civico","cap","username","email","password"];
        foreach($campi as $campo){
            if(!isset($_POST[$campo]) || trim($_POST[$campo]) == ""){
                $_SESSION["gp_errore"] = "Tutti i campi sono obbligatori";
                header("location:gestionePersonale.php");
                exit();
            }
        }

        if(strlen($_POST["password"]) != 64){
            $_SESSION["gp_errore"] = "Hash password non valido";
            header("location:gestionePersonale.php");
            exit();
        }

        $ruoliValidi = ["nessuno","cuoco","cameriere"];
        if(!in_array($_POST["ruolo"], $ruoliValidi)){
            $_SESSION["gp_errore"] = "Ruolo non valido";
            header("location:gestionePersonale.php");
            exit();
        }

        $chk = $conn->prepare("SELECT IDAccount FROM account WHERE Username = ? OR Email = ?");
        $chk->execute([$_POST["username"], $_POST["email"]]);
        if($chk->rowCount() > 0){
            $_SESSION["gp_errore"] = "Username o email già in uso";
            header("location:gestionePersonale.php");
            exit();
        }

        $insP = $conn->prepare("INSERT INTO personale 
            (Nome, Cognome, Turno, Stipendio, Immagine, Indirizzo_Comune, Indirizzo_Via, Indirizzo_Civico, Indirizzo_CAP)
            VALUES (?, ?, ?, ?, 'default.webp', ?, ?, ?, ?)");
        $insP->execute([
            trim($_POST["nome"]),
            trim($_POST["cognome"]),
            $_POST["turno"],
            (float)$_POST["stipendio"],
            trim($_POST["comune"]),
            trim($_POST["via"]),
            trim($_POST["civico"]),
            trim($_POST["cap"])
        ]);
        $idPersonale = $conn->lastInsertId();

        if($_POST["ruolo"] === "cuoco"){
            $conn->prepare("INSERT INTO cuochi (IDPersonale) VALUES (?)")->execute([$idPersonale]);
        } elseif($_POST["ruolo"] === "cameriere"){
            $conn->prepare("INSERT INTO camerieri (IDPersonale) VALUES (?)")->execute([$idPersonale]);
        }

        $salt = hash('sha256', rand());
        $salt_div = str_split($salt, strlen($salt)/2);
        $passwordFinale = hash('sha256', $salt_div[0].$_POST["password"].$salt_div[1]);

        $insA = $conn->prepare("INSERT INTO account (Username, Email, Password, Salt, IDPersonale) VALUES (?, ?, ?, ?, ?)");
        $insA->execute([
            trim($_POST["username"]),
            trim($_POST["email"]),
            $passwordFinale,
            $salt,
            $idPersonale
        ]);

        $_SESSION["gp_ok"] = "Dipendente ".trim($_POST["nome"])." ".trim($_POST["cognome"])." aggiunto con successo";
        header("location:gestionePersonale.php");
        exit();
    }

    if($azione === "elimina"){
        if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
            $_SESSION["gp_errore"] = "ID non valido";
            header("location:gestionePersonale.php");
            exit();
        }
        $id = (int)$_GET["id"];

        $conn->prepare("DELETE FROM account WHERE IDPersonale = ?")->execute([$id]);
        $conn->prepare("DELETE FROM cuochi WHERE IDPersonale = ?")->execute([$id]);
        $conn->prepare("DELETE FROM camerieri WHERE IDPersonale = ?")->execute([$id]);
        $del = $conn->prepare("DELETE FROM personale WHERE IDPersonale = ?");
        $del->execute([$id]);

        if($del->rowCount() == 1){
            $_SESSION["gp_ok"] = "Dipendente eliminato con successo";
        } else {
            $_SESSION["gp_errore"] = "Dipendente non trovato";
        }
        header("location:gestionePersonale.php");
        exit();
    }

    header("location:gestionePersonale.php");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>