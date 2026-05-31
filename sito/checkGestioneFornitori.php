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
        $campi = ["nome","piva","comune","via","civico","cap","username","email","password"];
        foreach($campi as $campo){
            if(!isset($_POST[$campo]) || trim($_POST[$campo]) == ""){
                $_SESSION["gf_errore"] = "Tutti i campi sono obbligatori";
                header("location:gestioneFornitori.php");
                exit();
            }
        }

        if(strlen($_POST["piva"]) != 11 || !ctype_digit($_POST["piva"])){
            $_SESSION["gf_errore"] = "La Partita IVA deve essere di 11 cifre numeriche";
            header("location:gestioneFornitori.php");
            exit();
        }

        if(strlen($_POST["password"]) != 64){
            $_SESSION["gf_errore"] = "Hash password non valido";
            header("location:gestioneFornitori.php");
            exit();
        }

        $chkPiva = $conn->prepare("SELECT IDFornitore FROM fornitori WHERE PIVA = ?");
        $chkPiva->execute([trim($_POST["piva"])]);
        if($chkPiva->rowCount() > 0){
            $_SESSION["gf_errore"] = "Partita IVA già registrata";
            header("location:gestioneFornitori.php");
            exit();
        }

        $chkAccount = $conn->prepare("SELECT IDAccount FROM account WHERE Username = ? OR Email = ?");
        $chkAccount->execute([trim($_POST["username"]), trim($_POST["email"])]);
        if($chkAccount->rowCount() > 0){
            $_SESSION["gf_errore"] = "Username o email già in uso";
            header("location:gestioneFornitori.php");
            exit();
        }

        $ins = $conn->prepare("INSERT INTO fornitori
            (PIVA, Nome, Indirizzo_Comune, Indirizzo_Via, Indirizzo_Civico, Indirizzo_CAP)
            VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([
            trim($_POST["piva"]),
            trim($_POST["nome"]),
            trim($_POST["comune"]),
            trim($_POST["via"]),
            trim($_POST["civico"]),
            trim($_POST["cap"])
        ]);
        $idFornitore = $conn->lastInsertId();

        $salt = hash('sha256', rand());
        $salt_div = str_split($salt, strlen($salt)/2);
        $passwordFinale = hash('sha256', $salt_div[0].$_POST["password"].$salt_div[1]);

        $insA = $conn->prepare("INSERT INTO account
            (Username, Email, Password, Salt, IDFornitore)
            VALUES (?, ?, ?, ?, ?)");
        $insA->execute([
            trim($_POST["username"]),
            trim($_POST["email"]),
            $passwordFinale,
            $salt,
            $idFornitore
        ]);

        $_SESSION["gf_ok"] = "Fornitore \"".trim($_POST["nome"])."\" aggiunto con successo";
        header("location:gestioneFornitori.php");
        exit();
    }

    if($azione === "elimina"){
        if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
            $_SESSION["gf_errore"] = "ID non valido";
            header("location:gestioneFornitori.php");
            exit();
        }
        $id = (int)$_GET["id"];

        $chkOrdini = $conn->prepare("SELECT COUNT(*) FROM ordinifornitori WHERE IDFornitore = ?");
        $chkOrdini->execute([$id]);
        if($chkOrdini->fetchColumn() > 0){
            $_SESSION["gf_errore"] = "Impossibile eliminare: il fornitore ha ordini collegati";
            header("location:gestioneFornitori.php");
            exit();
        }

        $conn->prepare("DELETE FROM account WHERE IDFornitore = ?")->execute([$id]);
        $del = $conn->prepare("DELETE FROM fornitori WHERE IDFornitore = ?");
        $del->execute([$id]);

        if($del->rowCount() == 1){
            $_SESSION["gf_ok"] = "Fornitore eliminato con successo";
        } else {
            $_SESSION["gf_errore"] = "Fornitore non trovato";
        }
        header("location:gestioneFornitori.php");
        exit();
    }

    header("location:gestioneFornitori.php");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>