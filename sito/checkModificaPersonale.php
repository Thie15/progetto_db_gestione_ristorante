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

    if(!isset($_POST["id"]) || !is_numeric($_POST["id"])){
        header("location:gestionePersonale.php");
        exit();
    }
    $id = (int)$_POST["id"];

    $campi = ["nome","cognome","turno","stipendio","comune","via","civico","cap","username","email"];
    foreach($campi as $campo){
        if(!isset($_POST[$campo]) || trim($_POST[$campo]) == ""){
            $_SESSION["mp_errore"] = "Tutti i campi sono obbligatori";
            header("location:modificaPersonale.php?id=$id");
            exit();
        }
    }

    $chk = $conn->prepare("SELECT IDAccount FROM account WHERE (Username = ? OR Email = ?) AND IDPersonale != ?");
    $chk->execute([$_POST["username"], $_POST["email"], $id]);
    if($chk->rowCount() > 0){
        $_SESSION["mp_errore"] = "Username o email già in uso da un altro account";
        header("location:modificaPersonale.php?id=$id");
        exit();
    }

    $upd = $conn->prepare("UPDATE personale SET
        Nome = ?, Cognome = ?, Turno = ?, Stipendio = ?,
        Indirizzo_Comune = ?, Indirizzo_Via = ?, Indirizzo_Civico = ?, Indirizzo_CAP = ?
        WHERE IDPersonale = ?");
    $upd->execute([
        trim($_POST["nome"]),
        trim($_POST["cognome"]),
        $_POST["turno"],
        (float)$_POST["stipendio"],
        trim($_POST["comune"]),
        trim($_POST["via"]),
        trim($_POST["civico"]),
        trim($_POST["cap"]),
        $id
    ]);

    $conn->prepare("UPDATE account SET Username = ?, Email = ? WHERE IDPersonale = ?")
         ->execute([trim($_POST["username"]), trim($_POST["email"]), $id]);

    $_SESSION["mp_ok"] = "Dipendente aggiornato con successo";
    header("location:modificaPersonale.php?id=$id");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>