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
        header("location:gestioneFornitori.php");
        exit();
    }
    $id = (int)$_POST["id"];

    $campi = ["nome","piva","comune","via","civico","cap","username","email"];
    foreach($campi as $campo){
        if(!isset($_POST[$campo]) || trim($_POST[$campo]) == ""){
            $_SESSION["mf_errore"] = "Tutti i campi sono obbligatori";
            header("location:modificaFornitore.php?id=$id");
            exit();
        }
    }

    if(strlen($_POST["piva"]) != 11 || !ctype_digit($_POST["piva"])){
        $_SESSION["mf_errore"] = "La Partita IVA deve essere di 11 cifre numeriche";
        header("location:modificaFornitore.php?id=$id");
        exit();
    }

    $chkPiva = $conn->prepare("SELECT IDFornitore FROM fornitori WHERE PIVA = ? AND IDFornitore != ?");
    $chkPiva->execute([trim($_POST["piva"]), $id]);
    if($chkPiva->rowCount() > 0){
        $_SESSION["mf_errore"] = "Partita IVA già in uso da un altro fornitore";
        header("location:modificaFornitore.php?id=$id");
        exit();
    }

    $chkAccount = $conn->prepare("SELECT IDAccount FROM account WHERE (Username = ? OR Email = ?) AND IDFornitore != ?");
    $chkAccount->execute([trim($_POST["username"]), trim($_POST["email"]), $id]);
    if($chkAccount->rowCount() > 0){
        $_SESSION["mf_errore"] = "Username o email già in uso da un altro account";
        header("location:modificaFornitore.php?id=$id");
        exit();
    }

    $conn->prepare("UPDATE fornitori SET
        Nome = ?, PIVA = ?,
        Indirizzo_Comune = ?, Indirizzo_Via = ?, Indirizzo_Civico = ?, Indirizzo_CAP = ?
        WHERE IDFornitore = ?")
        ->execute([
            trim($_POST["nome"]),
            trim($_POST["piva"]),
            trim($_POST["comune"]),
            trim($_POST["via"]),
            trim($_POST["civico"]),
            trim($_POST["cap"]),
            $id
        ]);

    $conn->prepare("UPDATE account SET Username = ?, Email = ? WHERE IDFornitore = ?")
         ->execute([trim($_POST["username"]), trim($_POST["email"]), $id]);

    $_SESSION["mf_ok"] = "Fornitore aggiornato con successo";
    header("location:modificaFornitore.php?id=$id");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>